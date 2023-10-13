<?php 
    declare(strict_types = 1);

    namespace App;

    class Student extends User
    {
        private string $index_number;
        private int $level;
        private int $program_id;
        
        public function __construct(Database $database, 
            int $user_id = 0, string $lname = '', 
            string $oname = '', string $username = '', int $user_role = 3,
            string $index_number = '', int $level = 0, int $program_id = 0){
                parent::__construct($database, $user_id, $lname, $oname, $username, $user_role);
                $this->index_number = $index_number;
                $this->level = $level;
                $this->program_id = $program_id;

                $this->set_defaults();
        }

        protected function set_defaults() :void{
            //attributes of the class
            self::$attributes = array_merge(parent::$attributes, [
                "index_number" => "string", "user_id" => "int",
                "level" => "int", "program_id" => "int"
            ]);

            //table for fetching data
            $this->table = [
                [
                    "join" => "users students", 
                    "alias" => "u s", 
                    "on" => "id user_id"
                ]
            ];
        }

        public function getIndexNumber() :string{
            return $this->index_number;
        }

        public function update(array $details) : string|bool{
            if(!empty($details["index_number"])){
                if(($response = self::find($details["id"])) !== false){
                    //store student details
                    $student = ["index_number" => $details["index_number"], "user_id" => $details["id"]];

                    $response = static::$connect->update($response->data(), $student, $this->class_table, ["index_number","user_id"], "AND");

                    if($response === true){
                        //remove index_number from list
                        unset($details["index_number"]);

                        //update users table
                        $this->class_table = "users";
                        $response = parent::update($details);
                    }
                }else{
                    $response = "Student provided is not found";
                }
            }else{
                $response = "No index number found";
            }

            $this->resetClassTable();

            return $response;
        }

        private function resetClassTable(){
            $this->class_table = "students";
        }

        public function login() :string|array|bool{
            $response = false;
            try {
                list("index_number" => $index_number, "password" => $password) = $_POST;

                //search the index number
                $tables = $this->table;
                $column = "u.username";
                $where = "s.index_number='$index_number'";
                $no_result = "Student with index number '$index_number' not found";

                $found_index = static::$connect->fetch($column,$tables,$where, no_results:$no_result);
                
                if(is_array($found_index)){
                    //store index number
                    $this->index_number = $index_number;

                    //pass username to parent to login
                    $_POST["username"] = $found_index[0]["username"];
                    $response = parent::login();
                }elseif($found_index !== false){
                    //provide error response string
                    $response = $found_index;
                }else{
                    //false response returned as a result of an error
                    $response = false;
                }
            } catch (\Throwable $th) {
                $response = $th->getMessage();
            }

            return $response;
        }

        public function data() :string|array{
            $response = parent::data();

            //add index number to response data
            if(is_array($response)){
                $response["index_number"] = $this->index_number;
                $response["level"] = $this->level;
                $response["program_id"] = $this->program_id;
            }

            return $response;
        }

        /**
         * This function is used to find the user's program
         */
        public function program() :string{
            $response = "Program not found";

            if($this->program_id > 0){
                $response = Program::find($this->program_id);

                if($response){
                    $response = $response->data()["id"];
                }
            }

            return $response;
        }

        public static function find(int|string $user_id, $table = []) :static|false{
            $table = [
                "columns" => "u.*, s.index_number, s.level, s.program_id",
                "tables" => [
                    [
                        "join" => "users students", 
                        "alias" => "u s", 
                        "on" => "id user_id"
                    ]
                ],
                "where" => "u.id=$user_id OR s.index_number='$user_id'"
            ];

            $response = parent::find($user_id, $table);

            return $response;
        }
        
        public function create(array $details) :bool|string{
            $response = true;

            //grab or create index number
            $index_number = $this->setDefault($details, "index_number", $this->createIndexNumber());

            //remove index number from 
            if(isset($details["index_number"])){
                unset($details["index_number"]);
            }

            //check for level and program then remove
            list("level"=>$level, "program_id"=>$program_id) = $this->removeKeys($details, ["level","program_id"]);

            //check the program
            if($this->checkProgram((int) $program_id)){
                //username should be the specified username or the index number
                $details["username"] = $this->setDefault($details, "username", $index_number);

                //parse user info to users table
                $response = parent::create($details);

                //parse user into students table
                if($response === true){
                    $student_data = [
                        "user_id" => $this->user_id,
                        "index_number" => $index_number,
                        "program_id" => $program_id,
                        "level" => $level
                    ];

                    $response = static::$connect->insert("students", $student_data);
                }
            }else{
                $response = "Program was not found or is not defined";
                static::$connect->setStatus($response, true);
            }

            return $response;
        }

        private function checkProgram(int $program_id){
            $response = (bool) Program::find($program_id);

            if(!$response){
                static::$connect->setStatus("Program '$program_id' does not exist", true);
            }

            return $response;
        }

        public function delete(string|int $user_id) :bool{
            //get user id
            $user = static::$connect->fetch(["user_id"], $this->class_table, ["index_number='$user_id'", "user_id=$user_id"], "OR")[0]["user_id"] ?? false;
            
            if(ctype_digit($user)){
                static::$connect->delete("students","user_id=$user");
                $response = parent::delete($user);
            }else{
                $response = false;
                static::$connect->setStatus("Student '$user_id' could not be deleted", true);
            }

            return $response;
        }

        public function createIndexNumber(){
            $department_id = "08";
            $indexNumber = "03" . date("y") . $department_id;
            $unique = rand(1,999);
            $indexNumber .= str_pad((string) $unique, 4, "0", STR_PAD_LEFT);

            return $indexNumber;
        }

        public function all() :string|array|bool{
            $column = "id, index_number, lname, oname, username"; 
            $table = [
                [
                    "join" => "users students", 
                    "alias" => "u s", 
                    "on" => "id user_id"
                ]
            ]; 
            $where = "user_role={$this->user_role}";

            $response = static::$connect->fetch($column, $table, $where);

            return $response;
        }

        protected function validate(array $data, string $mode) :bool|string{
            $response = true;
            $student_check = [
                "index_number" => ["index number","string"],
                "program_id" => ["program", "int"],
                "level" => ["program level", "int"]
            ];

            //check if there are valid data
            if($this->class_table == "student"){
                $response = $this->check($data, $student_check);
            }

            //validate other keys
            if($response === true){
                $response = parent::validate($data, $mode);
            }

            static::$connect->setStatus($response); $response = false;

            return $response;
        }
    }
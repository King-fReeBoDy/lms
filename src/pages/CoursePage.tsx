import { GoVideo } from "react-icons/go";
import { IoIosArrowBack, IoIosArrowForward } from "react-icons/io";
import { Link } from "react-router-dom";

const CoursePage = () => {
  return (
    <section className=" lg:block lg:px-5 lg:py-3 ">
      <div className="flex justify-between items-center">
        <div className="flex items-center space-x-5">
          <div className="flex items-center">
            <i className="p-2 hover:bg-slate-200 rounded-full transition-all duration-300">
              <IoIosArrowBack />
            </i>
            <i className="p-2 hover:bg-slate-200 rounded-full transition-all duration-300">
              <IoIosArrowForward />
            </i>
          </div>
          <p className="hidden lg:block text-xs">
            Dashboard / <span className="font-bold">Coruse-page</span>
          </p>
        </div>
        <div className="hidden lg:flex items-center space-x-5">
          <i>///</i>
          <i>///</i>
        </div>
      </div>

      <section className="py-5 mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center">
        <div className="group w-[90%] lg:w-52 mx-auto lg:mx-0 border rounded-lg border-slate-300 cursor-pointer">
          <Link to="coursedetails">
            <div className="flex justify-center items-center h-36">
              <i>
                <GoVideo className="text-3xl group-hover:scale-110 transition-all duration-300" />
              </i>
            </div>
            <h3 className=" text-black lg:w-52 truncate font-bold py-2 px-5 group-hover:text-blue-700">
              How to query database <br />
              <span className="font-normal text-slate-500 text-sm">DBMS</span>
            </h3>
          </Link>
        </div>
      </section>
    </section>
  );
};

export default CoursePage;

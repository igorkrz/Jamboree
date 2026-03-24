import React from "react";
import { Link } from "react-router-dom";
import { ArrowLeftIcon, HomeIcon } from "@heroicons/react/24/outline";

export default function ResourceNotAvailable() {
    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 transition-colors duration-200">
            <div className="sm:mx-auto sm:w-full sm:max-w-md text-center">
                <div className="inline-flex items-center justify-center p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl mb-6">
                    <span className="text-4xl font-bold text-indigo-600 dark:text-indigo-400">404</span>
                </div>
                
                <h2 className="mt-2 text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight sm:text-5xl">
                    Page not found
                </h2>
                
                <p className="mt-4 text-base text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                    Sorry, we couldn’t find the page you’re looking for. It might have been moved or deleted.
                </p>

                <div className="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <button 
                        onClick={() => window.history.back()}
                        className="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-700 shadow-sm text-base font-medium rounded-xl text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                    >
                        <ArrowLeftIcon className="mr-2 h-5 w-5" />
                        Go back
                    </button>
                    
                    <Link
                        to="/"
                        className="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                    >
                        <HomeIcon className="mr-2 h-5 w-5" />
                        Back to home
                    </Link>
                </div>
            </div>

            <div className="mt-12 text-center">
                <p className="text-sm text-gray-400 dark:text-gray-600 uppercase tracking-widest font-semibold">
                    Jamboree • Events Platform
                </p>
            </div>
        </div>
    );
}

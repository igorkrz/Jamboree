import React from "react";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/react/24/outline";

export default function Pagination({currentPage, firstPage, lastPage, totalItems, itemsPerPage, onPageChange}) {
    const handlePageChange = (page) => {
        if (page >= firstPage && page <= lastPage) {
            onPageChange(page);
        }
    };

    const getPaginationRange = () => {
        const range = [];
        const maxVisiblePages = 6;

        if (lastPage <= maxVisiblePages) {
            for (let i = 1; i <= lastPage; i++) {
                range.push(i);
            }

            console.log(range, currentPage);

            return range;
        }

        range.push(firstPage);

        currentPage > 4 && range.push("...");
        currentPage > 3 && range.push(currentPage - 2)
        currentPage > 2 && range.push(currentPage - 1)
        currentPage > 1 && range.push(currentPage);
        lastPage - currentPage > 1 && range.push(currentPage + 1)
        lastPage - currentPage > 2 && range.push(currentPage + 2)
        lastPage - currentPage > 3 && range.push("...");
        currentPage !== lastPage && range.push(lastPage);

        return range;
    };

    return (
        <div className="mt-8 flex items-center justify-between border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-4 sm:px-6 rounded-2xl shadow-sm transition-colors duration-300">
            <div className="flex flex-1 justify-between sm:hidden">
                <button
                    onClick={() => handlePageChange(currentPage - 1)}
                    disabled={currentPage === 1}
                    className="relative inline-flex items-center rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 transition-colors"
                >
                    Previous
                </button>
                <button
                    onClick={() => handlePageChange(currentPage + 1)}
                    disabled={currentPage === lastPage}
                    className="relative ml-3 inline-flex items-center rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 transition-colors"
                >
                    Next
                </button>
            </div>

            <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p className="text-sm text-gray-700 dark:text-gray-400">
                        Showing <span className="font-semibold text-gray-900 dark:text-gray-100">{1 + itemsPerPage * (currentPage - 1)} </span>
                        to <span className="font-semibold text-gray-900 dark:text-gray-100">{totalItems < (itemsPerPage * currentPage) ? totalItems : (itemsPerPage * currentPage)} </span>
                        of{' '}<span className="font-semibold text-gray-900 dark:text-gray-100">{totalItems}</span> results
                    </p>
                </div>
                <div>
                    <nav aria-label="Pagination" className="isolate inline-flex -space-x-px rounded-xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-800">
                        <button
                            onClick={() => handlePageChange(currentPage - 1)}
                            disabled={currentPage === 1}
                            className="relative inline-flex items-center px-3 py-2 text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0 disabled:opacity-50 transition-colors"
                        >
                            <span className="sr-only">Previous</span>
                            <ChevronLeftIcon className="h-5 w-5" aria-hidden="true" />
                        </button>

                        {getPaginationRange().map((page, index) => (
                            <button
                                key={index}
                                onClick={() => typeof page === "number" && handlePageChange(page)}
                                className={`relative inline-flex items-center px-4 py-2 text-sm font-semibold transition-colors ${
                                    page === Number(currentPage)
                                        ? "z-10 bg-gray-900 dark:bg-indigo-600 text-white"
                                        : "text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border-x border-gray-200 dark:border-gray-800 first:border-l-0 last:border-r-0"
                                } focus:z-20 focus:outline-offset-0`}
                                disabled={typeof page !== "number"}
                            >
                                {page}
                            </button>
                        ))}

                        <button
                            onClick={() => handlePageChange(currentPage + 1)}
                            disabled={Number(currentPage) === Number(lastPage)}
                            className="relative inline-flex items-center px-3 py-2 text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-20 focus:outline-offset-0 disabled:opacity-50 transition-colors"
                        >
                            <span className="sr-only">Next</span>
                            <ChevronRightIcon className="h-5 w-5" aria-hidden="true" />
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    );
}

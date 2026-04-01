import React, { useEffect, useState } from "react";
import useAxios from "../helpers/useAxios.jsx";
import { FunnelIcon, ArrowsUpDownIcon } from "@heroicons/react/24/outline/index.js";

export default function EventControls({ 
    selectedProvider, 
    handleProviderChange, 
    selectedOrder, 
    handleOrderChange, 
    totalItems,
    itemLabel = "Events"
}) {
    const [providers, setProviders] = useState([]);

    useEffect(() => {
        useAxios.get('/api/event_providers')
            .then(response => {
                setProviders(response.data['member']);
            })
            .catch(error => {
                console.error("Error fetching providers", error);
            });
    }, []);

    return (
        <div className="mt-10 flex flex-col md:flex-row gap-4 items-center">
            <div className="relative w-full md:w-72 group">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <FunnelIcon className="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" />
                </div>
                <select
                    value={selectedProvider}
                    onChange={handleProviderChange}
                    className="block w-full pl-11 pr-10 py-3.5 bg-gray-50 dark:bg-gray-800 border-0 text-gray-900 dark:text-gray-100 text-sm font-medium rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-500 transition-all appearance-none cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-750"
                >
                    <option value="">All Providers</option>
                    {providers.map((provider) => (
                        <option key={provider.id} value={provider.name}>
                            {provider.name}
                        </option>
                    ))}
                </select>
                <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                </div>
            </div>

            <div className="relative w-full md:w-72 group">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <ArrowsUpDownIcon className="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" />
                </div>
                <select
                    value={selectedOrder}
                    onChange={handleOrderChange}
                    className="block w-full pl-11 pr-10 py-3.5 bg-gray-50 dark:bg-gray-800 border-0 text-gray-900 dark:text-gray-100 text-sm font-medium rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-500 transition-all appearance-none cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-750"
                >
                    <optgroup label="Date">
                        <option value="holdingDate:asc">Date (Nearest first)</option>
                        <option value="holdingDate:desc">Date (Furthest first)</option>
                    </optgroup>
                    <optgroup label="Name">
                        <option value="name:asc">Name (A-Z)</option>
                        <option value="name:desc">Name (Z-A)</option>
                    </optgroup>
                </select>
                <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                </div>
            </div>

            <div className="hidden md:block ml-auto">
                <span className="inline-flex items-center px-4 py-2 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-bold">
                    {totalItems} {totalItems === 1 ? itemLabel.replace(/s$/, '') : itemLabel} Available
                </span>
            </div>
        </div>
    );
}

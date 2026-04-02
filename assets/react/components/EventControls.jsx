import React, { useEffect, useState, useRef } from "react";
import useAxios from "../helpers/useAxios.jsx";
import { FunnelIcon, ArrowsUpDownIcon, MagnifyingGlassIcon, XMarkIcon } from "@heroicons/react/24/outline/index.js";

export default function EventControls({ 
    selectedProvider, 
    handleProviderChange, 
    selectedTag,
    handleTagChange,
    selectedOrder, 
    handleOrderChange, 
    totalItems,
    itemLabel = "Events",
    onlyUserTags = false
}) {
    const [providers, setProviders] = useState([]);
    const [tags, setTags] = useState([]);
    const [isTagDropdownOpen, setIsTagDropdownOpen] = useState(false);
    const [tagSearch, setTagSearch] = useState("");
    const dropdownRef = useRef(null);

    const [isProviderDropdownOpen, setIsProviderDropdownOpen] = useState(false);
    const [isOrderDropdownOpen, setIsOrderDropdownOpen] = useState(false);
    const providerDropdownRef = useRef(null);
    const orderDropdownRef = useRef(null);

    useEffect(() => {
        useAxios.get('/api/event_providers')
            .then(response => {
                setProviders(response.data['member']);
            })
            .catch(error => {
                console.error("Error fetching providers", error);
            });

        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsTagDropdownOpen(false);
            }
            if (providerDropdownRef.current && !providerDropdownRef.current.contains(event.target)) {
                setIsProviderDropdownOpen(false);
            }
            if (orderDropdownRef.current && !orderDropdownRef.current.contains(event.target)) {
                setIsOrderDropdownOpen(false);
            }
        };

        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    useEffect(() => {
        const fetchTags = async () => {
            if (tagSearch.length > 0 && tagSearch.length < 3) {
                return;
            }

            try {
                const params = { name: tagSearch };
                if (onlyUserTags) {
                    params.usedByMe = true;
                }
                const response = await useAxios.get('/api/tags', {
                    params: params
                });
                setTags(response.data['member']);
            } catch (error) {
                console.error("Error fetching tags", error);
            }
        };

        const timeoutId = setTimeout(() => {
            fetchTags();
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [tagSearch, onlyUserTags]);

    const filteredTags = tags;

    return (
        <div className="mt-10 flex flex-col lg:flex-row gap-4 items-center">
            {/* Providers Dropdown */}
            <div className="relative w-full lg:w-64 group" ref={providerDropdownRef}>
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <FunnelIcon className="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" />
                </div>
                <button
                    onClick={() => setIsProviderDropdownOpen(!isProviderDropdownOpen)}
                    className="block w-full pl-11 pr-10 py-3.5 bg-gray-50 dark:bg-gray-800 border-0 text-left text-gray-900 dark:text-gray-100 text-sm font-medium rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-500 transition-all hover:bg-gray-100 dark:hover:bg-gray-750"
                >
                    <span className={`truncate ${selectedProvider ? 'text-indigo-600 dark:text-indigo-400 font-bold' : ''}`}>
                        {selectedProvider || "All Providers"}
                    </span>
                </button>
                <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg className={`h-5 w-5 text-gray-400 transition-transform ${isProviderDropdownOpen ? 'rotate-180' : ''}`} viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                </div>

                {isProviderDropdownOpen && (
                    <div className="absolute z-50 mt-2 w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-black ring-opacity-5 overflow-hidden">
                        <div className="max-h-60 overflow-y-auto py-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                            <button
                                onClick={() => {
                                    handleProviderChange({ target: { value: "" } });
                                    setIsProviderDropdownOpen(false);
                                }}
                                className={`block w-full text-left px-4 py-2 text-sm transition-colors ${!selectedProvider ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 dark:hover:text-indigo-400'}`}
                            >
                                All Providers
                            </button>
                            {providers.map((provider) => (
                                <button
                                    key={provider.id}
                                    onClick={() => {
                                        handleProviderChange({ target: { value: provider.name } });
                                        setIsProviderDropdownOpen(false);
                                    }}
                                    className={`block w-full text-left px-4 py-2 text-sm transition-colors ${selectedProvider === provider.name ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 dark:hover:text-indigo-400'}`}
                                >
                                    {provider.name}
                                </button>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Genres Dropdown */}
            <div className="relative w-full lg:w-64 group" ref={dropdownRef}>
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <FunnelIcon className="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" />
                </div>
                <button
                    onClick={() => setIsTagDropdownOpen(!isTagDropdownOpen)}
                    className="block w-full pl-11 pr-10 py-3.5 bg-gray-50 dark:bg-gray-800 border-0 text-left text-gray-900 dark:text-gray-100 text-sm font-medium rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-500 transition-all hover:bg-gray-100 dark:hover:bg-gray-750"
                >
                    <span className={`truncate ${selectedTag ? 'text-indigo-600 dark:text-indigo-400 font-bold' : ''}`}>
                        {selectedTag || "All Genres"}
                    </span>
                </button>
                <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg className={`h-5 w-5 text-gray-400 transition-transform ${isTagDropdownOpen ? 'rotate-180' : ''}`} viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                </div>

                {isTagDropdownOpen && (
                    <div className="absolute z-50 mt-2 w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-black ring-opacity-5 overflow-hidden">
                        <div className="p-2 border-b border-gray-100 dark:border-gray-700">
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                    <MagnifyingGlassIcon className="h-4 w-4 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    className="block w-full pl-8 pr-8 py-2 bg-gray-50 dark:bg-gray-900 border-0 text-gray-900 dark:text-gray-100 text-xs rounded-lg focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Search genres..."
                                    value={tagSearch}
                                    onChange={(e) => setTagSearch(e.target.value)}
                                    autoFocus
                                />
                                {tagSearch && (
                                    <button 
                                        onClick={() => setTagSearch("")}
                                        className="absolute inset-y-0 right-0 pr-2.5 flex items-center"
                                    >
                                        <XMarkIcon className="h-4 w-4 text-gray-400 hover:text-gray-600" />
                                    </button>
                                )}
                            </div>
                        </div>
                        <div className="max-h-60 overflow-y-auto py-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                            <button
                                onClick={() => {
                                    handleTagChange({ target: { value: "" } });
                                    setIsTagDropdownOpen(false);
                                    setTagSearch("");
                                }}
                                className={`block w-full text-left px-4 py-2 text-sm transition-colors ${!selectedTag ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 dark:hover:text-indigo-400'}`}
                            >
                                All Genres
                            </button>
                            {filteredTags.length > 0 ? (
                                filteredTags.map((tag) => (
                                    <button
                                        key={tag.id}
                                        onClick={() => {
                                            handleTagChange({ target: { value: tag.name } });
                                            setIsTagDropdownOpen(false);
                                            setTagSearch("");
                                        }}
                                        className={`block w-full text-left px-4 py-2 text-sm transition-colors ${selectedTag === tag.name ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 dark:hover:text-indigo-400'}`}
                                    >
                                        <div className="flex justify-between items-center">
                                            <span className="truncate">{tag.name}</span>
                                            {tag.events && tag.events.length > 0 && (
                                                <span className="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-500 px-1.5 py-0.5 rounded-full ml-2">
                                                    {tag.events.length}
                                                </span>
                                            )}
                                        </div>
                                    </button>
                                ))
                            ) : (
                                <div className="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center italic">
                                    No genres found
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* Sort Dropdown */}
            <div className="relative w-full lg:w-64 group" ref={orderDropdownRef}>
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <ArrowsUpDownIcon className="h-5 w-5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" />
                </div>
                <button
                    onClick={() => setIsOrderDropdownOpen(!isOrderDropdownOpen)}
                    className="block w-full pl-11 pr-10 py-3.5 bg-gray-50 dark:bg-gray-800 border-0 text-left text-gray-900 dark:text-gray-100 text-sm font-medium rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-500 transition-all hover:bg-gray-100 dark:hover:bg-gray-750"
                >
                    <span className={`truncate ${selectedOrder ? 'text-indigo-600 dark:text-indigo-400 font-bold' : ''}`}>
                        {(() => {
                            const options = {
                                "holdingDate:asc": "Date (Nearest first)",
                                "holdingDate:desc": "Date (Furthest first)",
                                "name:asc": "Name (A-Z)",
                                "name:desc": "Name (Z-A)"
                            };
                            return options[selectedOrder] || "Order by";
                        })()}
                    </span>
                </button>
                <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg className={`h-5 w-5 text-gray-400 transition-transform ${isOrderDropdownOpen ? 'rotate-180' : ''}`} viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                    </svg>
                </div>

                {isOrderDropdownOpen && (
                    <div className="absolute z-50 mt-2 w-full bg-white dark:bg-gray-800 rounded-2xl shadow-xl ring-1 ring-black ring-opacity-5 overflow-hidden">
                        <div className="max-h-60 overflow-y-auto py-1 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-700">
                            <div className="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-900/50">Date</div>
                            {[
                                { value: "holdingDate:asc", label: "Date (Nearest first)" },
                                { value: "holdingDate:desc", label: "Date (Furthest first)" }
                            ].map((option) => (
                                <button
                                    key={option.value}
                                    onClick={() => {
                                        handleOrderChange({ target: { value: option.value } });
                                        setIsOrderDropdownOpen(false);
                                    }}
                                    className={`block w-full text-left px-4 py-2 text-sm transition-colors ${selectedOrder === option.value ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 dark:hover:text-indigo-400'}`}
                                >
                                    {option.label}
                                </button>
                            ))}
                            <div className="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider bg-gray-50 dark:bg-gray-900/50">Name</div>
                            {[
                                { value: "name:asc", label: "Name (A-Z)" },
                                { value: "name:desc", label: "Name (Z-A)" }
                            ].map((option) => (
                                <button
                                    key={option.value}
                                    onClick={() => {
                                        handleOrderChange({ target: { value: option.value } });
                                        setIsOrderDropdownOpen(false);
                                    }}
                                    className={`block w-full text-left px-4 py-2 text-sm transition-colors ${selectedOrder === option.value ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold' : 'text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-600 dark:hover:text-indigo-400'}`}
                                >
                                    {option.label}
                                </button>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            <div className="hidden md:block ml-auto">
                <span className="inline-flex items-center px-4 py-2 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-bold">
                    {totalItems} {totalItems === 1 ? itemLabel.replace(/s$/, '') : itemLabel} Available
                </span>
            </div>
        </div>
    );
}

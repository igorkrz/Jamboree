import React, { useEffect, useState } from "react";
import Event from "../components/Event.jsx";
import useAxios from "../helpers/useAxios.jsx";
import Pagination from "../components/Pagination.jsx";
import EventControls from "../components/EventControls.jsx";
import { useSearchParams } from "react-router-dom";
import { FunnelIcon } from "@heroicons/react/24/outline/index.js";

export default function UserEvents() {
    const [searchParams, setSearchParams] = useSearchParams();
    const [events, setEvents] = useState([]);
    const [isLoading, setLoading] = useState(true);
    const [firstPage, setFirstPage] = useState(1);
    const [lastPage, setLastPage] = useState(null);
    const [itemsPerPage, setItemsPerPage] = useState(30);
    const [currentPage, setCurrentPage] = useState(null);
    const [totalItems, setTotalItems] = useState(0);
    const [selectedProvider, setSelectedProvider] = useState('');
    const [selectedTag, setSelectedTag] = useState('');
    const [selectedOrder, setSelectedOrder] = useState('holdingDate:asc');


    const getUserEvents = () => {
        setLoading(true);

        let url = `/api/user_events?page=${currentPage}`;
        if (selectedProvider) {
            url += `&event.provider.name=${selectedProvider}`;
        }

        if (selectedTag) {
            url += `&event.tags.name=${selectedTag}`;
        }

        const [orderField, orderDirection] = selectedOrder.split(':');
        if (orderField === 'holdingDate' || orderField === 'name') {
            url += `&order[event.${orderField}]=${orderDirection}`;
            url += `&order[customEvent.${orderField}]=${orderDirection}`;
        }

        useAxios.get(url)
            .then(response => {
                console.log(response.data);
                const data = response.data;

                if (data['view']) {
                    const firstPageUrl = data['view']['first'] ?? "";
                    const lastPageUrl = data['view']['last'] ?? "";

                    const firstMatch = firstPageUrl.match(/[?&]page=(\d+)/);
                    const lastMatch = lastPageUrl.match(/[?&]page=(\d+)/);

                    setFirstPage(firstMatch ? firstMatch[1] : 1);
                    setLastPage(lastMatch ? lastMatch[1] : 1);
                } else {
                    setFirstPage(1);
                    setLastPage(1);
                }

                const totalItems = data['totalItems'];

                setTotalItems(totalItems);
                setEvents(data['member']);

                setLoading(false);
            })
            .catch(error => {
                console.error(error);
            });
    }

    const handlePageChange = (newPage) => {
        setCurrentPage(newPage);
        const params = new URLSearchParams(searchParams);
        params.set('page', newPage.toString());
        syncUrl(params);
    };

    const handleProviderChange = (event) => {
        const newProviderName = event.target.value;
        setSelectedProvider(newProviderName);
        setCurrentPage(1);
        
        const params = new URLSearchParams(searchParams);
        params.set('page', '1');
        if (newProviderName) {
            params.set('provider.name', newProviderName);
        } else {
            params.delete('provider.name');
        }
        syncUrl(params);
    };

    const handleTagChange = (event) => {
        const newTag = event.target.value;
        setSelectedTag(newTag);
        setCurrentPage(1);

        const params = new URLSearchParams(searchParams);
        params.set('page', '1');
        if (newTag) {
            params.set('tag.name', newTag);
        } else {
            params.delete('tag.name');
        }
        syncUrl(params);
    };

    const handleOrderChange = (event) => {
        const newOrder = event.target.value;
        setSelectedOrder(newOrder);
        setCurrentPage(1);

        const params = new URLSearchParams(searchParams);
        params.set('page', '1');
        params.set('order', newOrder);
        syncUrl(params);
    };

    const syncUrl = (params) => {
        setSearchParams(params);
    };

    useEffect(() => {
        const pageNumber = searchParams.get('page') ? parseInt(searchParams.get('page')) : 1;
        const providerFromUrl = searchParams.get('provider.name') || '';
        const tagFromUrl = searchParams.get('tag.name') || '';
        const orderFromUrl = searchParams.get('order') || 'holdingDate:asc';
        
        if (currentPage === null || currentPage !== pageNumber || selectedProvider !== providerFromUrl || selectedTag !== tagFromUrl || selectedOrder !== orderFromUrl) {
            setCurrentPage(pageNumber);
            setSelectedProvider(providerFromUrl);
            setSelectedTag(tagFromUrl);
            setSelectedOrder(orderFromUrl);
            return;
        }

        getUserEvents();
    }, [currentPage, searchParams]);

    if (isLoading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
            </div>
        )
    }

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
            <div className="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
                <div className="container mx-auto px-4 py-12">
                    <div className="max-w-3xl">
                        <h1 className="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 tracking-tight">
                            My <span className="text-indigo-600 dark:text-indigo-400">Events</span>
                        </h1>
                        <p className="text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                            Your personal collection of events. Keep track of what you're attending and discover your saved favorites.
                        </p>
                    </div>

                    <EventControls 
                        selectedProvider={selectedProvider}
                        handleProviderChange={handleProviderChange}
                        selectedTag={selectedTag}
                        handleTagChange={handleTagChange}
                        selectedOrder={selectedOrder}
                        handleOrderChange={handleOrderChange}
                        totalItems={totalItems}
                        onlyUserTags={true}
                    />
                </div>
            </div>

            <div className="container mx-auto px-4 py-12">
                {events.length > 0 ? (
                    <>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                            {events.map((event) => (
                                <Event
                                    key={event.id}
                                    userEventId={event.id}
                                    event={event.event ?? event.customEvent}
                                    isFavorite={true}
                                    isAuthenticated={true}
                                />
                            ))}
                        </div>

                        <Pagination
                            currentPage={currentPage}
                            firstPage={firstPage}
                            lastPage={lastPage}
                            totalItems={totalItems}
                            itemsPerPage={itemsPerPage}
                            onPageChange={handlePageChange}
                        />
                    </>
                ) : (
                    <div className="flex flex-col items-center justify-center py-20 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm transition-colors duration-300">
                        <div className="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6">
                            <FunnelIcon className="w-10 h-10 text-gray-300 dark:text-gray-600" />
                        </div>
                        <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-2">No events found</h3>
                        <p className="text-gray-500 dark:text-gray-400 max-w-xs text-center">
                            {selectedProvider || selectedTag || selectedOrder !== 'holdingDate:asc' 
                                ? "Try adjusting your filters to find your events." 
                                : "You haven't added any events to your collection yet."}
                        </p>
                        {(selectedProvider || selectedTag || selectedOrder !== 'holdingDate:asc') && (
                            <button 
                                onClick={() => {
                                    setSelectedProvider('');
                                    setSelectedTag('');
                                    setSelectedOrder('holdingDate:asc');
                                    const params = new URLSearchParams();
                                    params.set('page', '1');
                                    syncUrl(params);
                                }}
                                className="mt-8 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-colors"
                            >
                                Clear all filters
                            </button>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

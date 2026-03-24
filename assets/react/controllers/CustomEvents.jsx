import React, { useEffect, useState } from "react";
import Event from "../components/Event.jsx";
import useAxios from "../helpers/useAxios.jsx";
import Pagination from "../components/Pagination.jsx";
import { useNavigate, useSearchParams } from "react-router-dom";

export default function CustomEvents() {
    const navigate = useNavigate();
    const [searchParams, setSearchParams] = useSearchParams();
    const [events, setEvents] = useState([]);
    const [userEvents, setUserEvents] = useState([]);
    const [isLoading, setLoading] = useState(true);
    const [firstPage, setFirstPage] = useState(1);
    const [lastPage, setLastPage] = useState(null);
    const [itemsPerPage, setItemsPerPage] = useState(30);
    const [currentPage, setCurrentPage] = useState(null);
    const [totalItems, setTotalItems] = useState(0);

    window.onpopstate = () => {
        navigate(-1);
    }

    const getCustomEvents = () => {
        setLoading(true);

        useAxios.get(`/api/custom_events?page=${currentPage}`)
            .then(response => {
                console.log(response.data);
                const data = response.data;

                if (data['view']) {
                    const firstPage = data['view']['first'] ?? 1;
                    const lastPage = data['view']['last'] ?? 1;

                    firstPage === 1 ? setFirstPage(1) : setFirstPage(firstPage.match(/\d+$/)[0]);
                    lastPage === 1 ? setLastPage(1) : setLastPage(lastPage.match(/\d+$/)[0]);
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
        navigate(`/custom_events?page=${newPage}`);
    };

    useEffect(() => {
        if (currentPage === null) {
            const pageNumber = searchParams.get('page') ? searchParams.get('page') : 1;
            const navigationPage = currentPage ? currentPage : pageNumber;
            navigate(`/custom_events?page=${navigationPage}`);
            setCurrentPage(navigationPage);
            return;
        }

        getCustomEvents();

        useAxios.get(`/api/user_events`)
            .then(response => {
                console.log(response.data);
                const data = response.data;
                setUserEvents(data['member']);
                setLoading(false);
            })
            .catch(error => {
                console.error(error);
            });
    }, [currentPage]);

    if (isLoading) {
        return (<h1>Loading screen</h1>)
    }

    return (
        <div className="container mx-auto px-4 py-8 min-h-screen">
            <h2 className="text-3xl font-bold mb-8 text-center text-gray-900 dark:text-gray-100">Custom Events</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                {
                    events.map((event) => {
                        console.log("EVENT", event);
                        console.log("USER EVENTS", userEvents)
                        const favoriteEvents = userEvents.filter((userEvent => userEvent.event && event.id === userEvent.event.id));
                        const userEventId = favoriteEvents.length > 0 ? favoriteEvents[0].id : null;

                        return <Event
                            key={event.id}
                            name={event.name}
                            event={event}
                            isFavorite={favoriteEvents.length > 0}
                            userEventId={userEventId}
                        />
                    })
                }
            </div>
            { events.length >0 && totalItems > itemsPerPage && (
                <Pagination
                    currentPage={currentPage}
                    firstPage={firstPage}
                    lastPage={lastPage}
                    totalItems={totalItems}
                    itemsPerPage={itemsPerPage}
                    onPageChange={handlePageChange}
                />
            )}
        </div>
    );
};

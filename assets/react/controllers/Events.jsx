import React, { useEffect, useState } from "react";
import Event from "../components/Event.jsx";
import Pagination from "../components/Pagination.jsx";
import useAxios from "../helpers/useAxios.jsx";
import { useSelector } from "react-redux";
import { useNavigate, useSearchParams } from "react-router-dom";

export default function Events() {
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
    const { isAuthenticated } = useSelector(
        (state) => state.authentication
    );

    window.onpopstate = () => {
        navigate(-1);
    }

    const getEvents = () => {
        setLoading(true);

        useAxios.get(`/api/events?page=${currentPage}&holdingDate[after]=today`)
            .then(response => {
                console.log(response.data);
                const data = response.data;

                if (data['hydra:view']) {
                    const firstPage = data['hydra:view']['hydra:first'] ?? 1;
                    const lastPage = data['hydra:view']['hydra:last'] ?? 1;

                    firstPage === 1 ? setFirstPage(1) : setFirstPage(firstPage.match(/\d+$/)[0]);
                    lastPage === 1 ? setLastPage(1) : setLastPage(lastPage.match(/\d+$/)[0]);
                }

                const totalItems = data['hydra:totalItems'];

                setTotalItems(totalItems);
                setEvents(data['hydra:member']);

                setLoading(false);
            })
            .catch(error => {
                console.error(error);
            });
    }

    const handlePageChange = (newPage) => {
        setCurrentPage(newPage);
        navigate(`/events?page=${newPage}`);
    };

    useEffect(() => {
        if (currentPage === null) {
            const pageNumber = searchParams.get('page') ? searchParams.get('page') : 1;
            const navigationPage = currentPage ? currentPage : pageNumber;
            navigate(`/events?page=${navigationPage}`);
            setCurrentPage(navigationPage);
            return;
        }

        getEvents();

        if (isAuthenticated) {
            useAxios.get(`/api/user_events`)
                .then(response => {
                    console.log(response.data);
                    const data = response.data;
                    setUserEvents(data['hydra:member']);
                })
                .catch(error => {
                    console.error(error);
                });
        }
    }, [currentPage]);

    if (isLoading) {
        return (<h1>Loading screen</h1>)
    }

    return (
        <div className="container mx-auto px-4 py-8">
            <h2 className="text-3xl font-bold mb-8 text-center">Upcoming Events</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                {
                    events.map((event) => {
                        let favoriteEvents = [];

                        if (isAuthenticated) {
                            favoriteEvents = userEvents.filter((userEvent => userEvent.event && event.id === userEvent.event.id));
                        }

                        return <Event
                            key={event.id}
                            name={event.name}
                            event={event}
                            isFavorite={favoriteEvents.length > 0}
                            isAuthenticated={isAuthenticated}
                        />
                    })
                }
            </div>
            { events.length > 0 && totalItems > itemsPerPage && (
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

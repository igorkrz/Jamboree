import React, { useEffect, useRef, useState } from "react";
import Event from "../components/Event.jsx";
import useAxios from "../helpers/useAxios.jsx";
import Pagination from "../components/Pagination.jsx";
import { useNavigate, useSearchParams } from "react-router-dom";

export default function UserEvents() {
    const navigate = useNavigate();
    const [searchParams, setSearchParams] = useSearchParams();
    const [events, setEvents] = useState([]);
    const [isLoading, setLoading] = useState(true);
    const [firstPage, setFirstPage] = useState(1);
    const [lastPage, setLastPage] = useState(null);
    const [itemsPerPage, setItemsPerPage] = useState(30);
    const [currentPage, setCurrentPage] = useState(null);
    const [totalItems, setTotalItems] = useState(0);

    window.onpopstate = () => {
        navigate(-1);
    }

    const getUserEvents = () => {
        setLoading(true);

        useAxios.get(`/api/user_events?page=${currentPage}`)
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
        navigate(`/user_events?page=${newPage}`);
    };

    useEffect(() => {
        if (currentPage === null) {
            const pageNumber = searchParams.get('page') ? searchParams.get('page') : 1;
            const navigationPage = currentPage ? currentPage : pageNumber;
            navigate(`/user_events?page=${navigationPage}`);
            setCurrentPage(navigationPage);
            return;
        }

        getUserEvents();
    }, [currentPage]);

    if (isLoading) {
        return (<h1>Loading screen</h1>)
    }

    if (events.length === 0) {
        return (<h1>No events</h1>)
    }

    return (
        <div className="container mx-auto px-4 py-8">
            <h2 className="text-3xl font-bold mb-8 text-center">My Events</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                {events.map((event) => (
                    <Event
                        key={event.id}
                        name={event.name}
                        userEventId={event.id}
                        event={event.event}
                        isFavorite={true}
                    />
                ))}
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

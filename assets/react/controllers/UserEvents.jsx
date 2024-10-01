import React, { useEffect, useRef, useState } from "react";
import Event from "../components/Event.jsx";
import useAxios from "../helpers/useAxios.jsx";

export default function UserEvents() {
    const [events, setEvents] = useState(null);
    const [isLoading, setLoading] = useState(true);

    useEffect(() => {
        useAxios.get(`/api/user_events`)
            .then(response => {
                console.log(response.data);
                setEvents(response.data)
                setLoading(false);
            })
            .catch(error => {
                console.error(error);
            });
    }, []);

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
        </div>
    );
};

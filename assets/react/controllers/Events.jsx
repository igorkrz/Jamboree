import React, { useEffect, useState } from "react";
import Event from "../components/Event.jsx";
import useAxios from "../helpers/useAxios.jsx";

export default function Events() {
    const [events, setEvents] = useState([]);
    const [userEvents, setUserEvents] = useState([]);

    useEffect(() => {
        useAxios.get(`/api/events`)
            .then(response => {
                console.log(response.data);
                setEvents(response.data)
            })
            .catch(error => {
                console.error(error);
            });

        useAxios.get(`/api/user_events`)
            .then(response => {
                console.log(response.data);
                setUserEvents(response.data)
            })
            .catch(error => {
                console.error(error);
            });
    }, []);

    return (
        <div className="container mx-auto px-4 py-8">
            <h2 className="text-3xl font-bold mb-8 text-center">Upcoming Events</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                {
                    events.map((event) => {
                        let test = userEvents.filter((userEvent => event.id === userEvent.event.id));
                        console.log(test);

                        return <Event
                            key={event.id}
                            name={event.name}
                            event={event}
                            isFavorite={test.length > 0}
                        />
                    })
                }
            </div>
        </div>
    );
};

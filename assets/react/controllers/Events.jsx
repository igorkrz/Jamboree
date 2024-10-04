import React, { useEffect, useState } from "react";
import Event from "../components/Event.jsx";
import useAxios from "../helpers/useAxios.jsx";
import

export default function Events() {
    const [events, setEvents] = useState([]);
    const [userEvents, setUserEvents] = useState([]);
    const [isLoading, setLoading] = useState(true);

    useEffect(() => {
        useAxios.get(`/api/events`)
            .then(response => {
                console.log(response.data);
                setEvents(response.data);
            })
            .catch(error => {
                console.error(error);
            });
        useAxios.get(`/api/user_events`)
            .then(response => {
                console.log(response.data);
                setUserEvents(response.data);
                setLoading(false);
            })
            .catch(error => {
                console.error(error);
            });
    }, []);

    if (isLoading) {
        return (<h1>Loading screen</h1>)
    }

    return (
        <div className="container mx-auto px-4 py-8">
            <h2 className="text-3xl font-bold mb-8 text-center">Upcoming Events</h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                {
                    events.map((event) => {
                        console.log("EVENT", event);
                        console.log("USER EVENTS", userEvents);
                        // let test = userEvents.filter(function (userEvent) {
                        //     console.log("USER EVENT", userEvent);
                        //     if (userEvent.event) {
                        //         console.log("USER EVENT YES", userEvent);
                        //         return 1;
                        //     }
                        //
                        //     return 0;
                        // });
                        let test = userEvents.filter((userEvent => userEvent.event && event.id === userEvent.event.id));
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

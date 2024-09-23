import React, { useEffect, useState } from "react";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import useAxios from "../helpers/useAxios.jsx";

export default function Calendar() {
    const [events, setEvents] = useState([]);
    let start = new Date();
    let end = new Date(start.getDate() + 10);
    start = start.toLocaleDateString();
    end = end.toLocaleDateString();

    useEffect(() => {
       useAxios.get(`/api/calendar`, {
           params: {
               start,
               end,
           }
       })
           .then(response => setEvents(JSON.parse(response.data)))
           .catch(error => console.error(error));
    }, []);

    return (
        <FullCalendar
            plugins={[ dayGridPlugin ]}
            initialView="dayGridMonth"
            events={events}
        />
    )
};

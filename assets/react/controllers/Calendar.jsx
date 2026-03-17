import React, { useEffect, useState } from "react";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import useAxios from "../helpers/useAxios.jsx";

export default function Calendar() {
    const [events, setEvents] = useState([]);
    let start = new Date();
    start = start.toLocaleDateString();

    useEffect(() => {
       useAxios.get(`/api/calendar`, {
           params: {
               start,
           }
       })
           .then(response => {
               console.log(response.data);
               setEvents(response.data['member'] || response.data);
           })
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

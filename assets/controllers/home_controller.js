import { Controller } from '@hotwired/stimulus';
import 'fullcalendar';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import axios from 'axios';

export default class extends Controller {
    static targets = ['active'];

    connect() {
        super.connect();
        const calendarEl = document.getElementById('calendar-holder');

        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin],
            initialView: 'dayGridMonth',
            editable: true,
            eventSources: [
                {
                    url: '/fc-load-events',
                    method: 'POST',
                    extraParams: {
                        filters: JSON.stringify({})
                    },
                    failure: () => {
                        alert('There was an error while fetching FullCalendar!');
                    },
                },
            ],
            headerToolbar: {
                start: 'prev,next today',
                center: 'title',
                end: 'dayGridMonth'
            },
            timeZone: 'UTC',
        });

        console.log(calendar);
        calendar.render();
    }

    export(event) {
        console.log('export');
        const eventToAdd = event.currentTarget;
        const path = eventToAdd.dataset.path;

        axios.post(path);
    }
}

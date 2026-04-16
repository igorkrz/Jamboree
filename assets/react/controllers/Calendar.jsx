import React, { useEffect, useState, Fragment, useRef } from "react";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import listPlugin from "@fullcalendar/list";
import useAxios from "../helpers/useAxios.jsx";
import { useFlash } from "../context/FlashContext.jsx";
import { useSelector } from "react-redux";
import { Menu, Transition, MenuButton, MenuItem, MenuItems } from "@headlessui/react";
import {
    ArrowDownTrayIcon,
    ArrowPathIcon,
    CalendarIcon, 
    ChevronDownIcon,
    TrashIcon,
    PlusIcon,
} from "@heroicons/react/24/outline";

export default function Calendar() {
    const { isAuthenticated: isLoggedIn, user } = useSelector((state) => state.authentication);
    const { showFlash } = useFlash();
    const [events, setEvents] = useState([]);
    const [isInitialLoading, setInitialLoading] = useState(true);
    const [isFetchingEvents, setFetchingEvents] = useState(false);
    const [isExporting, setExporting] = useState(false);
    const [currentDate, setCurrentDate] = useState(localStorage.getItem('calendar_date') || new Date().toISOString());
    const [view, setView] = useState(() => {
        const savedView = localStorage.getItem('calendar_view');
        const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;
        
        if (isMobile) return 'listMonth';
        if (savedView) return savedView;
        return 'dayGridMonth';
    });

    const [isMobile, setIsMobile] = useState(typeof window !== 'undefined' && window.innerWidth < 768);

    const calendarRef = useRef(null);

    useEffect(() => {
        const handleResize = () => {
            const mobile = window.innerWidth < 768;
            setIsMobile(mobile);

            if (mobile) {
                if (calendarRef.current?.getApi().view.type !== 'listMonth') {
                    setView('listMonth');
                    calendarRef.current?.getApi().changeView('listMonth');
                }
            }
            
            calendarRef.current?.getApi().updateSize();
        };

        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    const formattedStart = new Date(currentDate).toISOString().split('T')[0];

    useEffect(() => {
        setFetchingEvents(true);
        useAxios.get(`/api/calendar`, {
            params: {
                start: formattedStart,
            }
        })
            .then(response => {
                const eventData = response.data['member'] || response.data;
                const formattedEvents = eventData.map(event => ({
                    ...event,
                    className: 'custom-calendar-event',
                    backgroundColor: event.backgroundColor || '#4f46e5',
                    borderColor: event.borderColor || '#4338ca',
                    textColor: '#ffffff',
                    url: '',
                    eventUrl: event.url,
                }));
                setEvents(formattedEvents);
            })
            .catch(error => console.error(error))
            .finally(() => {
                setFetchingEvents(false);
                setInitialLoading(false);
            });
    }, [formattedStart]);

    const handleDatesSet = (dateInfo) => {
        const newDate = dateInfo.view.currentStart.toISOString();
        const newView = dateInfo.view.type;

        if (newDate !== currentDate) {
            setCurrentDate(newDate);
            localStorage.setItem('calendar_date', newDate);
        }

        if (newView !== view) {
            setView(newView);
            localStorage.setItem('calendar_view', newView);
        }
    };

    const exportFormats = [
        { name: 'iCalendar (.ics)', icon: CalendarIcon, format: 'ics' },
        ...(user?.googleAccount ? [
            { name: 'Google Calendar (Add)', icon: PlusIcon, format: 'google' },
            { name: 'Google Calendar (Sync)', icon: ArrowPathIcon, format: 'google_sync' },
            { name: 'Google Calendar (Delete)', icon: TrashIcon, format: 'google_delete' },
        ] : []),
    ];

    const handleExport = async (format) => {
        setExporting(true);
        try {
            if (format === 'ics') {
                try {
                    const response = await useAxios.get('/api/calendar/ics', {
                        responseType: 'blob'
                    });

                    const blob = new Blob([response.data], {type: 'text/calendar'});
                    const url = window.URL.createObjectURL(blob);

                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'jamboree_calendar.ics';
                    document.body.appendChild(link);
                    link.click();

                    link.remove();
                    window.URL.revokeObjectURL(url);
                } catch (error) {
                    console.error(error);

                    let message = 'Download failed';

                    if (error.response?.data instanceof Blob) {
                        try {
                            const text = await error.response.data.text();

                            try {
                                const json = JSON.parse(text);
                                message = json.message || json.error || message;
                            } catch {
                                message = text;
                            }

                        } catch {
                            message = 'Unable to read error response';
                        }
                    }
                    showFlash(message, 'error');
                }
            }

            if (format === 'google') {
                try {
                    const response = await useAxios.post(
                        '/api/calendar/google',
                        {'summary': 'Jamboree'},
                        {'headers': {'Content-Type': 'application/json'}}
                    );

                    showFlash(response.data.message ?? 'Google Calendar created successfully.', 'success');
                } catch (error) {
                    showFlash(error.response?.data?.message ?? 'Failed to create Google Calendar.', 'error');
                }
            }

            if (format === 'google_sync') {
                try {
                    const response = await useAxios.post(
                        '/api/calendar/google/sync',
                        {},
                        {'headers': {'Content-Type': 'application/json'}}
                    );

                    showFlash(response.data.message ?? 'Google Calendar synced successfully.', 'success');
                } catch (error) {
                    showFlash(error.response?.data?.message ?? 'Failed to sync Google Calendar.', 'error');
                }
            }

            if (format === 'google_delete') {
                try {
                    const response = await useAxios.delete(
                        '/api/calendar/google',
                        {},
                        {'headers': {'Content-Type': 'application/json'}}
                    );

                    showFlash(response.data.message ?? 'Google Calendar deleted successfully.', 'success');
                } catch (error) {
                    showFlash(error.response?.data?.message ?? 'Failed to delete Google Calendar.', 'error');
                }
            }
        } finally {
            setExporting(false);
        }
    };

    const handleEventClick = (info) => {
        const url = info.event.extendedProps?.eventUrl;
        
        if (url) {
            info.jsEvent.preventDefault();
            window.location.href = url;
        }
    };

    return (
        <div className="container mx-auto px-4 py-8 lg:py-12 min-h-screen">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                <div>
                    <h1 className="text-3xl font-extrabold text-gray-900 dark:text-gray-100 flex items-center">
                        <CalendarIcon className="h-8 w-8 mr-3 text-indigo-600 dark:text-indigo-400" />
                        Event Calendar
                    </h1>
                    <p className="mt-2 text-gray-500 dark:text-gray-400">
                        Stay organized and never miss an upcoming event.
                    </p>
                </div>

                <div className="flex items-center gap-3">
                    {!isMobile && (
                        <div className="flex bg-gray-100 dark:bg-gray-700/50 p-1 rounded-xl mr-2">
                            <button
                                onClick={() => {
                                    setView('dayGridMonth');
                                    localStorage.setItem('calendar_view', 'dayGridMonth');
                                    calendarRef.current?.getApi().changeView('dayGridMonth');
                                }}
                                className={`px-3 py-1.5 text-xs font-bold rounded-lg transition-all ${view === 'dayGridMonth' ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'}`}
                            >
                                Month
                            </button>
                            <button
                                onClick={() => {
                                    setView('listMonth');
                                    localStorage.setItem('calendar_view', 'listMonth');
                                    calendarRef.current?.getApi().changeView('listMonth');
                                }}
                                className={`px-3 py-1.5 text-xs font-bold rounded-lg transition-all ${view === 'listMonth' ? 'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'}`}
                            >
                                List
                            </button>
                        </div>
                    )}
                    {isLoggedIn && (
                        <Menu as="div" className="relative">
                            <MenuButton 
                                disabled={isExporting}
                                className={`inline-flex items-center gap-x-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-indigo-500 transition-all active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 ${isExporting ? 'opacity-70 cursor-not-allowed' : ''}`}
                            >
                                {isExporting ? (
                                    <div className="h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                ) : (
                                    <ArrowDownTrayIcon className="h-5 w-5" />
                                )}
                                {isExporting ? 'Exporting...' : 'Export Events'}
                                <ChevronDownIcon className="h-4 w-4 opacity-70" />
                            </MenuButton>

                            <Transition
                                as={Fragment}
                                enter="transition ease-out duration-100"
                                enterFrom="transform opacity-0 scale-95"
                                enterTo="transform opacity-100 scale-100"
                                leave="transition ease-in duration-75"
                                leaveFrom="transform opacity-100 scale-100"
                                leaveTo="transform opacity-0 scale-95"
                            >
                                <MenuItems className="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-2xl bg-white dark:bg-gray-800 p-1.5 shadow-2xl ring-1 ring-black ring-opacity-5 dark:ring-gray-700 focus:outline-none">
                                    <div className="px-3 py-2 border-b border-gray-50 dark:border-gray-700 mb-1">
                                        <p className="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Select Format</p>
                                    </div>
                                    {exportFormats.map((item) => (
                                        <MenuItem key={item.format}>
                                            {({ active }) => (
                                                <button
                                                    onClick={() => handleExport(item.format)}
                                                    className={`${
                                                        active ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-200'
                                                    } group flex w-full items-center rounded-xl px-3 py-2.5 text-sm transition-colors`}
                                                >
                                                    <item.icon className={`mr-3 h-5 w-5 ${active ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500'}`} />
                                                    {item.name}
                                                </button>
                                            )}
                                        </MenuItem>
                                    ))}
                                </MenuItems>
                            </Transition>
                        </Menu>
                    )}
                </div>
            </div>

            <div className="bg-white dark:bg-gray-800 rounded-3xl p-4 sm:p-8 shadow-xl border border-gray-100 dark:border-gray-700 transition-all overflow-hidden relative">
                {isFetchingEvents && !isInitialLoading && (
                    <div className="absolute top-4 right-4 z-10">
                        <div className="h-5 w-5 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                )}
                
                {isInitialLoading ? (
                    <div className="flex flex-col items-center justify-center py-32">
                        <div className="h-12 w-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p className="text-gray-500 dark:text-gray-400 font-medium">Loading your schedule...</p>
                    </div>
                ) : (
                    <div className="calendar-container">
                        <FullCalendar
                            ref={calendarRef}
                            plugins={[dayGridPlugin, interactionPlugin, listPlugin]}
                            initialView={view}
                            viewClassNames={view}
                            initialDate={currentDate}
                            events={events}
                            headerToolbar={{
                                left: 'prev,next today',
                                center: '',
                                right: 'title'
                            }}
                            eventClick={handleEventClick}
                            datesSet={handleDatesSet}
                            displayEventTime={false}
                            displayEventEnd={false}
                            noEventsContent="No events for this month"
                            eventContent={(eventInfo) => {
                                const googleUrl = eventInfo.event.extendedProps.google_url;
                                
                                if (eventInfo.view.type === 'listMonth') {
                                    return (
                                        <div className="flex items-center justify-between w-full cursor-pointer">
                                            <span className="font-semibold">{eventInfo.event.title}</span>
                                            {googleUrl && user?.googleAccount && (
                                                <a 
                                                    href={googleUrl}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    onClick={(e) => e.stopPropagation()}
                                                    className="ml-2 p-1.5 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
                                                    title="Add to Google Calendar"
                                                >
                                                    <PlusIcon className="h-4 w-4 text-white" />
                                                </a>
                                            )}
                                        </div>
                                    );
                                }

                                return (
                                    <div className="flex items-center justify-between w-full px-1 py-0.5 group/event">
                                        <div className="truncate flex-1">
                                            <div className="fc-event-time font-bold text-[10px] leading-tight">
                                                {eventInfo.timeText}
                                            </div>
                                            <div className="fc-event-title font-semibold text-[11px] leading-tight truncate">
                                                {eventInfo.event.title}
                                            </div>
                                        </div>
                                        {googleUrl && user?.googleAccount && (
                                            <a 
                                                href={googleUrl}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                onClick={(e) => e.stopPropagation()}
                                                className="hidden group-hover/event:flex ml-1 p-1 hover:bg-white/20 rounded-md transition-colors"
                                                title="Add to Google Calendar"
                                            >
                                                <PlusIcon className="h-4 w-4 text-white" />
                                            </a>
                                        )}
                                    </div>
                                );
                            }}
                            height="auto"
                            aspectRatio={1.35}
                            dayMaxEvents={true}
                            moreLinkClick="popover"
                            eventTimeFormat={{
                                hour: 'numeric',
                                minute: '2-digit',
                                meridiem: 'short'
                            }}
                        />
                    </div>
                )}
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .calendar-container .fc {
                    --fc-border-color: rgba(229, 231, 235, 1);
                    --fc-button-bg-color: transparent;
                    --fc-button-border-color: rgba(229, 231, 235, 1);
                    --fc-button-text-color: rgba(55, 65, 81, 1);
                    --fc-button-hover-bg-color: rgba(243, 244, 246, 1);
                    --fc-button-active-bg-color: rgba(79, 70, 229, 1);
                    --fc-button-active-border-color: rgba(79, 70, 229, 1);
                    --fc-event-bg-color: #4f46e5;
                    --fc-event-border-color: #4338ca;
                    --fc-today-bg-color: rgba(79, 70, 229, 0.05);
                    font-family: inherit;
                }

                .dark .calendar-container .fc {
                    --fc-border-color: rgba(75, 85, 99, 0.4);
                    --fc-button-border-color: rgba(75, 85, 99, 1);
                    --fc-button-text-color: rgba(229, 231, 235, 1);
                    --fc-button-hover-bg-color: rgba(55, 65, 81, 1);
                    --fc-today-bg-color: rgba(79, 70, 229, 0.15);
                    --fc-page-bg-color: rgba(75, 85, 99, 0.3);
                    --fc-list-event-hover-bg-color: rgba(55, 65, 81, 1);
                    --fc-list-event-title-text-color: rgba(229, 231, 235, 1);
                }

                .calendar-container .fc-header-toolbar {
                    margin-bottom: 2rem !important;
                    flex-wrap: wrap;
                    gap: 1rem;
                }

                .calendar-container .fc-toolbar-title {
                    font-size: 1.25rem !important;
                    font-weight: 800 !important;
                    color: inherit;
                }

                .dark .calendar-container .fc-toolbar-title {
                    color: rgba(229, 231, 235, 1) !important;
                }

                .calendar-container .fc-button {
                    border-radius: 0.75rem !important;
                    padding: 0.5rem 1rem !important;
                    font-weight: 600 !important;
                    text-transform: capitalize !important;
                    transition: all 0.2s !important;
                    font-size: 0.875rem !important;
                }

                .calendar-container .fc-button-primary:not(:disabled).fc-button-active,
                .calendar-container .fc-button-primary:not(:disabled):active {
                    color: white !important;
                }

                .calendar-container .fc-daygrid-day-number {
                    padding: 0.75rem !important;
                    font-weight: 600 !important;
                    color: inherit;
                    opacity: 0.8;
                }

                .calendar-container .fc-col-header-cell {
                    padding: 1rem 0 !important;
                    background: rgba(243, 244, 246, 0.7);
                    font-weight: 700 !important;
                    text-transform: uppercase;
                    font-size: 0.75rem;
                    letter-spacing: 0.05em;
                    color: rgba(107, 114, 128, 1);
                    border-bottom: 1px solid rgba(229, 231, 235, 1) !important;
                }

                .dark .calendar-container .fc-col-header-cell {
                    background: rgba(75, 85, 99, 0.3);
                    color: rgba(156, 163, 175, 1);
                    border-bottom: 1px solid rgba(75, 85, 99, 0.4) !important;
                }

                .dark .calendar-container .fc-daygrid-day-number {
                    color: rgba(229, 231, 235, 1) !important;
                    opacity: 1 !important;
                }

                .dark .calendar-container .fc-list-event-title {
                    color: rgba(229, 231, 235, 1) !important;
                }

                .dark .calendar-container .fc-list-day-text,
                .dark .calendar-container .fc-list-day-side-text {
                    color: rgba(229, 231, 235, 1) !important;
                }

                .dark .calendar-container .fc-daygrid-day:hover {
                    background: rgba(55, 65, 81, 0.2);
                }

                .calendar-container .fc-event {
                    border-radius: 6px !important;
                    padding: 2px 6px !important;
                    margin: 1px 2px !important;
                    font-size: 0.75rem !important;
                    font-weight: 600 !important;
                    border: none !important;
                    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .calendar-container .fc-daygrid-event:hover {
                    transform: scale(1.02);
                    filter: brightness(1.1);
                }

                .calendar-container .fc-list-event:hover td {
                    background-color: rgba(79, 70, 229, 0.05) !important;
                }

                .dark .calendar-container .fc-list-event:hover td {
                    background-color: rgba(79, 70, 229, 0.1) !important;
                }

                .calendar-container .fc-list-day-cushion {
                    display: flex !important;
                    justify-content: space-between !important;
                    padding: 10px 15px !important;
                    width: 100%;
                    background: transparent !important;
                }

                .calendar-container .fc-list-day-text {
                    order: 2;
                    font-weight: 700 !important;
                    flex: 1;
                    text-align: right;
                }

                .calendar-container .fc-list-day-side-text {
                    order: 1;
                    font-weight: 700;
                    color: #4f46e5 !important;
                    flex: 1;
                    text-align: left;
                }

                .dark .calendar-container .fc-list-day-side-text {
                    color: #818cf8 !important;
                }

                @media (max-width: 768px) {
                    .calendar-container .fc-header-toolbar {
                        flex-direction: column;
                        align-items: center;
                    }
                    
                    .calendar-container .fc-toolbar-chunk {
                        display: flex;
                        justify-content: center;
                        width: 100%;
                    }

                    .calendar-container .fc-list-event {
                        cursor: pointer;
                    }

                    .calendar-container .fc-list-event-title {
                        padding: 12px !important;
                        font-size: 0.95rem !important;
                        font-weight: 600 !important;
                    }

                    .calendar-container .fc-list-event-time {
                        padding: 12px !important;
                        font-weight: 700 !important;
                        color: #4f46e5 !important;
                    }

                    .dark .calendar-container .fc-list-event-time {
                        color: #818cf8 !important;
                    }
                }
            ` }} />
        </div>
    );
};

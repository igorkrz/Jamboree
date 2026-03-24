import React, { useEffect, useState } from "react";
import useAxios from "../helpers/useAxios.jsx";
import { useParams, Link } from "react-router-dom";
import { 
    CalendarIcon, 
    MapPinIcon, 
    BanknotesIcon, 
    GlobeAltIcon,
    ArrowLeftIcon,
    InformationCircleIcon,
    TagIcon,
    ClockIcon
} from "@heroicons/react/24/outline";

export default function SingleEvent() {
    const { id } = useParams();
    const [event, setEvent] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        useAxios.get(`/api/events/${id}`)
            .then(response => {
                setEvent(response.data);
            })
            .catch(error => {
                console.error(error);
            })
            .finally(() => {
                setLoading(false);
            });
    }, [id]);

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center dark:bg-gray-900">
                <div className="flex flex-col items-center">
                    <div className="h-12 w-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p className="text-gray-500 dark:text-gray-400 font-medium">Loading event details...</p>
                </div>
            </div>
        );
    }

    if (!event) {
        return (
            <div className="min-h-screen flex flex-col items-center justify-center p-4 dark:bg-gray-900">
                <InformationCircleIcon className="h-16 w-16 text-gray-400 mb-4" />
                <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Event not found</h2>
                <Link to="/events" className="mt-4 text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center">
                    <ArrowLeftIcon className="h-4 w-4 mr-2" />
                    Back to all events
                </Link>
            </div>
        );
    }

    const eventDate = event.holdingDate ? new Date(event.holdingDate) : null;
    const formattedDate = eventDate ? eventDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }) : 'Date TBD';

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
            <div className="relative h-[40vh] md:h-[60vh] w-full overflow-hidden">
                <img 
                    className="w-full h-full object-cover"
                    src={event.picture ? `/images/events/${event.picture.fileName}` : event.imageUrl || "https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=2070&auto=format&fit=crop"}
                    alt={event.name}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
                
                <div className="absolute bottom-0 left-0 w-full p-6 md:p-12">
                    <div className="container mx-auto">
                        <Link to="/events" className="inline-flex items-center text-white/80 hover:text-white mb-6 transition-colors group">
                            <ArrowLeftIcon className="h-5 w-5 mr-2 group-hover:-translate-x-1 transition-transform" />
                            Back to Events
                        </Link>
                        <h1 className="text-4xl md:text-6xl font-black text-white mb-4 tracking-tight leading-tight">
                            {event.name || 'Unnamed Event'}
                        </h1>
                        <div className="flex flex-wrap items-center gap-6 text-white/90">
                            {eventDate && (
                                <div className="flex items-center">
                                    <CalendarIcon className="h-6 w-6 mr-2 text-indigo-400" />
                                    <span className="font-semibold">{formattedDate}</span>
                                </div>
                            )}
                            {event.location?.city && (
                                <div className="flex items-center">
                                    <MapPinIcon className="h-6 w-6 mr-2 text-indigo-400" />
                                    <span className="font-semibold">{event.location.city}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <div className="container mx-auto px-4 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <div className="lg:col-span-2 space-y-12">
                        <section className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                            <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                                <InformationCircleIcon className="h-6 w-6 mr-3 text-indigo-600 dark:text-indigo-400" />
                                About this Event
                            </h2>
                            <p className="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line text-lg">
                                {event.description || "No description available for this event."}
                            </p>
                            
                            {event.tags && event.tags.length > 0 && (
                                <div className="mt-8 flex flex-wrap gap-2">
                                    {event.tags.map(tag => (
                                        <span 
                                            key={tag.id}
                                            className="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold shadow-sm"
                                            style={{ backgroundColor: tag.color ? `${tag.color}20` : '#3b82f620', color: tag.color || '#3b82f6' }}
                                        >
                                            <TagIcon className="h-4 w-4 mr-2" />
                                            {tag.name}
                                        </span>
                                    ))}
                                </div>
                            )}
                        </section>

                        {event.location?.venue && (
                            <section className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                                <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                                    <MapPinIcon className="h-6 w-6 mr-3 text-indigo-600 dark:text-indigo-400" />
                                    Location & Venue
                                </h2>
                                <div className="flex items-start gap-4">
                                    <div className="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl">
                                        <MapPinIcon className="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">{event.location.venue}</h3>
                                        <p className="text-gray-500 dark:text-gray-400">{event.location.city}, {event.location.country || 'Slovenia'}</p>
                                    </div>
                                </div>
                            </section>
                        )}
                    </div>

                    <div className="space-y-8">
                        <div className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-xl border border-gray-100 dark:border-gray-700 sticky top-24">
                            <div className="space-y-6">
                                <div className="pb-6 border-b border-gray-100 dark:border-gray-700">
                                    <p className="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Price</p>
                                    <div className="flex items-center">
                                        <BanknotesIcon className="h-8 w-8 text-emerald-600 dark:text-emerald-400 mr-3" />
                                        <span className="text-4xl font-black text-gray-900 dark:text-gray-100">
                                            {event.price ? `€${event.price}` : 'Free'}
                                        </span>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center text-gray-600 dark:text-gray-400">
                                            <CalendarIcon className="h-5 w-5 mr-3" />
                                            <span className="font-medium text-sm">Date</span>
                                        </div>
                                        <span className="text-gray-900 dark:text-gray-100 font-bold text-sm">{eventDate ? eventDate.toLocaleDateString() : 'TBD'}</span>
                                    </div>
                                </div>

                                {event.url ? (
                                    <a 
                                        href={event.url} 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-200 dark:shadow-none transition-all active:scale-[0.98] mt-4 flex items-center justify-center"
                                    >
                                        Get Tickets
                                    </a>
                                ) : (
                                    <button disabled className="w-full bg-gray-400 text-white font-black py-4 rounded-2xl cursor-not-allowed mt-4">
                                        Tickets Unavailable
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

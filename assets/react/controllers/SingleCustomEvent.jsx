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
    ClockIcon,
    SparklesIcon
} from "@heroicons/react/24/outline";

export default function SingleCustomEvent() {
    const { id } = useParams();
    const [event, setEvent] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        useAxios.get(`/api/custom_events/${id}`)
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
            <div className="min-h-screen flex items-center justify-center dark:bg-gray-900 transition-colors duration-300">
                <div className="flex flex-col items-center">
                    <div className="h-12 w-12 border-4 border-purple-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                    <p className="text-gray-500 dark:text-gray-400 font-medium">Loading custom event...</p>
                </div>
            </div>
        );
    }

    if (!event) {
        return (
            <div className="min-h-screen flex flex-col items-center justify-center p-4 dark:bg-gray-900 transition-colors duration-300">
                <SparklesIcon className="h-16 w-16 text-gray-400 mb-4" />
                <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Custom event not found</h2>
                <Link to="/custom_events" className="mt-4 text-purple-600 dark:text-purple-400 font-semibold hover:underline flex items-center">
                    <ArrowLeftIcon className="h-4 w-4 mr-2" />
                    Back to custom events
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
                    src={event.picture ? `/images/custom_events/${event.picture.fileName}` : event.imageUrl || "https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=2070&auto=format&fit=crop"}
                    alt={event.name}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
                
                <div className="absolute top-6 left-6 z-20">
                    <span className="inline-flex items-center px-4 py-1 rounded-full text-xs font-black bg-purple-600 text-white uppercase tracking-widest shadow-lg">
                        <SparklesIcon className="h-3 w-3 mr-1" />
                        Custom Event
                    </span>
                </div>

                <div className="absolute bottom-0 left-0 w-full p-6 md:p-12 z-10">
                    <div className="container mx-auto">
                        <Link to="/custom_events" className="inline-flex items-center text-white/80 hover:text-white mb-6 transition-colors group">
                            <ArrowLeftIcon className="h-5 w-5 mr-2 group-hover:-translate-x-1 transition-transform" />
                            Back to Custom Events
                        </Link>
                        <h1 className="text-4xl md:text-6xl font-black text-white mb-4 tracking-tight leading-tight drop-shadow-sm">
                            {event.name || 'Unnamed Event'}
                        </h1>
                        <div className="flex flex-wrap items-center gap-6 text-white/90">
                            {eventDate && (
                                <div className="flex items-center">
                                    <CalendarIcon className="h-6 w-6 mr-2 text-purple-400" />
                                    <span className="font-semibold">{formattedDate}</span>
                                </div>
                            )}
                            {event.location?.city && (
                                <div className="flex items-center">
                                    <MapPinIcon className="h-6 w-6 mr-2 text-purple-400" />
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
                                <InformationCircleIcon className="h-6 w-6 mr-3 text-purple-600 dark:text-purple-400" />
                                Event Description
                            </h2>
                            <p className="text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line text-lg">
                                {event.description || "No description provided."}
                            </p>
                            
                            {event.tags && event.tags.length > 0 && (
                                <div className="mt-8 flex flex-wrap gap-2">
                                    {event.tags.map(tag => (
                                        <span 
                                            key={tag.id}
                                            className="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold shadow-sm"
                                            style={{ backgroundColor: tag.color ? `${tag.color}20` : '#a855f720', color: tag.color || '#a855f7' }}
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
                                    <MapPinIcon className="h-6 w-6 mr-3 text-purple-600 dark:text-purple-400" />
                                    Venue Details
                                </h2>
                                <div className="flex items-start gap-4">
                                    <div className="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-2xl">
                                        <MapPinIcon className="h-8 w-8 text-purple-600 dark:text-purple-400" />
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
                                    <p className="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Estimated Price</p>
                                    <div className="flex items-center">
                                        <BanknotesIcon className="h-8 w-8 text-emerald-600 dark:text-emerald-400 mr-3" />
                                        <span className="text-4xl font-black text-gray-900 dark:text-gray-100">
                                            {event.price ? `€${event.price}` : 'Free Entry'}
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
                                        className="w-full bg-purple-600 hover:bg-purple-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-purple-200 dark:shadow-none transition-all active:scale-[0.98] mt-4 flex items-center justify-center"
                                    >
                                        <SparklesIcon className="h-5 w-5 mr-2" />
                                        Join Custom Event
                                    </a>
                                ) : (
                                    <button disabled className="w-full bg-gray-400 text-white font-black py-4 rounded-2xl cursor-not-allowed mt-4 flex items-center justify-center">
                                        <SparklesIcon className="h-5 w-5 mr-2" />
                                        Unavailable
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

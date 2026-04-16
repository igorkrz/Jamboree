import React, { useState, useEffect } from "react";
import { HeartIcon, MapPinIcon, CalendarIcon, BanknotesIcon, UserGroupIcon } from "@heroicons/react/24/outline/index.js";
import { HeartIcon as SelectedHeartIcon } from "@heroicons/react/24/solid/index.js";
import useAxios from "../helpers/useAxios.jsx";
import { Link, useNavigate } from "react-router-dom";

export default function Event({ event, isFavorite, isAuthenticated = true, userEventId = null }) {
    const [isFavorited, setFavorite] = useState(isFavorite);
    const [currentUserEventId, setCurrentUserEventId] = useState(userEventId);
    const [isProcessing, setIsProcessing] = useState(false);
    const navigate = useNavigate();

    useEffect(() => {
        setFavorite(isFavorite);
        setCurrentUserEventId(userEventId);
    }, [isFavorite, userEventId]);

    const handleFavorite = (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (!isAuthenticated) {
            navigate("/login");
            return;
        }

        if (isProcessing) {
            return;
        }

        setIsProcessing(true);

        if (!isFavorited) {
            const iri = event.provider ?  `/api/events/${event.id}` : `/api/custom_events/${event.id}`
            useAxios.post('/api/user_events/add', {
                event: iri
            }).then(response => {
                setCurrentUserEventId(response.data.user_event);
                setFavorite(true);
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                setIsProcessing(false);
            });
        } else {
            useAxios.delete(`/api/user_events/${currentUserEventId}`)
            .then(response => {
                setCurrentUserEventId(null);
                setFavorite(false);
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                setIsProcessing(false);
            });
        }
    };

    const eventImageUrl = event.provider ?
        `/images/events/${event.picture ? event.picture.fileName : event.imageUrl}` :
        `/images/custom_events/${event.picture ? event.picture.fileName : event.imageUrl}`;

    const eventLink = event.provider ? `/events/${event.id}` : `/custom_events/${event.id}`;

    return (
        <div className="group flex flex-col bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl shadow-sm hover:shadow-xl dark:shadow-none dark:hover:shadow-indigo-900/10 transition-all duration-300 overflow-hidden h-full">
            <div className="relative aspect-[16/9] overflow-hidden">
                <img 
                    className="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500"
                    src={eventImageUrl}
                    alt={event.name}
                />

                <button
                    onClick={handleFavorite}
                    disabled={isProcessing}
                    className={`absolute top-4 right-4 p-2 rounded-full bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm shadow-md transition-all duration-200 
                        ${isFavorited ? 'text-red-500' : 'text-gray-400 dark:text-gray-500 hover:text-red-500 hover:scale-110'} 
                        ${isProcessing ? 'opacity-50 cursor-not-allowed' : ''}`}
                >
                    {isFavorited ? (
                        <SelectedHeartIcon className="w-6 h-6" />
                    ) : (
                        <HeartIcon className="w-6 h-6" />
                    )}
                </button>
            </div>

            <div className="flex flex-col flex-1 p-5">
                <div className="flex-1">
                    <div className="mb-4">
                        <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight mb-1 group-hover:text-blue-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1">
                            {event.name}
                        </h3>
                        {event.provider && (
                            <div className="flex items-center text-gray-500 dark:text-gray-400 text-sm">
                                <UserGroupIcon className="w-4 h-4 mr-1.5 shrink-0" />
                                <span className="font-medium truncate">{event.provider.name}</span>
                            </div>
                        )}
                    </div>

                    <p className="text-gray-600 dark:text-gray-400 text-sm mb-6 line-clamp-2 leading-relaxed">
                        {event.description}
                    </p>

                    <div className="grid grid-cols-1 gap-y-2.5 mb-6">
                        <div className="flex items-start text-gray-600 dark:text-gray-400">
                            <MapPinIcon className="w-4 h-4 mt-0.5 mr-2.5 text-blue-500 dark:text-indigo-400 shrink-0" />
                            <span className="text-sm truncate">
                                {event.location ? `${event.location.venue}, ${event.location.city}` : 'Location TBD'}
                            </span>
                        </div>
                        <div className="flex items-center text-gray-600 dark:text-gray-400">
                            <CalendarIcon className="w-4 h-4 mr-2.5 text-blue-500 dark:text-indigo-400 shrink-0" />
                            <span className="text-sm">
                                {new Date(event.holdingDate).toLocaleDateString('en-GB', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                })}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-between pt-4 border-t border-gray-50 dark:border-gray-800">
                    <div className="flex items-center text-gray-900 dark:text-gray-100">
                        <BanknotesIcon className="w-5 h-5 mr-1.5 text-green-500 dark:text-green-400" />
                        <span className="text-lg font-bold">
                            {event.price > 0 ? `€${event.price}` : 'Free'}
                        </span>
                    </div>
                    
                    <Link
                        to={eventLink}
                        className="inline-flex items-center justify-center bg-gray-900 dark:bg-indigo-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-blue-600 dark:hover:bg-indigo-500 transform active:scale-95 transition-all duration-200"
                    >
                        View Details
                    </Link>
                </div>
            </div>
        </div>
    );
};

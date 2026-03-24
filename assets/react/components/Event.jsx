import React, { useState, useEffect } from "react";
import { HeartIcon } from "@heroicons/react/24/outline/index.js";
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

    const handleFavorite = () => {
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
            console.log("favorite", event);
            useAxios.post('/api/user_events/add', {
                event: iri
            }).then(response => {
                console.log(response.data);
                setCurrentUserEventId(response.data.user_event);
                setFavorite(true);
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                setIsProcessing(false);
            });
        } else {
            console.log("not favorite", event);
            useAxios.delete(`/api/user_events/${currentUserEventId}`)
            .then(response => {
                console.log(response.data);
                setCurrentUserEventId(null);
                setFavorite(false);
            }).catch(error => {
                console.error(error);
            }).finally(() => {
                setIsProcessing(false);
            });
        }
    };

    return (
        <div className="relative bg-white shadow-md rounded-lg overflow-hidden">
            <img className="w-full h-48 object-cover"
                 src={event.provider ?
                     `/images/events/${event.picture ? event.picture.fileName : event.imageUrl}` :
                     `/images/custom_events/${event.picture ? event.picture.fileName : event.imageUrl}`}
                 alt={event.name}
            />

            <button
                onClick={handleFavorite}
                disabled={isProcessing}
                className={`absolute top-3 right-3 text-red-500 hover:text-red-600 focus:outline-none ${isProcessing ? 'opacity-50 cursor-not-allowed' : ''}`}
                style={{width: 30, height: 30}}
            >
                {
                    isFavorited ?
                    <SelectedHeartIcon /> :
                    <HeartIcon />
                }
            </button>

            <div className="p-4">
                <h3 className="text-xl font-bold mb-2">{event.name}</h3>
                <p className="text-gray-700 text-sm mb-2 line-clamp-3">{event.description}</p>
                {event.provider  && (
                    <p className="text-gray-600 mb-2">
                        <strong>Provider: </strong>{event.provider.name}
                    </p>
                )}
                <p className="text-gray-600 mb-2">
                    <strong>Location: </strong>
                    {event.location && (
                        event.location.venue + ', ' + event.location.city
                    )}
                </p>
                <p className="text-gray-600 mb-2">
                    <strong>Date:</strong> {new Date(event.holdingDate).toLocaleDateString()}
                </p>
                <p className="text-gray-900 font-semibold mb-2">
                    <strong>Price: </strong>€{event.price}
                </p>
                <Link
                    to={event.provider ? `/events/${event.id}` : `/custom_events/${event.id}`}
                    className="inline-block mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition"
                >
                    View Event
                </Link>
            </div>
        </div>
    );
};

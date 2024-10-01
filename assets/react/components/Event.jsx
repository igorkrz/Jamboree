import React, { useState } from "react";
import { HeartIcon } from "@heroicons/react/24/outline/index.js";
import { HeartIcon as SelectedHeartIcon } from "@heroicons/react/24/solid/index.js";
import useAxios from "../helpers/useAxios.jsx";
import { Link } from "react-router-dom";

export default function Event({ event, isFavorite, userEventId = null }) {
    const [isFavorited, setFavorite] = useState(isFavorite);
    const handleFavorite = () => {
        if (!isFavorited) {
            const iri = event.provider ?  `/api/events/${event.id}` : `/api/custom_events/${event.id}`
            console.log("favorite", event);
            useAxios.post('/api/user_events/add', {
                event: iri
            }).then(response => {
                console.log(response.data);
            }).catch(error => {
                console.error(error);
            });
        } else {
            console.log("not favorite", event);
            useAxios.delete(`/api/user_events/${userEventId}`)
            .then(response => {
                console.log(response.data);
            }).catch(error => {
                console.error(error);
            });
        }
        setFavorite(!isFavorited);
    };

    return (
        <div className="relative bg-white shadow-md rounded-lg overflow-hidden">
            <img className="w-full h-48 object-cover"
                 src={event.picture ? event.picture.filePath : event.imageUrl}
                 alt={event.name}
            />

            <button
                onClick={handleFavorite}
                className="absolute top-3 right-3 text-red-500 hover:text-red-600 focus:outline-none"
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
                <p className="text-gray-700 text-sm mb-2">{event.description}</p>
                {event.provider && (
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

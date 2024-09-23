import React, { useEffect, useState } from "react";
import useAxios from "../helpers/useAxios.jsx";
import { useParams } from "react-router-dom";

export default function SingleEvent() {
    const {id} = useParams();
    const [event, setEvent] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        console.log(id);
        useAxios.get(`/api/events/${id}`)
            .then(response => {
                console.log(response.data);
                setEvent(response.data);
            })
            .catch(error => {
                console.error(error);
            });

        setLoading(false);
    }, [id]);

    if (loading) {
        return <p>Loading...</p>;
    }

    if (!event) {
        return <p>Event not found</p>;
    }

    return (
        <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100 p-4">
            <div className="max-w-4xl w-full bg-white shadow-lg rounded-lg overflow-hidden">
                {event.imageUrl ? (
                    <img
                        src={event.imageUrl}
                        alt={event.name}
                        className="w-full h-64 object-cover"
                    />
                ) : (
                    <div className="w-full h-64 bg-gray-200 flex items-center justify-center">
                        <p className="text-gray-500">No Image Available</p>
                    </div>
                )}

                <div className="p-6">
                    <h1 className="text-3xl font-bold text-gray-800 mb-4">{event.name || 'Unnamed Event'}</h1>

                    {event.description && (
                        <p className="text-gray-600 mb-6">{event.description}</p>
                    )}

                    <div className="flex flex-col sm:flex-row justify-between mb-6">
                        {event.location && (
                            <div className="mb-4 sm:mb-0">
                                <h2 className="text-lg font-semibold text-gray-700">Location</h2>
                                <p className="text-gray-600">{event.location}</p>
                            </div>
                        )}

                        {event.price && (
                            <div className="mb-4 sm:mb-0">
                                <h2 className="text-lg font-semibold text-gray-700">Price</h2>
                                <p className="text-gray-600">€{event.price}</p>
                            </div>
                        )}

                        {event.holdingDate && (
                            <div>
                                <h2 className="text-lg font-semibold text-gray-700">Date</h2>
                                <p className="text-gray-600">{new Date(event.holdingDate).toLocaleDateString()}</p>
                            </div>
                        )}
                    </div>

                    {event.url && (
                        <div className="mb-6">
                            <a
                                href={event.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-blue-500 hover:underline"
                            >
                                Event Website
                            </a>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

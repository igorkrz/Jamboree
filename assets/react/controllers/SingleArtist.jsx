import React, { useEffect, useState } from "react";
import useAxios from "../helpers/useAxios.jsx";
import { useParams, Link } from "react-router-dom";
import {
    ArrowLeftIcon,
    InformationCircleIcon,
    MusicalNoteIcon
} from "@heroicons/react/24/outline";

export default function SingleArtist() {
    const { id } = useParams();
    const [artist, setArtist] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        useAxios.get(`/api/artists/${id}`)
            .then(response => {
                setArtist(response.data);
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
                    <p className="text-gray-500 dark:text-gray-400 font-medium">Loading artist details...</p>
                </div>
            </div>
        );
    }

    if (!artist) {
        return (
            <div className="min-h-screen flex flex-col items-center justify-center p-4 dark:bg-gray-900">
                <InformationCircleIcon className="h-16 w-16 text-gray-400 mb-4" />
                <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100">Artist not found</h2>
                <Link to="/events" className="mt-4 text-indigo-600 dark:text-indigo-400 font-semibold hover:underline flex items-center">
                    <ArrowLeftIcon className="h-4 w-4 mr-2" />
                    Back to events
                </Link>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
            <div className="relative h-[40vh] md:h-[50vh] w-full overflow-hidden">
                <img 
                    className="w-full h-full object-cover"
                    src={artist.picture ? `/images/artists/${artist.picture.fileName}` : "https://images.unsplash.com/photo-1493225255756-d9584f8606e9?q=80&w=2070&auto=format&fit=crop"}
                    alt={artist.name}
                />
                <div className="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
                
                <div className="absolute bottom-0 left-0 w-full p-6 md:p-12">
                    <div className="container mx-auto">
                        <Link to={-1} className="inline-flex items-center text-white/80 hover:text-white mb-6 transition-colors group">
                            <ArrowLeftIcon className="h-5 w-5 mr-2 group-hover:-translate-x-1 transition-transform" />
                            Back
                        </Link>
                        <h1 className="text-4xl md:text-6xl font-black text-white mb-4 tracking-tight leading-tight">
                            {artist.name}
                        </h1>
                    </div>
                </div>
            </div>

            <div className="container mx-auto px-4 py-12">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <div className="lg:col-span-2 space-y-12">
                        <section className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                            <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                                <InformationCircleIcon className="h-6 w-6 mr-3 text-indigo-600 dark:text-indigo-400" />
                                Biography
                            </h2>
                            {artist.bio ? (
                                <div className="text-gray-600 dark:text-gray-400 leading-relaxed prose dark:prose-invert max-w-none text-lg" 
                                     dangerouslySetInnerHTML={{ __html: artist.bio.replace(/<a\s+href="([^"]+)">([^<]+)<\/a>/g, (match, p1, p2) => {
                                         return `<a href="${p1}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">${p2}</a>`;
                                     }) }} 
                                />
                            ) : (
                                <p className="text-gray-500 dark:text-gray-400 italic">No biography available.</p>
                            )}
                        </section>
                    </div>

                    <div className="space-y-8">
                        <div className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <MusicalNoteIcon className="h-5 w-5 mr-2 text-indigo-600 dark:text-indigo-400" />
                                Tags
                            </h3>
                            <div className="flex flex-wrap gap-2">
                                {artist.tags && artist.tags.length > 0 ? (
                                    artist.tags.map(tag => (
                                        <span 
                                            key={tag.id}
                                            className="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                                        >
                                            {tag.name}
                                        </span>
                                    ))
                                ) : (
                                    <span className="text-gray-500 text-sm">No tags available</span>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

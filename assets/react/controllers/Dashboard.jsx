import React from "react";
import { Link } from "react-router-dom";
import { 
    TicketIcon, 
    HeartIcon, 
    SparklesIcon, 
    CalendarIcon,
    ArrowRightIcon,
    ChartBarIcon
} from "@heroicons/react/24/outline";
import { useSelector } from "react-redux";

export default function Dashboard() {
    const { isAuthenticated, user } = useSelector((state) => state.authentication);

    const quickActions = [
        { 
            name: 'Explore Events', 
            description: 'Browse all upcoming events in your area.',
            href: '/events', 
            icon: TicketIcon,
            color: 'bg-indigo-600'
        },
        { 
            name: 'My Favorites', 
            description: 'View events you have saved for later.',
            href: isAuthenticated ? '/user_events' : '/login', 
            icon: HeartIcon,
            color: 'bg-pink-600'
        },
        { 
            name: 'Create Event', 
            description: 'Organize your own custom event.',
            href: isAuthenticated ? '/custom_events/create/' : '/login', 
            icon: SparklesIcon,
            color: 'bg-purple-600'
        },
    ];

    return (
        <div className="container mx-auto px-4 py-8 lg:py-12 min-h-screen transition-colors duration-300 overflow-x-hidden">
            <div className="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-indigo-600 px-6 py-12 sm:px-12 sm:py-20 shadow-2xl mb-12">
                <div className="relative z-10 max-w-2xl text-left">
                    <h1 className="text-3xl font-extrabold tracking-tight text-white sm:text-6xl mb-6">
                        Welcome back, {user?.firstName || 'Explorer'}!
                    </h1>
                    <p className="text-base sm:text-lg text-indigo-100 mb-8 leading-relaxed">
                        Discover the most exciting events happening around you. From concerts to workshops, find your next adventure with Jamboree.
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4">
                        <Link
                            to="/events"
                            className="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-indigo-600 shadow-sm hover:bg-indigo-50 transition-all active:scale-95"
                        >
                            Browse Events
                            <ArrowRightIcon className="ml-2 h-4 w-4" />
                        </Link>
                        {!isAuthenticated && (
                            <Link
                                to="/register"
                                className="inline-flex items-center justify-center rounded-full bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 transition-all active:scale-95"
                            >
                                Join Community
                            </Link>
                        )}
                    </div>
                </div>
                <div className="absolute -top-24 -right-24 h-64 w-64 sm:h-96 sm:w-96 rounded-full bg-indigo-500 opacity-20 blur-3xl"></div>
                <div className="absolute -bottom-24 -left-24 h-64 w-64 sm:h-96 sm:w-96 rounded-full bg-purple-500 opacity-20 blur-3xl"></div>
            </div>

            <div className="mb-16">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-8 flex items-center">
                    <ChartBarIcon className="h-6 w-6 mr-3 text-indigo-600" />
                    Getting Started
                </h2>
                <div className="grid grid-cols-1 gap-8 sm:grid-cols-3">
                    {quickActions.map((action) => (
                        <Link
                            key={action.name}
                            to={action.href}
                            className="group relative flex flex-col bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 transition-all hover:-translate-y-1 hover:shadow-xl overflow-hidden"
                        >
                            <div className={`mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl ${action.color} text-white shadow-lg group-hover:scale-110 transition-transform`}>
                                <action.icon className="h-6 w-6" />
                            </div>
                            <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                {action.name}
                            </h3>
                            <p className="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                                {action.description}
                            </p>
                            <div className="mt-8 flex items-center text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                Get Started
                                <ArrowRightIcon className="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </div>
                        </Link>
                    ))}
                </div>
            </div>

            {/*<div className="bg-gray-900 dark:bg-indigo-950 rounded-3xl p-8 sm:p-16 text-center text-white relative overflow-hidden">*/}
            {/*    <div className="relative z-10">*/}
            {/*        <h2 className="text-3xl font-bold mb-4">Never miss an event!</h2>*/}
            {/*        <p className="text-gray-400 dark:text-indigo-200 mb-8 max-w-md mx-auto">*/}
            {/*            Subscribe to our newsletter and get the latest updates on the most exciting events happening in your city.*/}
            {/*        </p>*/}
            {/*        <div className="flex flex-col sm:flex-row gap-4 justify-center max-w-lg mx-auto">*/}
            {/*            <input*/}
            {/*                type="email"*/}
            {/*                placeholder="Enter your email"*/}
            {/*                className="flex-1 rounded-full bg-white/10 border border-white/20 px-6 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"*/}
            {/*            />*/}
            {/*            <button className="rounded-full bg-indigo-600 px-8 py-3 font-bold hover:bg-indigo-500 transition-all active:scale-95">*/}
            {/*                Subscribe*/}
            {/*            </button>*/}
            {/*        </div>*/}
            {/*    </div>*/}
            {/*    <SparklesIcon className="absolute top-10 right-10 h-32 w-32 text-indigo-500/10 rotate-12" />*/}
            {/*    <CalendarIcon className="absolute -bottom-10 -left-10 h-48 w-48 text-purple-500/10 -rotate-12" />*/}
            {/*</div>*/}
        </div>
    );
}

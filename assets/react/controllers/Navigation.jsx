import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/react"
import { Bars3Icon, XMarkIcon, CalendarIcon, TicketIcon, PlusIcon, Squares2X2Icon, HeartIcon, SparklesIcon, SunIcon, MoonIcon } from "@heroicons/react/24/outline"
import { Link, useLocation } from "react-router-dom";
import UserMenu from "./UserMenu.jsx"
import NotificationBell from "./NotificationBell.jsx"
import { useSelector } from "react-redux";
import React, { useEffect, useState } from "react";

export default function Navigation() {
    const { isAuthenticated, isLoginChecked } = useSelector(
        (state) => state.authentication
    );
    const location = useLocation();
    const [isDark, setIsDark] = useState(
        localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
    );

    useEffect(() => {
        const root = document.documentElement;
        if (isDark) {
            root.classList.add('dark');
            localStorage.theme = 'dark';
        } else {
            root.classList.remove('dark');
            localStorage.theme = 'light';
        }
    }, [isDark]);

    const toggleDarkMode = () => {
        setIsDark(!isDark);
    };

    const navigation = [
        { name: 'Dashboard', href: '/', icon: Squares2X2Icon },
        { name: 'Events', href: '/events', icon: TicketIcon },
        { name: 'My Events', href: isAuthenticated ? '/user_events' : '/login', icon: HeartIcon },
        { name: 'Created Events', href: isAuthenticated ? '/custom_events' : '/login', icon: SparklesIcon },
        { name: 'Calendar', href: '/calendar', icon: CalendarIcon },
        { name: 'Create Event', href: isAuthenticated ? '/custom_events/create/' : '/login', icon: PlusIcon },
    ]

    function classNames(...classes) {
        return classes.filter(Boolean).join(' ')
    }

    const isActive = (path) => {
        if (path === '/' && location.pathname !== '/') return false;
        return location.pathname.startsWith(path);
    };

    return (
        <Disclosure as="nav" className="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-100 dark:border-gray-800 transition-colors duration-300">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div className="relative flex h-16 items-center justify-between">
                    <div className="absolute inset-y-0 left-0 flex items-center sm:hidden">
                        <DisclosureButton className="group relative inline-flex items-center justify-center rounded-xl p-2 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                            <span className="sr-only">Open main menu</span>
                            <Bars3Icon aria-hidden="true" className="block h-6 w-6 group-data-[open]:hidden" />
                            <XMarkIcon aria-hidden="true" className="hidden h-6 w-6 group-data-[open]:block" />
                        </DisclosureButton>
                    </div>
                    
                    <div className="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                        <div className="flex flex-shrink-0 items-center">
                            <span className="text-2xl font-black bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent tracking-tight">
                                Jamboree
                            </span>
                        </div>
                        <div className="hidden sm:ml-8 sm:block">
                            <div className="flex space-x-1">
                                {navigation.map((item) => {
                                    const active = isActive(item.href);
                                    return (
                                        <Link
                                            key={item.name}
                                            aria-current={active ? 'page' : undefined}
                                            className={classNames(
                                                active 
                                                    ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400' 
                                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400',
                                                'group flex items-center rounded-lg px-3 py-2 text-sm font-semibold transition-all duration-200 ease-in-out',
                                            )}
                                            to={item.href}
                                        >
                                            <item.icon className={classNames(
                                                active ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-500',
                                                'mr-1.5 h-4 w-4 transition-colors duration-200'
                                            )} />
                                            {item.name}
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center space-x-4">
                        <button
                            onClick={toggleDarkMode}
                            className="p-2 rounded-xl text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors"
                            aria-label="Toggle dark mode"
                        >
                            {isDark ? (
                                <SunIcon className="h-5 w-5" />
                            ) : (
                                <MoonIcon className="h-5 w-5" />
                            )}
                        </button>
                        {isLoginChecked && (
                            isAuthenticated ? (
                                <>
                                    <NotificationBell />
                                    <UserMenu />
                                </>
                            ) : (
                                <Link
                                    className="inline-flex items-center justify-center rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all active:scale-95"
                                    to="/login"
                                >
                                    Sign in
                                </Link>
                            )
                        )}
                    </div>
                </div>
            </div>

            <DisclosurePanel className="sm:hidden border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 transition-colors">
                <div className="space-y-1 px-4 pb-3 pt-2">
                    {navigation.map((item) => {
                        const active = isActive(item.href);
                        return (
                            <DisclosureButton
                                key={item.name}
                                as={Link}
                                aria-current={active ? 'page' : undefined}
                                className={classNames(
                                    active 
                                        ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400' 
                                        : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400',
                                    'flex items-center rounded-xl px-3 py-3 text-base font-medium transition-colors',
                                )}
                                to={item.href}
                            >
                                <item.icon className={classNames(
                                    active ? 'text-indigo-600' : 'text-gray-400',
                                    'mr-3 h-6 w-6'
                                )} />
                                {item.name}
                            </DisclosureButton>
                        );
                    })}
                </div>
            </DisclosurePanel>
        </Disclosure>
    )
}

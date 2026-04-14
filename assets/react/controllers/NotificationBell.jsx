import React, { useEffect, useState } from "react";
import { BellIcon } from "@heroicons/react/24/outline";
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/react";
import useAxios from "../helpers/useAxios.jsx";
import { useNavigate } from "react-router-dom";

export default function NotificationBell() {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const navigate = useNavigate();

    const fetchNotifications = async () => {
        try {
            const response = await useAxios.get('/api/notifications');
            const items = response.data['member'] || [];
            setNotifications(items);
            setUnreadCount(items.length);
        } catch (error) {
            console.error("Failed to fetch notifications", error);
        }
    };

    useEffect(() => {
        fetchNotifications();
        const interval = setInterval(fetchNotifications, 300000);
        return () => clearInterval(interval);
    }, []);

    const handleNotificationClick = async (notification) => {
        const id = notification.id?.split('/').pop() || notification.id;
        try {
            await useAxios.delete(`/api/notifications/${id}`);
            fetchNotifications();
            if (notification.data?.eventId) {
                navigate(`/events/${notification.data.eventId}`);
            }
        } catch (error) {
            console.error("Failed to process notification click", error);
        }
    };

    const deleteAllNotifications = async () => {
        try {
            await useAxios.post('/api/notifications/read-all', {}, {
                headers: { 'Content-Type': 'application/json+ld' }
            });
            fetchNotifications();
        } catch (error) {
            console.error("Failed to delete all notifications", error);
        }
    };

    return (
        <Menu as="div" className="relative">
            <MenuButton className="relative p-2 rounded-xl text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
                <span className="sr-only">View notifications</span>
                <BellIcon className="h-6 w-6" aria-hidden="true" />
                {unreadCount > 0 && (
                    <span className="absolute top-2 right-2 flex h-4 w-4">
                        <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span className="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white items-center justify-center font-bold">
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                    </span>
                )}
            </MenuButton>

            <MenuItems transition className="absolute right-0 z-10 mt-2 w-80 origin-top-right rounded-2xl bg-white dark:bg-gray-800 p-1.5 shadow-xl ring-1 ring-black ring-opacity-5 dark:ring-gray-700 transition focus:outline-none data-[closed]:scale-95 data-[closed]:transform data-[closed]:opacity-0 data-[enter]:duration-100 data-[leave]:duration-75 data-[enter]:ease-out data-[leave]:ease-in">
                <div className="px-4 py-2 border-b border-gray-50 dark:border-gray-700 mb-1 flex justify-between items-center">
                    <p className="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Notifications</p>
                    <div className="flex items-center gap-2">
                        {unreadCount > 0 && (
                            <button 
                                onClick={deleteAllNotifications}
                                className="text-[10px] text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                            >
                                Clear all
                            </button>
                        )}
                        {unreadCount > 0 && <span className="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-bold">{unreadCount}</span>}
                    </div>
                </div>
                <div className="max-h-96 overflow-y-auto">
                    {notifications.length === 0 ? (
                        <div className="px-4 py-6 text-center">
                            <p className="text-sm text-gray-500 dark:text-gray-400">No notifications yet</p>
                        </div>
                    ) : (
                        notifications.map((notification) => (
                            <MenuItem key={notification.id}>
                                <div 
                                    className={`group flex flex-col rounded-xl px-4 py-3 text-sm transition-colors bg-indigo-50/50 dark:bg-indigo-900/10 hover:bg-indigo-200 dark:hover:bg-indigo-900/40 text-gray-900 dark:text-gray-100 font-medium cursor-pointer`}
                                    onClick={() => handleNotificationClick(notification)}
                                >
                                    <div className="flex justify-between items-start">
                                        <span className="block text-sm">{notification.title}</span>
                                        <span className="h-2 w-2 rounded-full bg-indigo-600 mt-1.5"></span>
                                    </div>
                                    <p className="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {notification.message}
                                    </p>
                                </div>
                            </MenuItem>
                        ))
                    )}
                </div>
            </MenuItems>
        </Menu>
    );
}

import React, { createContext, useContext, useState, useCallback } from 'react';
import { XMarkIcon, CheckCircleIcon, ExclamationCircleIcon, InformationCircleIcon } from '@heroicons/react/24/outline';

const FlashContext = createContext(null);

export const FlashProvider = ({ children }) => {
    const [flashes, setFlashes] = useState([]);

    const showFlash = useCallback((message, type = 'success', duration = 5000) => {
        const id = Date.now();
        setFlashes((prev) => [...prev, { id, message, type }]);
        
        if (duration) {
            setTimeout(() => {
                removeFlash(id);
            }, duration);
        }
    }, []);

    const removeFlash = useCallback((id) => {
        setFlashes((prev) => prev.filter((flash) => flash.id !== id));
    }, []);

    return (
        <FlashContext.Provider value={{ showFlash }}>
            {children}
            <div className="fixed top-0 right-0 p-6 z-[60] flex flex-col gap-3 pointer-events-none sm:max-w-md w-full">
                {flashes.map((flash) => (
                    <FlashMessage 
                        key={flash.id} 
                        flash={flash} 
                        onClose={() => removeFlash(flash.id)} 
                    />
                ))}
            </div>
        </FlashContext.Provider>
    );
};

const FlashMessage = ({ flash, onClose }) => {
    const icons = {
        success: <CheckCircleIcon className="h-6 w-6 text-green-500" />,
        error: <ExclamationCircleIcon className="h-6 w-6 text-red-500" />,
        info: <InformationCircleIcon className="h-6 w-6 text-blue-500" />,
        warning: <ExclamationCircleIcon className="h-6 w-6 text-yellow-500" />
    };

    const bgColors = {
        success: 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800',
        error: 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800',
        info: 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800',
        warning: 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800'
    };

    return (
        <div className={`pointer-events-auto flex items-center p-4 border rounded-xl shadow-lg transition-all duration-300 animate-slide-in ${bgColors[flash.type]}`}>
            <div className="flex-shrink-0">
                {icons[flash.type]}
            </div>
            <div className="ml-3 flex-1">
                <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {flash.message}
                </p>
            </div>
            <div className="ml-4 flex-shrink-0 flex">
                <button
                    onClick={onClose}
                    className="inline-flex text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 focus:outline-none"
                >
                    <XMarkIcon className="h-5 w-5" />
                </button>
            </div>
        </div>
    );
};

export const useFlash = () => {
    const context = useContext(FlashContext);
    if (!context) {
        throw new Error('useFlash must be used within a FlashProvider');
    }
    return context;
};

// import './bootstrap.js';
import React from "react";
import { createRoot } from "react-dom/client";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import App from "./react/controllers/App.jsx";
import './app.css';
import Calendar from "./react/controllers/Calendar.jsx";
import Events from "./react/controllers/Events.jsx";
import LoginForm from "./react/components/LoginForm.jsx";
import RegistrationForm from "./react/components/RegistrationForm.jsx";
import UserEvents from "./react/controllers/UserEvents.jsx";
import SingleEvent from "./react/controllers/SingleEvent.jsx";
import ResourceNotAvailable from "./react/components/ResourceNotAvailable.jsx";
import CustomEventCreate from "./react/controllers/CustomEventCreate.jsx";
import SingleCustomEvent from "./react/controllers/SingleCustomEvent.jsx";
import CustomEvents from "./react/controllers/CustomEvents.jsx";
import Dashboard from "./react/controllers/Dashboard.jsx";

const router = createBrowserRouter([
    {
        path: "/",
        element: <App />,
        children: [{
            index: true,
            element: <Dashboard />,
        },
            {
                path: 'login/',
                element: <LoginForm />,
            },
            {
                path: 'register/',
                element: <RegistrationForm />,
            },
            {
                path: 'calendar/',
                element: <Calendar />,
            },
            {
                path: 'user_events/',
                element: <UserEvents />
            },
            {
                path: 'events/',
                element: <Events />
            },
            {
                path: 'events/:id/',
                element: <SingleEvent />
            },
            {
                path: 'custom_events/',
                element: <CustomEvents />
            },
            {
                path: 'custom_events/:id/',
                element: <SingleCustomEvent />
            },
            {
                path: 'custom_events/create/',
                element: <CustomEventCreate />
            },
            {
                path: '*',
                element: <ResourceNotAvailable />
            }]
    }
]);

const rootElement = document.getElementById('root');
if (rootElement) {
    const root = createRoot(rootElement);
    root.render(<RouterProvider router={router} />);
}

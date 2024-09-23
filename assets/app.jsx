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

const router = createBrowserRouter([
    {
        path: "/",
        element: <App />,
        // errorElement: <ResourceNotAvailable />,
        children: [{
            index: true,
            element: <ResourceNotAvailable />,
        },
            {
                path: 'login/',
                element: <LoginForm />,
                // loader: getUsersAllLoader,
            },
            {
                path: 'register/',
                element: <RegistrationForm />,
                // loader: getUsersAllLoader,
            },
            {
                path: 'calendar/',
                element: <Calendar />,
                // loader: getUsersAllLoader,
            },
            // {
            //     path: "users/:id",
            //     element: <UserDetails />,
            //     loader: getUserByIdLoader
            // },
            {
                path: 'user_events/',
                element: <UserEvents />
            },
            {
                path: 'events/',
                element: <Events />
            },
            {
                path: 'events/:id',
                element: <SingleEvent />
            }]
    }
]);

// ReactDOM.createRoot(document.getElementById("root")).render(
//     <RouterProvider router={router} />
// );

const rootElement = document.getElementById('root');
if (rootElement) {
    const root = createRoot(rootElement);
    root.render(<RouterProvider router={router} />);
}

import React from "react";
import { Route, Routes } from "react-router-dom";
import Dashboard from "./Dashboard.jsx";
import Calendar from "./Calendar.jsx";
import Events from "./Events.jsx";
import UserEvents from "./UserEvents.jsx";
import SingleEvent from "./SingleEvent.jsx";
import CustomEventCreate from "./CustomEventCreate.jsx";
import SingleCustomEvent from "./SingleCustomEvent.jsx";
import CustomEvents from "./CustomEvents.jsx";
import LoginForm from "../components/LoginForm.jsx";
import RegistrationForm from "../components/RegistrationForm.jsx";
import ResourceNotAvailable from "../components/ResourceNotAvailable.jsx";

export default function AppRoutes() {
    return (
        <Routes>
            <Route path={'/'} element={<Dashboard />}></Route>
            <Route path={'/login'} element={<LoginForm />}></Route>
            <Route path={'/register'} element={<RegistrationForm />}></Route>
            <Route path={'/calendar'} element={<Calendar />}></Route>
            <Route path={'/user_events'} element={<UserEvents />}></Route>
            <Route path={'/events'} element={<Events />}></Route>
            <Route path={'/events/:id'} element={<SingleEvent />}></Route>
            <Route path={'/custom_events'} element={<CustomEvents />}></Route>
            <Route path={'/custom_events/:id'} element={<SingleCustomEvent />}></Route>
            <Route path={'/custom_events/create'} element={<CustomEventCreate />}></Route>
            <Route path={'*'} element={<ResourceNotAvailable />}></Route>
        </Routes>
    )
}

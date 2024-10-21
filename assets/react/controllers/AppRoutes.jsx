import React from "react";
import { Route, Routes } from "react-router-dom";
import Dashboard from "./Dashboard";
import Calendar from "./Calendar";
import Events from "./Events";
import UserEvents from "./UserEvents";
import SingleEvent from "./SingleEvent";
import CustomEventCreate from "./CustomEventCreate";
import SingleCustomEvent from "./SingleCustomEvent";
import CustomEvents from "./CustomEvents";
import LoginForm from "../components/LoginForm";
import RegistrationForm from "../components/RegistrationForm";
import ResourceNotAvailable from "../components/ResourceNotAvailable";

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

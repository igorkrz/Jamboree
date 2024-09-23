import React from "react";
import Navigation from "./Navigation.jsx";
import { Outlet } from "react-router-dom";
import useToken from "../helpers/useToken.jsx";
// import LoginForm from "../components/LoginForm.jsx";

export default function App() {
    const { token, setToken } = useToken();
    console.log('APP', token);

    // if(token === undefined || !token) {
    //     return <LoginForm setToken={setToken} />
    // }

    return (
        <div>
            <Navigation isLoggedIn={token} />
            <Outlet />
        </div>
    );
};

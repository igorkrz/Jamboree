import React, { useEffect } from "react";
import Navigation from "./Navigation.jsx";
import { BrowserRouter as Router, Outlet } from "react-router-dom";
import { checkLogin } from "../../redux/reducers/authSlice";
import AppRoutes from "./AppRoutes.jsx";
import { useDispatch, useSelector } from "react-redux";

export default function App() {
    const dispatch = useDispatch();
    const { isAuthenticated, isLoginChecked } = useSelector(
        (state) => state.authentication
    );

    useEffect(() => {
        dispatch(checkLogin());
    }, []);

    return (
        <Router>
            <Navigation />
            <AppRoutes />
        </Router>
    );
};

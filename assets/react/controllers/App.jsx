import React, { useEffect } from "react";
import Navigation from "./Navigation.jsx";
import { BrowserRouter as Router } from "react-router-dom";
import { checkLogin } from "../../redux/reducers/authSlice";
import AppRoutes from "./AppRoutes.jsx";
import { useDispatch } from "react-redux";
import { FlashProvider } from "../context/FlashContext.jsx";

export default function App() {
    const dispatch = useDispatch();

    useEffect(() => {
        dispatch(checkLogin());
    }, [dispatch]);

    return (
        <Router>
            <FlashProvider>
                <div className="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
                    <Navigation />
                    <main>
                        <AppRoutes />
                    </main>
                </div>
            </FlashProvider>
        </Router>
    );
};

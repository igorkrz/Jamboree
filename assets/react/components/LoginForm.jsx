import React, { useEffect, useState } from "react";
import { Link, Outlet, useNavigate } from "react-router-dom";
import useAxios from "../helpers/useAxios.jsx";
import useToken from "../helpers/useToken.jsx"
import { ENDPOINTS } from "../../api/constants/Endpoints";
import { EnvelopeIcon, LockClosedIcon, ArrowRightOnRectangleIcon } from "@heroicons/react/24/outline";
import { useFlash } from "../context/FlashContext.jsx";
import { useDispatch } from "react-redux";
import { checkLogin } from "../../redux/reducers/authSlice";

export default function LoginForm() {
    const { setToken } = useToken();
    const [username, setUsername] = useState('');
    const [password, setPassword] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();
    const { showFlash } = useFlash();
    const dispatch = useDispatch();

    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        if (token) {
            setToken(token);
            const newUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);

            dispatch(checkLogin());
            navigate("/");
        }
    }, [setToken, navigate, showFlash, dispatch]);
    
    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        try {
            const response = await useAxios.post(ENDPOINTS.LOGIN, {
                username: username,
                password: password,
            });
            setToken(response.data.token);
            dispatch(checkLogin());
            navigate("/");
        } catch (error) {
            const message = error.response?.data?.message || "Invalid credentials. Please try again.";
            showFlash(message, "error");
            console.error(error);
        } finally {
            setIsLoading(false);
        }
    }

    return (
        <div className="min-h-[calc(100vh-64px)] flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
            <div className="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700">
                <div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                        Welcome Back
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                        Please enter your details to sign in
                    </p>
                </div>
                
                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    <div className="rounded-md shadow-sm space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email Address
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <EnvelopeIcon className="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input
                                    type="email"
                                    required
                                    className="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-xl placeholder-gray-500 text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm transition-all"
                                    placeholder="you@example.com"
                                    onChange={e => setUsername(e.target.value)}
                                />
                            </div>
                        </div>
                        
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Password
                            </label>
                            <div className="relative">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <LockClosedIcon className="h-5 w-5 text-gray-400" aria-hidden="true" />
                                </div>
                                <input
                                    type="password"
                                    required
                                    className="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-xl placeholder-gray-500 text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm transition-all"
                                    placeholder="••••••••"
                                    onChange={e => setPassword(e.target.value)}
                                />
                            </div>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            disabled={isLoading}
                            className={`group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gray-900 dark:bg-indigo-600 hover:bg-gray-800 dark:hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ${
                                isLoading ? 'opacity-70 cursor-not-allowed' : ''
                            }`}
                        >
                            {isLoading ? (
                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            ) : (
                                <ArrowRightOnRectangleIcon className="h-5 w-5 mr-2 group-hover:translate-x-1 transition-transform" aria-hidden="true" />
                            )}
                            {isLoading ? 'Signing in...' : 'Sign In'}
                        </button>
                    </div>

                    <div className="text-center">
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account?{' '}
                            <Link to="/register" className="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors">
                                Create an account
                            </Link>
                        </p>
                    </div>

                    <div className="relative my-6">
                        <div className="absolute inset-0 flex items-center" aria-hidden="true">
                            <div className="w-full border-t border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div className="relative flex justify-center text-sm">
                            <span className="px-2 bg-white dark:bg-gray-800 text-gray-500">Or continue with</span>
                        </div>
                    </div>

                    <div>
                        <button
                            type="button"
                            onClick={() => window.location.href = '/google/connect'}
                            className="w-full flex justify-center items-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
                        >
                            <svg className="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                            </svg>
                            Sign in with Google
                        </button>
                    </div>
                </form>
            </div>
            <Outlet />
        </div>
    );
}

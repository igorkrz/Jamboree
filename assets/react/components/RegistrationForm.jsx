import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import useAxios from "../helpers/useAxios.jsx";

export default function RegistrationForm() {
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [repeatPassword, setRepeatPassword] = useState('');
    const navigate = useNavigate();

    async function registerUser(credentials) {
        console.log(credentials);
        return useAxios.post('/api/security/register', credentials
            // {
            // data: JSON.stringify({
            //     firstName: credentials.firstName,
            //     lastName: credentials.lastName,
            //     email: credentials.email,
            //     plainPassword: credentials.plainPassword,
            // })}
        )
            .then(response => response.data.access_token)
            .catch(error => console.error(error));
    }

    const handleSubmit = async e => {
        e.preventDefault();

        let plainPassword = {
            first: password,
            second: repeatPassword
        }

        const token = await registerUser({
            firstName,
            lastName,
            email,
            plainPassword
        });

        console.log('TOKEN', token);

        // navigate("/");
    }

    return (
        <div className="h-[100vh] items-center flex justify-center px-5 lg:px-0">
            <div className="max-w-screen-xl bg-white border shadow sm:rounded-lg flex justify-center flex-1">
                <div className="flex-1 bg-blue-900 text-center hidden md:flex">
                    <div
                        className="m-12 xl:m-16 w-full bg-contain bg-center bg-no-repeat"
                        style={{
                            backgroundImage: `url(https://www.tailwindtap.com/assets/common/marketing.svg)`,
                        }}
                    ></div>
                </div>
                <div className="lg:w-1/2 xl:w-5/12 p-6 sm:p-12">
                    <div className=" flex flex-col items-center">
                        <div className="text-center">
                            <h1 className="text-2xl xl:text-4xl font-extrabold text-blue-900">
                                Register
                            </h1>
                            <p className="text-[12px] text-gray-500">
                                Hey enter your details to create your account
                            </p>
                        </div>
                        <div className="w-full flex-1 mt-8">
                            <div className="mx-auto max-w-xs flex flex-col gap-4">
                                <form onSubmit={handleSubmit}>
                                    <input
                                        className="w-full px-5 py-3 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
                                        type="text"
                                        placeholder="Enter your first name"
                                        onChange={e => setFirstName(e.target.value)}
                                    />
                                    <input
                                        className="w-full px-5 py-3 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
                                        type="text"
                                        placeholder="Enter your last name"
                                        onChange={e => setLastName(e.target.value)}
                                    />
                                    <input
                                        className="w-full px-5 py-3 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
                                        type="email"
                                        placeholder="Enter your email"
                                        onChange={e => setEmail(e.target.value)}
                                    />
                                    <input
                                        className="w-full px-5 py-3 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
                                        type="password"
                                        placeholder="Password"
                                        onChange={e => setPassword(e.target.value)}
                                    />
                                    <input
                                        className="w-full px-5 py-3 rounded-lg font-medium bg-gray-100 border border-gray-200 placeholder-gray-500 text-sm focus:outline-none focus:border-gray-400 focus:bg-white"
                                        type="password"
                                        placeholder="Repeat password"
                                        onChange={e => setRepeatPassword(e.target.value)}
                                    />
                                    <button
                                        className="mt-5 tracking-wide font-semibold bg-blue-900 text-gray-100 w-full py-4 rounded-lg hover:bg-indigo-700 transition-all duration-300 ease-in-out flex items-center justify-center focus:shadow-outline focus:outline-none"
                                        type={"submit"}
                                    >
                                        <svg
                                            className="w-6 h-6 -ml-2"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            strokeLinecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                            <circle cx="8.5" cy="7" r="4"/>
                                            <path d="M20 8v6M23 11h-6"/>
                                        </svg>
                                        <span className="ml-3">Register</span>
                                    </button>
                                    <p className="mt-6 text-xs text-gray-600 text-center">
                                        Already have an account?{" "}
                                        <Link
                                            to={'/login/'}
                                            className={"text-blue-900 font-semibold"}
                                        >
                                            Sign in here
                                        </Link>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

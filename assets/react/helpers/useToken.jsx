import { useState } from "react";
import useAxios from "./useAxios.jsx";

export default function useToken() {
    const getToken = () => {
        return sessionStorage.getItem('access_token');
    };

    const [token, setToken] = useState(getToken());

    const saveToken = userToken => {
        const accessToken = typeof userToken === 'object' ? userToken.token : userToken;
        sessionStorage.setItem('access_token', accessToken);
        useAxios.defaults.headers.common['Authorization'] = `Bearer ${accessToken}`
        setToken(accessToken);
    };

    return {
        setToken: saveToken,
        token
    }
}
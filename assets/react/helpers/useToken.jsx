import { useState } from "react";
import axios from "axios";

export default function useToken() {
    const getToken = () => {
        console.log('getToken', sessionStorage.getItem('access_token'));
        return sessionStorage.getItem('access_token');
    };

    const [token, setToken] = useState(getToken());

    const saveToken = userToken => {
        console.log('saveToken', userToken);
        sessionStorage.setItem('access_token', JSON.stringify(userToken));
        const accessToken = sessionStorage.getItem('access_token');
        console.log(accessToken);
        axios.defaults.headers.common['Authorization'] = `Bearer ${accessToken}`
        setToken(userToken.access_token);
    };

    return {
        setToken: saveToken,
        token
    }
}
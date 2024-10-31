import axios from "axios";

const useAxios = axios.create({
    baseURL: process.env.NODE_ENV === 'production'
        ? 'http://49.13.134.200:8380'
        : 'http://localhost:8380',
    headers: {
        'Accept': 'application/json+ld'
    }
});

useAxios.interceptors.request.use((config) => {
    const token = sessionStorage.getItem('access_token');

    if (token) {
        config.headers['Authorization'] = 'Bearer ' + sessionStorage.getItem('access_token').replace(/^"(.*)"$/, '$1');
    }

    return config;
}, (error) => {
    return Promise.reject(error);
});

export default useAxios;

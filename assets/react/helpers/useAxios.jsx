import axios from "axios";

const useAxios = axios.create({
    baseURL: 'http://localhost:8380',
});

// Add an interceptor to attach the Authorization header to every request
useAxios.interceptors.request.use((config) => {
    // Get the token from localStorage or any storage where you keep the auth token
    const token = sessionStorage.getItem('access_token');

    if (token) {
        config.headers['Authorization'] = 'Bearer ' + sessionStorage.getItem('access_token').replace(/^"(.*)"$/, '$1'); // Add the Authorization header
    }

    return config;
}, (error) => {
    return Promise.reject(error);
});

export default useAxios;

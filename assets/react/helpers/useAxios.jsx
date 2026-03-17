import axios from "axios";

const useAxios = axios.create({
    baseURL: import.meta.env.VITE_ENV === 'dev' ? 'http://127.0.0.1' : 'http://n8n.jamboree.cloud',
    headers: {
        'Accept': 'application/json+ld'
    }
});

useAxios.interceptors.request.use((config) => {
    const token = sessionStorage.getItem('access_token');

    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }

    return config;
}, (error) => {
    return Promise.reject(error);
});

export default useAxios;

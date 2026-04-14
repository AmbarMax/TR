import axios from "axios";
import refreshToken from "../services/refresh-token.js";
import store from "../store/store.js";
import router from "../router/router.js";

const api = axios.create();

api.interceptors.request.use(config => {
    if (localStorage.getItem('access_token')) {
        config.headers.authorization = `Bearer ${localStorage.getItem('access_token')}`
    }
    return config
}, error => {});

api.interceptors.response.use(config => {
    if (localStorage.getItem('access_token')) {
        config.headers.authorization = `Bearer ${localStorage.getItem('access_token')}`
    }
    return config
}, async (error) => {
    if (error.response.status === 401) {
        if (error.response.data.message === 'Token expired') {
            await refreshToken();
            return api(error.config);
        } else {
            localStorage.removeItem('access_token');
            localStorage.removeItem('user');
            store.state.authorized = false;
            if (!router.currentRoute.value.path.includes('virtual-hall')) {
                router.push('/login');
            }
        }
    }
    if (error.response.status === 402) {
        store.state.notification = {
            message: error.response.data.message,
            type: 'info',
            show: true
        }
    }
    return Promise.reject(error);
});

export default api;

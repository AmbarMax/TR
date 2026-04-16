import axios from 'axios';

// Bare axios for brand/bot endpoints — does NOT use the global auth interceptor
// (which would log the user out on 401). Errors here should fail silently.
const botApi = axios.create();

botApi.interceptors.request.use(config => {
    const token = localStorage.getItem('access_token');
    if (token) config.headers.authorization = `Bearer ${token}`;
    return config;
});

export default botApi;

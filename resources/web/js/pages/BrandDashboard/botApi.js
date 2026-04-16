import axios from 'axios';

// Bare axios for bot endpoints — does NOT use the global auth interceptor
// (which would log the user out on 401). Bot endpoints require bot_api_key,
// not user JWT, so 401/403 here is expected and should fail silently.
const botApi = axios.create();

botApi.interceptors.request.use(config => {
    const token = localStorage.getItem('access_token');
    if (token) config.headers.authorization = `Bearer ${token}`;
    return config;
});

export default botApi;

import api from "../api/api.js";
import store from "../store/store.js";
import router from "../router/router.js";

export default function refreshToken() {
    if (!store.state.refreshTokenPromise) {
        const refreshPromise = new Promise((resolve, reject) => {
            api.post('/api/refresh-token', {}).then(response => {
                if (response.status === 200) {
                    localStorage.setItem('access_token', response.data.token.access_token);
                    api.get('/api/profile').then(resp => {
                        if (resp.status === 200) {
                            const user = resp.data.user.data;
                            localStorage.setItem('user', JSON.stringify({
                                id: user.id,
                                name: user.name,
                                username: user.username,
                                email: user.email,
                                avatar: user.avatar,
                                background: user.background,
                                balances: user.balances,
                            }));
                            if (user.avatar === '/images/avatar/default-profile-img.png') {
                                store.state.userAvatar = '';
                            } else {
                                store.state.userAvatar = user.avatar;
                            }
                            store.state.userUsername = user.username;
                            store.state.google2fa_status = user.google2fa_status;
                            store.state.authorized = true;
                        }
                        resolve();
                    })
                }
            }).catch(error => {
                localStorage.removeItem('access_token');
                localStorage.removeItem('user');
                store.state.authorized = false;
                router.push('/login');
                reject(error);
            });
        });

        store.commit('setRefreshTokenPromise', refreshPromise);
    }

    return store.state.refreshTokenPromise;
}

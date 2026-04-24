import api from "../api/api.js";
import store from "../store/store.js";

export default function getProfileData()
{
    api.get('/api/profile').then( resp => {
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
                roles: user.roles ?? [],
                account_type: user.account_type ?? null,
                permissions: user.permissions ?? [],
                is_staff_legacy: user.is_staff_legacy ?? false,
            }));

            store.state.user.balances = user.balances;
            store.state.user.roles = user.roles ?? [];
            store.state.user.account_type = user.account_type ?? null;
            store.state.user.permissions = user.permissions ?? [];
            if (user.avatar === '/images/avatar/default-profile-img.png') {
                store.state.userAvatar = '';
            } else {
                store.state.userAvatar = user.avatar;
            }
            store.state.userUsername = user.username;
            store.state.google2fa_status = user.google2fa_status;
        }
    })
}

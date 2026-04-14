import { createRouter, createWebHistory } from "vue-router";
import routes from "./routes.js"

import VueJwtDecode from 'vue-jwt-decode';
import moment from "moment";
import store from "../store/store.js";


const router = createRouter({
    history: createWebHistory(),
    routes
});


router.beforeEach( (to, from, next) => {
    store.state.activeSideBar = false;
    store.state.userName = localStorage.getItem('user_name');
    store.state.userEmail = localStorage.getItem('user_email');
    // store.state.userAvatar = localStorage.getItem('user_avatar');
    store.state.userBackground = localStorage.getItem('user_background');
    // const now = new Date().getTime();
    // const token_expired = localStorage.getItem('access_token_expires_time');
    // if (now < token_expired) {
    //     store.state.authorized = true;
    // }

    const accessToken = localStorage.getItem('access_token');
    if (accessToken) {
        const token = VueJwtDecode.decode(accessToken);
        if(moment().isSameOrAfter(moment.unix(token.exp))) {
            // refreshToken();
        } else {
            store.state.authorized = true;
        }
    }

    if ((!publicPages(to.name)) && !accessToken) {
        return next({
            name: 'login'
        })
    }

    next();
});

function publicPages(name) {
    const pages = [
        'login', 'sign-up', 'forgot-password', 'reset-password','virtual-hall', 'reset-2fa'
    ];

    if(pages.includes(name)){ return true }
    else { return false }

}

export default router;

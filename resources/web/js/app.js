import '../../js/bootstrap.js';
import { createApp } from "vue";
import App from './App.vue';
import Router from "./router/router.js";
import Store from "./store/store.js";
import "../css/style.scss"
import '@vuepic/vue-datepicker/dist/main.css';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();

import.meta.glob([
    '../images/**',
]);

// Rehydrate selected user fields from localStorage BEFORE mount, so components using
// $store.state.user have correct values on first render after a page refresh.
// Tolerates malformed payloads silently (logs only).
try {
    const stored = JSON.parse(localStorage.getItem('user') || '{}');
    if (stored && stored.id) {
        const fieldsToHydrate = ['balances', 'roles', 'account_type', 'account_status', 'permissions'];
        fieldsToHydrate.forEach(key => {
            if (stored[key] !== undefined) {
                Store.state.user[key] = stored[key];
            }
        });
    }
} catch (e) {
    console.warn('User hydration from localStorage failed:', e);
}

createApp(App)
    .use(Router)
    .use(Store)
    .use(VueDatePicker)
    .mount('#web-app');



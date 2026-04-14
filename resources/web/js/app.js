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

createApp(App)
    .use(Router)
    .use(Store)
    .use(VueDatePicker)
    .mount('#web-app');



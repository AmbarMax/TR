<template>
    <div :class="['notification', getNotificationStatusClass()]">
        <img :src="icon[type]" alt="mark" class="notification__image">
        <p class="notification__text">
            {{ message }}
        </p>
        <img @click="closeNotification" src="../../../../../public/web/img/icons/close.svg" alt="close-button" class="notification__close-button">
    </div>
</template>

<script>
import store from "../../store/store.js";
export default {
    components: {
        store
    },
    data() {
        return {
            message: '',
            type: '',
            icon: {
                success: '/web/img/icons/notifications/success.svg',
                warning: '/web/img/icons/notifications/warning.svg',
                error: '/web/img/icons/notifications/error.svg',
                info: '/web/img/icons/notifications/info.svg',
            },
            currentIcon: ''
        }
    },
    mounted() {
        this.type = store.state.notification.type;
        this.message = store.state.notification.message;
        setTimeout(this.closeNotification, 3000);
    },
    methods: {
        closeNotification() {
            store.state.notification.show = false;
            store.state.notification.message = '';
        },
        getNotificationStatusClass() {
            return `notification_${this.type}`;
        },
    }
}
</script>

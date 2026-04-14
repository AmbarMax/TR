<template>
    <div class="auth_wrapper">
        <div class="auth_welcome_block">
            <div class="header_logo">
                <img src="../../../web/images/web/img/logo.svg" alt="logo">
            </div>
        </div>
        <div v-if="!status_success" class="auth_form_block">
            <h2 class="modal_header">
                Reset Password 🔒
            </h2>
            <h3 class="forgot_password_text">
                Your new password must be different from previously used passwords
            </h3>
            <h4 class="auth_label">New password</h4>
            <input type="password" class="modal_input" v-model="password">
            <h4 class="auth_label">Confirm password</h4>
            <input type="password" class="modal_input" v-model="confirm_password">
            <button-white :text="set_new_password_button" class="sign_in_button" @click="resetPassword"></button-white>
            <div class="modal_dont_have_account">
                <router-link to="/login" class="login-signup_link">
                    Back to login
                </router-link>
            </div>
        </div>
        <div v-else class="auth_form_block">
            <h2 class="modal_header">
                Password changed!
            </h2>
            <h3 class="forgot_password_text">
                The password has been successfully changed. You can return to the login page.
            </h3>
            <button-white :text="'Back to login'" class="sign_in_button" @click="redirectToLogin"></button-white>
        </div>
    </div>
</template>

<script>

import buttonWhite from "../parts/button.vue";
import store from "../store/store.js";
import router from "../router/router.js";
import api from "../api/api.js";
export default {
    components: {
        buttonWhite
    },
    data() {
        return {
            set_new_password_button: 'Set New Password',
            password: '',
            confirm_password: '',
            token: '',
            email: '',
            status_success: false,
        }
    },
    methods: {
        resetPassword() {
            if (this.password === this.confirm_password && this.password !== ""){
                api.post('/api/reset-password', {
                    password: this.password,
                    password_confirmation: this.confirm_password,
                    email: this.email,
                    token: this.token,
                }).then( response => {
                    if (response.status === 201) {
                        this.status_success = true;
                    }
                })
            }
        },
        redirectToLogin() {
            this.$router.push('/login');
        }
    },
    mounted: function () {
        const urlSearchParams = new URLSearchParams(new URL(window.location.href).search);
        this.token = urlSearchParams.get('token');
        this.email = urlSearchParams.get('email');
    }
}
</script>

<style scoped>
.forgot_password_text {
    margin-top: 12px;
    color: white;
    font-size: 18px;
    font-weight: 400;
    line-height: 150%;
}
</style>

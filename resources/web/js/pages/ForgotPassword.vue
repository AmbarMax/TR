<template>
    <div class="auth_wrapper forgot_password">
        <div class="auth_welcome_block">
            <div class="header_logo">
                <img src="../../../web/images/web/img/tr-isologo.svg" alt="logo">
            </div>
        </div>
        <div v-if="!status_success" class="auth_form_block no-bg">
            <h2 class="modal_header">
                Forgot Password? 🔒
            </h2>
            <h3 class="forgot_password_text">
                Enter your email and we'll send you instructions to reset your password
            </h3>
            <h4 class="auth_label">Email</h4>
            <input type="email" class="modal_input" v-model="email" placeholder="john@example.com">
            <button-white :text="send_reset_button" class="sign_in_button" @click="sendLink"></button-white>
            <div class="modal_dont_have_account">
                <router-link to="/login" class="login-signup_link">
                    Back to login
                </router-link>
            </div>
        </div>
        <div v-else class="auth_form_block">
            <h2 class="modal_header">
                Reset link sent to your email!
            </h2>
            <h3 class="forgot_password_text">
                Follow the link sent in the email to reset your password.
            </h3>
            <div class="modal_dont_have_account">
                <router-link to="/login" class="login-signup_link">
                    Back to login
                </router-link>
            </div>
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
            send_reset_button: 'Send reset link',
            email: '',
            status_success: false,
        }
    },
    methods: {
        sendLink() {
            api.post('/api/forgot-password', {
                email: this.email,
            }).then( response => {
                if (response.status === 201) {
                    this.status_success = true;
                }
            })
        },
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

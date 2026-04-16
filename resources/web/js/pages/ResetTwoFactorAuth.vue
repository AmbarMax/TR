<template>
    <div class="auth_wrapper">
        <div class="auth_welcome_block">
            <div class="header_logo">
                <img src="../../../web/images/web/img/tr-isologo.png" alt="logo">
            </div>
        </div>
        <div v-if="!status" class="auth_form_block">
            <h2 class="modal_header">
                Reset two-factor authentication 🔒
            </h2>
            <h3 class="modal_label">
                To reset two-factor authentication, enter the code you received after enabling two-factor authentication.
            </h3>
            <input style="margin-top: 24px" type="text" class="modal_input" v-model="secret_key">
            <span class="validation_error" v-if="twoFactorErrorStatus">{{twoFactorErrorStatus}}</span>
            <button-white :text="'Confirm'" class="sign_in_button" @click="reset2fa"></button-white>
            <h3 style="font-size: 12px" class="modal_label">
                If you have any problems, contact support <strong>{{ support_email }}</strong>.
            </h3>
        </div>
        <div v-if="status" class="auth_form_block">
            <h2 class="modal_header">
                Two-factor authentication 🔒
            </h2>
            <h3 class="modal_label">
                Two-factor authentication has been successfully disabled. You can return to the login page.
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
import getProfileData from "../services/profile-data.js";
export default {
    components: {
        buttonWhite
    },
    data() {
        return {
            status: false,
            twoFactorErrorStatus: '',
            secret_key: '',
            support_email: '',
        }
    },
    methods: {
        reset2fa() {
            const urlSearchParams = new URLSearchParams(new URL(window.location.href).search);
            const email = urlSearchParams.get('email');
            api.post('/api/reset-2fa', {
                email: email,
                secret_key: this.secret_key,
            }).then( response => {
                if (response.status === 200) {
                    this.status = true;
                }
            }).catch(error => {
                if (error.response.status === 400){
                    this.twoFactorErrorStatus = error.response.data.message;
                }
            });

        },
        redirectToLogin() {
            this.$router.push('/login');
        },
        getSupportEmail(){
            api.get('/api/get-current-email').then( response => {
                if (response.status === 200) {
                    this.support_email = response.data;
                }
            })
        }
    },
    mounted: function () {
       this.getSupportEmail();
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

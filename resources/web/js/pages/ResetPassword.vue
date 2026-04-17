<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Reset password</h1>
      <p class="auth-tagline">Choose a new password for your account.</p>
    </div>

    <!-- Reset Form -->
    <div class="auth-card" v-if="!status_success">
      <div class="auth-field">
        <label class="auth-label">New password</label>
        <input type="password" class="auth-input" v-model="password">
      </div>

      <div class="auth-field">
        <label class="auth-label">Confirm password</label>
        <input type="password" class="auth-input" v-model="confirm_password">
      </div>

      <div class="auth-submit">
        <button-white :text="set_new_password_button" @click="resetPassword"></button-white>
      </div>

      <div class="auth-footer">
        <router-link to="/login" class="auth-link">Back to login</router-link>
      </div>
    </div>

    <!-- Success State -->
    <div class="auth-card" v-else style="text-align: center;">
      <div class="auth-success-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c1f527" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <h2 class="auth-title" style="font-size: 18px;">Password changed!</h2>
      <p class="auth-success-message">Your password has been updated. You can now sign in.</p>
      <div class="auth-submit" style="margin-top: 20px;">
        <button-white :text="'Back to login'" @click="redirectToLogin"></button-white>
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


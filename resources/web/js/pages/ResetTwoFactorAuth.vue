<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Reset two-factor auth</h1>
      <p class="auth-tagline">Enter your recovery code to disable 2FA.</p>
    </div>

    <!-- Reset 2FA Form -->
    <div class="auth-card" v-if="!status">
      <div class="auth-field">
        <label class="auth-label">Recovery code</label>
        <input type="text" class="auth-input auth-2fa-input" v-model="secret_key">
        <span class="auth-error" v-if="twoFactorErrorStatus">{{ twoFactorErrorStatus }}</span>
      </div>
      <p class="auth-2fa-help">
        Having trouble? Contact support at <strong>{{ support_email }}</strong>
      </p>
      <div class="auth-submit">
        <button-white :text="'Confirm'" @click="reset2fa"></button-white>
      </div>
      <div class="auth-footer">
        <router-link to="/login" class="auth-link">Back to login</router-link>
      </div>
    </div>

    <!-- Success State -->
    <div class="auth-card" v-if="status" style="text-align: center;">
      <div class="auth-success-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c1f527" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <h2 class="auth-title" style="font-size: 18px;">2FA disabled</h2>
      <p class="auth-success-message">Two-factor authentication has been successfully disabled.</p>
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


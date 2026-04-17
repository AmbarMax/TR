<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Forgot password?</h1>
      <p class="auth-tagline">We'll send you a reset link.</p>
    </div>

    <!-- Email Form -->
    <div class="auth-card" v-if="!status_success">
      <div class="auth-field">
        <label class="auth-label">Email</label>
        <input type="email" class="auth-input" v-model="email" placeholder="player@example.com">
      </div>

      <div class="auth-submit">
        <button-white :text="send_reset_button" @click="sendLink"></button-white>
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
      <h2 class="auth-title" style="font-size: 18px;">Reset link sent!</h2>
      <p class="auth-success-message">Check your email and follow the link to reset your password.</p>
      <div class="auth-footer" style="margin-top: 24px;">
        <router-link to="/login" class="auth-link">Back to login</router-link>
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


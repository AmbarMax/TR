<template>
  <div class="forgot-card">
    <!-- Back link -->
    <router-link to="/login" class="forgot-back">
      <span>←</span>
      <span>Back to login</span>
    </router-link>

    <!-- Form state -->
    <div v-if="!status_success">
      <div class="forgot-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </div>
      <h2 class="forgot-title">Reset your password</h2>
      <p class="forgot-sub">Enter the email associated with your TrophyRoom account. We'll send you a link to reset your password.</p>

      <div class="form-field">
        <label class="form-label">Email</label>
        <input type="email" class="form-input" v-model="email" placeholder="you@example.com" @keyup.enter="sendLink" />
      </div>

      <button class="form-submit" @click="sendLink">
        <span>{{ send_reset_button }}</span>
        <span class="arrow">→</span>
      </button>
    </div>

    <!-- Success state -->
    <div v-else class="forgot-success">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="forgot-success-icon">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
      <h2 class="forgot-title">Reset link sent!</h2>
      <p class="forgot-sub">Check your email and follow the link to reset your password.</p>
    </div>

    <div class="form-footer">
      Remembered it? <router-link to="/login">Back to login →</router-link>
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

<style lang="scss" scoped>
.forgot-card {
  width: 100%; max-width: 460px;
  padding: 56px 48px;
  background: rgba(10,11,13,0.85);
  border: 1px solid rgba(42,44,46,0.8);
  box-shadow: 0 30px 80px rgba(0,0,0,0.6), inset 0 1px 0 rgba(254,237,223,0.03);
}
.forgot-back {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 10px; color: var(--text-muted);
  letter-spacing: 0.22em; text-transform: uppercase;
  margin-bottom: 36px;
  transition: color 0.15s;
  text-decoration: none;
}
.forgot-back:hover { color: var(--primary); }

.forgot-icon {
  width: 56px; height: 56px;
  border: 1px solid rgba(255,97,0,0.3);
  background: rgba(255,97,0,0.05);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 28px;
  color: var(--primary);
}

.forgot-title {
  font-family: var(--display);
  font-size: 40px; line-height: 1;
  color: var(--text);
  margin-bottom: 12px;
  letter-spacing: 0.015em;
}
.forgot-sub {
  font-size: 13px; color: var(--text-muted);
  line-height: 1.7;
  margin-bottom: 32px;
}

.forgot-success { text-align: center; }
.forgot-success-icon {
  display: block;
  margin: 0 auto 20px;
  color: var(--accent);
  filter: drop-shadow(0 0 10px var(--accent-glow));
}
.forgot-success .forgot-title { margin-bottom: 12px; }

/* Shared form styles */
.form-field { margin-bottom: 14px; }
.form-label {
  display: block; font-size: 10px; color: var(--text-muted);
  letter-spacing: 0.18em; text-transform: uppercase;
  margin-bottom: 6px;
}
.form-input {
  width: 100%; padding: 12px 14px;
  background: rgba(14,15,17,0.6);
  border: 1px solid var(--border);
  color: var(--text); font-family: var(--mono);
  font-size: 13px; letter-spacing: 0.02em;
  transition: border-color 0.15s, background 0.15s;
  box-sizing: border-box;
}
.form-input:focus {
  outline: none; border-color: var(--primary);
  background: rgba(255,97,0,0.03);
  box-shadow: 0 0 0 1px rgba(255,97,0,0.15);
}
.form-input::placeholder { color: var(--text-dim); }

.form-submit {
  width: 100%; padding: 15px;
  background: var(--primary); color: var(--bg);
  border: 1px solid var(--primary);
  font-family: var(--mono); font-size: 12px;
  letter-spacing: 0.25em; text-transform: uppercase;
  margin-top: 18px; cursor: pointer;
  transition: all 0.15s;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  box-shadow: 0 0 20px rgba(255,97,0,0.25);
}
.form-submit:hover {
  background: #ff7e2e; border-color: #ff7e2e;
  box-shadow: 0 0 32px rgba(255,97,0,0.5);
}
.form-submit .arrow { transition: transform 0.15s; }
.form-submit:hover .arrow { transform: translateX(4px); }

.form-footer {
  margin-top: 24px; padding-top: 20px;
  border-top: 1px solid rgba(42,44,46,0.6);
  text-align: center; font-size: 12px;
  color: var(--text-muted); letter-spacing: 0.02em;
}
.form-footer a {
  color: var(--accent);
  text-decoration: none;
}

@media (max-width: 500px) {
  .forgot-card { padding: 40px 28px; }
  .forgot-title { font-size: 32px; }
}
</style>

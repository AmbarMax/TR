<template>
  <div class="auth-card">

    <!-- LEFT SIDE — narrative immersion -->
    <div class="auth-left">
      <div class="auth-left-glow"></div>
      <div class="auth-left-scanlines"></div>

      <!-- Brand -->
      <div class="auth-brand">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" class="auth-logo" />
        <span class="auth-brand-name">Trophy<span class="dot">Room</span></span>
      </div>

      <!-- Constellation: T-Rex + 10 orbiting badges (all synced for login) -->
      <div class="auth-constellation">
        <div class="constellation-inner">
          <div class="constellation-rings"></div>
          <div class="mascot-halo"></div>

          <img :src="trexHero" alt="TrophyRoom mascot" class="mascot-center" />

          <div class="orbit-badge synced" style="top:0;left:50%;transform:translateX(-50%)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="top:12%;right:5%">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="top:38%;right:-8%">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L3 7v10l9 5 9-5V7l-9-5zm7 14l-7 4-7-4V8l7-4 7 4v8z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:25%;right:0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.9 2.9 1.3 3.6 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.2-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.7.2 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:5%;right:20%">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116-3.065 6.076-5.15-10.098H1.61l8.423 16.595 3.065-6.076 2.089 4.116 6.203-12.285h-3.473zM18.897 0l-2.797 5.516-2.796-5.516h-3.473l6.269 12.393L22.369 0z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:5%;left:20%">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3.537 0C2.165 0 1.66.506 1.66 1.879V22.12c0 1.374.504 1.88 1.877 1.88h16.926c1.374 0 1.877-.506 1.877-1.88V1.88C22.34.506 21.837 0 20.463 0zm9.27 3.958h4.076v1.04h-2.86v2.544h2.548v1.04H13.98v2.78h2.94v1.04h-4.112zm-5.775 0h1.235l1.87 5.206 1.868-5.206h1.175L10.99 12.37H9.555zM4.59 3.958h4.17v1.04H5.825v2.544h2.6v1.04h-2.6v2.78h2.998v1.04H4.59z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:25%;left:0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8.985 2.596v17.548l3.915 1.261V6.688c0-.69.304-1.151.794-.991.636.18.76.814.76 1.505v5.876c2.441 1.193 4.362-.002 4.362-3.153 0-3.21-1.263-4.834-4.659-5.94C12.408 3.453 10.21 2.906 8.985 2.596zm4.608 16.49l6.033-2.169c.684-.263.79-.603.237-.756-.552-.153-1.553-.11-2.236.153l-4.034 1.46v-1.97l.233-.083s1.166-.422 2.808-.602c1.642-.18 3.65.024 5.228.627 1.782.539 1.981 1.336 1.527 1.882-.455.547-1.565.948-1.565.948l-8.23 2.983v-2.473z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="top:38%;left:-8%">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M4.102 21.033C6.211 22.881 8.977 24 12 24c3.026 0 5.789-1.119 7.902-2.967-1.998-1.109-5.653-5.209-7.902-7.77-2.249 2.561-5.904 6.661-7.898 7.77zM12 3.192c1.584 1.593 3.182 3.576 4.486 5.744 2.373 3.941 3.58 7.602 3.67 9.145A9.946 9.946 0 0 0 22 12c0-3.044-1.363-5.768-3.512-7.602zM5.512 4.398A9.946 9.946 0 0 0 2 12c0 2.581.977 4.934 2.582 6.713.597-.908 2.417-4.989 3.67-9.145C9.564 6.598 11.001 4.612 12 3.192zM12 0C8.678 0 5.683 1.355 3.592 3.571c1.658-.453 3.592.027 5.169 1.078C10.218 5.763 11.206 7.315 12 8.88c.794-1.565 1.782-3.117 3.239-4.231 1.577-1.051 3.511-1.531 5.169-1.078C18.317 1.355 15.322 0 12 0z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="top:12%;left:5%">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M0 .6h7.1c2.5 0 4.5 1.3 5.6 3.2H0V.6zm0 4.8h13.4c.3.8.5 1.7.5 2.6s-.2 1.8-.5 2.6H0V5.4zM0 12h12.7c-1.1 1.9-3.1 3.2-5.6 3.2H0V12zm16.8-11.4c4 0 7.2 3.2 7.2 7.2v8.4c0 4-3.2 7.2-7.2 7.2s-7.2-3.2-7.2-7.2V7.8c0-4 3.2-7.2 7.2-7.2zm0 3.6a2.4 2.4 0 1 0 0 4.8 2.4 2.4 0 0 0 0-4.8z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:12%;left:50%;transform:translateX(-50%)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 0 1-.61-.92V2.734a1 1 0 0 1 .609-.92zm10.89 10.893l2.302 2.302-10.937 6.333zm2.302-3.516L6.864 2.858l10.937 6.333zM20.16 10.95L17.6 9.468l-2.523 2.531 2.523 2.531 2.56-1.482a1.123 1.123 0 0 0 0-2.098z"/></svg>
            <span class="plat-dot"></span>
          </div>
        </div>
      </div>

      <!-- Narrative copy -->
      <div class="auth-copy">
        <div class="auth-copy-tag">Welcome back</div>
        <h2 class="auth-copy-title">Your vault<br>is <span class="accent">waiting</span>.</h2>
        <p class="auth-copy-sub">Pick up where you left off.</p>
      </div>
    </div>

    <!-- RIGHT SIDE — form -->
    <div class="auth-right">
      <div class="form-head">
        <div class="form-head-label">Log in</div>
        <h3 class="form-head-title">Enter the room</h3>
        <p class="form-head-sub">Sign in with your preferred platform or use your TrophyRoom credentials.</p>
      </div>

      <!-- Social auth buttons -->
      <div class="social-row">
        <div class="social-btn discord">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/></svg>
          <span>Discord</span>
        </div>
        <div class="social-btn steam" @click="loginSteam">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>
          <span>Steam</span>
        </div>
        <div class="social-btn github" @click="loginGithub">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.9 2.9 1.3 3.6 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.2-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.7.2 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3"/></svg>
          <span>GitHub</span>
        </div>
        <div class="social-btn google" @click="loginGoogle">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M21.35 11.1h-9.17v2.87h5.28c-.23 1.36-1.66 3.98-5.28 3.98-3.18 0-5.77-2.63-5.77-5.87s2.59-5.87 5.77-5.87c1.81 0 3.02.77 3.71 1.43l2.53-2.44C16.82 3.62 14.76 2.7 12.18 2.7 6.98 2.7 2.77 6.91 2.77 12.1s4.21 9.4 9.41 9.4c5.43 0 9.03-3.82 9.03-9.18 0-.62-.07-1.09-.16-1.22z"/></svg>
          <span>Google</span>
        </div>
      </div>

      <div class="divider">Or continue with email</div>

      <!-- LOGIN FORM — Block 1 -->
      <div v-if="!twoFactorAuth && !twoFactorReset">
        <div class="form-field">
          <label class="form-label">Email</label>
          <input type="email" class="form-input" v-model="email" placeholder="player@example.com" />
          <span class="form-error" v-if="errorLogin">Invalid credentials</span>
        </div>

        <div class="form-field">
          <div class="form-label-row">
            <label class="form-label">Password</label>
            <router-link to="/forgot-password" class="form-forgot">Forgot?</router-link>
          </div>
          <div class="form-password-wrap">
            <input
              ref="passwordInput"
              type="password"
              class="form-input"
              v-model="password"
              placeholder="••••••••"
              @keyup.enter="signIn"
            />
            <span class="form-eye" @click="togglePassView">{{ eyeIsOpen ? 'Hide' : 'Show' }}</span>
          </div>
        </div>

        <button class="form-submit" @click="signIn">
          <span>{{ sign_in_button_text }}</span>
          <span class="arrow">→</span>
        </button>

        <div class="form-footer">
          Don't have an account? <router-link to="/sign-up">Create one →</router-link>
        </div>
      </div>

      <!-- 2FA FORM — Block 2 -->
      <div v-else-if="twoFactorAuth && !twoFactorReset">
        <div class="form-field">
          <label class="form-label">One-time code</label>
          <input
            type="text"
            class="form-input form-2fa-input"
            v-model="twoFactorCode"
            @input="formatInput"
            placeholder="000000"
            @keyup.enter="signInContinue"
          />
          <span class="form-error" v-if="twoFactorErrorStatus">{{ twoFactorErrorMessage }}</span>
        </div>

        <p class="form-helper-text">
          Don't have access? <a class="form-link" @click="disable2FA">Use recovery code</a>
        </p>

        <button class="form-submit" @click="signInContinue">
          <span>Continue</span>
          <span class="arrow">→</span>
        </button>
      </div>

      <!-- 2FA RESET CONFIRMATION — Block 3 -->
      <div v-else-if="twoFactorReset" class="tfa-reset-confirm">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="tfa-check-icon">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
          <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <h2 class="tfa-reset-title">Check your email</h2>
        <p class="tfa-reset-sub">We've sent you instructions to reset your 2FA. Follow the link in the email.</p>
        <router-link to="/login" class="form-link" @click="goToLoginPage">Back to login →</router-link>
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
import trexHero from '../../../web/images/web/img/mascot/trex-voxel-hero.png';

export default {
    components: {
        buttonWhite
    },
    data() {
        return {
            trexHero,
            sign_in_button_text: 'Sign In',
            git_hub_button_text: 'Continue with GitHub',
            git_hub_button_image: 'github',
            discord_button_text: 'Continue with Discord',
            discord_button_image: 'discord',
            steam_button_text: 'Continue with Steam',
            steam_button_image: 'steam',
            email: '',
            password: '',
            eyeIsOpen: false,
            errorLogin: false,
            twoFactorAuth: false,
            twoFactorCode: null,
            twoFactorErrorStatus: false,
            twoFactorErrorMessage: '',
            twoFactorReset: false,
        }
    },
    methods: {
        goToLoginPage() {
            this.twoFactorReset = false;
            this.twoFactorAuth = false;
        },
        closeSignIn() {
            store.state.signUpModalOpen = false;
        },
        disable2FA() {
            this.$router.push('/reset-2fa?email=' + this.email);
        },
        signIn() {
            api.post('/api/startLogin', {
                email: this.email,
                password: this.password
            }).then( response => {
                if (response.status === 202) {
                    this.twoFactorAuth = !this.twoFactorAuth;
                } else if (response.status === 200) {
                    localStorage.setItem('access_token', response.data.token.access_token);
                    localStorage.setItem('centrifugo_token', response.data.token.centrifugo_token);
                    getProfileData();
                    if (localStorage.getItem('access_token')){
                        this.checkAuth();
                        router.push('/dashboard');
                    }
                }
            })
            .catch(error => {
                this.errorLogin = true;
            });
        },
        signInContinue() {
            this.twoFactorErrorStatus = false;
            api.post('/api/login', {
                email: this.email,
                password: this.password,
                one_time_password: this.twoFactorCode
            }).then( response => {
                if (response.status === 200) {
                    localStorage.setItem('access_token', response.data.token.access_token);
                    localStorage.setItem('centrifugo_token', response.data.token.centrifugo_token);
                    getProfileData();
                    if (localStorage.getItem('access_token')){
                        this.checkAuth();
                        api.get('/api/badges/').then( response => {
                            if (response.status === 200) {
                                if (response.data.data.length == 0){
                                    store.state.importBudgesModalOpen = true;
                                }
                            }
                        })
                        router.push('/dashboard');
                    }
                }
            })
            .catch(error => {
                if ((error.response.status === 400 && error.response.data.message === 'Invalid code')) {
                    this.twoFactorErrorStatus = true;
                    this.twoFactorErrorMessage = error.response.data.message;
                } else if (error.response.status === 422 && error.response.data.message === 'The one time password field is required.') {
                    this.twoFactorErrorStatus = true;
                    this.twoFactorErrorMessage = error.response.data.message;
                }
            });
        },
        checkAuth(){
            // 2FA prompt removed — opt-in from Profile settings only
        },
        togglePassView() {
            this.eyeIsOpen = !this.eyeIsOpen;
            if (this.$refs.passwordInput.type === 'password') {
                this.$refs.passwordInput.type = 'text';
            } else {
                this.$refs.passwordInput.type = 'password';
            }
        },
        formatInput() {
            this.twoFactorErrorStatus = false;
            this.twoFactorCode = this.twoFactorCode.replace(/\D/g, '');
            if (this.twoFactorCode.length >= 6) {
                this.twoFactorCode = this.twoFactorCode.substring(0, 6);
            }
        },
        loginSteam() {
            window.location.href = '/login/steam';
        },
        loginGithub() {
            window.location.href = '/login/github';
        },
        loginGoogle() {
            window.location.href = '/login/google';
        }
    }

}
</script>

<style lang="scss" scoped>
/* ═══ SPLIT CARD ═══ */
.auth-card {
  width: 100%; max-width: 1080px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  background: rgba(10,11,13,0.85);
  border: 1px solid rgba(42,44,46,0.8);
  box-shadow: 0 30px 80px rgba(0,0,0,0.6), inset 0 1px 0 rgba(254,237,223,0.03);
  overflow: hidden;
}

/* ═══ LEFT SIDE ═══ */
.auth-left {
  position: relative;
  padding: 56px 48px;
  border-right: 1px solid rgba(42,44,46,0.6);
  background:
    linear-gradient(135deg, rgba(255,97,0,0.04), transparent 50%),
    rgba(14,15,17,0.6);
  overflow: hidden;
  min-height: 600px;
  display: flex; flex-direction: column;
}
.auth-left-glow {
  position: absolute; inset: 0; pointer-events: none;
  background: radial-gradient(ellipse 500px 400px at 50% 40%, rgba(255,97,0,0.12), transparent 60%);
}
.auth-left-scanlines {
  position: absolute; inset: 0; pointer-events: none;
  background: repeating-linear-gradient(0deg, rgba(0,0,0,0.08) 0px, rgba(0,0,0,0.08) 1px, transparent 1px, transparent 4px);
  mix-blend-mode: multiply;
}

/* Brand */
.auth-brand {
  position: relative; z-index: 2;
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 36px;
}
.auth-logo {
  width: 32px; height: 32px;
  filter: drop-shadow(0 0 8px rgba(255,97,0,0.3));
}
.auth-brand-name {
  font-family: var(--display);
  font-size: 22px;
  color: var(--text);
  letter-spacing: 0.02em;
}
.auth-brand-name .dot { color: var(--primary); }

/* Constellation */
.auth-constellation {
  position: relative; z-index: 2;
  flex: 1;
  display: flex; align-items: center; justify-content: center;
  margin: 20px 0;
}
.constellation-inner {
  position: relative;
  width: 280px; height: 280px;
}
.constellation-rings {
  position: absolute; inset: 0;
  border: 1px dashed rgba(255,97,0,0.15);
  border-radius: 50%;
  animation: slow-spin 40s linear infinite;
}
.constellation-rings::before {
  content: '';
  position: absolute; inset: 30px;
  border: 1px dashed rgba(193,245,39,0.12);
  border-radius: 50%;
  animation: slow-spin-rev 30s linear infinite;
}
@keyframes slow-spin { from { transform: rotate(0); } to { transform: rotate(360deg); } }
@keyframes slow-spin-rev { from { transform: rotate(0); } to { transform: rotate(-360deg); } }

.mascot-halo {
  position: absolute;
  top: 50%; left: 50%;
  width: 160px; height: 160px;
  transform: translate(-50%, -50%);
  background: radial-gradient(circle, rgba(255,97,0,0.25), transparent 60%);
  filter: blur(18px);
  animation: halo-pulse 3s ease-in-out infinite;
  pointer-events: none;
}
@keyframes halo-pulse {
  0%, 100% { opacity: 0.7; }
  50% { opacity: 1; }
}

.mascot-center {
  position: absolute;
  top: 50%; left: 50%;
  width: 170px; height: 170px;
  transform: translate(-50%, -50%);
  object-fit: contain;
  animation: mascot-float 4s ease-in-out infinite;
  filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));
  z-index: 2;
}
@keyframes mascot-float {
  0%, 100% { transform: translate(-50%, -50%); }
  50% { transform: translate(-50%, calc(-50% - 8px)); }
}

.orbit-badge {
  position: absolute;
  width: 38px; height: 38px;
  display: flex; align-items: center; justify-content: center;
  background: rgba(14,15,17,0.85);
  border: 1px solid rgba(193,245,39,0.35);
  z-index: 3;
  transition: all 0.25s;
}
.orbit-badge.synced {
  color: var(--accent);
  box-shadow: 0 0 10px rgba(193,245,39,0.25);
}
.orbit-badge.synced:hover {
  transform: scale(1.1);
  box-shadow: 0 0 16px rgba(193,245,39,0.5);
}
.orbit-badge .plat-dot {
  position: absolute;
  top: -3px; right: -3px;
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 6px var(--accent);
}

/* Narrative copy */
.auth-copy {
  position: relative; z-index: 2;
  margin-top: auto;
}
.auth-copy-tag {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.32em; text-transform: uppercase;
  margin-bottom: 16px;
  display: inline-flex; align-items: center; gap: 10px;
}
.auth-copy-tag::before {
  content: ''; width: 28px; height: 1px; background: var(--primary);
  box-shadow: 0 0 6px var(--primary);
}
.auth-copy-title {
  font-family: var(--display);
  font-size: 48px; line-height: 0.95;
  color: var(--text); letter-spacing: 0.015em;
  margin-bottom: 14px;
  text-shadow: 0 0 30px rgba(255,97,0,0.18);
}
.auth-copy-title .accent {
  color: var(--primary);
  text-shadow: 0 0 22px var(--primary-glow);
}
.auth-copy-sub {
  font-size: 13px; color: var(--text-muted);
  letter-spacing: 0.04em; line-height: 1.6;
  max-width: 360px;
}

/* ═══ RIGHT SIDE (FORM) ═══ */
.auth-right {
  padding: 56px 48px;
  display: flex; flex-direction: column;
  justify-content: center;
}

.form-head {
  margin-bottom: 28px;
}
.form-head-label {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.32em; text-transform: uppercase;
  margin-bottom: 12px;
  display: inline-flex; align-items: center; gap: 10px;
}
.form-head-label::before {
  content: ''; width: 20px; height: 1px; background: var(--primary);
}
.form-head-title {
  font-family: var(--display);
  font-size: 36px; line-height: 1;
  color: var(--text); letter-spacing: 0.015em;
  margin-bottom: 8px;
}
.form-head-sub {
  font-size: 12px; color: var(--text-muted);
  letter-spacing: 0.03em; line-height: 1.6;
}

/* Social row */
.social-row {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;
  margin-bottom: 20px;
}
.social-btn {
  padding: 12px 10px;
  background: rgba(14,15,17,0.6);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center; gap: 8px;
  font-family: var(--mono); font-size: 11px;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.15s;
}
.social-btn:hover {
  background: rgba(255,97,0,0.04);
  border-color: var(--primary);
  color: var(--text);
}
.social-btn.discord:hover { color: #5865F2; border-color: #5865F2; }
.social-btn.steam:hover   { color: #66c0f4; border-color: #66c0f4; }
.social-btn.github:hover  { color: var(--text); border-color: var(--text); }
.social-btn.google:hover  { color: #4285F4; border-color: #4285F4; }

.divider {
  display: flex; align-items: center; gap: 12px;
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.18em; text-transform: uppercase;
  margin: 18px 0;
}
.divider::before, .divider::after {
  content: ''; flex: 1; height: 1px;
  background: linear-gradient(90deg, transparent, var(--border), transparent);
}

/* Form fields */
.form-field { margin-bottom: 14px; }
.form-label {
  display: block; font-size: 10px; color: var(--text-muted);
  letter-spacing: 0.18em; text-transform: uppercase;
  margin-bottom: 6px;
}
.form-label-row {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 6px;
}
.form-label-row .form-label { margin-bottom: 0; }
.form-forgot {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.14em; text-transform: uppercase;
  text-decoration: none;
  transition: opacity 0.15s;
}
.form-forgot:hover { opacity: 0.7; }

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
.form-error {
  display: block;
  font-size: 11px; color: #e24b4a;
  letter-spacing: 0.02em;
  margin-top: 6px;
}
.form-2fa-input {
  text-align: center;
  font-family: var(--display);
  font-size: 28px;
  letter-spacing: 0.4em;
}

.form-password-wrap { position: relative; }
.form-password-wrap .form-input { padding-right: 60px; }
.form-eye {
  position: absolute; right: 14px; top: 50%;
  transform: translateY(-50%);
  font-size: 10px; letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--text-muted); cursor: pointer;
  transition: color 0.15s;
}
.form-eye:hover { color: var(--primary); }

.form-helper-text {
  font-size: 11px; color: var(--text-muted);
  margin: 4px 0 14px;
}
.form-link {
  color: var(--primary); cursor: pointer;
  text-decoration: none;
}
.form-link:hover { opacity: 0.7; }

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
  margin-top: 22px; padding-top: 20px;
  border-top: 1px solid rgba(42,44,46,0.6);
  text-align: center; font-size: 12px;
  color: var(--text-muted); letter-spacing: 0.02em;
}
.form-footer a {
  color: var(--accent);
  text-decoration: none;
}

.tfa-reset-confirm {
  text-align: center;
  padding: 20px 0;
}
.tfa-check-icon {
  color: var(--accent);
  filter: drop-shadow(0 0 10px var(--accent-glow));
  margin-bottom: 20px;
}
.tfa-reset-title {
  font-family: var(--display);
  font-size: 28px;
  color: var(--text);
  letter-spacing: 0.015em;
  margin-bottom: 10px;
}
.tfa-reset-sub {
  font-size: 12px; color: var(--text-muted);
  line-height: 1.6;
  margin-bottom: 22px;
  max-width: 320px;
  margin-left: auto; margin-right: auto;
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 900px) {
  .auth-card {
    grid-template-columns: 1fr;
    max-width: 560px;
  }
  .auth-left {
    min-height: 360px;
    padding: 36px 28px;
    border-right: none;
    border-bottom: 1px solid rgba(42,44,46,0.6);
  }
  .auth-right { padding: 36px 28px; }
  .constellation-inner { width: 220px; height: 220px; }
  .mascot-center { width: 130px; height: 130px; }
  .auth-copy-title { font-size: 36px; }
  .form-head-title { font-size: 28px; }
}
@media (max-width: 500px) {
  .auth-left { padding: 28px 20px; min-height: 320px; }
  .auth-right { padding: 28px 20px; }
  .constellation-inner { width: 180px; height: 180px; }
  .mascot-center { width: 100px; height: 100px; }
  .auth-copy-title { font-size: 28px; }
  .form-head-title { font-size: 24px; }
  .social-row { grid-template-columns: 1fr; }
}
</style>

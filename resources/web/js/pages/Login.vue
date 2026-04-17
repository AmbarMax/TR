<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Sign in to TrophyRoom</h1>
      <p class="auth-tagline">Your achievements. One place.</p>
    </div>

    <!-- Login Form -->
    <div class="auth-card" v-if="!twoFactorAuth && !twoFactorReset">
      <div class="auth-field">
        <label class="auth-label">Username or email</label>
        <input type="email" class="auth-input" v-model="email" placeholder="player@example.com">
        <span class="auth-error" v-if="errorLogin">Invalid credentials</span>
      </div>

      <div class="auth-field">
        <div class="auth-label-row">
          <label class="auth-label" style="margin-bottom: 0;">Password</label>
          <a href="/forgot-password" class="auth-forgot-link">Forgot?</a>
        </div>
        <div class="auth-password-wrap">
          <input type="password" class="auth-input" v-model="password" ref="passwordInput" @keyup.enter="signIn">
          <span class="auth-password-toggle" @click="togglePassView">
            <img v-if="eyeIsOpen" src="../../../web/images/web/img/icons/eye-open.svg" alt="show">
            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="hide">
          </span>
        </div>
      </div>

      <div class="auth-submit">
        <button-white :text="sign_in_button_text" @click="signIn"></button-white>
      </div>

      <div class="auth-footer">
        <span>Don't have an account? </span>
        <router-link to="/sign-up" class="auth-link">Sign up</router-link>
      </div>
    </div>

    <!-- 2FA Form -->
    <div class="auth-card" v-else-if="twoFactorAuth && !twoFactorReset">
      <div class="auth-field">
        <label class="auth-label">One-time code</label>
        <input type="text" class="auth-input auth-2fa-input" v-model="twoFactorCode" @input="formatInput" placeholder="000000">
        <span class="auth-error" v-if="twoFactorErrorStatus">{{ twoFactorErrorMessage }}</span>
      </div>
      <p class="auth-2fa-help">
        Don't have access? <a @click="disable2FA" href="#">Use recovery code</a>
      </p>
      <div class="auth-submit">
        <button-white :text="'Continue'" @click="signInContinue"></button-white>
      </div>
    </div>

    <!-- 2FA Reset Confirmation -->
    <div class="auth-card" v-else-if="twoFactorReset" style="text-align: center;">
      <div class="auth-success-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c1f527" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <h2 class="auth-title" style="font-size: 18px;">Reset link sent!</h2>
      <p class="auth-success-message">Check your email and follow the link to reset your password.</p>
      <div class="auth-footer" style="margin-top: 24px;">
        <router-link to="/login" class="auth-link" @click="goToLoginPage">Back to login</router-link>
      </div>
    </div>

    <!-- Social Login -->
    <div class="auth-social" v-if="!twoFactorAuth && !twoFactorReset">
      <div class="auth-social-divider">
        <div class="auth-social-divider-line"></div>
        <span class="auth-social-divider-text">or continue with</span>
        <div class="auth-social-divider-line"></div>
      </div>
      <div class="auth-social-buttons">
        <div class="auth-social-btn">Discord</div>
        <div class="auth-social-btn" @click="loginSteam">Steam</div>
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
                        router.push('/trophy-room');
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
                        router.push('/trophy-room');
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
/*
            window.location.href = '/login/steam';
*/
        },
        loginGithub() {
/*
            window.location.href = '/login/github';
*/
        }
    }

}
</script>

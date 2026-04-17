<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-tagline">Start building your trophy case.</p>
    </div>

    <!-- Signup Form -->
    <div class="auth-card">
      <div class="auth-field">
        <label class="auth-label">Name</label>
        <input type="text" class="auth-input" v-model="name" :class="{ 'has-error': getError('name') }" placeholder="Your name">
        <span class="auth-error" v-if="getError('name')">{{ getError('name') }}</span>
      </div>

      <div class="auth-field">
        <label class="auth-label">Username</label>
        <input type="text" class="auth-input" v-model="username" @input="filterUsername" :class="{ 'has-error': getError('username') }" placeholder="Choose a username">
        <span class="auth-error" v-if="getError('username')">{{ getError('username') }}</span>
      </div>

      <div class="auth-field">
        <label class="auth-label">Email</label>
        <input type="email" class="auth-input" v-model="email" :class="{ 'has-error': getError('email') }" placeholder="player@example.com">
        <span class="auth-error" v-if="getError('email')">{{ getError('email') }}</span>
      </div>

      <div class="auth-field">
        <label class="auth-label">Password</label>
        <div class="auth-password-wrap">
          <input type="password" class="auth-input" v-model="password" ref="passwordInput" :class="{ 'has-error': getError('password') }">
          <span class="auth-password-toggle" @click="togglePassView">
            <img v-if="passEyes.pass" src="../../../web/images/web/img/icons/eye-open.svg" alt="show">
            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="hide">
          </span>
        </div>
        <span class="auth-error" v-if="getError('password')">{{ getError('password') }}</span>
      </div>

      <div class="auth-field">
        <label class="auth-label">Confirm password</label>
        <div class="auth-password-wrap">
          <input type="password" class="auth-input" v-model="confirm_password" ref="passwordInputConfirm" :class="{ 'has-error': getError('confirm_password') }">
          <span class="auth-password-toggle" @click="togglePassConfirmView">
            <img v-if="passEyes.confirm" src="../../../web/images/web/img/icons/eye-open.svg" alt="show">
            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="hide">
          </span>
        </div>
        <span class="auth-error" v-if="getError('confirm_password')">{{ getError('confirm_password') }}</span>
      </div>

      <div class="auth-submit">
        <button-white :text="create_account_button_text" @click="signUp"></button-white>
      </div>

      <div class="auth-footer">
        <span>Already have an account? </span>
        <router-link to="/login" class="auth-link">Sign in</router-link>
      </div>
    </div>

    <!-- Social Signup -->
    <div class="auth-social">
      <div class="auth-social-divider">
        <div class="auth-social-divider-line"></div>
        <span class="auth-social-divider-text">or sign up with</span>
        <div class="auth-social-divider-line"></div>
      </div>
      <div class="auth-social-buttons">
        <div class="auth-social-btn">Discord</div>
        <div class="auth-social-btn">Steam</div>
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
            create_account_button_text: 'Create Account',
            name: '',
            username: '',
            email: '',
            password: '',
            phone_number: '',
            date_of_birth: '2023-09-22',
            confirm_password: '',
            passEyes: {
                pass: false,
                confirm: false
            },
            eyeIsOpenConfirm: false,
            errors: [],
            twoFactorAuth: false,
            secretKey: null,
            QR: null,
        }
    },
    methods: {
        closeSignIn() {
            store.state.signUpModalOpen = false;
        },
        signUp() {
            api.post('/api/register', {
                name: this.name,
                username: this.username,
                email: this.email,
                password: this.password,
                confirm_password: this.confirm_password,
            }).then( response => {
                if (response.status === 201) {
                    localStorage.setItem('access_token', response.data.token.access_token);
                    getProfileData();
                    if (localStorage.getItem('access_token')) {
                        store.state.showTestData = false;
                        //store.state.importBudgesModalOpen = true;
                        store.state.modals.connect2faModalOpen.show = true;
                        router.push('/trophy-room');
                    }
                }
            })
            .catch(error => {
                this.errors = [];
                for (const field in error.response.data.errors) {
                    if (error.response.data.errors.hasOwnProperty(field)) {
                        this.errors.push({ field, text: error.response.data.errors[field][0] });
                    }
                }
            });
        },
        togglePassView() {
            this.passEyes.pass = !this.passEyes.pass;
            if (this.$refs.passwordInput.type === 'password') {
                this.$refs.passwordInput.type = 'text';
            } else {
                this.$refs.passwordInput.type = 'password';
            }
        },
        togglePassConfirmView() {
            this.passEyes.confirm = !this.passEyes.confirm;
            if (this.$refs.passwordInputConfirm.type === 'password') {
                this.$refs.passwordInputConfirm.type = 'text';
            } else {
                this.$refs.passwordInputConfirm.type = 'password';
            }
        },
        getError(fieldName) {
            const error = this.errors.find(error => error.field === fieldName);
            return error ? error.text : null;
        },
        filterUsername() {
            this.username = this.username.replace(/\s/g, '');
        }
    }
}
</script>

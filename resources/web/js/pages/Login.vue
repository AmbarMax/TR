<template>
    <div class="auth_wrapper">
        <div class="auth_welcome_block">
            <div class="header_logo">
                <img src="../../../web/images/web/img/tr-isologo.png" alt="logo">
            </div>
            <div class="greeting_block">
                <h1 >
                    Welcome to Ambar
                </h1>
                <p>
                    A realm where users validate, compete, and immortalize achievements, crafting a legacy of growth in a rewarding ecosystem.
                </p>
            </div>
        </div>
        <div class="auth_form_block" v-if="!twoFactorAuth && !twoFactorReset">
            <h2 class="modal_header">
                Sign in to Ambar
            </h2>
            <h4 class="modal_label">Username or Email</h4>
            <input type="email" class="modal_input" v-model="email">
            <span class="validation_error" v-if="errorLogin">Invalid credentials</span>
            <div class="modal_password">
                <h4>Password</h4>
                <a href="/forgot-password">Forgot?</a>
            </div>
            <div class="password_input_block">
                <input type="password" class="modal_input" v-model="password" ref="passwordInput">
                <img v-if="eyeIsOpen" src="../../../web/images/web/img/icons/eye-open.svg" alt="eye_open" @click="togglePassView">
                <img v-if="!eyeIsOpen" src="../../../web/images/web/img/icons/eye-close.svg" alt="eye_close" @click="togglePassView">
            </div>
            <button-white :text="sign_in_button_text" class="sign_in_button" @click="signIn"></button-white>
            <div class="modal_dont_have_account">
                <span>
                    Don't have an account?
                </span>
                <router-link to="/sign-up" class="login-signup_link">
                    Sign up
                </router-link>
            </div>
<!--            <div class="separator">
                <div class="separator_text">
                    or sign in with
                </div>
            </div>
            <div class="modal_sign_up_with_buttons">
                <button-white @click="loginGithub" :text="git_hub_button_text" :img_link="git_hub_button_image" class="modal_sign_up_with_button"></button-white>
                <button-white :text="discord_button_text" :img_link="discord_button_image" class="modal_sign_up_with_button"></button-white>
                <button-white @click="loginSteam" :text="steam_button_text" :img_link="steam_button_image" class="modal_sign_up_with_button"></button-white>
            </div>-->
        </div>
        <div class="auth_form_block" v-else-if="twoFactorAuth && !twoFactorReset">
            <h2 class="modal_header">
                Two factor authentication
            </h2>
            <h4 class="modal_label">Please fill your single use code:</h4>
            <input type="text" class="modal_input" v-model="twoFactorCode" @input="formatInput">
            <span class="validation_error" v-if="twoFactorErrorStatus">{{twoFactorErrorMessage}}</span>
            <a style="font-size: 14px; margin-top:6px;" class="modal_label">Dont have access? <a @click="disable2FA" style="color: white;font-size: 14px; margin-top:6px;" class="modal_label" href="#">Use one-time code</a></a>

            <button-white :text="'Continue'" class="sign_in_button" @click="signInContinue"></button-white>
        </div>
        <div class="auth_form_block" v-else-if="twoFactorReset">
            <h2 class="modal_header">
                Reset link sent to your email!
            </h2>
            <h3 style="font-size: 14px" class="modal_header">
                Follow the link sent in the email to reset your password.
            </h3>
            <div class="modal_dont_have_account">
                <router-link @click="goToLoginPage" to="/login" class="login-signup_link">
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
            api.get('/api/2fa-status').then( resp => {
                if (resp && resp.status === 200) {
                    if (resp.data.twoFAStatus === 0){
                        store.state.modals.connect2faModalOpen.show = true;
                    }
                }
            }).catch(error => {
                console.log('api profile error: ', error)
            })
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

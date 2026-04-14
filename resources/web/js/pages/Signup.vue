<template>
    <div class="auth_wrapper">
        <div class="auth_welcome_block">
            <div class="header_logo">
                <img src="../../../web/images/web/img/logo.svg" alt="logo">
            </div>
            <div class="greeting_block">
                <h1>
                    Welcome to Ambar
                </h1>
                <p>
                    A realm where users validate, compete, and immortalize achievements, crafting a legacy of growth in a rewarding ecosystem
                </p>
            </div>
        </div>
        <div class="auth_form_block">
            <h2 class="modal_header">
                Sign up to Ambar
            </h2>
            <h4 class="auth_label">Name</h4>
            <input type="text" class="modal_input" v-model="name" v-bind:class="{ 'has-error': getError('name') }">
            <span class="validation_error" v-if="getError('name')">{{ getError('name') }}</span>
            <h4 class="auth_label">Username</h4>
            <input type="text" @input="filterUsername" class="modal_input" v-model="username" v-bind:class="{ 'has-error': getError('username') }">
            <span class="validation_error" v-if="getError('username')">{{ getError('username') }}</span>
            <h4 class="auth_label">Email</h4>
            <input type="email" class="modal_input" v-model="email" v-bind:class="{ 'has-error': getError('email') }">
            <span class="validation_error" v-if="getError('email')">{{ getError('email') }}</span>
            <h4 class="auth_label">Password</h4>
            <div class="password_input_block">
                <input type="password" class="modal_input" v-model="password" ref="passwordInput" v-bind:class="{ 'has-error': getError('password') }">
                <img v-if="passEyes.pass" src="../../../web/images/web/img/icons/eye-open.svg" alt="eye_open" @click="togglePassView">
                <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="eye_close" @click="togglePassView">
            </div>
            <span class="validation_error" v-if="getError('password')">{{ getError('password') }}</span>
            <h4 class="auth_label">Confirm your password</h4>
            <div class="password_input_block">
                <input type="password" class="modal_input" v-model="confirm_password" ref="passwordInputConfirm" v-bind:class="{ 'has-error': getError('confirm_password') }">
                <img v-if="passEyes.confirm" src="../../../web/images/web/img/icons/eye-open.svg" alt="eye_open" @click="togglePassConfirmView">
                <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="eye_close" @click="togglePassConfirmView">
            </div>
            <span class="validation_error" v-if="getError('confirm_password')">{{ getError('confirm_password') }}</span>
            <button-white :text="create_account_button_text" class="sign_in_button" @click="signUp"></button-white>
            <div class="modal_dont_have_account">
                <span>
                    Already have an account?
                </span>
                <router-link to="/login" class="login-signup_link btn-yellow">
                    Sign in
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

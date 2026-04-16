<template>
    <div class="modal_background" @click.self="closeImportBudges">
        <div class="modal_window">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeImportBudges" class="modal_close_button">
            <h1 class="modal_header">
                2FA authentication
            </h1>
            <h3 class="modal_label">
                Scan QR code or use this secret key using Google Authenticator:
            </h3>
            <h4 class="two_fa_secret">
                {{ $store.state.modals.twoFactorAuthModal.data.secret }}
            </h4>
            <img :src="$store.state.modals.twoFactorAuthModal.data.QR" alt="qr" class="qr_image">

            <label style="width: unset" for="" class="profile_input">
                        <span>
                            Enter the authorization key
                        </span>
                <input @input="handleInput" type="text" autocomplete="off" v-model="auth_key"/>
                <span class="validation_error" v-if="errorMessage">{{errorMessage}}</span>
            </label>

            <div class="modal_sign_up_with_buttons modal_buttons">
                <button-white :text="'Activate'" @click="activateTwoFa" class="modal_sign_up_with_button"></button-white>
                <button-white :text="'Cancel'" @click="cancelActivateTwoFa" class="modal_sign_up_with_button"></button-white>
            </div>
        </div>
    </div>
</template>

<script>

import buttonWhite from "../../parts/button.vue";
import store from "../../store/store.js";
import router from "../../router/router.js";
import Loader from "../Loader.vue";
import api from "../../api/api.js";
import getProfileData from "../../services/profile-data.js";
export default {
    components: {
        buttonWhite
    },
    data() {
        return {
            sign_in_button_text: 'Sign In',
            git_hub_button_text: 'Import from GitHub',
            git_hub_button_image: 'github',
            discord_button_text: 'Import from Discord',
            discord_button_image: 'discord',
            steam_button_text: 'Import from Steam',
            steam_button_image: 'steam',
            email: null,
            password: null,
            auth_key: '',
            errorMessage: '',
        }
    },
    methods: {
        handleInput() {
            this.auth_key = this.auth_key.replace(/\D/g, "");
        },
        closeImportBudges() {
            store.state.modals.twoFactorAuthModal.show = false;
        },
        cancelActivateTwoFa() {
            store.state.modals.twoFactorAuthModal.show = false;
            store.state.modals.twoFactorAuthModal.data = null;
        },
        activateTwoFa() {
            api.post('/api/2fa-activate', {
                google2fa_secret: store.state.modals.twoFactorAuthModal.data.secret,
                one_time_password: this.auth_key,
            }).then( response => {
                if (response.status === 201) {
                    store.state.notification = {
                        message: response.data.message,
                        type: "success",
                        show: true
                    }
                    store.state.modals.twoFactorAuthModal.show = false;
                    store.state.modals.twoFactorAuthModal.data = null;
                    store.state.modals.twoFactorAuthModalSuccess.show = true;
                    getProfileData();
                }
            })
            .catch(error => {
                this.errorMessage = 'Invalid authorization key';
            });
        },

    }
}
</script>

<style scoped>
.modal_window {
    padding: 30px 40px 60px 30px;
}

.modal_sign_up_with_button {
    height: 40px;
}
.modal_buttons {
    display: flex;
    flex-direction: row;
    gap: 12px;
    width: 100%;
    align-items: center;
    justify-content: center;

    @media (max-width: 768px) {
        flex-wrap: wrap;
    }
}

.modal_buttons > div {
    width: calc(50% - 6px) !important;
}

.validation_error {
    margin-top: 0;
    color: #BE2020;
    font-size: 14px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 400;
    line-height: 20px;
    word-wrap: break-word;
    margin-bottom: -15px;
}
</style>

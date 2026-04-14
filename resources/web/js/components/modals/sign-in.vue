<template>
    <div class="modal_background">
        <div class="modal_window">
            <div class="modal_header_block">
                <img src="../../../../web/images/web/img/logo.svg" alt="logo" class="modal_logo">
                <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeSignIn" class="modal_close_button">
            </div>
            <h2 class="modal_header">
                Sign in to Ambar
            </h2>
            <h4 class="modal_label">Username or Email</h4>
            <input type="email" class="modal_input" v-model="email">
            <div class="modal_password">
                <h4>Password</h4>
                <a href="#">Forgot?</a>
            </div>
            <input type="password" class="modal_input" v-model="password">
            <button-white :text="sign_in_button_text" class="sign_in_button" @click="signIn"></button-white>
            <div class="modal_dont_have_account">
                <span>
                    Don't have an account?
                </span>
                <a href="#">
                    Sign up
                </a>
            </div>
<!--            <div class="separator">
                <div class="separator_text">
                    or sign in with
                </div>
            </div>
            <div class="modal_sign_up_with_buttons">
                <button-white :text="git_hub_button_text" :img_link="git_hub_button_image" class="modal_sign_up_with_button"></button-white>
                <button-white :text="discord_button_text" :img_link="discord_button_image" class="modal_sign_up_with_button"></button-white>
                <button-white :text="steam_button_text" :img_link="steam_button_image" class="modal_sign_up_with_button"></button-white>
            </div>-->
        </div>
    </div>
</template>

<script>

import buttonWhite from "../../parts/button.vue";
import store from "../../store/store.js";
export default {
    components: {
        buttonWhite
    },
    data() {
        return {
            sign_in_button_text: 'Sign In',
            git_hub_button_text: 'Continue with GitHub',
            git_hub_button_image: '../../../../web/images/web/img/social_icons/mdi_github.svg',
            discord_button_text: 'Continue with Discord',
            discord_button_image: '../../../../web/images/web/img/social_icons/ic_baseline-discord_black.svg',
            steam_button_text: 'Continue with Steam',
            steam_button_image: '../../../../web/images/web/img/social_icons/mdi_steam.svg',
            email: null,
            password: null
        }
    },
    methods: {
        closeSignIn() {
            store.state.signUpModalOpen = false;
        },
        signIn() {
            console.log(
                {
                    email: this.email,
                    password: this.password
                }
            )
        }
    }

}
</script>

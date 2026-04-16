<template>
    <div class="modal_background" @click.self="closeImportBudges">
        <div class="modal_window">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeImportBudges" class="modal_close_button">
            <h1 class="modal_header">
                Import your Badges
            </h1>
            <h3 class="modal_label">
                Choose your platform:
            </h3>
            <div class="modal_sign_up_with_buttons">
<!--                <button-white :text="git_hub_button_text" :img_link="git_hub_button_image" @click="ImportData('github')" class="modal_sign_up_with_button"></button-white>-->
                <button-white :text="discord_button_text" :img_link="discord_button_image" @click="ImportData('discord')" class="modal_sign_up_with_button"></button-white>
                <button-white :text="steam_button_text" :img_link="steam_button_image" @click="ImportData('steam')" class="modal_sign_up_with_button"></button-white>
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
            password: null
        }
    },
    methods: {
        closeImportBudges() {
            store.state.importBudgesModalOpen = false;
        },
        ImportData(type) {
            store.state.showTestData = true;
            const token = localStorage.getItem('access_token');
            window.location.href = '/login/' + type + '?token=' + encodeURIComponent(token);
            store.state.importBudgesModalOpen = false;
            store.state.loaderStatus = true;
        }
    }

}
</script>

<style scoped>
.modal_window {
    padding: 30px 40px 60px 30px;
}

.main-button {
    border-radius: 2px;
    font-size: 18px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 20px;
}
.modal_sign_up_with_button {
    height: 40px;
}
</style>

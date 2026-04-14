<template>
    <div class="modal_background" @click.self="closeCreateYourAchievement">
        <div class="modal_window_transparent">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeCreateYourAchievement" class="modal_close_button">
            <h1 class="modal_header">
                Create your Achievement
            </h1>
            <h3 class="modal_label">
                Choose your category:
            </h3>
            <div class="modal_achievements_block">
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/adventure.svg" alt="icon">
                    <span>
                        Adventure
                    </span>
                </div>
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/creative.svg" alt="icon">
                    <span>
                        Creative
                    </span>
                </div>
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/ecological.svg" alt="icon">
                    <span>
                        Ecological
                    </span>
                </div>
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/historical.svg" alt="icon">
                    <span>
                        Historical
                    </span>
                </div>
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/personal.svg" alt="icon">
                    <span>
                        Personal
                    </span>
                </div>
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/professional.svg" alt="icon">
                    <span>
                        Professional
                    </span>
                </div>
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/scientific.svg" alt="icon">
                    <span>
                        Scientific
                    </span>
                </div>
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/social.svg" alt="icon">
                    <span>
                        Social
                    </span>
                </div>
                <div class="modal_achievements_card" @click="OpenModalCreateAchievement">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/sports.svg" alt="icon">
                    <span>
                        Sports
                    </span>
                </div>
            </div>
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
            git_hub_button_text: 'Import from GitHub',
            git_hub_button_image: '../../../../web/images/web/img/social_icons/mdi_github.svg',
            discord_button_text: 'Import from Discord',
            discord_button_image: '../../../../web/images/web/img/social_icons/ic_baseline-discord_black.svg',
            steam_button_text: 'Import from Steam',
            steam_button_image: '../../../../web/images/web/img/social_icons/mdi_steam.svg',
            email: null,
            password: null
        }
    },
    methods: {
        closeCreateYourAchievement() {
            store.state.createYourAchievementModalOpen = false;
        },
        OpenModalCreateAchievement() {
            store.state.createYourAchievementModalOpen = false;
            store.state.modals.createYourAchievement.show = true;
        }
    }

}
</script>

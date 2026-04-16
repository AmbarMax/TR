<template>
    <div class="modal_background" @click.self="closeImportBudges">
        <div class="modal_window">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeImportBudges" class="modal_close_button">
            <h1 class="modal_header">
                Two-factor authentication
            </h1>
            <h3 class="modal_label">
                Before utilizing any non-fungible tokens (NFTs) on this platform, it is mandatory to set up two-factor authentication (2FA) for security purposes.
            </h3>
            <div class="modal_sign_up_with_buttons" style="flex-direction: row; justify-content: space-between">
                <button-white :text="'Connect'" @click="goTo2fa" class="modal_sign_up_with_button"></button-white>
                <button-white :text="'Not now'" @click="closeImportBudges" class="modal_sign_up_with_button" style="background-color: gray;"></button-white>
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
        }
    },
    methods: {
        closeImportBudges() {
            store.state.modals.connect2faModalOpen.show = false;
            api.get('/api/badges/').then( response => {
                if (response.status === 200) {
                    if (response.data.data.length == 0){
                        store.state.importBudgesModalOpen = true;
                    }
                }
            })

        },
        goTo2fa(){
            api.post('/api/2fa-get').then( response => {
                if (response.status === 200) {
                    store.state.modals.connect2faModalOpen.show = false;
                    store.state.modals.twoFactorAuthModal.show = true;
                    store.state.modals.twoFactorAuthModal.data = response.data;
                    this.$router.push('/profile');
                }
            })
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

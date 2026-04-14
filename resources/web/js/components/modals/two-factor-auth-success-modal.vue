<template>
    <div class="modal_background" @click.self="closeImportBudges">
        <div class="modal_window">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeImportBudges" class="modal_close_button">
            <h1 class="modal_header">
                2FA authentication activated!
            </h1>

            <h3 style="font-size: 16px; margin-top: 30px" class="modal_label">
                In the event of loss of access to your two-factor authentication device, we hereby provide you with a one-time code for disabling two-factor authentication. Please ensure the confidentiality of this code and refrain from sharing it with unauthorized individuals.
            </h3>

            <h3 style="font-weight: bold; margin: 12px auto 0 auto" class="modal_label">
                {{ secret_key }}
            </h3>

            <div class="modal_sign_up_with_buttons modal_buttons">
                <button-white :text="'Close'" @click="closeImportBudges" class="modal_sign_up_with_button"></button-white>
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
            secret_key: '',
        }
    },
    methods: {
        closeImportBudges() {
            store.state.modals.twoFactorAuthModalSuccess.show = false;
            api.get('/api/badges/').then( response => {
                if (response.status === 200) {
                    if (response.data.data.length == 0){
                        store.state.importBudgesModalOpen = true;
                    }
                }
            })
        },
        generateSecretKey() {
            api.get('/api/generate-secret-key').then( response => {
                this.secret_key = response.data.secret_key
            })
            .catch(error => {
                console.log(error)
            });
        }

    },
    mounted() {
        this.generateSecretKey();
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
</style>

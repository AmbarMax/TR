<template>
    <div class="modal_background" @click.self="closeRequestValidate">
        <div class="modal_window">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeRequestValidate" class="modal_close_button">
            <h1 class="modal_header">
                Validate your achievement
            </h1>
            <h3 class="modal_label">
                Choose your protocol:
            </h3>
            <button-white :text="ambar_proof_button_text" :disabled="true" :img_link="proof_button_image" class="modal_sign_up_with_button"></button-white>
            <h4 class="modal_small_label">
              *Coming soon
            </h4>
            <button-white :text="social_proof_button_text" :img_link="proof_button_image" @click="OpenModalNetworkValidate" class="modal_sign_up_with_button"></button-white>
            <h4 class="modal_small_label">
                *Social proof of validation
            </h4>
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
            ambar_proof_button_text: 'Our AI will validate you',
            proof_button_image: 'proof',
            social_proof_button_text: 'Social proof of achievement',
        }
    },
    methods: {
        closeRequestValidate() {
            store.state.requestValidateModalOpen = false;
        },
        OpenModalAmbarValidate() {
            store.state.requestValidateModalOpen = false;
            store.state.ambarValidateModalOpen = true;
        },
        OpenModalNetworkValidate() {
            store.state.modals.createAchievement.data.proof = 2;
            store.state.requestValidateModalOpen = false;
            store.state.networkValidateModalOpen = true;
        }
    }

}
</script>

<style scoped>
.modal_window {
    max-width: 550px;
    width: 550px;
    padding: 40px 30px 60px 30px;
}

.main-button {
    margin-top: 40px;
    margin-bottom: 12px;
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

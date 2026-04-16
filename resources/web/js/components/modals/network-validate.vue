<template>
    <div class="modal_background" @click.self="closeNetworkValidate">
        <div class="modal_window">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeNetworkValidate" class="modal_close_button">
            <h1 class="modal_header">
                Your network will validate
            </h1>
            <h3 class="modal_label">
                You are going to need:
            </h3>
          <div class="point_need">
            {{ point_need }} Runes
            <img src="../../../../web/images/web/img/points/rune.svg" alt="rune">
          </div>
            <div class="point_current">
                You currently have <span class="number">{{ userRunesForValidate }}/{{ point_need }}</span>
            </div>
            <button-white
                @click="validateAchievement"
                :text="validate_button_text"
                :disabled="userRunesForValidate < point_need || button_is_locked"
                class="validate_achievement_with_button">

            </button-white>
            <h4 class="modal_small_label">
                Or
            </h4>
            <button-white  @click="requestSocialProof" :text="social_proof_button_text" class="margin-fix"></button-white>
            <h4 class="modal_small_label">
                Validation could take some time
            </h4>
        </div>
    </div>
</template>

<script>

import buttonWhite from "../../parts/button.vue";
import store from "../../store/store.js";
import api from "../../api/api.js";
export default {
    components: {
        buttonWhite
    },
    data() {
        return {
            buy_button_text: 'Buy for Ambars',
            validate_button_text: 'Validate achievement now',
            social_proof_button_text: 'Request runes from friends',
            proof_button_image: '../../../../web/images/web/img/social_icons/proof.svg',
            point_need: 5,
            button_is_locked: false
        }
    },
    methods: {
        closeNetworkValidate() {
            store.state.networkValidateModalOpen = false;
            store.state.modals.createAchievement.data = {};
        },
        validateAchievement() {
            this.button_is_locked = true;
            api.post('/api/achievement', store.state.modals.createAchievement.data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            })
            .then( resp => {
                if (resp.status === 200) {
                    this.closeNetworkValidate();
                    store.state.notification = {
                        message: resp.data.message,
                        type: 'success',
                        show: true
                    };
                    this.$store.commit('updateDataOnValidationPage');
                }
            }).catch((error) => {
              console.log('Validate achievement: ', error)
            });
        },
        requestSocialProof() {
            store.state.networkValidateModalOpen = false;
            store.state.SocialProofValidateModalOpen = true;
        }
    },
    computed: {
        userRunesForValidate() {
            if ( store.state.user.balances.rune >= this.point_need ) {
                return this.point_need;
            } else {
                return store.state.user.balances.rune;
            }
        }
    }

}
</script>

<style scoped>
.modal_window {
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

.point_current {
    margin-top: 30px;
    margin-bottom: 12px;
    color: rgba(255, 255, 255, 0.90);
    font-size: 20px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 28px;
}

.number {
    color: #FF0088;
}

.point_need {
    display: flex;
    align-items: center;
    text-align: center;
    gap: 8px;
    color: #FF0088;
    font-size: 16px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 22px;
    word-wrap: break-word
}

.small-button {
    width: 200px;
    padding: 8px 24px;
    margin-bottom: 0;
}

.margin-fix {
    margin-top: 12px !important;
}
</style>

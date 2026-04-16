<template>
    <div class="modal_background" @click.self="closeExchangeModal">
        <div class="modal_window">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeExchangeModal" class="modal_close_button">
            <h1 class="modal_header">
                Exchange
            </h1>
            <h3 class="modal_label">
                Enter the number of your cryptocurrency wallet where you want to receive the cryptocurrency
            </h3>


            <label for="" class="profile_input">
                <span>
                    Wallet number
                </span>
                <input type="text" name="walletNumber" autocomplete="off" v-model="walletNumber"/>
                <span style="color: #BE2020; font-size: 14px; margin-top: -8px; margin-bottom: -8px " class="validation_error" v-if="walletNumberError">This field is required</span>
                <span class="input_info">
                   ERC-20 Network
                </span>
            </label>

            <div class="info-block">
                <div class="text">
                    You pay <span>{{ exchangeAmount }} Uru</span>
                </div>
                <div class="text">
                    You receive <span>{{ exchangeOutput.toFixed(5) }} {{ currentCurrency }}</span>
                </div>

            </div>


            <button-white @click="exchangeSendRequest" :text="'Exchange'" class="validate_achievement_with_button"></button-white>

        </div>
    </div>
</template>

<script>

import buttonWhite from "../../parts/button.vue";
import store from "../../store/store.js";
import CustomSelect from "../../parts/custom-select.vue";
import api from "../../api/api.js";
export default {
    props: {
        exchangeAmount: {
            type: Number,
            required: true,
        },
        exchangeOutput: {
            type: Number,
            required: true,
        },
        currentCurrency: {
            type: String,
            required: true,
        },
    },
    components: {
        CustomSelect,
        buttonWhite
    },
    data() {
        return {
            walletNumber: '',
            walletNumberError: false,
        }
    },
    methods: {
        closeExchangeModal() {
            store.state.exchangeModalOpen = false;
        },
        async exchangeSendRequest(){
            if (this.walletNumber == '') {
                this.walletNumberError = true;
                return;
            }
            this.walletNumberError = false;
            await api.post('/api/exchange/store', {
                'input_amount': this.exchangeAmount,
                'input_currency': 'uru',
                'output_amount': this.exchangeOutput,
                'output_currency': this.currentCurrency,
                'wallet_number': this.walletNumber,
            }).then(response => {
                if (response.status === 200) {
                    store.state.notification = {
                        message: 'Request for exchange of funds has been successfully sent!',
                        type: 'success',
                        show: true
                    }
                    this.$emit('updateList');
                    this.closeExchangeModal()
                }
            }).catch(error => {
                store.state.notification = {
                    message: 'Something went wrong. Try again later.',
                    type: 'error',
                    show: true
                }
            });
        }
    }

}
</script>

<style scoped>
.modal_window {
    padding: 40px 30px 40px 30px;
}

.info-block {
    margin-top: 30px;
    margin-bottom: 6px;
    display: flex;
    justify-content: space-between;
    .text {
        font-family: 'Share Tech Mono', monospace;
        color: var(--H, rgba(255, 255, 255, 0.90));
        font-size: 20px;
        font-style: normal;
        font-weight: 700;
        line-height: 140%;
        span {
            margin-left: 8px;
            color: var(--main, #CAFB01);
            font-size: 16px;
        }
    }
}

.profile_input {
    margin-top: 30px;
    display: flex;
    gap: 16px;
    width: 100%;
    .input_info {
        color: var(--text, #BABABA);
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 130%; /* 18.2px */
    }
}

.main-button {
    margin-top: 24px;
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
    color: #CAFB01;
}

.point_need {
    display: flex;
    align-items: center;
    text-align: center;
    gap: 8px;
    color: #CAFB01;
    font-size: 16px;
    font-family: $orbitron;
    font-weight: 700;
    line-height: 22px;
    word-wrap: break-word;
    margin-top: 30px;
}

.trophies {
    display: flex;
    gap: 14px;
    margin-top: 12px;
    margin-bottom: 16px;
}

.mb-0 {
    margin-bottom: 0!important;
}

.mb-30 {
    margin-bottom: 30px!important;
}

.modal_small_label {
    margin-bottom: 12px;
}

.upload_image {
    margin: 30px 0 0;
}

.upload_image:hover {
    cursor: pointer;
}

.uploaded_image_block {
    border-radius: 4px;
    border: 1px solid rgba(186, 186, 186, 0.60);
    max-height: 250px;
    height: 250px;
    width: 100%;
    padding: 30px 16px;
    background: rgba(186, 186, 186, 0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 30px;
}

.uploaded_image {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.remove_image {
    position: absolute;
    top: 200px;
    right: 46px;
}

.remove_image:hover {
    cursor: pointer;
}
</style>

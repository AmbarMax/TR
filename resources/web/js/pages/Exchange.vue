<template>
    <div class="main_block">
        <div class="page_info">
            <div class="title">
                <span>Exchange</span>
            </div>
        </div>
        <div class="exchange_block">
            <div class="exchange_header">
                <div class="block">
                    <div class="title">
                        <span>Your balance</span>
                    </div>
                    <div class="description">
                        <span>This is your current balance. Based on this amount you can exchange Uru for any cryptocurrency.</span>
                    </div>
                </div>

                <div class="profile_tokens_badge_block">
                    <div class="profile_tokens_badge">
                        <img src="../../../web/images/web/img/points/uru.svg" alt="crystal">
                        <span>{{$store.state.user.balances.uru < 0 ? 0 : $store.state.user.balances.uru}}</span>
                    </div>
                </div>



            </div>

            <div class="line"/>

            <div class="exchange_content">

                <div class="exchange_inputs">
                    <label for="" class="profile_input">
                        <span>
                            Uru Amount
                        </span>
                        <input @input="updateExchangeRate" type="number" name="exchangeAmount" autocomplete="off" v-model="exchangeAmount"/>
                        <span class="input_info">
                           1 Uru = {{ this.exchangeRate[this.currentCurrency] }} {{ this.currentCurrency }}
                        </span>
                    </label>

                    <div style="margin:0 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
                            <path d="M9.5625 14.625L4.5 9.5625M4.5 9.5625L9.5625 4.5M4.5 9.5625H31.5M26.4375 21.375L31.5 26.4375M31.5 26.4375L26.4375 31.5M31.5 26.4375H4.5" stroke="#CAFB01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <label for="" class="profile_input">
                        <span>
                            Cryptocurrency Amount
                        </span>
                        <input disabled type="text" name="exchangeAmount" autocomplete="off" v-model="exchangeOutput"/>
                        <span class="input_info">
                              1 {{ this.currentCurrency }} = {{ (1 / this.exchangeRate[this.currentCurrency]).toFixed(2) }} Uru
                        </span>
                    </label>

                    <label for="" class="profile_input">
                        <span>
                            Cryptocurrency name
                        </span>
                        <CustomSelect
                            :options="exchangeRateOptions"
                            @input="(newValue) => (this.updateCurrency(newValue))"
                            :class="'select-exchange'">
                        </CustomSelect>
                        <span class="input_info">
ㅤ                        </span>
                    </label>

                    <div class="exchange-buttons">
                        <button-white :text="'Exchange'" class="button-exchange" @click="exchange()"></button-white>
                    </div>

                </div>
            </div>

            <div class="line"/>

            <div class="title">
                <div class="text">
                    <span>Transactions history</span>
                </div>
                <div class="exchange-filter">
                    <div style="white-space: nowrap;">Sort by status</div>
                    <CustomSelect
                        :options="filterOptions"
                        @input="(newValue) => (this.updateFilter(newValue))"
                        :class="'select-exchange'">
                    </CustomSelect>
                </div>
            </div>
            <div class="list-wrapper" v-if="list.length !== 0 && !isMobile">
                <table>
                    <thead>
                    <tr>
                        <th>Uru Amount</th>
                        <th>Cryptocurrency Amount</th>
                        <th>Status</th>
                        <th>Wallet number</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in list" :key="item.id">
                        <td>{{ item.inputAmount }}</td>
                        <td>{{ item.outputAmount }} {{ item.outputCurrency }}</td>
                        <td>
                            <div class="status-info">
                                {{ item.status }}
                            </div>
                        </td>
                        <td>{{ item.walletNumber }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div style="display: flex; flex-direction: column; gap: 24px" v-if="list.length !== 0 && isMobile">
                <div style="gap: 0; padding: 0 16px" class="list-wrapper"  v-for="item in list">
                        <div class="mobile-block">
                                {{ item.inputAmount }} Uru
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" fill="none">
                                    <path d="M9.5625 14.625L4.5 9.5625M4.5 9.5625L9.5625 4.5M4.5 9.5625H31.5M26.4375 21.375L31.5 26.4375M31.5 26.4375L26.4375 31.5M31.5 26.4375H4.5" stroke="#CAFB01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ item.outputAmount }} {{ item.outputCurrency }}
                            <div class="mobile-card-status">
                                {{ item.status }}
                            </div>
                        </div>


                        <div class="mobile-block">
                            Wallet number: {{ item.walletNumber }}
                        </div>
                    </div>
            </div>

            <PagePagination
                :items="this.list"
                :key="paginationKey"
                :current-page="this.currentPage"
                :total="this.total"
                :items-per-page="8"
                :method="getExchanges"
            />

        </div>


    </div>

    <exchange-modal @updateList="getExchanges" :exchangeAmount="exchangeAmount" :exchangeOutput="exchangeOutput" :currentCurrency="currentCurrency" v-if="exchangeModalOpen"></exchange-modal>
</template>

<script>

import {defineComponent} from "vue";
import forgeCard from "../parts/forge-card.vue";
import buttonWhite from "../parts/button.vue";
import CustomSelect from "../parts/custom-select.vue";
import store from "../store/store.js";
import Pagination from "../parts/Pagination.vue";
import api from "../api/api.js";
import {exchangeRate} from "../constans/exchangeRate.js";
import ExchangeModal from "../components/modals/ExchangeModal.vue";
import PagePagination from "../components/PagePagination.vue";

export default defineComponent({
    components: {
        PagePagination,
        ExchangeModal,
        Pagination,
        CustomSelect,
        forgeCard,
        buttonWhite,
        store
    },
    data() {
        return {
            filterOptions: ['All', 'Pending', 'Canceled', 'Paid'],
            currentFilter: 'All',
            exchangeAmount: 1,
            exchangeOutput: 0,
            exchangeRate: exchangeRate,
            exchangeRateOptions: Object.keys(exchangeRate),
            currentCurrency: Object.keys(exchangeRate)[0],
            list: [],
            total: 0,
            currentPage: 1,
            paginationKey: 1,
            isMobile: window.innerWidth < 600
        }
    },
    methods: {
        exchange(){
            store.state.exchangeModalOpen = true;
        },
        updateExchangeRate(){
            this.validatePositiveNumber();
            this.exchangeOutput = this.exchangeRate[this.currentCurrency] * this.exchangeAmount;
        },
        updateCurrency(value){
            this.currentCurrency = value;
            this.updateExchangeRate();
        },
        validatePositiveNumber() {
            if (parseFloat(this.exchangeAmount) <= 0) {
                this.exchangeAmount = 1
            }
            if (parseFloat(this.exchangeAmount) > this.$store.state.user.balances.uru) {
                this.exchangeAmount = this.$store.state.user.balances.uru
            }
        },
        async getExchanges(page = 1){
            this.currentPage = page;
            await api.get("/api/exchange/index?page="+this.currentPage+"&filter="+this.currentFilter).then(response => {
                if (response.status === 200) {
                    this.list = response.data.data;
                    this.total = response.data.total
                }
            }).catch(error => {
                store.state.notification = {
                    message: 'Something went wrong. Try again later.',
                    type: 'error',
                    show: true
                }
            });
        },
        updateFilter(value) {
            this.paginationKey++;
            this.currentPage = 1;
            this.currentFilter = value;
            this.getExchanges();
        }
    },
    async mounted(){
        await this.getExchanges();
    },
    computed: {
        exchangeModalOpen: function () {
            return store.state.exchangeModalOpen;
        },
    }


})
</script>

<style scoped>

.exchange-filter{
    display: flex;
    align-items: center;
    gap: 16px;
    max-width: 300px;
    width: 100%;
    color: #FFF;
    font-family: 'Share Tech Mono', monospace;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 130%; /* 20.8px */
    @media (max-width: 768px) {
      max-width: 100%;
    }
}

.title {
    display: flex;
    margin-bottom: 32px;
    justify-content: space-between;

    @media (max-width: 768px) {
       flex-direction: column;
        gap: 16px;
    }

    .text {
        color: #FFF;
        font-family: 'Share Tech Mono', monospace;
        font-size: 20px;
        font-style: normal;
        font-weight: 700;
        line-height: normal;
    }

}

.input_info {
    color: var(--text, #BABABA);
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 130%; /* 18.2px */
}

.select-exchange {

}

.line {
    display: flex;
    margin-bottom: 30px;
    padding-top: 30px;
    align-items: flex-start;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.exchange_block {
    .exchange_header {
        @media (max-width: 768px) {
            flex-direction: column;
        }
        display: flex;
        flex-direction: row;
        gap: 24px;
        justify-content: space-between;
        .block {
            display: flex;
            max-width: 560px;
            flex-direction: column;
            gap: 12px;
            .title {
                color: #FFF;
                font-family: 'Share Tech Mono', monospace;
                font-size: 20px;
                font-style: normal;
                font-weight: 700;
                line-height: normal;
            }

            .description {
                color: var(--text, #BABABA);
                font-family: 'Share Tech Mono', monospace;
                font-size: 14px;
                font-style: normal;
                font-weight: 700;
                line-height: 130%; /* 18.2px */
            }
        }

    }

    .exchange-buttons {
        @media (max-width: 768px) {
            width: 100%;
            button {
                margin-top: -16px;
            }
        }
    }
}

.exchange_inputs {
    @media (max-width: 768px) {
        flex-wrap: wrap;
        justify-content: center;
    }
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 16px;
}
.mobile-block {
    display: flex;
    width: 100%;
    color: rgba(255, 255, 255, 0.9);
    font-family: 'Share Tech Mono', monospace;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    font-size: 16px;
    font-weight: 400;

    .mobile-card-status {
        border-radius: 2px;
        border: 0.5px solid #CAFB01;
        background: #1F1C20;
        backdrop-filter: blur(2.5px);
        display: inline-block;
        padding: 4px 10px;
        justify-content: center;
        align-items: center;
        gap: 10px;
        color: var(--main, #CAFB01);
        text-align: center;
        font-size: 16px;
        font-style: normal;
        font-weight: 400;
        line-height: 120%; /* 19.2px */
    }
}


</style>

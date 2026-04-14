<template>
    <div class="main_block">
        <div class="page_info">
            <div class="title">
                <span>Reward Chest</span>
            </div>
            <div class="sub_title">
                <span>This displays all of your Reward Chest.</span>
            </div>
        </div>

        <div class="tabs">
            <div :class="{ 'selected': activeTab === 1 }" class="tab" @click="activeTab = 1">
                All Chest
            </div>
            <div :class="{ 'selected': activeTab === 2 }" class="tab" @click="activeTab = 2">
                My Chest
            </div>
        </div>

        <div style="margin-top: 24px;" class="achievements_block">
            <div class="achievements_content">
                <chest-card v-if="activeTab === 1" v-for="index in chests.length"
                            :key="index"
                            :achievement_data="chests[index - 1]"
                            :achievement_button="'Open'"
                            @chest-opened="getChests">
                </chest-card>

                <chest-card v-if="activeTab === 2" v-for="index in myChests.length"
                            :key="index"
                            :achievement_data="myChests[index - 1]"
                            :achievement_button="'View'">
                </chest-card>
            </div>
        </div>
    </div>
</template>

<script>

import {defineComponent} from "vue";
import forgeCard from "../parts/forge-card.vue";
import buttonWhite from "../parts/button.vue";
import CustomSelect from "../parts/custom-select.vue";
import store from "../store/store.js";
import Pagination from "../parts/Pagination.vue";
import api from "../api/api.js";
import ChestCard from "../parts/chest-card.vue";

export default defineComponent({
    components: {
        ChestCard,
        Pagination,
        CustomSelect,
        forgeCard,
        buttonWhite,
        store
    },
    data() {
        return {
            activeTab: 1,
            currentPage: 1,
            totalPages: 3,
            achievements: [],
            button_text: "Forge Trophy",
            icon_type: "Ambar",
            chests: [],

            myChests: [],
        }
    },
    methods: {
        OpenModalClaimTrophy() {
            store.state.claimTrophyModal = true;
        },
        changePage(newPage) {
            this.currentPage = newPage;
        },
        getChests() {
            api.get(`/api/chests`).then(resp => {
                this.chests = resp.data.chests;
                console.log(resp.data)
                if (resp.data?.keys.length > 0) {
                    this.checkAvailability(resp.data.keys);
                }
            })
            api.get(`/api/chests/user`).then(resp => {
                this.myChests = resp.data.chests;
            })
        },
        async checkAvailability(keys) {
            if (!store.state.userAddress) {
                await this.$store.dispatch('initMetaMaskConnection', {nftType: 'key'});
            } else {
                await this.$store.dispatch('initializeContract', {nftType: 'key'});
            }
            const userAddress = store.state.userAddress;
            const balance = await store.state.contract.methods.balanceOf(userAddress).call();

            if (keys && keys.length > 0 && balance > 0) {
                for (const keyObj of keys) {
                    const token = keyObj.token_id;
                    const keyId = keyObj.key_id;
                    const owner = await store.state.contract.methods.ownerOf(token).call();
                    if (owner === userAddress) {
                        const chest = this.chests.find(chest => chest.key_id === keyId);
                        if (chest) {
                            if (!chest.availability) {
                                chest.availability = true;
                            }
                        }
                    }
                }
            }
        }
    },
    mounted() {
        this.totalPages = Math.ceil(this.achievements.length / 3);
        this.getChests();
    },
    computed: {
        displayedAchievements() {
            const startIndex = (this.currentPage - 1) * 3;
            const endIndex = startIndex + 3;
            return this.achievements.slice(startIndex, endIndex);
        },
        trophiesChanged() {
            return store.state.modals.claimTrophyModal.show;
        }
    }
})
</script>

<style scoped>
.achievements_header {
    margin-bottom: 30px !important;
}

</style>

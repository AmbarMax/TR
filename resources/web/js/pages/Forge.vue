<template>
    <div class="main_block">
        <div class="page_info">
            <div class="title">
                <span>Forge</span>
            </div>
            <div class="sub_title">
                <span>You can forge a trophy to claim it as your own (as long as you meet the requirements for that trophy). You can also "forge a legacy", that is, mint an NFT </span>
            </div>
        </div>
        <div class="achievements_block">
            <div class="achievements_header">
                <div class="title">
                    <span>Trophies</span>
                </div>
            </div>
            <div class="achievements_content">
                <forge-card v-for="index in achievements.length"
                                  :key="index"
                                  :achievement_data="achievements[index - 1]"
                                  :achievement_button="button_text"
                                  :trophies="trophies"
                                  :type="'weight'"
                                  :button_action="'ForgeTrophy'"
                                  :icon_type="icon_type">
                </forge-card>
            </div>
<!--            <div class="achievements_content pagination only_mobile">-->
<!--                <forge-card-->
<!--                    v-for="(achievement, index) in displayedAchievements"-->
<!--                    :key="achievement.id"-->
<!--                    :achievement_data="achievement"-->
<!--                    :achievement_button="button_text"-->
<!--                    :trophies="trophies"-->
<!--                    :type="'weight'"-->
<!--                    :button_action="'ForgeTrophy'"-->
<!--                    :icon_type="icon_type"-->
<!--                ></forge-card>-->
<!--                <Pagination-->
<!--                    :current-page="currentPage"-->
<!--                    :total-pages="totalPages"-->
<!--                    @page-changed="changePage"-->
<!--                ></Pagination>-->
<!--            </div>-->
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

export default defineComponent({
    components: {
        Pagination,
        CustomSelect,
        forgeCard,
        buttonWhite,
        store
    },
    data() {
        return {
            create_achievement: "Create Achievement",
            currentPage: 1,
            totalPages: 3,
            achievements: [],
            button_text: "Forge Trophy",
            icon_type: "Ambar",
            trophies: []
        }
    },
    methods: {
        OpenModalClaimTrophy() {
            store.state.claimTrophyModal = true;
        },
        changePage(newPage) {
            this.currentPage = newPage;
        },
        getAllForges() {
            api.get(`/api/forge`).then(resp => {
                this.achievements = resp.data.trophies;
            })
        },
        getUserTrophies() {
            api.get('/api/forge/available-trophies').then( resp => {
                if (resp.status === 200) {
                    this.trophies = resp.data.trophies;
                }
            });
        }
    },
    mounted(){
        this.totalPages = Math.ceil(this.achievements.length / 3);
        this.getAllForges();
        this.getUserTrophies();
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
    },
    watch: {
        trophiesChanged() {
            this.getUserTrophies();
        }
    }
})
</script>

<style scoped>
.achievements_header {
    margin-bottom: 30px!important;
}

</style>

<template>
    <div class="main_block">
        <div class="page_info">
            <div class="title">
                <span>Validate</span>
            </div>
            <div class="sub_title">
                <span>You can create, validate and integrate your achievements. Every triumph has its medal here.</span>
            </div>
        </div>
        <div class="achievements_block">
            <div class="achievements_header">
                <div class="title">
                    <span>Achievements</span>
                </div>
            </div>
            <div class="achievements_card_block">
                <div class="achievement_create_button">
                    <button-white :text="create_achievement" @click="OpenCreateAchievementModal" class="create_button validate_button"></button-white>
                </div>
                <div class="achievements_content only_desktop">
                    <Loader v-if="loading"/>
                    <validate-card v-for="index in achievements.length" v-if="!loading"
                                   :key="index"
                                   :achievement_data="achievements[index - 1]"
                                   :achievement_button="button_text"
                                   :type="'validate'"
                                   @getAchievements="getAchievements"
                                   :icon_type="icon_type">
                    </validate-card>
                </div>
                <div class="achievements_content pagination only_mobile">
                    <validate-card
                        v-for="(achievement, index) in displayedAchievements"
                        :key="achievement.id"
                        :achievement_data="achievement"
                        :achievement_button="button_text"
                        :type="'validate'"
                        @getAchievements="getAchievements"
                        :icon_type="icon_type"
                    ></validate-card>
                </div>
            </div>
        </div>


        <PagePagination
            :items="this.achievements"
            :current-page="1"
            :total="this.achievementsData.total"
            :items-per-page="10"
            :method="getAchievements"
        />

    </div>
</template>

<script>

import {defineComponent} from "vue";
import validateCard from "../parts/validate-card.vue";
import buttonWhite from "../parts/button.vue";
import CustomSelect from "../parts/custom-select.vue";
import store from "../store/store.js";
import CreateAchievement from "../components/modals/create-achievement.vue";
import api from "../api/api.js";
import PagePagination from "../components/PagePagination.vue";
import Loader from "../components/Loader.vue";


export default defineComponent({
    computed: {
        CreateAchievement() {
            return CreateAchievement
        },
        displayedAchievements() {
            const startIndex = (this.currentPage - 1) * 10;
            const endIndex = startIndex + 10;
            return this.achievements.slice(startIndex, endIndex);
        },
        validationAchievementsChanged() {
            return store.state.getDataOnValidationPage;
        }
    },
    mounted() {
        this.totalP = Math.ceil(this.achievements.length / 10);
        this.getAchievements(this.currentPage);
    },
    components: {
        Loader,
        PagePagination,
        CustomSelect,
        validateCard,
        buttonWhite,
        store
    },
    data() {
        return {
            create_achievement: "Create Achievement",
            currentPage: 1,
            achievements: [],
            achievementsData: [],
            totalP: 3,
            button_text: "Reject Validation",
            icon_type: "Ambar",
            loading: false
        }
    },
    methods: {
        requestValidateModalOpen(){
            store.state.requestValidateModalOpen = true;
        },
        OpenCreateAchievementModal(){
            store.state.modals.createAchievement.show = true;
        },
        changePage(newPage) {
            this.currentPage = newPage;
        },
        getAchievements(page){
            this.loading = true;

            if (!page) {
                page = 1;
            }
            api.get('/api/achievement?page='+page).then( resp => {
                if (resp.status === 200) {
                    this.achievements = resp.data.data;
                    store.state.achievements = resp.data.data;
                    this.total = resp.data.total;
                    this.achievementsData = resp.data;
                }
            }).catch(e => {
                console.log('Achievement data: ', e)
            }).finally(() => {
                this.loading = false;
            });
        }
    },
    watch: {
        validationAchievementsChanged() {
            this.getAchievements();
        }
    }
})
</script>

<style scoped>
.achievements_header {
    flex-direction: row;
    margin-bottom: 30px!important;
}

</style>

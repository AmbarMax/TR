<template>
    <div class="main_block">
        <div class="page_info">
            <div class="title">
                <span>Trophy Room</span>
            </div>
            <div class="sub_title">
                <span>Craft Your Own Virtual Hall to showcase and cherish the milestones of your journey: unique achievements, trophies, and NFTs</span>
            </div>
        </div>
        <div class="tabs">
            <div :class="{ 'selected': activeTab === 1 }" class="tab" @click="changeTab(1)">
                My Vault
            </div>
            <div :class="{ 'selected': activeTab === 2 }" class="tab" @click="changeTab(2)">
                My NFTs
            </div>
        </div>

        <div class="link_to_page" @click="RedirectToVirtualHall">
            <div class="label">
                Virtual hall preview
            </div>
            <img class="arrow-left" src="../../../web/images/web/img/icons/arrow-green.svg" alt="arrow-right" />
        </div>

        <div v-if="activeTab === 1" class="my-vault-tab">
            <div class="trophies">

                <div class="achievements_title">
                    <div class="main_title">
                        <span>Select Your Legendary Achievements</span>
                    </div>
                    <div class="sub_title">
                        <span>Embrace the power to handpick the crowning achievements of your journey, showcasing them in the Virtual Hall for everyone to see!</span>
                    </div>
                </div>

                <h4 v-if="discord_achievements.length || github_achievements.length || steam_achievements.length">Badges</h4>
                <Loader v-if="badgesLoading"/>

                <div class="achievements_block">
                    <div class="achievements_header">
                        <div class="sub_title">
                            <img src="../../../web/images/web/img/icons/discord.svg" alt="icon">
                            <span>Discord</span>
                        </div>
                    </div>
                   <div class="achievements_content" v-if="discord_achievements.length" :class="{ 'no-scroll': discord_achievements.length === 1 }">
                        <achievement-card v-for="index in discord_achievements.length"
                                          :key="index"
                                          :achievement_data="discord_achievements[index - 1]"
                                          :type="'take'"
                                          :achievement_button="button_text"
                                          :icon_type="icon_type"
                                          :service="'discord'"
                                          :class="{ 'alone-element': discord_achievements.length === 1 }">
                        </achievement-card>
                    </div>
                    <div class="achievement_create_button">
                        <button-white @click="ImportData('discord')" :text="import_text" class="create_button btn-outline"></button-white>
                    </div>
                </div>
                <div class="achievements_block">
                    <div class="achievements_header">
                        <div class="sub_title">
                            <img src="../../../web/images/web/img/icons/github.svg" alt="icon">
                            <span>Github</span>
                        </div>
                    </div>
                    <div class="achievements_content" v-if="github_achievements.length" :class="{ 'no-scroll': github_achievements.length === 1 }">
                        <achievement-card v-for="index in github_achievements.length"
                                          :key="index"
                                          :achievement_data="github_achievements[index - 1]"
                                          :type="'take'"
                                          :achievement_button="button_text"
                                          :icon_type="icon_type"
                                          :service="'github'"
                                          :class="{ 'alone-element': github_achievements.length === 1 }">
                        </achievement-card>
                    </div>
                    <div class="achievement_create_button">
                        <button-white @click="ImportData('github')" :text="import_text" class="create_button btn-outline"></button-white>
                    </div>
                </div>
                <div class="achievements_block">
                    <div class="achievements_header">
                        <div class="sub_title">
                            <img src="../../../web/images/web/img/icons/steam.svg" alt="icon">
                            <span>Steam</span>
                        </div>
                    </div>
                    <div class="achievements_content" v-if="steam_achievements.length" :class="{ 'no-scroll': steam_achievements.length === 1 }">
                        <achievement-card v-for="index in steam_achievements.length"
                                          :key="index"
                                          :achievement_data="steam_achievements[index - 1]"
                                          :achievement_button="button_text"
                                          :type="'take'"
                                          :icon_type="icon_type"
                                          :service="'steam'"
                                          :class="{ 'alone-element': steam_achievements.length === 1 }">
                        </achievement-card>
                    </div>
                    <div class="achievement_create_button">
                        <button-white @click="ImportData('steam')" :text="import_text" class="create_button btn-outline trophy_button"></button-white>
                    </div>
                </div>

<!--                <Loader v-if="keysLoading"/>
                <div class="achievements_block" v-if="keys.length">
                    <div class="achievements_header">
                        <div class="sub_title">
                            <span>Keys</span>
                        </div>
                    </div>
                    <div class="achievements_content">
                        <forge-card v-for="index in keys.length" :key="index"
                                    :achievement_data="keys[index - 1]"
                                    :achievement_button="button_text"
                                    :type="'key'">
                        </forge-card>
                    </div>
                </div>-->

              <Loader v-if="trophiesLoading"/>
              <div class="achievements_block" v-if="trophies.length">
                    <div class="achievements_header">
                        <div class="sub_title">
                            <span>Trophies</span>
                        </div>
                    </div>
                    <div class="achievements_content">
                        <forge-card v-for="index in trophies.length" :key="index"
                                          :achievement_data="trophies[index - 1]"
                                          :achievement_button="button_text"
                                          :type="'weight'"
                                          :icon_type="icon_type">
                        </forge-card>
                    </div>
                </div>
                <Loader v-if="achievementLoading" />
                <div class="achievements_block" v-if="my_achievements.length">
                    <div class="achievements_header">
                        <div class="sub_title">
                            <span>Achievements</span>
                        </div>
                    </div>
                    <div class="achievements_content">
                        <validate-card v-for="index in my_achievements.length"
                                          :key="index"
                                          :achievement_data="my_achievements[index - 1]"
                                          :achievement_button="button_text"
                                          :type="'trophy-room'"
                                          @getAchievements="getAchievements"
                                          :icon_type="icon_type">
                        </validate-card>
                    </div>
                </div>
            </div>

<!--            <div class="no_trophies">-->

<!--                <div class="achievements_title">-->
<!--                    <div class="main_title">-->
<!--                        <span>Select Your Legendary Achievements</span>-->
<!--                    </div>-->
<!--                    <div class="sub_title">-->
<!--                        <span>Embrace the power to handpick the crowning achievements of your journey, showcasing them in the Virtual Hall for everyone to see!</span>-->
<!--                    </div>-->
<!--                    <div class="achievement_create_button mt-40">-->
<!--                        <button-white @click="importBudgesModalOpen" :text="import_text" class="create_button btn-outline"></button-white>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
        </div>
        <div v-if="activeTab === 2" class="my-nfts-tab">
            <div class="achievements_block">
                <div class="achievements_title">
                    <div class="main_title">
                        <span>My NFTs</span>
                    </div>
                    <div class="sub_title">
                        <span>You can choose which NFTs you want to add to the Virtual Hall for everyone to see!</span>
                    </div>
                </div>
                <div class="achievements_content only_desktop">
                    <forge-card v-for="index in NFTs.length" :key="index"
                                :achievement_data="NFTs[index - 1]"
                                :achievement_button="button_text"
                                :type="'weight'"
                                :icon_type="icon_type">
                    </forge-card>
                </div>
                <div class="achievements_content pagination only_mobile">
                    <achievement-card
                        v-for="(NFT, index) in NFTs"
                        :key="NFT.id"
                        :achievement_data="NFT"
                        :achievement_button="button_text"
                        :type="'take'"
                        :icon_type="icon_type"
                    ></achievement-card>
<!--                    <Pagination-->
<!--                        :current-page="currentPage"-->
<!--                        :total-pages="totalPages"-->
<!--                        @page-changed="changePage"-->
<!--                    ></Pagination>-->
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import {defineComponent} from "vue";
import achievementCard from "../parts/achievement-card.vue";
import buttonWhite from "../parts/button.vue";
import CustomSelect from "../parts/custom-select.vue";
import store from "../store/store.js";
// import Pagination from "../parts/Pagination.vue";
import api from "../api/api.js";
import forgeCard from "../parts/forge-card.vue";
import ValidateCard from "../parts/validate-card.vue";
import Loader from "../components/Loader.vue";

export default defineComponent({
    components: {
      Loader,
        ValidateCard,
        // Pagination,
        CustomSelect,
        achievementCard,
        buttonWhite,
        store,
        forgeCard
    },
    data() {
        return {
            count_achievements: 0,
            activeTab: 1,
            currentPage: 1,
            totalPages: 3,
            import_text: "Import",
            showTestData: true,
            trophies: [],
            keys: [],
            NFTs: [],
            achievements: [],
            github_achievements: [],
            steam_achievements: [],
            discord_achievements: [],
            button_text: "Showcase",
            icon_type: "Ambar",
            my_achievements: [],
            badgesLoading: false,
            trophiesLoading: false,
            keysLoading: false,
            achievementLoading: false
        }
    },
    methods: {
        changeTab(tabNumber) {
            this.activeTab = tabNumber;
        },
        importBudgesModalOpen(){
            store.state.importBudgesModalOpen = true;
        },
        RedirectToVirtualHall(){
            // let username = JSON.parse(localStorage.getItem('user')).username;
            window.open(`/virtual-hall/${store.state.userUsername}`, '_blank');
        },
        changePage(newPage) {
            this.currentPage = newPage;
        },
        ImportData(type) {
            const token = localStorage.getItem('access_token');
            window.location.href = '/login/' + type + '?token=' + encodeURIComponent(token);
            store.state.loaderStatus = true;
        },
        getAchievements() {
            this.achievementLoading = true;
            api.get('/api/achievement?status=1').then( resp => {
                if (resp.status === 200) {
                    this.my_achievements = resp.data.data;
                }
            }).catch(e => {
              console.log('achievement', e);
            }).finally(() => {
              this.achievementLoading = false;
            });
        },
        getKeys() {
            this.keysLoading = true;
            api.get('/api/keys').then( resp => {
                if (resp.status === 200) {
                    this.keys = resp.data;
                }
            }).catch(e => {
                console.log('keys', e);
            }).finally(() => {
                this.keysLoading = false;
            });
        }
    },
    mounted(){
        this.badgesLoading = true;
        this.trophiesLoading = true;
        this.keysLoading = true;
        this.showTestData = store.state.showTestData;
        this.totalPages = Math.ceil(this.achievements.length / 3);
        this.getKeys();
        api.get('/api/badges').then( resp => {
            if (resp.status === 200) {
                const data = resp.data.data;
                this.count_achievements = data.length;
                this.discord_achievements =  data.filter(achievement => achievement.integration === 'discord');
                this.steam_achievements =  data.filter(achievement => achievement.integration === 'steam');
                this.github_achievements =  data.filter(achievement => achievement.integration === 'github');
            }
        }).catch(e => {
          console.log('badges', e)
        }).finally(() => {
          this.badgesLoading = false;
        });
        api.get('/api/forge/trophies').then( resp => {
            if (resp.status === 200) {
              const allTrophies = resp.data.trophies;
              this.trophies = allTrophies.filter(trophy => trophy.is_nft === 0);
              this.NFTs = allTrophies.filter(trophy => trophy.is_nft === 1);
            }
        }).catch(e => {
          console.log('trophies', e)
        }).finally(() => {
          this.trophiesLoading = false;
        });
        this.getAchievements();
    },
    computed: {
        displayedAchievements() {
            const startIndex = (this.currentPage - 1) * 3;
            const endIndex = startIndex + 3;
            return this.achievements.slice(startIndex, endIndex);
        },
    }
})
</script>

<style scoped>
 .link_to_page:hover{
     cursor: pointer;
 }
 .no-scroll {
     overflow-x: auto;
 }
</style>

<template>
    <div v-if="!isDeleted" class="achievement_card" @mouseleave="closeDotsDropdown">
        <div v-if="icon_type" class="validate_card_header">
            <div v-if="achievement_data.status === 2 || achievement_data.status === 3" class="validation_status_validate_wrapper">
                <div class="validation_status_validate">
                    {{ statusString() }}
                </div>
            </div>
            <button v-if="achievement_data.is_share === 0" class="validation_share_button" @click.prevent="share">
              <svg class="share-svg" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Share in Feed</title>
                <path d="M16 5L21 10L16 15" stroke="#CAFB01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 10H13C7.47715 10 3 14.4772 3 20V21" stroke="#CAFB01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <div v-if="achievement_button" class="dots">
                <img class="tabler_dots" @click="ActivateDotsDropdown" src="../../../web/images/web/img/icons/tabler_dots.svg">
                <div class="dots_dropdown" v-if="isActiveDotsDropdown" ref="headerDropdown">
                    <ul>
                        <li @click="OpenViewModal">
                            <a class="dots_dropdown_link">
                                <img src="../../../web/images/web/img/icons/open.svg">
                                <span class="green_text">
                                    Open
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="dots_dropdown_link" @click="deleteAchievement">
                                <img src="../../../web/images/web/img/icons/trash.svg">
                                <span>
                                    Delete
                                </span>
                            </a>
                        </li>
                      <li v-if="achievement_data.status === 2 || achievement_data.status === 3">
                        <a class="dots_dropdown_link" @click="revalidate(achievement_data)" >
                          <img src="../../../web/images/web/img/icons/edit.svg">
                          <span>
                             Revalidate
                          </span>
                        </a>
                      </li>
                    </ul>
                </div>
            </div>

        </div>

        <div class="card_content">
            <div class="validation_area">

                <div class="validation_image">
                    <div class="hexagon" style="display: flex; align-items: center; justify-content: center; position:relative; width: 110%; left: -5%;">
                        <img
                            :style="{ opacity: (type === 'validate' || type === 'trophy-room') ? 0 : 1 }"
                            src="../../../web/images/web/img/achievements/borders/border.svg" class="hexagon_image" alt="hexagon image">
                        <div class="hexagon-image" style="padding-bottom: 4%; position: absolute; top:0; left:0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center">
                            <div class="hexagon_inside" style="z-index: 0; display: flex; align-items: center; justify-content: center; width: 75%; height: 40%;">
                                <img class="achievement_image" v-if="achievement_data.image !== undefined" alt="achievement image" :src="getImageUrl()">
                                <img v-else-if="service !== 'discord'" alt="achievement image" src="../../../web/images/web/img/achievements/Frame36300.svg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card_name">
                <div> {{achievement_data.name}}</div>
            </div>
            <div class="validation_message">
                <div> {{achievement_data.validation_message}}</div>
            </div>
            <button-white v-if="achievement_button && achievement_button === 'Showcase' && !achievement_data.display" :text="'Showcase'" @click="showcase(achievement_data.name)" class="action_button"></button-white>
            <button-white v-else-if="achievement_button && achievement_button === 'Showcase' && achievement_data.display" :text="'Remove'" @click="remove(achievement_data.name)" class="action_button"></button-white>

            <button-white v-else-if="achievement_button && button_action === 'ForgeTrophy'" :text="achievement_button" @click="ForgeTrophy" class="action_button"></button-white>
            <button-white v-else-if="achievement_button && (!achievement_data.status || (achievement_data.status && achievement_data.status === 'Pending review'))" :text="achievement_button" class="action_button"></button-white>

            <button-white v-if="type === 'validate' && achievement_data.status === 2" :text="'Dismiss for now'" @click="rejectValidation" class="action_button"></button-white>

            <div v-if="achievement_data.status && !virtualHall" class="label">
                <div class="text">You get 100 Ambars</div>
                <div>
                    <img v-if="icon_type === 'Ambar'" src="../../../web/images/web/img/points/ambar.svg">
                </div>
            </div>
        </div>
    </div>

</template>

<script>

import buttonWhite from "../parts/button.vue";
import CustomSelect from "./custom-select.vue";
import store from "../store/store.js";
import api from "../api/api.js";

export default {
    components: {CustomSelect, buttonWhite, store},
    props: [ 'img_link', 'text', 'achievement_data', 'achievement_button', 'icon_type', 'type', 'button_action', 'service', 'virtualHall'],
    data() {
        return {
            isDeleted: false,
            isActiveDotsDropdown: false,
            testId: 'testId'
        }
    },
    mounted() {
    },
    methods: {
        ActivateDotsDropdown(){
            this.isActiveDotsDropdown = !this.isActiveDotsDropdown;
        },
        closeDotsDropdown(){
            this.isActiveDotsDropdown = false;
        },
        handleClickOutside(){
        },
        OpenViewModal(){
            store.state.modals.trophyRoomBadge.show = true;
            store.state.modals.trophyRoomBadge.data.imageUrl = this.getImageUrl();
            store.state.modals.trophyRoomBadge.data.name = this.achievement_data.name;
            store.state.modals.trophyRoomBadge.data.description = this.achievement_data.description;
        },
        ForgeTrophy(){
            store.state.claimTrophyModal = true;
        },
        getImageUrl() {
            if (this.type === 'take') {
                return `/storage/integrations/${this.service}/${this.achievement_data.image}`;
            } else if (this.type === 'validate' || this.type === 'trophy-room') {
                return `/storage/achievements/${this.achievement_data.image}`;
            }
        },
        deleteAchievement() {
            let vm = this;
            api.post('/api/achievement/delete',{
                id: this.achievement_data.id
            }).then(resp => {
                if (resp.status === 200) {
                    vm.$emit('getAchievements');
                    store.state.notification = {
                        message: 'Achievement successfully deleted',
                        type: 'success',
                        show: true
                    };
                }
            })
        },
        revalidate(ach) {
          store.state.revalidateModal.show = true;
          store.state.revalidateAch = ach;
        },
        showcase(name) {
            let vm = this;
            api.get(`/api/achievement/${this.achievement_data.id}/showcase`).then(resp => {
                if (resp.status === 200) {
                    this.achievement_data.display = true;
                    vm.addTrophyToVirtualHallNotification(name);
                }
            })
        },
        remove(name) {
            let vm = this;
            api.get(`/api/achievement/${this.achievement_data.id}/removeShowcase`).then(resp => {
                if (resp.status === 200) {
                    this.achievement_data.display = false;
                    vm.removeTrophyToVirtualHallNotification(name);
                }
            })
        },
        addTrophyToVirtualHallNotification(name) {
            store.state.notification = {
                message: `${name} has been added to the virtual hall`,
                type: "success",
                show: true
            }
        },
        removeTrophyToVirtualHallNotification(name) {
            store.state.notification = {
                message: `${name} has been removed from the virtual hall`,
                type: "success",
                show: true
            }
        },
        shareModal(testId) {
          console.log('Share action');
          api.post(`/api/feed/share`, {id: testId}).then(resp => {
            if (resp.status === 200) {

            }
          }).catch()
        },
        statusString() {
            if (this.achievement_data.status === 1) {
                return 'Validated';
            } else if (this.achievement_data.status === 2) {
                return 'Not validated';
            } else if (this.achievement_data.status === 3) {
                return 'Rejected';
            }
        },
        rejectValidation() {
            let vm = this;
            api.post(`/api/achievement/reject`, {id: this.achievement_data.id}).then(resp => {
                if (resp.status === 200) {
                    store.state.notification = {
                        message: `${this.achievement_data.name} has been rejected`,
                        type: "info",
                        show: true
                    }
                    vm.$emit('getAchievements');
                }
            });
        },
        share() {
            let vm = this;
            api.post(`/api/achievement/share`, {id: this.achievement_data.id}).then(resp => {
                if (resp.status === 200) {
                    store.state.notification = {
                        message: `${this.achievement_data.name} achievement is shared in you Feed`,
                        type: "success",
                        show: true
                    }
                    vm.$emit('getAchievements');
                }
            });
        }
    },
}

</script>

<style scoped>
.dots {
    position: relative;
}

.dots_dropdown {
    padding: 12px 0;
    right: 0;
    top: 30px;
    z-index: 10;
    position: absolute;
    width: 180px;
    background-color: #212124;
    border-radius: 4px; flex-direction: column; justify-content: flex-start; align-items: flex-start;
}

.dots_dropdown_link {
    padding: 0 12px;
    display: flex;
    flex-direction: row;
    gap: 8px;
    align-items: center;
    justify-content: flex-start;
    height: 40px;
}

.dots_dropdown_link span {
    font-size: 14px;
    font-weight: 400;
    line-height: 130%;
    cursor: pointer;
    color: rgba(186, 186, 186, 0.60);

}

.dots_dropdown_link svg {
    width: 20px;
    height: 20px;
}

.dots_dropdown ul li:hover {
    background-color: #313133;
    cursor: pointer;
}

.green_text {
    color: #CAFB01!important;
}

.validation_image img {
    display: block;
    z-index: 2;
}

.share-svg{
  cursor: pointer;
}

.hexagon_inside{
  width: 560px!important;
  height: 250px!important;
  overflow: hidden;
}

.hexagon_inside .achievement_image{
  width: 100%;
  height: 100%;
  object-fit: cover;
}

</style>

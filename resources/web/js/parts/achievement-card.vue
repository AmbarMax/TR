<template>
    <div v-if="!isDeleted" class="achievement_card" @mouseleave="closeDotsDropdown">
        <div v-if="icon_type" class="card_header">
            <div v-if="type === 'take'" class="label">
                <div class="text">You get 50 Ambars</div>
                <div>
                    <img v-if="icon_type === 'Ambar'" src="../../../web/images/web/img/points/ambar.svg">
                </div>
            </div>
            <button v-if="achievement_data.is_share === false" class="validation_share_button" @click.prevent="share">
                <svg class="share-svg" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <title>Share in Feed</title>
                    <path d="M16 5L21 10L16 15" stroke="#CAFB01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 10H13C7.47715 10 3 14.4772 3 20V21" stroke="#CAFB01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div v-if="type === 'weight'" class="label type-weight">
                <div class="text">Weight &nbsp {{achievement_data.count}}</div>
                <div>
                    <img style="max-width: 22px" v-if="icon_type === 'Ambar'" src="../../../web/images/web/img/points/ambar.svg">
                </div>
            </div>
            <div v-if="achievement_button" class="dots">
<!--              <svg @click="shareModal(testId)" class="share-svg" width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">-->
<!--                <path d="M16 5L21 10L16 15" stroke="#CAFB01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>-->
<!--                <path d="M21 10H13C7.47715 10 3 14.4772 3 20V21" stroke="#CAFB01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>-->
<!--              </svg>-->
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
                            <a class="dots_dropdown_link" @click="deleteBadge">
                                <img src="../../../web/images/web/img/icons/trash.svg">
                                <span>
                                    Delete
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
                            :style="{ opacity: (type === 'validate') ? 0 : 1 }"
                            src="../../../web/images/web/img/achievements/borders/border.svg" class="hexagon_image" alt="hexagon image">
                        <div class="hexagon-image" style="padding-bottom: 4%; position: absolute; top:0; left:0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center">
                            <div class="hexagon_inside" style="z-index: 0; display: flex; align-items: center; justify-content: center; width: 75%; height: 40%;">
                                <img class="achievement_image" v-if="achievement_data.image !== undefined" alt="achievement image" :src="getImageUrl()">
                                <img v-else-if="service !== 'discord'" alt="achievement image" src="../../../web/images/web/img/achievements/Frame36300.svg">
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="achievement_data.status" class="validation_status">
                    {{ statusString() }}
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

            <button-white v-if="type === 'validate' && achievement_data.status === 2" :text="'Reject Validation'" @click="rejectValidation" class="action_button"></button-white>


        </div>
    </div>

</template>

<script>

import buttonWhite from "../parts/button.vue";
import requestValidate from "../components/modals/request-validate.vue";
import CustomSelect from "./custom-select.vue";
import store from "../store/store.js";
import api from "../api/api.js";


export default {
    components: {CustomSelect, buttonWhite, store},
    props: [ 'img_link', 'text', 'achievement_data', 'achievement_button', 'icon_type', 'type', 'button_action', 'service'],
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
        },
        ForgeTrophy(){
            store.state.claimTrophyModal = true;
        },
        getImageUrl() {
            return `/storage/integrations/${this.service}/${this.achievement_data.image}`;
        },
        deleteBadge() {
            api.get(`/api/badges/${this.achievement_data.id}/destroy`).then(resp => {
                if (resp.status === 200) {
                    this.isDeleted = true;
                }
            })
        },
        share() {
            let vm = this;
            api.post(`/api/badges/share`, {id: this.achievement_data.id,}).then(resp => {
                if (resp.status === 200) {
                    store.state.notification = {
                        message: `${this.achievement_data.name} badge is shared in you Feed`,
                        type: "success",
                        show: true
                    }
                    this.achievement_data.is_share = true;
                }
            });
        },
        showcase(name) {
            let vm = this;
            api.get(`/api/badges/${this.achievement_data.id}/showcase`).then(resp => {
                if (resp.status === 200) {
                    this.achievement_data.display = true;
                    vm.addTrophyToVirtualHallNotification(name);
                }
            })
        },
        remove(name) {
            let vm = this;
            api.get(`/api/badges/${this.achievement_data.id}/remove`).then(resp => {
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
            return this.achievement_data.status === 1 ? 'Validated' : 'Not validated';
        },
        rejectValidation() {
            //
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
  width: 75%!important;
  height: 75%!important;
  overflow: hidden;
}

.hexagon_inside .achievement_image{
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.achievement_card .validation_area .validation_image img {
    max-height: 100%!important;
}

.achievement_card {
    min-width: 260px;
}

.action_button {
    margin-top: 0;
}

</style>

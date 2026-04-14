<template>
    <div v-if="!isDeleted" class="achievement_card" @mouseleave="closeDotsDropdown">
        <div v-if="icon_type" class="card_header">
            <div v-if="type === 'take' && page !== 'virtual-hall'" class="label">
                <div class="text">You get {{achievement_data.count}} Ambars</div>
                <div>
                    <img v-if="icon_type === 'Ambar'" src="../../../web/images/web/img/points/ambar.svg">
                </div>
            </div>
            <div v-if="type === 'weight' && page !== 'virtual-hall'" class="label type-weight">
                <div class="text">Weight &nbsp {{ Math.floor(achievement_data.price) }}</div>
                <div>
                    <img style="max-width: 22px" v-if="icon_type === 'Ambar'" src="../../../web/images/web/img/points/ambar.svg">
                </div>
            </div>
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
                    </ul>
                </div>
            </div>
        </div>

        <div class="card_content">
            <div class="validation_area">

                <div class="validation_image">
                    <div class="hexagon" style="display: flex; align-items: center; justify-content: center; position:relative; width: 110%; left: -5%;">
                        <img
                            :style="{
                                  opacity: (this.achievement_data.type === '0' || this.achievement_data.type === '1' || this.achievement_data.type === '2' ) ? '1' : '0',
                                }"
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
                    {{achievement_data.status}}
                </div>
            </div>
            <div class="card-wrapper">
                <div class="card_name">
                      {{achievement_data.name}}
                </div>

                    <div v-if="achievement_data['is_nft']" class="nft_ammount">
                        <span>NFT</span>
                        <span>{{achievement_data['minted'] + '/' +  achievement_data['max_supply'] }}</span>
                    </div>
            </div>
            <div class="validation_message">
                  {{achievement_data.description}}
            </div>
            <template v-if="type === 'key'">
                <button-white :text="'View'" @click="showModal()" class="action_button"></button-white>
            </template>
            <template v-else>
                <button-white v-if="achievement_button && achievement_button === 'Showcase' && !achievement_data.pivot.display" :text="'Showcase'" @click="showcase(achievement_data.name)" class="action_button"></button-white>
                <button-white v-else-if="achievement_button && achievement_button === 'Showcase' && achievement_data.pivot.display" :text="'Remove'" @click="remove(achievement_data.name)" class="action_button"></button-white>

                <button-white
                    :disabled="isDisabledButton"
                    v-else-if="achievement_button && button_action === 'ForgeTrophy'" :text="achievement_button" @click="ForgeTrophy(achievement_data)" class="action_button"></button-white>
                <button-white v-else-if="achievement_button && (!achievement_data.status || (achievement_data.status && achievement_data.status === 'Pending review'))" :text="achievement_button" class="action_button"></button-white>
            </template>

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
    props: [ 'img_link', 'text', 'achievement_data', 'achievement_button', 'icon_type', 'type', 'button_action', 'service', 'trophies', 'page'],
    data() {
        return {
            isDeleted: false,
            isActiveDotsDropdown: false,
        }
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
        ForgeTrophy(achievement_data){
            store.state.modals.claimTrophyModal.show = true;
            store.state.modals.claimTrophyModal.data = achievement_data;
        },
        getImageUrl() {
            if (this.type === 'key'){
                return `/storage/keys/${this.achievement_data.image}`;
            } else {
                return `/storage/trophies/${this.achievement_data.image}`;
            }
          //TODO: Replace with token url that will be returned from Blockchain using token id.
        },
        showModal(){
            store.state.modals.chestShowModal.data.name = this.achievement_data.name;
            store.state.modals.chestShowModal.data.type = 'key';
            store.state.modals.chestShowModal.data.description = this.achievement_data.description;
            store.state.modals.chestShowModal.data.image = this.achievement_data.image;
            store.state.modals.chestShowModal.show = true;
        },
        showcase(name) {
            let vm = this;
            api.get(`/api/forge/${this.achievement_data.id}/showcase`).then(resp => {
                if (resp.status === 200) {
                    this.achievement_data.pivot.display = true;
                    vm.addTrophyToVirtualHallNotification(name);
                }
            })
        },
        remove(name) {
            let vm = this;
            api.get(`/api/forge/${this.achievement_data.id}/remove`).then(resp => {
                if (resp.status === 200) {
                    this.achievement_data.pivot.display = false;
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
        }
    },
    computed: {
        isDisabledButton() {
            return this.trophies.find(item => item.id === this.achievement_data.id)
        }

    }
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

.validation_image {
  /*  padding: 50px;
    position: relative; !* Устанавливаем позицию элемента как относительную *!*/
}

.image_overlay {
    bottom: 7px;
    position: absolute;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url(../../images/web/img/achievements/borders/border.svg);
    background-size: cover;
    z-index: 1;
}

.validation_image img {
    display: block;
    z-index: 2;
}

.hexagon_inside{
  width: 560px!important;
  height: 250px!important;
  overflow: hidden;
}

.hexagon_inside .achievement_image{
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.nft_ammount{
    color: #CAFB01;
    display: flex;
    flex-direction: column;
}

.card-wrapper {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    gap: 20px;
}

</style>

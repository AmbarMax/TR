<template>
    <div class="achievement_card">
        <div v-if="achievement_button === 'Open'" class="card_header">
            <div class="label">
                <div class="text">You need to have key <!--‘{{ achievement_data.key.name }}’--></div>
                <div>
                    <img style="width: 20px; height: 20px; object-fit: cover; margin-left: 8px;" :src="getKeyImageUrl(achievement_data.key.image)">
                </div>
            </div>
        </div>

        <div class="card_content">
            <div class="validation_area" :class="{'inactive': achievement_button === 'Open' && !achievement_data.availability}">
                <div class="validation_image">
                    <div class="hexagon" style="display: flex; align-items: center; justify-content: center; position:relative; width: 110%; left: -5%;">
                        <img
                            :style="{
                                  opacity: (this.achievement_data.type === '0' || this.achievement_data.type === '1' || this.achievement_data.type === '2' ) ? '1' : '0',
                                }"
                             src="../../../web/images/web/img/achievements/borders/border.svg" class="hexagon_image" alt="hexagon image">
                        <div class="hexagon-image" style="padding-bottom: 4%; position: absolute; top:0; left:0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center">
                            <div v-if="achievement_button === 'Open'" class="hexagon_inside" style="z-index: 0; display: flex; align-items: center; justify-content: center; width: 75%; height: 40%;">
                                <img class="achievement_image" v-if="achievement_data.image_closed !== null" alt="achievement image" :src="getImageUrl(achievement_data.image_closed)">
                                <img v-else alt="achievement image" src="../../../web/images/web/img/chests/closed-chest.svg">
                            </div>
                            <div v-else class="hexagon_inside" style="z-index: 0; display: flex; align-items: center; justify-content: center; width: 75%; height: 40%;">
                                <img class="achievement_image" v-if="achievement_data.image_open !== null" alt="achievement image" :src="getImageUrl(achievement_data.image_open)">
                                <img v-else alt="achievement image" src="../../../web/images/web/img/chests/open-chest.svg">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card-wrapper">
                <div class="card_name">
                      {{achievement_data.name}}
                </div>

            </div>
            <div class="validation_message">
                  {{achievement_data.description}}
            </div>
            <template v-if="achievement_button === 'View'" >
                <button-white :text="'View'" @click="view(achievement_data.id)" class="action_button btn-outline"></button-white>
            </template>
            <template v-if="achievement_button === 'Open'">
                <button-white v-if="achievement_data.availability" :text="'Open'" @click="open(achievement_data.id)" class="action_button"></button-white>
                <button-white v-else :text="'Open'" :disabled="true" class="action_button"></button-white>
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
        open(id){
            api.get(`/api/chests/${this.achievement_data.id}/open`).then(resp => {
                if (resp.status === 200) {
                    store.state.notification = {
                        message: `${this.achievement_data.name} was successfully opened`,
                        type: "success",
                        show: true
                    }
                    this.$emit('chest-opened');
                }
            })
        },
        view(){
            api.get(`/api/chests/${this.achievement_data.id}/view`).then(resp => {
                if (resp.status === 200) {
                    store.state.modals.chestShowModal.data.name = this.achievement_data.name;
                    store.state.modals.chestShowModal.data.items = resp.data.chest.items;
                    store.state.modals.chestShowModal.data.description = "This chest contains skins such as:";
                    store.state.modals.chestShowModal.show = true;
                }
            })
        },
        ForgeTrophy(achievement_data){
            store.state.modals.claimTrophyModal.show = true;
            store.state.modals.claimTrophyModal.data = achievement_data;
        },
        getImageUrl(image) {
          return `/storage/chests/${image}`;
        },
        getKeyImageUrl(image) {
            return `/storage/keys/${image}`;
        },
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

.validation_area.inactive {
    opacity: 0.3;
}

</style>

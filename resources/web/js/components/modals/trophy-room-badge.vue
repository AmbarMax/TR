<template>
    <div class="modal_background" @click.self="closeModal">
        <div class="modal_window_transparent popup_card_content">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeModal" class="modal_close_button">
            <div class="image-block">
                <img :src="imageUrl" alt="achievement" class="achievement">
                <img v-if="!description" src="../../../../web/images/web/img/achievements/borders/border.svg" alt="hexagon" class="hexagon-modal">
            </div>
            <div class="card_category_name_block">
                <div class="card_category_name">
                    <span>Name</span>
                </div>
                <h2>
                    {{ name }}
                </h2>
            </div>
            <div v-if="description" class="card_category_description_block">
                <div class="card_category_name">
                    <span>Description</span>
                </div>
                <p>
                  {{ description }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>

import buttonWhite from "../../parts/button.vue";
import store from "../../store/store.js";
export default {
    components: {
        buttonWhite
    },
    data() {
        return {
            name: null,
            imageUrl: null,
            description: null
        }
    },
    methods: {
        closeModal() {
            store.state.modals.trophyRoomBadge.show = false;
            store.state.modals.trophyRoomBadge.data.name = null;
            store.state.modals.trophyRoomBadge.data.description = null;
        },
    },
    mounted() {
        this.name = store.state.modals.trophyRoomBadge.data.name;
        this.imageUrl = store.state.modals.trophyRoomBadge.data.imageUrl;
        this.description = store.state.modals.trophyRoomBadge.data.description;
    }

}
</script>

<style scoped>
    .image-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .achievement {
        width: 400px;
        height: 400px;
        margin-bottom: 1%;
        object-fit: cover;
    }
    .hexagon-modal {
        width: 300px;
        height: 300px;
        margin-top: -300px;
    }
    @media (max-width: 508px) {
      .achievement{
        width: 320px;
        height: 400px;
      }
    }
</style>

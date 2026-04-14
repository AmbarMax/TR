<template>
    <div class="modal_background" @click.self="closeModal">
        <div class="modal_window_transparent popup_card_content">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeModal" class="modal_close_button">

            <div class="card_category_name_block">
                <h2>
                    {{ name }}
                </h2>
            </div>
            <div v-if="description" class="card_category_description_block">
                <p>
                    {{ description }}
                </p>
            </div>

            <div v-if="type === 'key'" class="items">
                <img :src="getImageUrl(imageUrl)" alt="achievement" class="achievement">
            </div>

            <div v-else class="items">
                <div v-for="item in items" class="item-block">
                    <img :src="getImageUrl(item.image)" alt="achievement" class="achievement">
                    <div class="title">{{ item.name}}</div>
                </div>
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
            description: null,
            items: [],
            type: 'chest',
        }
    },
    methods: {
        closeModal() {
            store.state.modals.chestShowModal.show = false;
            store.state.modals.chestShowModal.data.name = null;
            store.state.modals.chestShowModal.data.description = null;
        },
        getImageUrl(image) {
            if (store.state.modals.chestShowModal.data.type === 'key'){
                return `/storage/keys/${image}`;
            } else {
                return `/storage/trophies/${image}`;
            }
        },
    },
    mounted() {
        this.name = store.state.modals.chestShowModal.data.name;
        this.imageUrl = store.state.modals.chestShowModal.data.imageUrl;
        this.description = store.state.modals.chestShowModal.data.description;
        this.items = store.state.modals.chestShowModal.data.items;
        if (store.state.modals.chestShowModal.data.type === 'key'){
            this.type = 'key'
            this.imageUrl = store.state.modals.chestShowModal.data.image
        }
    }

}
</script>

<style scoped>
.items {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 16px;


    .item-block {
        display: flex;
        padding: 12px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 6px;
        flex: 1 0 0;
        border-radius: 3px;
        border: 1px solid var(--main, #6d7f24);
        background: rgba(255, 255, 255, 0.10);
        backdrop-filter: blur(2.5px);
        max-width: 140px;
        width: 100%;
        height: 140px;
        img {
            width: 100%;
            height: 100%;
        }
        .title {
            color: var(--H, rgba(255, 255, 255, 0.90));
            font-family: "JetBrains Mono";
            font-size: 16px;
            font-style: normal;
            font-weight: 400;
            line-height: 130%; /* 20.8px */
        }
    }
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

<template>
    <div class="modal_background" @click.self="closeModal">
        <div class="trb-modal">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeModal" class="modal_close_button">
            <div class="trb-image-block">
                <img :src="imageUrl" alt="badge" class="trb-image">
            </div>
            <h2 class="trb-name">{{ name }}</h2>
            <p v-if="description" class="trb-description">{{ description }}</p>
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
.trb-modal {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 8px;
    padding: 32px 28px 36px;
    max-width: 380px;
    width: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
}

.trb-image-block {
    display: flex;
    justify-content: center;
}

.trb-image {
    width: 100%;
    max-width: 300px;
    border-radius: 8px;
    object-fit: contain;
}

.trb-name {
    color: #feeddf;
    font-family: 'Share Tech Mono', monospace;
    font-size: 20px;
    font-weight: 400;
    margin: 0;
    text-align: center;
}

.trb-description {
    color: #9a9590;
    font-family: 'Share Tech Mono', monospace;
    font-size: 14px;
    margin: 0;
    text-align: center;
    line-height: 1.5;
}
</style>

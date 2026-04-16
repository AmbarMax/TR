<template>
    <div class="modal_background" @click.self="closeAmbarValidate">
        <div class="modal_window">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeAmbarValidate" class="modal_close_button">
            <h1 class="modal_header">
                Ambar will validate
            </h1>
            <h3 class="modal_label">
                You are going to need upload your evidence:
            </h3>

            <div v-if="selectedImage" class="uploaded_image_block">
                <img :src="selectedImage" alt="Selected Image" class="uploaded_image" />
                <img src="../../../../web/images/web/img/icons/trash.svg" alt="close" @click="clearImage" class="remove_image">
            </div>
            <div v-else>
                <img src="../../../../web/images/web/img/inputs/upload_file.svg" alt="close" class="upload_image"
                     @click="openFileInput"
                     @dragover.prevent
                     @drop="handleDrop"
                >
            </div>

            <input
                ref="fileInput"
                type="file"
                style="display: none;"
                @change="handleFileChange"
            />
            <div class="point_need">
                {{ point_number }} Ambars
                <img src="../../../../web/images/web/img/points/ambar.svg" alt="ambar">
            </div>

            <h3 class="modal_label">
                Have this 3 badges:
            </h3>
            <div class="trophies">
                <img src="../../../../web/images/web/img/trophies/trophy_1.svg" alt="troph">
                <img src="../../../../web/images/web/img/trophies/trophy_2.svg" alt="rune">
                <img src="../../../../web/images/web/img/trophies/trophy_3.svg" alt="rune">
            </div>

            <button-white :text="'Validate Achievement'" class="validate_achievement_with_button"></button-white>
            <h4 class="modal_small_label">
                Validation could take some time
            </h4>
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
            social_proof_button_text: 'Validate achievement',
            proof_button_image: '../../../../web/images/web/img/social_icons/proof.svg',
            selectedImage: null,
            point_number: 10,
        }
    },
    methods: {
        closeAmbarValidate() {
            store.state.ambarValidateModalOpen = false;
        },
        openFileInput() {
            this.$refs.fileInput.click();
        },
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    this.selectedImage = reader.result;
                };
                reader.readAsDataURL(file);
            }
        },
        handleDrop(event) {
            event.preventDefault();
            const file = event.dataTransfer.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    this.selectedImage = reader.result;
                };
                reader.readAsDataURL(file);
            }
        },
        clearImage() {
            this.selectedImage = null;
        }
    }

}
</script>

<style scoped>
.modal_window {
    padding: 40px 30px 60px 30px;
}

.main-button {
    margin-top: 24px;
    margin-bottom: 12px;
    border-radius: 2px;
    font-size: 18px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 20px;
}
.modal_sign_up_with_button {
    height: 40px;
}

.point_current {
    margin-top: 30px;
    margin-bottom: 12px;
    color: rgba(255, 255, 255, 0.90);
    font-size: 20px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 28px;
}

.number {
    color: #CAFB01;
}

.point_need {
    display: flex;
    align-items: center;
    text-align: center;
    gap: 8px;
    color: #CAFB01;
    font-size: 16px;
    font-family: $orbitron;
    font-weight: 700;
    line-height: 22px;
    word-wrap: break-word;
    margin-top: 30px;
}

.trophies {
    display: flex;
    gap: 14px;
    margin-top: 12px;
    margin-bottom: 16px;
}

.mb-0 {
    margin-bottom: 0!important;
}

.mb-30 {
    margin-bottom: 30px!important;
}

.modal_small_label {
    margin-bottom: 12px;
}

.upload_image {
    margin: 30px 0 0;
}

.upload_image:hover {
    cursor: pointer;
}

.uploaded_image_block {
    border-radius: 4px;
    border: 1px solid rgba(186, 186, 186, 0.60);
    max-height: 250px;
    height: 250px;
    width: 100%;
    padding: 30px 16px;
    background: rgba(186, 186, 186, 0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 30px;
}

.uploaded_image {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.remove_image {
    position: absolute;
    top: 200px;
    right: 46px;
}

.remove_image:hover {
    cursor: pointer;
}
</style>

<template>
    <div class="modal_background" @click.self="closeCreateAchievement">
        <div class="modal_window_transparent popup_card_content">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeCreateAchievement" class="modal_close_button">

            <h1 class="modal_header">
                Create your achievement
            </h1>

            <div v-if="selectedImage" class="uploaded_image_block">
                <img :src="selectedImage" alt="Selected Image" class="uploaded_image" />
                <img src="../../../../web/images/web/img/icons/trash.svg" alt="close" @click="clearImage" class="remove_image">
            </div>
            <div class="default_image" v-else>
                <img src="../../../../web/images/web/img/inputs/upload_file.svg" alt="close" class="upload_image"
                     @click="openFileInput"
                     @dragover.prevent
                     @drop="handleDrop"
                >
                <div class="upload_file"><span class="white">Upload a file</span> or drag and drop PNG, JPG up to 10MB</div>
            </div>

            <input
                ref="fileInput"
                type="file"
                style="display: none;"
                accept=".jpg, .jpeg, .png"
                size="10485760"
                @change="handleFileChange"
            />

            <div class="card_category_name_block">
                <div class="card_category_name">
                    <span>Name</span>
                </div>
                <input type="text" v-model="name" class="validate_modal_input">
            </div>

            <div class="card_category_description_block">
                <div class="card_category_name">
                    <span>Description</span>
                </div>
                <textarea class="validate_modal_textarea" v-model="description" rows="8" @input="lengthLimit">
                </textarea>
                <div class="charsCount">
                    {{description.length}}/255
                </div>
            </div>

            <button-white @click="OpenModalCreateAchievement" :text="create_button_text" :disabled="!activeButton" class="modal_sign_up_with_button"></button-white>
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
            create_button_text: 'Create Achievement',
            name: '',
            description: '',
            selectedImage: null,
            selectedImageFile: null,
        }
    },
    methods: {
        closeCreateAchievement() {
            store.state.modals.createAchievement.show = false;
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
                this.selectedImageFile = file;
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
                this.selectedImageFile = file;
            }
        },
        clearImage() {
            this.selectedImage = null;
            this.selectedImageFile = null;
        },
        OpenModalCreateAchievement() {
            store.state.modals.createAchievement.show = false;
            store.state.modals.createAchievement.data = {
                image: this.selectedImageFile,
                name: this.name,
                description: this.description
            };
            store.state.requestValidateModalOpen = true;
        },
        lengthLimit(){
            if (this.description.length > 255) {
                this.description = this.description.slice(0, 255);
            }
        }
    },
    computed: {
        activeButton() {
            return !!(this.selectedImage && this.name.length && this.description);
        }
    }

}
</script>

<style scoped>

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
    top: 160px;
    right: 46px;
}

.remove_image:hover {
    cursor: pointer;
}

.default_image {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
}

.upload_file {
    text-align: center;
    max-width: 300px;
    bottom: 20px;
    position: absolute;
    color: #BABABA;
    font-size: 14px;
    font-family: JetBrains Mono;
    font-weight: 400;
    line-height: 18.20px;
    word-wrap: break-word
}

.white {
    color: white;
}

@media (max-width: 969px) {
    .upload_file {
        bottom: 8px;
    }
}
</style>

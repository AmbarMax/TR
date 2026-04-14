<template>
    <div class="modal_background" @click.self="closeValidatePopUpOpenCardModalOpen">
        <div class="modal_window_transparent popup_card_content">
            <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeValidatePopUpOpenCardModalOpen" class="modal_close_button">

            <img src="../../../../web/images/web/img/achievement.svg" alt="achievement" class="popup_card_main_image">

            <div class="card_category">
                <h4>
                    Сategory
                </h4>
                <div class="card_category_type">
                    <img src="../../../../web/images/web/img/modal-achievements-icons/historical.svg" alt="icon">
                    <span>
                        Historical
                    </span>
                </div>
            </div>

            <div class="card_category_name_block">
                <div class="card_category_name">
                    <span>Name</span>
                    <img src="../../../../web/images/web/img/icons/edit.svg" alt="edit" @click="nameEditFunc">
                </div>
                <h2 v-if="!nameEdit">
                    {{ name }}
                </h2>
                <input v-if="nameEdit" type="text" v-model="name" class="validate_modal_input">
            </div>

            <div class="card_category_description_block">
                <div class="card_category_name">
                    <span>Description</span>
                    <img src="../../../../web/images/web/img/icons/edit.svg" alt="edit" @click="descriptionEditFunc">
                </div>
                <p v-if="!descriptionEdit">
                    {{ description }}
                </p>
                <textarea v-if="descriptionEdit" class="validate_modal_textarea" v-model="description" rows="8">
                </textarea>
                <div v-if="descriptionEdit" class="charsCount">
                    {{description.length}}/255
                </div>
            </div>

            <button-white @click="updateData" v-if="updateButtonIsOpen" :text="update_button_text" class="modal_sign_up_with_button"></button-white>
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
            update_button_text: 'Update',
            updateButtonIsOpen: false,
            name: 'Book of dragons',
            description: 'Precious Achievement. Inside it are stored age-old secrets and legends of mighty dragons. It' +
                'serves as a symbol of wisdom and knowledge, as well as opening the path to incredible adventures' +
                'and treasures in the world of fantasy creatures.',
            nameEdit: false,
            descriptionEdit: false
        }
    },
    methods: {
        closeValidatePopUpOpenCardModalOpen() {
            store.state.validatePopUpOpenCardModalOpen = false;
        },
        nameEditFunc() {
            this.updateButtonIsOpen = true
            this.nameEdit = true
        },
        descriptionEditFunc() {
            this.updateButtonIsOpen = true
            this.descriptionEdit = true

        },
        updateData() {
            this.updateButtonIsOpen = false
            this.nameEdit = false
            this.descriptionEdit = false
        }
    }

}
</script>

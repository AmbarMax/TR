<template>
    <div class="main_block profile_page">
        <h1 class="profile_header">
            My Profile
        </h1>
        <div class="profile_images_block">
            <div class="profile_background_image profile_page_image">
                <div>
                    <img v-if="background !== null" :src="getBackground()" alt="user_bg" style="position: absolute">
                    <img @click="openInputBackground" src="../../../web/images/web/img/icons/upload.svg" class="trash-icon" alt="user_bg">
<!--
                    <img v-else @click="deleteBackground" src="../../../web/images/web/img/icons/trash-2.svg" class="trash-icon" alt="user_bg">
-->

                    <input accept=".jpg, .jpeg, .png" name="background" ref="fileInputBackground" type="file" @change="uploadBackground" style="display: none" />
                </div>
            </div>
            <div class="avatar_block">
                <div class="profile_avatar">
                    <img v-if="avatar !== null" alt="achievement image" :src="getAvatar()">
                    <img v-else  src="../../../web/images/web/img/user.svg" alt="user">
                </div>
                <div class="change_avatar">
                    <button-white :text="'Choose your profile photo'" @click="openInputAvatar" class="button-flex-start btn-outline"></button-white>
                    <input accept=".jpg, .jpeg, .png" name="avatar" ref="fileInput" type="file" @change="uploadAvatar" style="display: none" />
                </div>
            </div>

        </div>
        <div class="profile_inputs_block">
            <h3 class="profile_subheader">
                Personal Information
            </h3>
            <div class="profile_inputs">
                <div class="profile_inputs_row">
                    <label for="" class="profile_input">
                        <span>
                            Name
                        </span>
                        <input type="text" autocomplete="off" v-model="name" />
                    </label>
                    <label for="" class="profile_input">
                        <span>
                            Username
                        </span>
                        <input @input="filterUsername" type="text" autocomplete="off" v-model="username" />
                    </label>
                    <label for="" class="profile_input">
                        <span>
                            Email
                        </span>
                        <input type="email" name="email" autocomplete="off" v-model="email"/>
                    </label>
                </div>
                <div class="profile_inputs_row">
                    <label for="" class="profile_input">
                        <span>
                            Phone Number
                        </span>
                        <input type="tel" autocomplete="off" v-model="phone_number"/>
                    </label>
                    <label for="" class="profile_input">
                        <span>
                            Date of Birth
                        </span>
                        <VueDatePicker class="datetime" v-model="date_of_birth" dark auto-apply :enable-time-picker="false" :max-date="maxDate" :only-calendar="true" />

                    </label>
                    <label for="" class="profile_input">
                        <span>
                            Country
                        </span>
<!--                        <input type="text"/>-->
                        <CustomSelectCountries
                            v-if="allCountries.length"
                            @handleCountry="handleChildData"
                            :id="country_id"
                            :options="allCountries"
                            class="profile_select"
                        />
                    </label>
                </div>
                <div class="profile_inputs_row">
                    <label for="" class="profile_input profile_input_description">
                        <span>
                            Description
                        </span>
                        <textarea type="text" autocomplete="off" v-model="description"></textarea>
                    </label>
                </div>
            </div>
            <button-white :text="update_btn" class="button-flex-start" @click="updateProfile"></button-white>
        </div>

        <div class="profile_password_block">
            <h3 class="profile_subheader_pass">
                Change password
            </h3>
            <div class="profile_inputs">

                <div class="profile_inputs_row">
                    <div class="password_input_block">
                        <label for="" class="profile_input" ref="passwordInputOld">
                            <span>
                                Old password
                            </span>
                            <input type="password" autocomplete="off" class="modal_input" ref="passwordInputOld" v-model="oldPassword">
                            <img v-if="passEyes.old" src="../../../web/images/web/img/icons/eye-open.svg" alt="eye_open" @click="togglePassViewOld">
                            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="eye_close" @click="togglePassViewOld">
                        </label>
                    </div>
                    <div class="password_input_block">
                        <label for="" class="profile_input" ref="passwordInputNew">
                            <span>
                                New password
                            </span>
                            <input type="password" autocomplete="off" class="modal_input" ref="passwordInputNew" v-model="newPassword">
                            <img v-if="passEyes.new" src="../../../web/images/web/img/icons/eye-open.svg" alt="eye_open" @click="togglePassViewNew">
                            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="eye_close" @click="togglePassViewNew">
                        </label>
                    </div>
                    <div class="password_input_block">
                        <label for="" class="profile_input" ref="passwordInputConfirm">
                            <span>
                                Confirm new password
                            </span>
                            <input type="password" class="modal_input" ref="passwordInputConfirm" v-model="confirmPassword">
                            <img v-if="passEyes.confirm" src="../../../web/images/web/img/icons/eye-open.svg" alt="eye_open" @click="togglePassViewConfirm">
                            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="eye_close" @click="togglePassViewConfirm">
                        </label>
                    </div>
                </div>
            </div>
            <button-white :text="update_btn" class="button-flex-start-pass" @click="updatePassword"></button-white>
        </div>

        <div class="profile_password_block">
            <h3 class="profile_subheader_pass">
                Two factor authentication
            </h3>
            <h3 class="profile_subheader_two_fa">
                Status: <strong> {{$store.state.google2fa_status ? 'Active' : 'Inactive'}} </strong>
            </h3>
            <div v-if="!$store.state.google2fa_status" class="profile_subheader_two_fa_buttons">
                <button-white :text="'Activate'" class="button-flex-start-pass" @click="activate2fa"></button-white>
            </div>
            <div v-if="$store.state.google2fa_status" class="profile_subheader_two_fa_buttons">
                <button-white :text="'Deactivate'" class="button-flex-start-pass" @click="deactivate2fa"></button-white>
                <button-white :text="'Change secret code'" class="button-flex-start-pass" @click="activate2fa"></button-white>
            </div>
        </div>


        <div class="blocks-separator"></div>

        <div class="profile_tokens_block">
            <div class="profile_tokens_info">
                <h3>
                    Tokens
                </h3>
                <p>
                    You can add tokens and exchange them to get Ambar, URU and runes
                </p>
                <button-white :text="exchange_tokens_btn" :disabled="true" class="button-flex-start mw-210"></button-white>
              <p>
                *Coming soon
              </p>
            </div>
            <div class="profile_tokens_badge_block">
                <div class="profile_tokens_badge">
                    <img src="../../../web/images/web/img/points/crystal.svg" alt="crystal">
                    <span>
                        100k
                    </span>
                </div>
                <span class="profile_tokens_add_button">
                    Add Tokens +
                </span>
            </div>
        </div>


    </div>
</template>

<script>

import buttonWhite from "../parts/button.vue";
import CustomSelectCountries from "../parts/custom-select-countries.vue";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import api from "../api/api.js";
import getProfileData from "../services/profile-data.js";
import router from "../router/router.js";
import {replace} from "lodash/string.js";
import refreshToken from "../services/refresh-token.js";
import store from "../store/store.js";


export default {
    components: {
        CustomSelectCountries,
        buttonWhite,
        VueDatePicker
    },
    data() {
        return {
            update_btn: 'Update',
            exchange_tokens_btn: 'Exchange Tokens',
            passEyes: {
                old: false,
                new: false,
                confirm: false,
            },
            maxDate: new Date(),

            name: null,
            avatar: null,
            background: null,
            username: null,
            email: null,
            phone_number: null,
            description: null,
            date_of_birth: '',
            country_id: 0,

            oldPassword: null,
            newPassword: null,
            confirmPassword: null,
            allCountries: []
        }
    },
    methods: {
        togglePassViewOld() {
            this.passEyes.old = !this.passEyes.old;
            if (this.$refs.passwordInputOld.type === 'password') {
                this.$refs.passwordInputOld.type = 'text';
            } else {
                this.$refs.passwordInputOld.type = 'password';
            }
        },
        togglePassViewNew() {
            this.passEyes.new = !this.passEyes.new;
            if (this.$refs.passwordInputNew.type === 'password') {
                this.$refs.passwordInputNew.type = 'text';
            } else {
                this.$refs.passwordInputNew.type = 'password';
            }
        },
        togglePassViewConfirm() {
            this.passEyes.confirm = !this.passEyes.confirm;
            if (this.$refs.passwordInputConfirm.type === 'password') {
                this.$refs.passwordInputConfirm.type = 'text';
            } else {
                this.$refs.passwordInputConfirm.type = 'password';
            }
        },
        handleChildData(data) {
            this.country_id = data;
        },
        updateProfile() {
            api.put('/api/profile/update', {
                email: this.email,
                name: this.name,
                username: this.username,
                phone_number: this.phone_number,
                date_of_birth: this.date_of_birth,
                country_id: this.country_id,
                description: this.description,
            }).then( response => {
                this.profileIndex();
                if (response.status === 201) {
                    store.state.userUsername = this.username;
                    store.state.notification = {
                        message: "Profile has been successfully updated",
                        type: "success",
                        show: true
                    }
                } else {
                    store.state.notification = {
                        message: "Profile has not been updated",
                        type: "error",
                        show: true
                    }
                }
            })
            .catch(error => {
                store.state.notification = {
                    message: "Profile has not been updated",
                    type: "error",
                    show: true
                }
            });
        },
        updatePassword() {
            api.put('/api/profile/update-password', {
                old_password: this.oldPassword,
                new_password: this.newPassword,
                confirm_password: this.confirmPassword,
            }).then( response => {
                this.profileIndex();
                if (response.status === 201) {
                    store.state.notification = {
                        message: "Password has been successfully changed",
                        type: "success",
                        show: true
                    }
                } else {
                    store.state.notification = {
                        message: "Password has not been changed",
                        type: "error",
                        show: true
                    }
                }
            })
            .catch(error => {
                store.state.notification = {
                    message: "Password has not been changed",
                    type: "error",
                    show: true
                }
            });
        },
        getAllCountries() {
            api.get('/api/getAllCountries').then( response => {
                this.allCountries = response.data.countries;
                this.profileIndex();
            })
            .catch(error => {
                console.log(error);
            });
        },
        profileIndex() {
            api.get('/api/profile/').then( response => {
                const user = response.data.user.data;
                if (user.avatar === '/images/avatar/default-profile-img.png') {
                    store.state.userAvatar = '';
                } else {
                    store.state.userAvatar = user.avatar;
                }
                store.state.userUsername = user.username;
                store.state.google2fa_status = user.google2fa_status;
                if (user.avatar
                    && user.avatar != '/images/avatar/default-profile-img.png'){
                    this.avatar = user.avatar;
                }
                if (user.background
                    && user.background != '/images/background/default-background-img.png'){
                    this.background = user.background;
                }
                this.name = user.name;
                this.username = user.username;
                this.email = user.email;
                this.phone_number = user.phone_number;
                this.description = user.description;
                this.date_of_birth = user.date_of_birth;
                if (user.country_id !== null) {
                    this.country_id = user.country_id;
                }
                refreshToken();
                this.$store.commit('updateHeaderData');
            })
                .catch(error => {
                    console.log(error);
                });
        },
        openInputAvatar() {
            this.$refs.fileInput.click();
        },
        uploadAvatar(event) {
            const file = event.target.files[0];
            const formData = new FormData();
            formData.append('avatar', file);
            api.post('/api/profile/update-avatar', formData).then(response => {
                this.profileIndex();
                if (response.status === 200) {
                    store.state.notification = {
                        message: "Avatar has been successfully changed",
                        type: "success",
                        show: true
                    }
                } else {
                    store.state.notification = {
                        message: "Avatar has not been changed",
                        type: "error",
                        show: true
                    }
                }
            }).catch(error => {
                store.state.notification = {
                    message: "Avatar has not been updated",
                    type: "error",
                    show: true
                }
            });
        },
        openInputBackground() {
            this.$refs.fileInputBackground.click();
        },
        uploadBackground(event) {
            const file = event.target.files[0];
            const formData = new FormData();
            formData.append('background', file);
            api.post('/api/profile/update-background', formData).then(response => {
                this.profileIndex();
                if (response.status === 200) {
                    store.state.notification = {
                        message: "Background has been successfully changed",
                        type: "success",
                        show: true
                    }
                } else {
                    store.state.notification = {
                        message: "Background has not been changed",
                        type: "error",
                        show: true
                    }
                }
            }).catch(error => {
                store.state.notification = {
                    message: "Background has not been changed",
                    type: "error",
                    show: true
                }
            });
        },
        getAvatar() {
            return this.avatar;
        },
        getBackground() {
            return this.background;
        },
        deleteBackground() {
            api.post('/api/profile/update-avatar', formData).then(response => {
                this.profileIndex();
            }).catch(error => {
            });
        },
        filterUsername() {
            this.username = this.username.replace(/\s/g, '');
        },
        activate2fa() {
            api.post('/api/2fa-get').then( response => {
                if (response.status === 200) {
                    store.state.modals.twoFactorAuthModal.show = true;
                    store.state.modals.twoFactorAuthModal.data = response.data;
                }
            })
            .catch(error => {
                console.log(error);
            });
        },
        deactivate2fa() {
            api.post('/api/2fa-deactivate').then( response => {
                if (response.status === 201) {
                    store.state.notification = {
                        message: response.data.message,
                        type: "success",
                        show: true
                    }
                    getProfileData();
                }
            })
            .catch(error => {
                console.log(error);
            });
        },
    },

    mounted() {
        this.getAllCountries();
    },
}


</script>

<style scoped>
.password_input_block {
    width: calc((100% - 64px) / 3)!important;
}
@media (max-width: 968px) {
    .password_input_block {
        width: 100%!important;
    }
  .avatar_block{
    flex-direction: column;
  }
}

.main_block {
    margin-bottom: 100px;
}

.dp__input {
    font-family: JetBrains Mono;
    background: rgba(186, 186, 186, 0.15);
    border-radius: 4px;
    padding: 10px 12px;
    color: rgba(255, 255, 255, 0.90);
    font-size: 16px;
    font-weight: 400;
    line-height: normal;
    border: 1px rgba(186, 186, 186, 0.60) solid;
}

</style>

<template>
    <div class="web-header" :style="getVirtualHallPadding">

        <router-link to="/trophy-room" class="virtual-hall-logo" v-if="$route.name === 'virtual-hall'">
            <div class="logo">
                <img src="../../../web/images/web/img/tr-logo.svg" alt="logo">
            </div>
        </router-link>
        <div v-if="store.state.authorized" class="header_right_block">
            <div v-if="isMobile">
                <router-link to="/trophy-room" >
                    <div @click="openMainPage" class="front-sidebar_logo">
                        <img src="../../../web/images/web/img/tr-logo.svg" alt="logo">
                    </div>
                </router-link>
            </div>
            <div v-if="isDesktop" class="header_achievement">
                <img src="../../../web/images/web/img/header_icons/01.svg" alt="circle" title="Uru">
                <span>{{$store.state.user.balances.uru < 0 ? 0 : $store.state.user.balances.uru}}</span>
                <div>
<!--                    <img src="../../../web/images/web/img/icons/plus.svg" alt="plus">-->
                </div>
            </div>
            <div v-if="isDesktop" class="header_achievement">
                <img src="../../../web/images/web/img/header_icons/02.svg" alt="rhombus" title="Ambar">
                <span>{{$store.state.user.balances.ambar < 0 ? 0 : $store.state.user.balances.ambar}}</span>
                <div>
<!--                    <img src="../../../web/images/web/img/icons/plus.svg" alt="plus">-->
                </div>
            </div>
            <div v-if="isDesktop" class="header_achievement">
                <img src="../../../web/images/web/img/header_icons/03.svg" alt="heart" title="Rune">
                <span>{{$store.state.user.balances.rune < 0 ? 0 : $store.state.user.balances.rune}}</span>
                <div>
<!--                    <img src="../../../web/images/web/img/icons/plus.svg" alt="plus">-->
                </div>
            </div>
            <div class="header_notification_indicator" @click="openCloseMessageNotification" ref="headerDropdownNotification">
                <div class="header_notification_bell_wrapper">
                    <img src="../../../web/images/web/img/icons/bell.svg" alt="bell">
                    <div v-if="store.state.unread_notifications_count" class="header_notification_indicator_count">
                        {{ messagesCount() }}
                    </div>
                </div>

                <div class="header_message_notification_wrapper">
                    <ambar-messages-notification v-if="store.state.messageNotification.show"></ambar-messages-notification>
                </div>
            </div>
            <div v-if="!isMobile" class="separator_vertical"></div>
            <div class="header_user_info" @click="openHeaderDropdown">
                <div class="header_user_avatar">
                    <img v-if="store.state.userAvatar" :src="store.state.userAvatar" alt="avatar" class="header_user_avatar_image">
                </div>
                <span v-if="!isMobile">
                    {{store.state.userUsername}}
                </span>
                <img src="../../../web/images/web/img/icons/arrow-down.svg" alt="arrow-down">
            </div>
            <div v-if="isMobile" class="header_user_info header-user-menu" @click="openSideBar">
                <img src="../../../web/images/web/img/icons/menu.svg" alt="arrow-down">
            </div>
        </div>
        <div v-if="!isDesktop && store.state.authorized" class="header_right_block_mobile">
            <div class="header_achievement">
                <img src="../../../web/images/web/img/header_icons/01.svg" alt="Uru" title="Uru">
                <span>{{$store.state.user.balances.uru}}</span>
                <div>
<!--                    <img src="../../../web/images/web/img/icons/plus.svg" alt="plus">-->
                </div>
            </div>
            <div class="header_achievement">
                <img src="../../../web/images/web/img/header_icons/02.svg" alt="rhombus" title="Ambar">
                <span>{{$store.state.user.balances.ambar}}</span>
                <div>
<!--                    <img src="../../../web/images/web/img/icons/plus.svg" alt="plus">-->
                </div>
            </div>
            <div class="header_achievement">
                <img src="../../../web/images/web/img/header_icons/03.svg" alt="heart" title="Rune">
                <span>{{$store.state.user.balances.rune}}</span>
                <div>
<!--                    <img src="../../../web/images/web/img/icons/plus.svg" alt="plus">-->
                </div>
            </div>
        </div>
        <div class="header_dropdown" v-if="isActiveHeaderDropdown && store.state.authorized" @mouseleave="closeHeaderDropdown" ref="headerDropdown" tabindex="-1">
            <ul>
                <li>
                    <router-link @click="closeHeaderDropdown" to="/profile" class="header_dropdown_link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M16.6663 17.5V15.8333C16.6663 14.9493 16.3152 14.1014 15.69 13.4763C15.0649 12.8512 14.2171 12.5 13.333 12.5H6.66634C5.78229 12.5 4.93444 12.8512 4.30932 13.4763C3.6842 14.1014 3.33301 14.9493 3.33301 15.8333V17.5M13.333 5.83333C13.333 7.67428 11.8406 9.16667 9.99967 9.16667C8.15873 9.16667 6.66634 7.67428 6.66634 5.83333C6.66634 3.99238 8.15873 2.5 9.99967 2.5C11.8406 2.5 13.333 3.99238 13.333 5.83333Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>
                            Account
                        </span>
                    </router-link>
                </li>
                <li>
                    <a class="header_dropdown_link" @click="logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M7.31681 3.33331H4.22857C3.76054 3.33331 3.31168 3.51769 2.98074 3.84588C2.64979 4.17406 2.46387 4.61918 2.46387 5.08331V15.5833C2.46387 16.0474 2.64979 16.4926 2.98074 16.8207C3.31168 17.1489 3.76054 17.3333 4.22857 17.3333H7.31681M7.53613 10.3333H17.5361M17.5361 10.3333L13.7152 6.33331M17.5361 10.3333L13.7152 14.3333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>
                            Log Out
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <router-link to="/login" v-if="!store.state.authorized">
            <button class="login-btn">
                <div class="text">
                    Log in
                </div>
                <img class="arrow" src="../../../web/images/web/img/icons/arrow-right-black.svg" alt="logo">
            </button>
        </router-link>
    </div>
</template>

<script>

import buttonWhite from "../parts/button.vue";
import store from "../store/store.js";
import api from "../api/api.js";
import router from "../router/router.js";
import { Centrifuge } from 'centrifuge';
import ambarNotification from "./modals/ambar-notification.vue";
import AmbarMessagesNotification from "./modals/ambar-messages-notification.vue";
export default {
    components: {
        AmbarMessagesNotification,
        ambarNotification,
        buttonWhite,
    },
    data() {
        return {
            text: 'Log in',
            username: '',
            unread_notifications_count: 0,
            avatar: '',
            ambar: 0,
            uru: 0,
            rune: 0,
            centrifugo: null,
            isActiveHeaderDropdown: false,
            windowWidth: window.innerWidth,
        }
    },
    methods: {
        handleClickOutside(event) {
            if (!(this.$refs.headerDropdown && this.$refs.headerDropdown.contains(event.target)) && this.isActiveHeaderDropdown) {
                this.closeHeaderDropdown();
            }
        },
        handleClickOutsideNotification(event) {
            if (!(this.$refs.headerDropdownNotification && this.$refs.headerDropdownNotification.contains(event.target)) && store.state.messageNotification.show) {
                this.openCloseMessageNotification();
            }
        },



        openHeaderDropdown() {
            setTimeout(() => {
                if (!this.isActiveHeaderDropdown){
                    this.isActiveHeaderDropdown = true;
                    this.$nextTick(() => {
                        this.$refs.headerDropdown.focus();
                    });
                } else {
                    this.isActiveHeaderDropdown = false;
                }
            }, 100);
        },
        openSideBar() {
            store.state.activeSideBar = true;
        },
        openMainPage() {
            router.push('/');
        },
        closeHeaderDropdown() {
            setTimeout( () => {
                this.isActiveHeaderDropdown = false;
            }, 50);
        },
        logout() {
            localStorage.removeItem('user');
            localStorage.removeItem('access_token');
            store.state.authorized = false;
            if (this.$route.name !== 'virtual-hall') {
                router.push('/login');
            }
        },
        getUserBalances() {
            api.get('/api/profile').then( resp => {
                if (resp && resp.status === 200) {
                    store.state.userUsername = resp.data.user.data.username;
                    store.state.google2fa_status = resp.data.user.data.google2fa_status;
                    if (resp.data.user.data.avatar === '/images/avatar/default-profile-img.png') {
                        store.state.userAvatar = '';
                    } else {
                        store.state.userAvatar = resp.data.user.data.avatar;
                    }
                    for (let balance of resp.data.user.data.balances) {
                        store.state.user.balances[balance.currency.name] = Math.floor(balance.amount);
                    }
                  this.unread_notifications_count = resp.data.user.data.unread_notifications_count;
                  store.state.unread_notifications_count = resp.data.user.data.unread_notifications_count;
                  store.state.user.roles = resp.data.user.data.roles;
                }
            }).catch(error => {
              console.log('api profile error: ', error)
            })
        },

      // async mountUserBalances(){
        //     let user = JSON.parse(localStorage.getItem('user'));
        //     while (!user) {
        //         await new Promise(resolve => setTimeout(resolve, 250));
        //         user = JSON.parse(localStorage.getItem('user'));
        //         user.balances.forEach((balance) =>{
        //             if (balance.currency.name === 'ambar') {
        //                 this.ambar = Math.floor(balance.amount);
        //             }
        //             if (balance.currency.name === 'uru') {
        //                 this.uru = Math.floor(balance.amount);
        //             }
        //             if (balance.currency.name === 'rune') {
        //                 this.rune = Math.floor(balance.amount);
        //             }
        //         })
        //     }
        // },
        async subscribeCentrifugoBalances(){
            this.centrifuge = new Centrifuge(localStorage.getItem('websocket_url'));
            this.centrifuge.setToken(localStorage.getItem('centrifugo_token'));

            let user = JSON.parse(localStorage.getItem('user'));
            while (!user) {
                await new Promise(resolve => setTimeout(resolve, 250));
                user = JSON.parse(localStorage.getItem('user'));
            }
            let vm = this;

            this.centrifuge.newSubscription('ambar-balance-ambar-' + user.id)
                .on('publication', function(ctx) {
                    store.state.user.balances.ambar = Math.floor(ctx.data.amount);
                }).subscribe();

            this.centrifuge.newSubscription('ambar-balance-uru-' + user.id)
                .on('publication', function(ctx) {
                    store.state.user.balances.uru = Math.floor(ctx.data.amount);
                }).subscribe();

            this.centrifuge.newSubscription('ambar-balance-rune-' + user.id)
                .on('publication', function(ctx) {
                    store.state.user.balances.rune = Math.floor(ctx.data.amount);
                }).subscribe();

          this.centrifuge.newSubscription('notification-user-' + user.id)
              .on('publication', function(notification) {
                vm.unread_notifications_count = notification.data.unreadCount;
                store.state.unread_notifications_count = notification.data.unreadCount;
              }).subscribe();

          this.centrifuge.newSubscription('sync-platform')
              .on('publication', function(data) {
                // console.log('platform', data.data.platform);
                // console.log('user', data.data.user);
                // console.log('result', data.data.result);

                if (data.data.result === true) {

                  store.state.notification = {
                    message: 'Congratulations! You have successfully obtained your badge from '+data.data.platform+'!',
                    type: 'success',
                    show: true
                  }

                }else{

                  store.state.notification = {
                    message: 'You are not available to get your badge from '+data.data.platform,
                    type: 'info',
                    show: true
                  }

                }

              }).subscribe();

            this.centrifuge.connect();
        },
        async setUserDataToHeader() {
            let user = JSON.parse(localStorage.getItem('user'));
            while (!user) {
                await new Promise(resolve => setTimeout(resolve, 250));
                user = JSON.parse(localStorage.getItem('user'));
            }
            this.username = user.username;
            if (user.avatar && user.avatar != '/images/avatar/default-profile-img.png'){
                this.avatar = user.avatar;
            }
        },
        handleResize() {
            this.windowWidth = window.innerWidth;
        },
        openCloseMessageNotification() {
            store.state.messageNotification.show = !store.state.messageNotification.show;
        },
        messagesCount(){
            const count = store.state.unread_notifications_count;
            if (count > 99) {
                return '99+'
            } else {
                return count
            }
        }
    },
    computed: {
        store() {
            return store
        },
        isMobile() {
            // return window.innerWidth <= 768;
            return this.windowWidth <= 968;
        },
        isDesktop() {
            // return window.innerWidth <= 768;
            return this.windowWidth >= 568;
        },
        updateHeaderData() {
            return this.$store.state.updateHeaderData;
        },
        getVirtualHallPadding() {
            if (this.$route.name === 'virtual-hall') {
                return {
                    paddingTop: '30px',
                };
            }
        },
    },
    watch: {
        updateHeaderData(newVal, oldVal) {
            setTimeout(()=>{
                this.setUserDataToHeader();
            }, 200);
        },
    },
    created() {
        window.addEventListener('resize', this.handleResize);
    },
    mounted() {
        this.setUserDataToHeader();
        this.subscribeCentrifugoBalances();
        this.getUserBalances();
        document.addEventListener('click', this.handleClickOutside);
        document.addEventListener('click', this.handleClickOutsideNotification);
        document.addEventListener('scroll', this.handleClickOutside);
        document.addEventListener('scroll', this.handleClickOutsideNotification);
        let user = JSON.parse(localStorage.getItem('user'));
        if (user) {
            this.getUserBalances();
        }

    },
    updated() {
        this.setUserDataToHeader();
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.handleClickOutside);
        document.removeEventListener('click', this.handleClickOutsideNotification);
        document.removeEventListener('scroll', this.handleClickOutside);
        document.removeEventListener('scroll', this.handleClickOutsideNotification);
    },
}

</script>

<style scoped>

.virtual-hall-logo {
    flex-grow: 1;
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

@media (max-width: 968px) {
  .virtual-hall-logo{
    display: none;
  }
}

.login-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #c1f527; height: 36px; width: 130px;
    color: #000003;
    font-size: 16px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 700;
    line-height: 20px;
    word-wrap: break-word;
}

.single-page-header .header-user-menu {
    display: none;
}

</style>

<template>
    <div class="header">
        <div class="logo">
            <img src="../../../web/images/web/img/logo.svg" alt="logo">
        </div>
        <div class="header_right_block">
            <router-link to="/login">
                <button class="login-btn">
                    <div class="text">
                        Log in
                    </div>
                    <img class="arrow" src="../../../web/images/web/img/icons/arrow-right-black.svg" alt="logo">
                </button>
            </router-link>
        </div>
    </div>
</template>

<script>

import buttonWhite from "../parts/button.vue";
import store from "../store/store.js";
import api from "../api/api.js";
import router from "../router/router.js";
export default {
    components: {
        buttonWhite
    },
    data() {
        return {

        }
    },
    methods: {

    },
    computed: {

    },
}

</script>

<style scoped>
    .header {
        display: flex;
        justify-content: space-between;
        height: 76px;
        margin: 0;
    }
    .logo {
        height: 32px;
        margin: 22px
    }
    .header_right_block {
        margin: auto 40px auto 10px;
    }

    .login-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #CAFB01; height: 36px; width: 130px;
        color: #18181B;
        font-size: 16px;
        font-family: JetBrains Mono;
        font-weight: 700;
        line-height: 20px;
        word-wrap: break-word;
    }

    .text {
        margin-right: 4px;
    }

</style>


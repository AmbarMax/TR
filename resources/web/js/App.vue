<template>
    <router-view></router-view>
</template>

<script>

import store from "./store/store.js";

export default {
    data() {
        return {
          //
        }
    },
    computed: {
        signUpModalOpen: function () {
            return store.state.signUpModalOpen;
        },
    },
    mounted() {
      //
    }
}
</script>


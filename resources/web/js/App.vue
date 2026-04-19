<template>
  <div class="app-root">
    <!-- Atmospheric layers (z-index 0, behind app) -->
    <div class="bg-atmosphere">
      <div class="bg-deep"></div>
      <div class="bg-hex"></div>
      <div class="bg-grid"></div>
    </div>

    <!-- App content (above atmosphere) -->
    <router-view class="app-content" />

    <!-- CRT overlays (z-index 997-999, above everything) -->
    <div class="bg-crt-vignette"></div>
    <div class="bg-scanlines"></div>
    <div class="bg-flicker"></div>
  </div>
</template>

<script>

import store from "./store/store.js";

export default {
    name: 'App',
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

<style lang="scss" scoped>
.app-root {
  position: relative;
  min-height: 100vh;
}
.app-content {
  position: relative;
  z-index: 1;
}
</style>

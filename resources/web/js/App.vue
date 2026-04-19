<template>
  <div class="app-root">
    <div class="bg-atmosphere">
      <div class="bg-deep"></div>
      <div class="bg-hex"></div>
      <div class="bg-grid"></div>
    </div>

    <div class="app-content">
      <router-view />
    </div>

    <div class="bg-crt-vignette"></div>
    <div class="bg-scanlines"></div>
    <div class="bg-flicker"></div>
  </div>
</template>

<script>
export default { name: 'App' };
</script>

<style lang="scss" scoped>
.app-root {
  position: relative;
  min-height: 100vh;
}
.app-content {
  position: relative;
  z-index: 1;
  min-height: 100vh;
}
</style>

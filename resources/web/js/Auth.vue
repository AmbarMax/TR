<template>
  <div class="auth-layout">
    <svg class="auth-hex-pattern" viewBox="0 0 900 900" preserveAspectRatio="xMidYMid slice">
      <defs>
        <pattern id="auth-hex" width="60" height="52" patternUnits="userSpaceOnUse">
          <polygon points="30,2 55,15 55,37 30,50 5,37 5,15" fill="none" stroke="#ff6100" stroke-width="0.8"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#auth-hex)"/>
    </svg>
    <div class="auth-glow"></div>
    <div class="auth-content">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
export default {}
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  background-color: #000003;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.auth-hex-pattern {
  position: fixed;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0.04;
  pointer-events: none;
  z-index: 0;
}

.auth-glow {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 120px;
  background: linear-gradient(to top, rgba(255, 97, 0, 0.03), transparent);
  pointer-events: none;
  z-index: 0;
}

.auth-content {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 380px;
  padding: 40px 20px;
}
</style>

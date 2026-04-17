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

    <div class="auth-particle auth-particle--hex" style="left: 8%; animation-duration: 12s; animation-delay: 0s;"></div>
    <div class="auth-particle auth-particle--dot" style="left: 15%; animation-duration: 9s; animation-delay: 1s;"></div>
    <div class="auth-particle auth-particle--diamond" style="left: 25%; animation-duration: 14s; animation-delay: 2s;"></div>
    <div class="auth-particle auth-particle--hex" style="left: 35%; animation-duration: 11s; animation-delay: 0.5s;"></div>
    <div class="auth-particle auth-particle--dot" style="left: 45%; animation-duration: 10s; animation-delay: 3s;"></div>
    <div class="auth-particle auth-particle--diamond" style="left: 55%; animation-duration: 13s; animation-delay: 1.5s;"></div>
    <div class="auth-particle auth-particle--hex" style="left: 65%; animation-duration: 15s; animation-delay: 4s;"></div>
    <div class="auth-particle auth-particle--dot" style="left: 72%; animation-duration: 8s; animation-delay: 2.5s;"></div>
    <div class="auth-particle auth-particle--diamond" style="left: 82%; animation-duration: 12s; animation-delay: 0.8s;"></div>
    <div class="auth-particle auth-particle--hex" style="left: 90%; animation-duration: 10s; animation-delay: 3.5s;"></div>
    <div class="auth-particle auth-particle--dot" style="left: 20%; animation-duration: 16s; animation-delay: 5s;"></div>
    <div class="auth-particle auth-particle--diamond" style="left: 50%; animation-duration: 11s; animation-delay: 6s;"></div>
    <div class="auth-particle auth-particle--hex" style="left: 78%; animation-duration: 14s; animation-delay: 4.5s;"></div>
    <div class="auth-particle auth-particle--dot" style="left: 40%; animation-duration: 9s; animation-delay: 7s;"></div>

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
  width: 100%;
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
  pointer-events: none;
  z-index: 0;
  animation: pulse-hex 8s ease-in-out infinite;
}

.auth-glow {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 200px;
  background: radial-gradient(ellipse at 50% 100%, rgba(255, 97, 0, 0.06) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
  animation: glow-shift 6s ease-in-out infinite;
}

.auth-content {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 380px;
  padding: 40px 20px;
}

.auth-particle {
  position: absolute;
  bottom: -20px;
  pointer-events: none;
  animation-name: float;
  animation-timing-function: ease-in-out;
  animation-iteration-count: infinite;
}

.auth-particle--hex {
  width: 12px;
  height: 14px;
  clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
  background: rgba(255, 97, 0, 0.15);
}

.auth-particle--diamond {
  width: 8px;
  height: 8px;
  transform-origin: center;
  background: rgba(193, 245, 39, 0.12);
  clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
}

.auth-particle--dot {
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: rgba(255, 97, 0, 0.25);
}

@keyframes float {
  0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0; }
  10% { opacity: 0.6; }
  90% { opacity: 0.6; }
  100% { transform: translateY(-100vh) rotate(30deg); opacity: 0; }
}

@keyframes pulse-hex {
  0%, 100% { opacity: 0.03; }
  50% { opacity: 0.06; }
}

@keyframes glow-shift {
  0%, 100% { opacity: 0.03; }
  50% { opacity: 0.06; }
}
</style>

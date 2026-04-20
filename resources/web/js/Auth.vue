<template>
  <div class="auth-layout">
    <!-- Auth-specific atmosphere (mirrors App.vue global but scoped to /login, /sign-up, etc.) -->
    <div class="auth-atmosphere">
      <div class="auth-deep"></div>
      <div class="auth-hex"></div>
      <div class="auth-grid"></div>
    </div>
    <div class="auth-scanlines"></div>
    <div class="auth-vignette"></div>

    <!-- Content — each child page (Login/Signup/Forgot) provides its own full layout -->
    <div class="auth-viewport">
      <router-view />
    </div>
  </div>
</template>

<script>
export default {}
</script>

<style lang="scss" scoped>
.auth-layout {
  min-height: 100vh;
  position: relative;
  background: var(--bg);
}

/* Auth-specific atmosphere (mirrors App.vue global but scoped to auth layout) */
.auth-atmosphere {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
}
.auth-deep {
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 1400px 800px at 80% -5%, rgba(255,97,0,0.22), transparent 50%),
    radial-gradient(ellipse 1000px 700px at 10% 100%, rgba(193,245,39,0.08), transparent 55%),
    linear-gradient(180deg, #050507 0%, #000003 100%);
}
.auth-hex {
  position: absolute; inset: 0;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='84' height='72' viewBox='0 0 84 72'><path d='M21 2 L63 2 L82 37 L63 70 L21 70 L2 37 Z' fill='none' stroke='%23ff6100' stroke-width='1' opacity='0.05'/></svg>");
  background-size: 84px 72px;
  opacity: 0.6;
}
.auth-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,97,0,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,97,0,0.04) 1px, transparent 1px);
  background-size: 64px 64px;
  mask-image: radial-gradient(ellipse 1000px 700px at 50% 30%, black 30%, transparent 75%);
  -webkit-mask-image: radial-gradient(ellipse 1000px 700px at 50% 30%, black 30%, transparent 75%);
}
.auth-scanlines {
  position: fixed; inset: 0; z-index: 999; pointer-events: none;
  background: repeating-linear-gradient(0deg, rgba(0,0,0,0.12) 0px, rgba(0,0,0,0.12) 1px, transparent 1px, transparent 3px);
  mix-blend-mode: multiply;
}
.auth-vignette {
  position: fixed; inset: 0; z-index: 998; pointer-events: none;
  background: radial-gradient(ellipse at center, transparent 40%, rgba(0,0,0,0.6) 100%);
}

.auth-viewport {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}
</style>

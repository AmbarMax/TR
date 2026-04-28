<template>
  <div>
    <div v-if="sideBarStatus && isMobile" class="sidebar-backdrop" @click="closeSideBar"></div>
    <aside class="front-sidebar" v-if="sideBarStatus || !isMobile">
    <button v-if="isMobile" class="sidebar-close" @click="closeSideBar" aria-label="Close menu">
      <span class="bars"></span>
    </button>
    <!-- Logo -->
    <div class="front-sidebar_logo">
      <router-link to="/trophy-room">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom">
      </router-link>
    </div>

    <!-- Nav -->
    <nav class="sidebar_menu">
      <!-- Dashboard -->
      <router-link to="/dashboard" class="nav-item" :class="{ active_item: $route.path === '/dashboard' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M3 11 L12 4 L21 11 L21 20 L15 20 L15 14 L9 14 L9 20 L3 20 Z" fill="rgba(255,97,0,0.2)"/>
              <path d="M10 7 L14 7" stroke="#c1f527" stroke-width="2.5"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-run')"></span>
        </span>
        <span class="nav-label">Dashboard</span>
      </router-link>

      <!-- Trophy Room -->
      <router-link to="/trophy-room" class="nav-item" :class="{ active_item: $route.path === '/trophy-room' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M7 4 L17 4 L17 10 C17 13 15 15 12 15 C9 15 7 13 7 10 Z"/>
              <path d="M7 6 C4 6 3 7 3 9 C3 11 5 12 7 12"/>
              <path d="M17 6 C20 6 21 7 21 9 C21 11 19 12 17 12"/>
              <path d="M9 15 L9 18 L15 18 L15 15"/>
              <path d="M8 20 L16 20"/>
              <path d="M10 7 L14 7" stroke="#c1f527" stroke-width="2.5"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-trophy')"></span>
        </span>
        <span class="nav-label">Trophy Room</span>
      </router-link>

      <!-- Forge -->
      <router-link to="/forge" class="nav-item" :class="{ active_item: $route.path === '/forge' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M15 4 L22 7 L18 10 L15 7 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M4 14 L18 14 L20 16 L16 18 L4 18 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M8 18 L8 21 M14 18 L14 21"/>
              <circle cx="17" cy="8" r="1.5" fill="#c1f527" stroke="none"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-forge')"></span>
        </span>
        <span class="nav-label">Forge</span>
      </router-link>

      <!-- Rewards -->
      <router-link to="/rewards" class="nav-item" :class="{ active_item: $route.path === '/rewards' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M3 10 L21 10 L21 20 L3 20 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M3 10 L3 7 L21 7 L21 10"/>
              <path d="M11 7 L11 20 M13 7 L13 20"/>
              <circle cx="12" cy="13" r="1.3" fill="#c1f527" stroke="none"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-rewards')"></span>
        </span>
        <span class="nav-label">Rewards</span>
      </router-link>

      <!-- Achievements -->
      <router-link to="/feed" class="nav-item" :class="{ active_item: $route.path === '/feed' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M4 5 L20 5 L20 9 L16 12 L12 11 L8 12 L4 9 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M12 11 L12 16"/>
              <path d="M8 19 L16 19 L15 16 L9 16 Z"/>
              <path d="M7 6 L10 6" stroke="#c1f527" stroke-width="2.5"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-podium')"></span>
        </span>
        <span class="nav-label">Achievements</span>
      </router-link>

      <!-- Studio (visible for brand owners + TR staff) -->
      <router-link v-if="isBrand || isStaff" to="/brand-dashboard" class="nav-item" :class="{ active_item: $route.path.startsWith('/brand-dashboard') }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="13" rx="1" fill="rgba(255,97,0,0.15)"/>
              <path d="M6 9 L18 9" stroke="#c1f527" stroke-width="2"/>
              <path d="M6 13 L14 13"/>
              <path d="M9 20 L15 20"/>
              <path d="M12 17 L12 20"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-admin')"></span>
        </span>
        <span class="nav-label">Studio</span>
      </router-link>

      <!-- Admin (guarded by isAdmin — TR staff only) -->
      <router-link v-if="isAdmin" to="/admin" class="nav-item" :class="{ active_item: $route.path.startsWith('/admin') }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M12 3 L19 6 L19 12 C19 16 16 19 12 20 C8 19 5 16 5 12 L5 6 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M9 12 L11 14 L15 10" stroke="#c1f527" stroke-width="2.5"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-admin')"></span>
        </span>
        <span class="nav-label">Admin</span>
      </router-link>
    </nav>

    <!-- Logout (restored in 9B, was lost in 9A header refactor) -->
    <button class="sidebar-logout" @click="handleLogout" aria-label="Logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
        <path d="M16 17l5-5-5-5M21 12H9M13 21H5V3h8"/>
      </svg>
      <span>Logout</span>
    </button>

    <!-- Social footer (preserved from legacy) -->
    <div class="sidebar-foot">
      <a href="https://twitter.com/TrophyRoomGG" target="_blank" rel="noopener" class="social" aria-label="Twitter">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
      </a>
      <a href="https://discord.gg/3sGk8uGQBT" target="_blank" rel="noopener" class="social" aria-label="Discord">
        <svg width="16" height="16" viewBox="0 0 127.14 96.36" fill="currentColor"><path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.25,60,73.25,53s5-12.74,11.44-12.74S96.23,46,96.12,53,91.08,65.69,84.69,65.69Z"/></svg>
      </a>
    </div>
  </aside>
  </div>
</template>

<script>
import store from "../store/store.js";
import raptorRun from '../../../web/images/web/img/mascot/raptor-run.png';
import raptorTrophy from '../../../web/images/web/img/mascot/raptor-trophy.png';
import raptorForge from '../../../web/images/web/img/mascot/raptor-forge.png';
import raptorRewards from '../../../web/images/web/img/mascot/raptor-rewards.png';
import raptorPodium from '../../../web/images/web/img/mascot/raptor-podium.png';
import raptorAdmin from '../../../web/images/web/img/mascot/raptor-admin.png';

export default {
    data() {
        return {
            windowWidth: window.innerWidth,
            sprites: {
                'raptor-run': raptorRun,
                'raptor-trophy': raptorTrophy,
                'raptor-forge': raptorForge,
                'raptor-rewards': raptorRewards,
                'raptor-podium': raptorPodium,
                'raptor-admin': raptorAdmin
            }
        }
    },
    computed: {
        sideBarStatus() {
            return store.state.activeSideBar;
        },
        isMobile() {
            return this.windowWidth <= 1024;
        },
        isAdmin() {
            return this.$store.getters.isAdmin;
        },
        isStaff() {
            return this.$store.getters.isStaff;
        },
        isBrand() {
            return this.$store.getters.isBrand;
        },
    },
    methods: {
        closeSideBar() {
            store.state.activeSideBar = false;
        },
        handleResize() {
            this.windowWidth = window.innerWidth;
            // console.log(this.windowWidth)
        },
      redirectToTwitter(){
        window.open('https://twitter.com/TrophyRoomGG', '_blank');
      },
      redirectToDiscord(){
        window.open('https://discord.gg/3sGk8uGQBT', '_blank');
      },
      redirectToSupport(){
        window.open('https://trophyroom.gg/support', '_blank');
      },
      pixelStyle(spriteName) {
        return {
          backgroundImage: `url(${this.sprites[spriteName]})`
        };
      },
      async handleLogout() {
        // No Vuex logout action exists in this project — use direct state + localStorage cleanup (mirrors legacy main-header behavior).
        try {
          localStorage.removeItem('access_token');
          localStorage.removeItem('user');
        } catch (e) {
          // ignore
        }
        try {
          store.state.authorized = false;
        } catch (e) {
          // ignore
        }
        this.$router.push({ path: '/login' });
      },
    },
    created() {
        window.addEventListener('resize', this.handleResize);
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize);
    },
}
</script>

<style lang="scss" scoped>
/* Sidebar container */
.front-sidebar {
  width: 270px;
  min-width: 270px;
  height: 100vh;
  min-height: 660px;
  position: sticky;
  top: 0;
  left: 0;
  z-index: 30;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  padding: 26px 0 18px;
  background: rgba(5, 5, 8, 0.75);
  backdrop-filter: blur(14px) saturate(1.2);
  -webkit-backdrop-filter: blur(14px) saturate(1.2);
  border-right: 1px solid rgba(255, 97, 0, 0.12);
  box-shadow: inset -1px 0 0 rgba(255, 97, 0, 0.05);
}

/* Logo area */
.front-sidebar_logo {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0 20px;
  margin-bottom: 28px;
  position: relative;
}
.front-sidebar_logo a {
  display: flex;
  align-items: center;
  justify-content: center;
}
.front-sidebar_logo img {
  width: 58px;
  height: 58px;
  filter: drop-shadow(0 0 14px rgba(255, 97, 0, 0.4));
}
.sidebar-close {
  position: absolute;
  top: 18px;
  right: 18px;
  z-index: 2;
  width: 40px;
  height: 40px;
  border: 2px solid var(--primary);
  background: rgba(13, 13, 15, 0.85);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  flex-shrink: 0;
}
.sidebar-close:hover {
  background: rgba(255, 97, 0, 0.12);
}
.sidebar-close .bars {
  width: 18px;
  height: 2px;
  background: transparent;
  position: relative;
  display: block;
}
.sidebar-close .bars::before,
.sidebar-close .bars::after {
  content: "";
  position: absolute;
  width: 18px;
  height: 2px;
  background: var(--primary);
  left: 0;
  top: 0;
}
.sidebar-close .bars::before {
  transform: rotate(45deg);
}
.sidebar-close .bars::after {
  transform: rotate(-45deg);
}

/* Nav menu */
.sidebar_menu {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 0 10px;
  flex: 1;
  list-style: none;
  margin: 0;
}

/* Nav item (router-link) */
.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  font-size: 11px;
  color: #ccc1b8;
  border-left: 2px solid transparent;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
  text-decoration: none;
}
.nav-item:hover {
  color: var(--text);
  background: rgba(255, 97, 0, 0.04);
}
.nav-item.active_item {
  color: var(--primary);
  background: linear-gradient(90deg, rgba(255, 97, 0, 0.14), rgba(255, 97, 0, 0.02));
  border-left-color: var(--primary);
  text-shadow: 0 0 10px rgba(255, 97, 0, 0.4);
}
.nav-item.active_item::before {
  content: '';
  position: absolute;
  left: -2px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--primary);
  box-shadow: 0 0 14px var(--primary);
}

.nav-label {
  flex: 1;
  min-width: 0;
}

/* ========== ICON HYBRID SYSTEM (SVG default + pixel-art hover/active) ========== */
.nav-icon {
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.nav-icon-svg {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.1s cubic-bezier(0.6, 0, 0.4, 1), opacity 0.1s;
  color: inherit;
}
.nav-icon-svg svg {
  width: 22px;
  height: 22px;
  color: inherit;
}
.nav-icon-pixel {
  position: absolute;
  inset: 0;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  image-rendering: pixelated;
  image-rendering: -moz-crisp-edges;
  image-rendering: crisp-edges;
  transform: scale(0);
  opacity: 0;
  transition:
    transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s,
    opacity 0.08s 0.1s;
}

/* Hover on non-active: SVG out, pixel pops in */
.nav-item:hover:not(.active_item) .nav-icon-svg {
  transform: scale(0);
  opacity: 0;
  transition-delay: 0s;
}
.nav-item:hover:not(.active_item) .nav-icon-pixel {
  transform: scale(1);
  opacity: 1;
  transition:
    transform 0.18s cubic-bezier(0.34, 1.8, 0.64, 1) 0.08s,
    opacity 0.08s 0.08s;
}

/* Active state: pixel permanent + idle bob */
.nav-item.active_item .nav-icon-svg {
  transform: scale(0);
  opacity: 0;
}
.nav-item.active_item .nav-icon-pixel {
  transform: scale(1);
  opacity: 1;
  animation: sidebar-idle-bob 1.6s ease-in-out infinite;
  filter: drop-shadow(0 0 6px rgba(255, 97, 0, 0.35));
}

@keyframes sidebar-idle-bob {
  0%, 100% {
    transform: scale(1) translateY(0);
  }
  50% {
    transform: scale(1) translateY(-2px);
  }
}

/* Motion reduction */
@media (prefers-reduced-motion: reduce) {
  .nav-icon-pixel,
  .nav-icon-svg {
    transition: none;
  }
  .nav-item.active_item .nav-icon-pixel {
    animation: none;
  }
}

/* ========== LOGOUT ========== */
.sidebar-logout {
  margin: auto 10px 4px;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 10px;
  color: var(--text-muted);
  letter-spacing: 0.12em;
  text-transform: uppercase;
  border-top: 1px dashed rgba(255, 97, 0, 0.1);
  padding-top: 14px;
  cursor: pointer;
  background: none;
  border-left: none;
  border-right: none;
  border-bottom: none;
  font-family: inherit;
  transition: color 0.15s;
  text-align: left;
  width: calc(100% - 20px);
}
.sidebar-logout:hover {
  color: var(--primary);
}
.sidebar-logout svg {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

/* ========== SOCIAL FOOTER ========== */
.sidebar-foot {
  padding: 14px 18px 0;
  display: flex;
  gap: 16px;
  align-items: center;
  justify-content: center;
  border-top: 1px solid rgba(255, 97, 0, 0.08);
}
.social {
  color: var(--text-muted);
  transition: all 0.15s;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.social:hover {
  color: var(--primary);
  filter: drop-shadow(0 0 4px var(--primary));
}

/* ========== MOBILE ========== */
@media (max-width: 1024px) {
  .front-sidebar {
    width: 100%;
    min-width: 0;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    overflow-y: auto;
  }

  /* Tap outside (handled via backdrop element added below) */
  .sidebar-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(2px);
    z-index: 99;
  }

  /* Center nav items (icon + label as a centered block) */
  .sidebar_menu {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .sidebar_menu .nav-item {
    justify-content: center;
    padding: 18px 24px;
    gap: 14px;
    width: auto;
    min-width: 240px;
  }

  /* Active item indicator stays on the left as accent — adjust if it conflicts */
  .nav-item.active_item::before {
    left: 0;
  }

  /* Logout also centered */
  .sidebar-logout {
    justify-content: center;
  }

  /* Bigger touch targets and slightly larger label on mobile */
  .nav-label {
    font-size: 14px;
  }
}
</style>

<template>
  <div class="player-hall">
    <div class="bg-atmosphere">
      <div class="bg-deep"></div>
      <div class="bg-hex"></div>
      <div class="bg-grid"></div>
    </div>
    <div class="bg-crt-vignette"></div>
    <div class="bg-scanlines"></div>
    <div class="bg-flicker"></div>

    <header class="public-chrome">
      <router-link to="/" class="chrome-brand">
        <svg class="chrome-logo" viewBox="0 0 100 100">
          <polygon points="50,5 93,30 93,75 50,95 7,75 7,30" fill="#000003" stroke="#ff6100" stroke-width="1.5" opacity="0.8"/>
          <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
          <polygon points="50,48 75,35 75,65 50,78" fill="#ff6100"/>
          <polygon points="50,48 25,35 25,65 50,78" fill="#d4500c"/>
        </svg>
        <span class="chrome-name">Trophy<span class="dot">Room</span></span>
      </router-link>
      <div class="chrome-right">
        <button class="chrome-share" type="button" @click="onShare">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8M16 6l-4-4-4 4M12 2v13"/>
          </svg>
          <span>{{ shareLabel }}</span>
        </button>
        <router-link :to="ctaTarget" class="chrome-cta">
          <span>{{ ctaLabel }}</span>
          <span>→</span>
        </router-link>
      </div>
    </header>

    <button class="hall-back" @click="$router.go(-1)">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </button>

    <div class="vh">
      <PlayerHallHero :user="user" :connected-platforms="connectedPlatforms">
        <template #showcase>
          <SignatureTrophy :trophy="signatureTrophy" :total-count="trophies.length" />
        </template>
      </PlayerHallHero>

      <section class="narrative">
        <div class="section">
          <div class="section-header">
            <h2 class="section-title">Forged <span class="accent-word">trophies</span></h2>
            <div class="section-meta">{{ trophies.length }} total · Curated by player</div>
          </div>
          <ForgedTrophies :trophies="trophies" />
        </div>

        <div class="section">
          <div class="section-header">
            <h2 class="section-title">Custom <span class="accent-word">achievements</span></h2>
            <div class="section-meta">{{ validatedCount }} validated · Community verified</div>
          </div>
          <CustomAchievements :achievements="achievements" />
        </div>

        <div class="section">
          <div class="section-header">
            <h2 class="section-title">Platform <span class="accent-word">badges</span></h2>
            <div class="section-meta">{{ badges.length }} earned · {{ connectedPlatforms.length }} {{ connectedPlatforms.length === 1 ? 'platform' : 'platforms' }}</div>
          </div>
          <PlatformBadges :badges="badges" />
        </div>
      </section>

      <footer class="vh-footer">
        <div class="vh-footer-inner">
          <div class="vh-footer-left">
            <strong>Inspired by {{ user.username }}?</strong><br>
            Build your own Hall. Connect Discord, Steam, and more — forge trophies across every platform you play.
          </div>
          <div class="vh-footer-actions">
            <router-link :to="footerCtaTarget" class="footer-btn">
              <span>{{ footerCtaLabel }}</span>
              <span>→</span>
            </router-link>
            <router-link to="/sign-up" class="footer-btn ghost">Learn more</router-link>
          </div>
        </div>
      </footer>

      <div class="terminal-strip">
        <div>trophyroom.gg/{{ user.username }} · public profile<span class="cursor-blink"></span></div>
        <div>{{ syncStatus }}</div>
      </div>
    </div>
  </div>
</template>

<script>
import api from "../../api/api.js";
import PlayerHallHero from "./components/PlayerHallHero.vue";
import SignatureTrophy from "./components/SignatureTrophy.vue";
import ForgedTrophies from "./components/ForgedTrophies.vue";
import CustomAchievements from "./components/CustomAchievements.vue";
import PlatformBadges from "./components/PlatformBadges.vue";

const PLATFORM_HANDLES = {
  discord: { name: "Discord", icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4C2.9 4 2 4.9 2 6v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM9.5 16c-1.4 0-2.5-1.1-2.5-2.5S8.1 11 9.5 11s2.5 1.1 2.5 2.5S10.9 16 9.5 16zm5 0c-1.4 0-2.5-1.1-2.5-2.5S13.1 11 14.5 11s2.5 1.1 2.5 2.5S15.9 16 14.5 16z"/></svg>' },
  steam: { name: "Steam", icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>' },
  github: { name: "GitHub", icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>' },
};

export default {
  name: "PlayerHallView",
  components: { PlayerHallHero, SignatureTrophy, ForgedTrophies, CustomAchievements, PlatformBadges },
  props: {
    user: { type: Object, required: true },
  },
  data() {
    return {
      badges: [],
      trophies: [],
      achievements: [],
      sharing: false,
      shareToast: "",
    };
  },
  computed: {
    isLoggedIn() {
      return this.$store?.getters?.isLoggedIn ?? !!localStorage.getItem("access_token");
    },
    isOwner() {
      if (!this.isLoggedIn) return false;
      const me = this.$store?.state?.user?.username || localStorage.getItem("user_name");
      return !!(me && me.toLowerCase() === (this.user.username || "").toLowerCase());
    },
    signatureTrophy() {
      return this.trophies.find(t => t.is_nft !== 1) || this.trophies[0] || null;
    },
    validatedCount() {
      return this.achievements.filter(a => a.status === 1 || a.status === "1").length;
    },
    connectedPlatforms() {
      const seen = new Set();
      const list = [];
      this.badges.forEach(b => {
        const integration = b.integration;
        const rawKey = (typeof integration === "object" ? integration?.name : integration) || "";
        const key = String(rawKey).toLowerCase();
        if (!key || seen.has(key)) return;
        seen.add(key);
        const cfg = PLATFORM_HANDLES[key] || { name: key.charAt(0).toUpperCase() + key.slice(1), icon: PLATFORM_HANDLES.github.icon };
        list.push({ key, name: cfg.name, icon: cfg.icon });
      });
      return list.slice(0, 3);
    },
    syncStatus() {
      return this.badges.length ? `${this.badges.length} badges synced · live` : "no badges synced yet";
    },
    shareLabel() {
      return this.shareToast || (this.sharing ? "Sharing…" : "Share profile");
    },
    ctaTarget() {
      if (this.isOwner) return "/trophy-room";
      return this.isLoggedIn ? "/dashboard" : "/sign-up";
    },
    ctaLabel() {
      return this.isOwner ? "Edit profile" : "Create your own";
    },
    footerCtaTarget() {
      return this.isOwner ? "/trophy-room" : "/sign-up";
    },
    footerCtaLabel() {
      return this.isOwner ? "Edit my profile" : "Create my profile";
    },
  },
  watch: {
    "user.username": {
      immediate: false,
      handler() { this.loadHallData(); },
    },
  },
  mounted() {
    this.loadHallData();
  },
  methods: {
    async loadHallData() {
      try {
        const { data } = await api.get(`/api/virtual-hall/${encodeURIComponent(this.user.username)}`);
        const userData = data?.user?.data || {};
        this.badges = userData.badges?.data || [];
        this.trophies = Array.isArray(userData.trophies) ? userData.trophies : [];
        this.achievements = Array.isArray(userData.achievements) ? userData.achievements : [];
      } catch (e) {
        console.error("PlayerHallView: failed to load rich profile data", e);
        this.badges = [];
        this.trophies = [];
        this.achievements = [];
      }
    },

    async onShare() {
      if (this.sharing) return;
      this.sharing = true;
      const url = `${window.location.origin}/${this.user.username}`;
      try {
        if (navigator.share) {
          await navigator.share({ title: this.user.username, url });
          this.flashToast("Shared");
          return;
        }
      } catch (e) { /* user cancelled */ }
      try {
        await navigator.clipboard.writeText(url);
        this.flashToast("Link copied");
      } catch (e) {
        window.prompt("Copy this link to share:", url);
      } finally {
        this.sharing = false;
      }
    },

    flashToast(msg) {
      this.shareToast = msg;
      setTimeout(() => { this.shareToast = ""; }, 1800);
    },
  },
};
</script>

<style>
/* Scoped via .player-hall parent selector — every rule in this block is
   prefixed so styles do not leak to other pages. Share Tech Mono and
   VT323 are already preloaded by the root Blade layout. */
.player-hall {
  font-family: var(--mono);
  font-size: 14px;
  line-height: 1.55;
  color: var(--text);
  background: var(--bg);
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
}
.player-hall *,
.player-hall *::before,
.player-hall *::after { box-sizing: border-box; margin: 0; padding: 0; }
.player-hall a { color: inherit; text-decoration: none; }
.player-hall button { font-family: inherit; cursor: pointer; background: none; border: none; color: inherit; }
.player-hall ul,
.player-hall ol { list-style: none; }

/* ATMOSPHERE */
.player-hall .bg-atmosphere { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
.player-hall .bg-deep {
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 1600px 900px at 70% 0%, rgba(255,97,0,0.28), transparent 50%),
    radial-gradient(ellipse 1200px 800px at 20% 90%, rgba(193,245,39,0.09), transparent 55%),
    linear-gradient(180deg, #050507 0%, #000003 60%);
}
.player-hall .bg-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,97,0,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,97,0,0.04) 1px, transparent 1px);
  background-size: 64px 64px;
  mask-image: radial-gradient(ellipse 1200px 800px at 50% 30%, black 20%, transparent 75%);
  -webkit-mask-image: radial-gradient(ellipse 1200px 800px at 50% 30%, black 20%, transparent 75%);
}
.player-hall .bg-hex {
  position: absolute; inset: 0;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='84' height='72' viewBox='0 0 84 72'><path d='M21 2 L63 2 L82 37 L63 70 L21 70 L2 37 Z' fill='none' stroke='%23ff6100' stroke-width='1' opacity='0.05'/></svg>");
  background-size: 84px 72px;
  opacity: 0.6;
}
.player-hall .bg-scanlines {
  position: fixed; inset: 0; z-index: 999; pointer-events: none;
  background: repeating-linear-gradient(0deg, rgba(0,0,0,0.05) 0px, rgba(0,0,0,0.05) 1px, transparent 1px, transparent 4px);
  opacity: 0.6;
}
.player-hall .bg-crt-vignette {
  position: fixed; inset: 0; z-index: 998; pointer-events: none;
  background: radial-gradient(ellipse at center, transparent 50%, rgba(0,0,0,0.25) 100%);
}
.player-hall .bg-flicker {
  position: fixed; inset: 0; z-index: 997; pointer-events: none;
  background: rgba(255,97,0,0.015);
  animation: playerHallFlicker 7s infinite;
}
@keyframes playerHallFlicker {
  0%, 100% { opacity: 0; }
  50% { opacity: 1; }
  52% { opacity: 0; }
  54% { opacity: 1; }
}

/* PUBLIC CHROME */
.player-hall .public-chrome {
  position: fixed; top: 0; left: 0; right: 0; z-index: 50;
  padding: 16px 32px;
  background: rgba(0,0,3,0.45);
  backdrop-filter: blur(12px) saturate(1.3);
  -webkit-backdrop-filter: blur(12px) saturate(1.3);
  border-bottom: 1px solid rgba(255,97,0,0.12);
  display: flex; align-items: center; justify-content: space-between;
}
.player-hall .chrome-brand { display: flex; align-items: center; gap: 12px; }
.player-hall .chrome-logo { width: 32px; height: 32px; filter: drop-shadow(0 0 8px rgba(255,97,0,0.4)); }
.player-hall .chrome-name { font-family: var(--display); font-size: 24px; letter-spacing: 0.05em; color: var(--text); line-height: 1; }
.player-hall .chrome-name .dot { color: var(--primary); text-shadow: 0 0 8px var(--primary-glow); }
.player-hall .chrome-right { display: flex; align-items: center; gap: 14px; }
.player-hall .chrome-share {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 10px; color: var(--text-muted);
  letter-spacing: 0.2em; text-transform: uppercase;
  padding: 8px 14px;
  border: 1px solid var(--border);
  transition: all 0.15s;
}
.player-hall .chrome-share:hover { color: var(--text); border-color: var(--text-dim); }
.player-hall .chrome-cta {
  display: inline-flex; align-items: center; gap: 10px;
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.22em; text-transform: uppercase;
  padding: 9px 18px;
  border: 1px solid var(--primary);
  transition: all 0.15s;
}
.player-hall .chrome-cta:hover { background: var(--primary); color: var(--bg); box-shadow: 0 0 18px rgba(255,97,0,0.4); }
.hall-back {
  position: fixed;
  top: 80px;
  left: 24px;
  z-index: 10;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 97, 0, 0.25);
  color: var(--text-muted);
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.hall-back:hover {
  color: var(--primary);
  border-color: var(--primary);
  background: rgba(255, 97, 0, 0.12);
}

/* MAIN */
.player-hall .vh { position: relative; z-index: 1; padding-top: 0; min-height: 100vh; }

/* HERO */
.player-hall .hero { position: relative; min-height: 100vh; max-height: 900px; padding: 48px 56px 48px; display: flex; align-items: center; overflow: hidden; }
.player-hall .hero-banner { position: absolute; top: -80px; left: 0; right: 0; bottom: 0; z-index: 0; overflow: hidden; }
.player-hall .hero-banner-img {
  position: absolute; inset: 0;
  background:
    linear-gradient(180deg, rgba(0,0,3,0) 0%, rgba(0,0,3,0) 40%, rgba(0,0,3,0.7) 80%, rgba(0,0,3,1) 100%),
    linear-gradient(90deg, rgba(0,0,3,0.5) 0%, rgba(0,0,3,0) 50%, rgba(0,0,3,0.3) 100%),
    radial-gradient(ellipse 800px 600px at 70% 40%, #4a1468 0%, #1e0824 40%, #000003 80%);
  filter: saturate(1.1) contrast(1.05);
}
.player-hall .hero-banner-asset-slot {
  position: absolute; top: 0; right: 0; bottom: 0; width: 65%;
  opacity: 0.4;
  mask-image: linear-gradient(270deg, black 20%, transparent 80%);
  -webkit-mask-image: linear-gradient(270deg, black 20%, transparent 80%);
}
.player-hall .hero-content { position: relative; z-index: 2; display: grid; grid-template-columns: 1.3fr 1fr; gap: 64px; width: 100%; max-width: 1400px; margin: 0 auto; align-items: center; }
.player-hall .hero-left { min-width: 0; }
.player-hall .hero-tag { display: inline-flex; align-items: center; gap: 10px; font-size: 10px; color: var(--primary); letter-spacing: 0.32em; text-transform: uppercase; margin-bottom: 24px; }
.player-hall .hero-tag::before { content: ''; width: 32px; height: 1px; background: var(--primary); box-shadow: 0 0 6px var(--primary); }
.player-hall .identity { display: flex; align-items: center; gap: 24px; margin-bottom: 28px; }

/* Player avatar — circular */
.player-hall .avatar-ring { position: relative; width: 120px; height: 120px; flex-shrink: 0; }
.player-hall .avatar-ring::before {
  content: ''; position: absolute; inset: -4px;
  border: 2px solid var(--primary);
  border-radius: 50%;
  box-shadow: 0 0 20px var(--primary-glow), inset 0 0 20px rgba(255,97,0,0.2);
  animation: playerHallRingPulse 3s ease-in-out infinite;
}
@keyframes playerHallRingPulse {
  0%, 100% { box-shadow: 0 0 20px var(--primary-glow), inset 0 0 20px rgba(255,97,0,0.2); }
  50% { box-shadow: 0 0 34px var(--primary-glow), inset 0 0 24px rgba(255,97,0,0.35); }
}
.player-hall .avatar-img {
  position: relative; width: 100%; height: 100%;
  border-radius: 50%;
  background: linear-gradient(135deg, #f5c547 0%, #d98c3a 60%, #8b5a2b 100%);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--display);
  font-size: 54px;
  color: var(--bg);
  overflow: hidden;
}
.player-hall .avatar-img.avatar-photo { background: transparent; object-fit: cover; }

.player-hall .identity-meta { flex: 1; min-width: 0; }
.player-hall .hero-name { font-family: var(--display); font-size: 92px; line-height: 0.9; letter-spacing: 0.015em; color: var(--text); margin-bottom: 6px; text-shadow: 0 0 36px rgba(255,97,0,0.25); }
.player-hall .hero-handle { display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--text-muted); letter-spacing: 0.08em; margin-bottom: 14px; flex-wrap: wrap; }
.player-hall .hero-handle .platforms { display: flex; gap: 8px; }
.player-hall .hero-handle .plat-icon { color: var(--text); opacity: 0.8; transition: opacity 0.15s; display: inline-flex; }
.player-hall .hero-handle .plat-icon:hover { opacity: 1; }
.player-hall .hero-handle-sep { opacity: 0.4; }
.player-hall .hero-tagline { font-size: 15px; color: var(--text); line-height: 1.6; max-width: 520px; font-style: italic; letter-spacing: 0.01em; }
.player-hall .hero-tagline::before { content: '"'; color: var(--primary); margin-right: 4px; }
.player-hall .hero-tagline::after { content: '"'; color: var(--primary); margin-left: 4px; }

/* Stats bar */
.player-hall .hero-stats {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 2px;
  background: rgba(42,44,46,0.5);
  border: 1px solid rgba(255,97,0,0.2);
  margin-top: 36px; max-width: 680px;
}
.player-hall .stat-cell { padding: 18px 16px; background: rgba(14,15,17,0.85); text-align: left; position: relative; }
.player-hall .stat-cell.highlight { background: rgba(255,97,0,0.06); }
.player-hall .stat-cell-num { font-family: var(--display); font-size: 42px; line-height: 0.95; color: var(--text); letter-spacing: 0.02em; }
.player-hall .stat-cell.highlight .stat-cell-num { color: var(--primary); text-shadow: 0 0 16px var(--primary-glow); }
.player-hall .stat-cell-lbl { font-size: 9px; color: var(--text-dim); letter-spacing: 0.22em; text-transform: uppercase; margin-top: 4px; }

/* Showcase card */
.player-hall .showcase-card {
  position: relative; padding: 32px;
  background: linear-gradient(135deg, rgba(193,245,39,0.06) 0%, transparent 50%), rgba(14,15,17,0.85);
  border: 1px solid rgba(193,245,39,0.25);
  box-shadow: 0 0 0 1px rgba(193,245,39,0.08), inset 0 0 80px rgba(193,245,39,0.04), 0 30px 80px rgba(0,0,0,0.6);
  overflow: hidden;
}
.player-hall .showcase-card::after {
  content: ''; position: absolute; top: -50px; right: -50px;
  width: 300px; height: 300px;
  background: radial-gradient(circle, rgba(193,245,39,0.12), transparent 65%);
  filter: blur(40px); pointer-events: none;
}
.player-hall .showcase-label { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid rgba(193,245,39,0.15); }
.player-hall .showcase-label-text {
  font-size: 10px; color: var(--accent);
  letter-spacing: 0.3em; text-transform: uppercase;
  display: flex; align-items: center; gap: 10px;
}
.player-hall .showcase-label-text::before {
  content: ''; width: 6px; height: 6px; background: var(--accent); border-radius: 50%;
  box-shadow: 0 0 8px var(--accent);
  animation: playerHallPulseDot 2s ease-in-out infinite;
}
@keyframes playerHallPulseDot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.2); }
}
.player-hall .showcase-label-count { font-size: 10px; color: var(--text-dim); letter-spacing: 0.15em; text-transform: uppercase; }
.player-hall .showcase-trophy { position: relative; display: flex; flex-direction: column; align-items: center; padding: 16px 0 4px; }
.player-hall .showcase-trophy-asset {
  width: 140px; height: 140px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px;
  filter: drop-shadow(0 0 30px var(--accent-glow));
}
.player-hall .showcase-trophy-img { max-width: 100%; max-height: 100%; object-fit: contain; }
.player-hall .showcase-trophy-name { font-family: var(--display); font-size: 28px; color: var(--text); letter-spacing: 0.02em; margin-bottom: 6px; text-align: center; }
.player-hall .showcase-trophy-sub { font-size: 11px; color: var(--text-muted); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 18px; text-align: center; }
.player-hall .showcase-trophy-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border-top: 1px solid rgba(42,44,46,0.6); margin-top: 4px; }
.player-hall .tmeta-cell { padding: 12px; text-align: center; border-right: 1px solid rgba(42,44,46,0.6); }
.player-hall .tmeta-cell:last-child { border-right: none; }
.player-hall .tmeta-val { font-family: var(--display); font-size: 22px; color: var(--primary); line-height: 1; text-shadow: 0 0 10px var(--primary-glow); }
.player-hall .tmeta-lbl { font-size: 9px; color: var(--text-dim); letter-spacing: 0.2em; text-transform: uppercase; margin-top: 4px; }
.player-hall .showcase-empty { padding: 40px 0; text-align: center; color: var(--text-muted); font-size: 12px; }

/* Scroll cue */
.player-hall .scroll-cue {
  position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.3em; text-transform: uppercase;
  display: flex; flex-direction: column; align-items: center; gap: 10px;
  animation: playerHallBounce 2.5s ease-in-out infinite;
  z-index: 3;
}
.player-hall .scroll-cue-line { width: 1px; height: 30px; background: linear-gradient(180deg, var(--primary), transparent); }
@keyframes playerHallBounce {
  0%, 100% { transform: translate(-50%, 0); }
  50% { transform: translate(-50%, 6px); }
}

/* SECTIONS */
.player-hall .narrative { padding: 80px 56px 120px; max-width: 1280px; margin: 0 auto; position: relative; }
.player-hall .section { margin-bottom: 96px; }
.player-hall .section:last-child { margin-bottom: 0; }
.player-hall .section-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 40px; gap: 24px; flex-wrap: wrap; }
.player-hall .section-title { font-family: var(--display); font-size: 56px; line-height: 1; color: var(--text); letter-spacing: 0.02em; }
.player-hall .section-title .accent-word { color: var(--primary); text-shadow: 0 0 20px var(--primary-glow); }
.player-hall .section-meta { font-size: 10px; color: var(--text-dim); letter-spacing: 0.22em; text-transform: uppercase; padding-bottom: 12px; }

/* Forged trophies */
.player-hall .trophies-featured { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
.player-hall .trophy-piece {
  position: relative; padding: 36px 28px 28px;
  background: rgba(14,15,17,0.7);
  border: 1px solid rgba(42,44,46,0.8);
  text-align: center; overflow: hidden;
  transition: all 0.25s; cursor: pointer;
}
.player-hall .trophy-piece:hover { border-color: var(--primary); background: rgba(255,97,0,0.03); transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 30px rgba(255,97,0,0.1); }
.player-hall .trophy-piece::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px; background: linear-gradient(90deg, transparent, var(--primary), transparent); opacity: 0; transition: opacity 0.25s; }
.player-hall .trophy-piece:hover::before { opacity: 0.6; }
.player-hall .trophy-piece.locked { opacity: 0.5; cursor: default; }
.player-hall .trophy-piece.locked:hover { border-color: rgba(42,44,46,0.8); background: rgba(14,15,17,0.7); transform: none; box-shadow: none; }
.player-hall .trophy-piece.locked:hover::before { opacity: 0; }
.player-hall .trophy-piece-asset { width: 100px; height: 100px; margin: 0 auto 22px; display: flex; align-items: center; justify-content: center; filter: drop-shadow(0 0 24px rgba(255,97,0,0.2)); }
.player-hall .trophy-piece-img { max-width: 100%; max-height: 100%; object-fit: contain; }
.player-hall .trophy-piece-name { font-family: var(--display); font-size: 24px; color: var(--text); margin-bottom: 6px; letter-spacing: 0.02em; }
.player-hall .trophy-piece-name.locked-name { color: #5a5550; }
.player-hall .trophy-piece-desc { font-size: 11px; color: var(--text-muted); letter-spacing: 0.05em; line-height: 1.5; margin-bottom: 16px; min-height: 33px; }
.player-hall .trophy-piece-footer { display: flex; align-items: center; justify-content: center; gap: 16px; padding-top: 14px; border-top: 1px solid rgba(42,44,46,0.6); font-size: 10px; color: var(--text-dim); letter-spacing: 0.15em; text-transform: uppercase; }
.player-hall .trophy-piece-footer .xp { color: var(--primary); }
.player-hall .trophy-piece-footer .sep { color: var(--text-dim); opacity: 0.4; }
.player-hall .trophy-piece-footer .locked-text { color: #5a5550; }

/* Achievements */
.player-hall .achievements-list { display: flex; flex-direction: column; border: 1px solid rgba(42,44,46,0.6); background: rgba(14,15,17,0.5); }
.player-hall .achievement-row {
  display: grid; grid-template-columns: 64px 1fr auto;
  gap: 20px; align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid rgba(42,44,46,0.4);
  transition: background 0.2s; cursor: pointer;
}
.player-hall .achievement-row:last-child { border-bottom: none; }
.player-hall .achievement-row:hover { background: rgba(255,97,0,0.03); }
.player-hall .ach-thumb { width: 56px; height: 56px; background: var(--surface-2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.player-hall .ach-thumb-img { width: 100%; height: 100%; object-fit: cover; }
.player-hall .ach-thumb-inner { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-family: var(--display); font-size: 18px; color: rgba(255,255,255,0.85); letter-spacing: 0.05em; }
.player-hall .ach-info { min-width: 0; }
.player-hall .ach-name { font-size: 15px; color: var(--text); margin-bottom: 4px; letter-spacing: 0.02em; }
.player-hall .ach-desc { font-size: 12px; color: var(--text-muted); letter-spacing: 0.03em; line-height: 1.5; }
.player-hall .ach-badge {
  font-size: 9px; color: var(--accent);
  letter-spacing: 0.22em; text-transform: uppercase;
  padding: 5px 10px;
  border: 1px solid rgba(193,245,39,0.3);
  background: rgba(193,245,39,0.06);
  display: inline-flex; align-items: center; gap: 6px;
  white-space: nowrap;
}
.player-hall .ach-badge::before { content: ''; width: 5px; height: 5px; background: var(--accent); border-radius: 50%; box-shadow: 0 0 6px var(--accent); }

/* Platforms */
.player-hall .platforms-section { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px; background: rgba(42,44,46,0.5); border: 1px solid rgba(42,44,46,0.8); }
.player-hall .platform-block { padding: 28px 28px 24px; background: rgba(14,15,17,0.8); position: relative; }
.player-hall .platform-block::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--primary); box-shadow: 0 0 10px var(--primary-glow); }
.player-hall .platform-block.platform-steam::before { background: #66c0f4; box-shadow: 0 0 10px rgba(102,192,244,0.4); }
.player-hall .platform-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid rgba(42,44,46,0.6); }
.player-hall .platform-title { display: flex; align-items: center; gap: 14px; }
.player-hall .platform-title-icon { width: 28px; height: 28px; color: var(--text); display: inline-flex; }
.player-hall .platform-title-name { font-family: var(--display); font-size: 26px; color: var(--text); letter-spacing: 0.04em; line-height: 1; }
.player-hall .platform-title-sub { font-size: 10px; color: var(--text-muted); letter-spacing: 0.2em; text-transform: uppercase; margin-top: 2px; }
.player-hall .platform-count { font-family: var(--display); font-size: 32px; color: var(--primary); letter-spacing: 0.04em; text-shadow: 0 0 14px var(--primary-glow); line-height: 1; }
.player-hall .badges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(56px, 1fr)); gap: 8px; }
.player-hall .badge-tile {
  aspect-ratio: 1;
  background: var(--surface-2);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  position: relative;
  cursor: pointer; transition: all 0.2s;
  overflow: hidden;
}
.player-hall .badge-tile:hover { border-color: var(--primary); transform: scale(1.05); box-shadow: 0 0 14px rgba(255,97,0,0.2); }
.player-hall .badge-emoji { font-size: 28px; line-height: 1; }
.player-hall .badge-img { width: 100%; height: 100%; object-fit: contain; }
.player-hall .badge-tile.legendary { border-color: rgba(255,97,0,0.4); box-shadow: inset 0 0 12px rgba(255,97,0,0.08); }
.player-hall .badge-tile.rare { border-color: rgba(193,245,39,0.4); }
.player-hall .player-empty { color: var(--text-muted); font-size: 12px; padding: 24px 0; text-align: center; border: 1px dashed var(--border); border-radius: 6px; }

/* Footer */
.player-hall .vh-footer { padding: 40px 56px 60px; border-top: 1px solid rgba(255,97,0,0.1); background: rgba(5,5,8,0.6); backdrop-filter: blur(10px); position: relative; z-index: 1; }
.player-hall .vh-footer-inner { max-width: 1280px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
.player-hall .vh-footer-left { font-size: 11px; color: var(--text-muted); letter-spacing: 0.08em; line-height: 1.7; }
.player-hall .vh-footer-left strong { color: var(--text); font-family: var(--display); font-size: 16px; letter-spacing: 0.04em; }
.player-hall .vh-footer-actions { display: flex; gap: 10px; }
.player-hall .footer-btn {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 12px 20px; font-size: 10px;
  letter-spacing: 0.22em; text-transform: uppercase;
  border: 1px solid var(--primary); background: var(--primary); color: var(--bg);
  transition: all 0.15s; box-shadow: 0 0 16px rgba(255,97,0,0.25);
}
.player-hall .footer-btn:hover { background: #ff7e2e; border-color: #ff7e2e; box-shadow: 0 0 28px rgba(255,97,0,0.5); }
.player-hall .footer-btn.ghost { background: transparent; color: var(--text); border-color: rgba(254,237,223,0.3); box-shadow: none; }
.player-hall .footer-btn.ghost:hover { border-color: var(--text); box-shadow: 0 0 14px rgba(254,237,223,0.15); }

/* Terminal strip */
.player-hall .terminal-strip {
  padding: 18px 56px;
  border-top: 1px solid rgba(42,44,46,0.5);
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  display: flex; justify-content: space-between;
  max-width: 1400px; margin: 0 auto;
}
.player-hall .cursor-blink {
  display: inline-block; width: 7px; height: 10px;
  background: var(--primary); margin-left: 4px; vertical-align: middle;
  animation: playerHallBlink 1s steps(1) infinite;
}
@keyframes playerHallBlink { 50% { opacity: 0; } }

/* RESPONSIVE */
@media (max-width: 1100px) {
  .player-hall .hero { padding: 36px 32px; min-height: auto; max-height: none; }
  .player-hall .hero-content { grid-template-columns: 1fr; gap: 40px; }
  .player-hall .showcase-card { max-width: 480px; }
  .player-hall .trophies-featured { grid-template-columns: repeat(2, 1fr); }
  .player-hall .platforms-section { grid-template-columns: 1fr; }
  .player-hall .narrative { padding: 56px 32px 80px; }
  .player-hall .section-title { font-size: 42px; }
  .player-hall .hero-stats { max-width: 100%; }
}
@media (max-width: 700px) {
  .player-hall .public-chrome { padding: 12px 16px; }
  .player-hall .chrome-share { display: none; }
  .player-hall .chrome-name { font-size: 18px; }
  .player-hall .hero { padding: 28px 16px 48px; }
  .player-hall .identity { flex-direction: column; align-items: flex-start; gap: 16px; }
  .player-hall .avatar-ring { width: 88px; height: 88px; }
  .player-hall .avatar-img { font-size: 38px; }
  .player-hall .hero-name { font-size: 58px; }
  .player-hall .hero-stats { grid-template-columns: repeat(2, 1fr); }
  .player-hall .stat-cell-num { font-size: 32px; }
  .player-hall .showcase-card { padding: 22px; }
  .player-hall .narrative { padding: 40px 16px 60px; }
  .player-hall .section { margin-bottom: 60px; }
  .player-hall .section-title { font-size: 32px; }
  .player-hall .section-header { align-items: flex-start; }
  .player-hall .trophies-featured { grid-template-columns: 1fr; }
  .player-hall .achievement-row { grid-template-columns: 48px 1fr; }
  .player-hall .ach-badge { grid-column: 2; justify-self: start; margin-top: 4px; }
  .player-hall .ach-thumb { width: 48px; height: 48px; }
  .player-hall .vh-footer { padding: 32px 16px; }
  .player-hall .vh-footer-actions { width: 100%; flex-direction: column; }
  .player-hall .footer-btn { justify-content: center; width: 100%; }
  .player-hall .terminal-strip { padding: 16px; flex-direction: column; gap: 8px; text-align: left; }
  .player-hall .scroll-cue { display: none; }
}
</style>

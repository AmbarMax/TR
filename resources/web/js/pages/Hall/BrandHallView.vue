<template>
  <div class="brand-hall" :style="brandStyle">
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
      <HallHero
        :user="user"
        :is-following="isFollowing"
        @toggle-follow="onToggleFollow"
        @share="onShare"
      >
        <template #showcase>
          <FeaturedTrophy
            :trophy="featuredTrophy"
            :is-pursuing="featuredIsPursuing"
            :sub-text="featuredSubText"
            @pursuit="onPursuit"
          />
        </template>
      </HallHero>

      <section class="narrative">
        <div class="section">
          <div class="section-header">
            <h2 class="section-title">Active <span class="accent-word">now</span></h2>
            <div class="section-meta">{{ activeMeta }}</div>
          </div>
          <ActiveItemsGrid
            :items="activeItems"
            :pursuing-ids="pursuingIds"
            :conquered-ids="conqueredIds"
            @pursuit="onPursuit"
          />
        </div>

        <div class="section">
          <div class="section-header">
            <h2 class="section-title">Wall of <span class="accent-word">conquerors</span></h2>
            <div class="section-meta">{{ wallMeta }}</div>
          </div>
          <WallOfConquerors :top="topConquerors" :latest="latestConquests" />
        </div>

        <div class="section">
          <div class="section-header">
            <h2 class="section-title">About this <span class="accent-word">Hall</span></h2>
            <div class="section-meta">Issuer info · External links</div>
          </div>
          <AboutHall :user="user" />
        </div>
      </section>

      <footer class="vh-footer">
        <div class="vh-footer-inner">
          <div class="vh-footer-left">
            <strong>Want to issue trophies your audience will pursue?</strong><br>
            TrophyRoom turns your campaigns into permanent achievements. Players keep them. You earn the attention.
          </div>
          <div class="vh-footer-actions">
            <router-link to="/sign-up" class="footer-btn">
              <span>Become an issuer</span>
              <span>→</span>
            </router-link>
            <router-link to="/sign-up" class="footer-btn ghost">Sign up as player</router-link>
          </div>
        </div>
      </footer>

      <div class="terminal-strip">
        <div>trophyroom.gg/{{ user.username }} · {{ verifiedLabel }} · public hall<span class="cursor-blink"></span></div>
        <div>{{ activeStatus }}</div>
      </div>
    </div>

    <SignupIntentModal
      :show="signupIntentVisible"
      :intent="signupIntentType"
      :payload="signupIntentPayload"
      :hall-context="hallContextForModal"
      @close="closeSignupIntent"
      @proceed="closeSignupIntent"
    />
  </div>
</template>

<script>
import api from "../../api/api.js";
import HallHero from "./components/HallHero.vue";
import FeaturedTrophy from "./components/FeaturedTrophy.vue";
import ActiveItemsGrid from "./components/ActiveItemsGrid.vue";
import WallOfConquerors from "./components/WallOfConquerors.vue";
import AboutHall from "./components/AboutHall.vue";
import SignupIntentModal from "../../components/SignupIntentModal.vue";

export default {
  name: "BrandHallView",
  components: { HallHero, FeaturedTrophy, ActiveItemsGrid, WallOfConquerors, AboutHall, SignupIntentModal },
  props: {
    user: { type: Object, required: true },
  },
  data() {
    return {
      activeItems: [],
      topConquerors: [],
      latestConquests: [],
      pursuingIds: [],
      conqueredIds: [],
      isFollowing: false,
      sharing: false,
      signupIntentVisible: false,
      signupIntentType: null,
      signupIntentPayload: null,
    };
  },
  computed: {
    isLoggedIn() {
      return this.$store?.getters?.isLoggedIn ?? !!localStorage.getItem("access_token");
    },
    brandStyle() {
      const accent = this.user?.accent_color || "#ff6100";
      return {
        "--brand-accent": accent,
        "--brand-accent-glow": this.hexToGlow(accent),
      };
    },
    featuredTrophy() {
      const fromSlots = Array.isArray(this.user?.featured_items) ? this.user.featured_items[0] : null;
      if (fromSlots && fromSlots.id) return fromSlots;
      return this.activeItems.find(i => i.type === "trophy") || null;
    },
    featuredIsPursuing() {
      return this.featuredTrophy ? this.pursuingIds.includes(this.featuredTrophy.id) : false;
    },
    featuredSubText() {
      if (!this.featuredTrophy) return "";
      return "Live campaign";
    },
    activeMeta() {
      const trophies = this.activeItems.filter(i => i.type === "trophy").length;
      const chests = this.activeItems.filter(i => i.type === "chest").length;
      const parts = [];
      if (trophies) parts.push(`${trophies} ${trophies === 1 ? "trophy" : "trophies"}`);
      if (chests) parts.push(`${chests} ${chests === 1 ? "chest" : "chests"}`);
      parts.push("Live this week");
      return parts.join(" · ");
    },
    wallMeta() {
      const total = this.user?.stats?.conquerors ?? 0;
      return `${total.toLocaleString()} total ${total === 1 ? "conqueror" : "conquerors"} · Updated live`;
    },
    verifiedLabel() {
      return this.user?.verified_at ? "verified brand" : "brand";
    },
    activeStatus() {
      const n = this.user?.stats?.active_now ?? 0;
      return n > 0 ? `${n} active campaign${n === 1 ? "" : "s"} · live` : "no active campaigns";
    },
    shareLabel() {
      return this.sharing ? "Copied!" : "Share profile";
    },
    ctaTarget() {
      return this.isLoggedIn ? "/dashboard" : "/sign-up";
    },
    ctaLabel() {
      return this.isLoggedIn ? "Dashboard" : "Create your own";
    },
    hallContextForModal() {
      return {
        username: this.user.username,
        name: this.user.name,
        accent_color: this.user.accent_color,
      };
    },
  },
  watch: {
    "user.username": {
      immediate: false,
      handler() {
        this.loadHallData();
      },
    },
  },
  mounted() {
    this.loadHallData();
  },
  methods: {
    hexToGlow(hex) {
      const m = /^#?([0-9a-f]{6})$/i.exec(hex || "");
      if (!m) return "rgba(255,97,0,0.45)";
      const r = parseInt(m[1].slice(0, 2), 16);
      const g = parseInt(m[1].slice(2, 4), 16);
      const b = parseInt(m[1].slice(4, 6), 16);
      return `rgba(${r},${g},${b},0.45)`;
    },

    async loadHallData() {
      const u = this.user.username;
      const [activeRes, conquerorsRes] = await Promise.allSettled([
        api.get(`/api/users/${encodeURIComponent(u)}/active-items`),
        api.get(`/api/users/${encodeURIComponent(u)}/conquerors`),
      ]);

      if (activeRes.status === "fulfilled") {
        this.activeItems = activeRes.value?.data?.data ?? [];
      } else {
        this.activeItems = [];
      }

      if (conquerorsRes.status === "fulfilled") {
        const payload = conquerorsRes.value?.data?.data ?? {};
        this.topConquerors = payload.top ?? [];
        this.latestConquests = payload.latest ?? [];
      } else {
        this.topConquerors = [];
        this.latestConquests = [];
      }

      if (this.isLoggedIn) {
        await this.loadPursuitsAndConquests();
      }

      // TODO 9N-C: no follow-status endpoint yet — assume not following.
      this.isFollowing = false;
    },

    async loadPursuitsAndConquests() {
      try {
        const { data } = await api.get("/api/pursuits");
        const list = Array.isArray(data?.data) ? data.data : [];
        this.pursuingIds = list.map(p => p.trophy_id).filter(Boolean);
      } catch (e) {
        this.pursuingIds = [];
      }
    },

    async onPursuit(item) {
      if (!item) return;
      if (!this.isLoggedIn) {
        this.openSignupIntent("pursuit", { trophyId: item.id, hallUsername: this.user.username });
        return;
      }
      if (this.pursuingIds.includes(item.id) || this.conqueredIds.includes(item.id)) {
        return;
      }
      // Optimistic
      this.pursuingIds.push(item.id);
      try {
        await api.post("/api/pursuits", { trophy_id: item.id });
      } catch (e) {
        this.pursuingIds = this.pursuingIds.filter(id => id !== item.id);
        console.error("Pursuit failed", e);
      }
    },

    async onToggleFollow() {
      if (!this.isLoggedIn) {
        this.openSignupIntent("follow", { hallUsername: this.user.username });
        return;
      }
      const username = this.user.username;
      const was = this.isFollowing;
      this.isFollowing = !was;
      try {
        if (was) {
          await api.delete(`/api/users/${encodeURIComponent(username)}/follow`);
        } else {
          await api.post(`/api/users/${encodeURIComponent(username)}/follow`);
        }
      } catch (e) {
        this.isFollowing = was;
        console.error("Follow toggle failed", e);
      }
    },

    openSignupIntent(type, payload) {
      this.signupIntentType = type;
      this.signupIntentPayload = payload;
      this.signupIntentVisible = true;
    },

    closeSignupIntent() {
      this.signupIntentVisible = false;
    },

    async onShare() {
      const url = `${window.location.origin}/${this.user.username}`;
      try {
        if (navigator.share) {
          await navigator.share({ title: this.user.name || this.user.username, url });
          return;
        }
      } catch (e) { /* user cancelled */ }
      try {
        await navigator.clipboard.writeText(url);
        this.sharing = true;
        setTimeout(() => { this.sharing = false; }, 1500);
      } catch (e) { /* clipboard blocked */ }
    },
  },
};
</script>

<style>
/* Scoped via .brand-hall parent selector — every rule in this block is
   prefixed so styles do not leak to other pages. Share Tech Mono and
   VT323 are already preloaded by the root Blade layout. */
.brand-hall {
  margin-top: -90px;
  font-family: var(--mono);
  font-size: 14px;
  line-height: 1.55;
  color: var(--text);
  background: var(--bg);
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
}
.brand-hall *,
.brand-hall *::before,
.brand-hall *::after { box-sizing: border-box; margin: 0; padding: 0; }
.brand-hall a { color: inherit; text-decoration: none; }
.brand-hall button { font-family: inherit; cursor: pointer; background: none; border: none; color: inherit; }
.brand-hall ul,
.brand-hall ol { list-style: none; }

/* ATMOSPHERE */
.brand-hall .bg-atmosphere { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
.brand-hall .bg-deep {
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 1600px 900px at 70% 0%, rgba(255,97,0,0.28), transparent 50%),
    radial-gradient(ellipse 1200px 800px at 20% 90%, rgba(193,245,39,0.09), transparent 55%),
    linear-gradient(180deg, #050507 0%, #000003 60%);
}
.brand-hall .bg-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,97,0,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,97,0,0.04) 1px, transparent 1px);
  background-size: 64px 64px;
  mask-image: radial-gradient(ellipse 1200px 800px at 50% 30%, black 20%, transparent 75%);
  -webkit-mask-image: radial-gradient(ellipse 1200px 800px at 50% 30%, black 20%, transparent 75%);
}
.brand-hall .bg-hex {
  position: absolute; inset: 0;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='84' height='72' viewBox='0 0 84 72'><path d='M21 2 L63 2 L82 37 L63 70 L21 70 L2 37 Z' fill='none' stroke='%23ff6100' stroke-width='1' opacity='0.05'/></svg>");
  background-size: 84px 72px;
  opacity: 0.6;
}
.brand-hall .bg-scanlines {
  position: fixed; inset: 0; z-index: 999; pointer-events: none;
  background: repeating-linear-gradient(0deg, rgba(0,0,0,0.05) 0px, rgba(0,0,0,0.05) 1px, transparent 1px, transparent 4px);
  opacity: 0.6;
}
.brand-hall .bg-crt-vignette {
  position: fixed; inset: 0; z-index: 998; pointer-events: none;
  background: radial-gradient(ellipse at center, transparent 50%, rgba(0,0,0,0.25) 100%);
}
.brand-hall .bg-flicker {
  position: fixed; inset: 0; z-index: 997; pointer-events: none;
  background: rgba(255,97,0,0.015);
  animation: brandHallFlicker 7s infinite;
}
@keyframes brandHallFlicker {
  0%, 100% { opacity: 0; }
  50% { opacity: 1; }
  52% { opacity: 0; }
  54% { opacity: 1; }
}

/* PUBLIC CHROME */
.brand-hall .public-chrome {
  position: fixed; top: 0; left: 0; right: 0; z-index: 50;
  padding: 16px 32px;
  background: rgba(0,0,3,0.45);
  backdrop-filter: blur(12px) saturate(1.3);
  -webkit-backdrop-filter: blur(12px) saturate(1.3);
  border-bottom: 1px solid rgba(255,97,0,0.12);
  display: flex; align-items: center; justify-content: space-between;
}
.brand-hall .chrome-brand { display: flex; align-items: center; gap: 12px; }
.brand-hall .chrome-logo { width: 32px; height: 32px; filter: drop-shadow(0 0 8px rgba(255,97,0,0.4)); }
.brand-hall .chrome-name { font-family: var(--display); font-size: 24px; letter-spacing: 0.05em; color: var(--text); line-height: 1; }
.brand-hall .chrome-name .dot { color: var(--primary); text-shadow: 0 0 8px var(--primary-glow); }
.brand-hall .chrome-right { display: flex; align-items: center; gap: 14px; }
.brand-hall .chrome-share {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 10px; color: var(--text-muted);
  letter-spacing: 0.2em; text-transform: uppercase;
  padding: 8px 14px;
  border: 1px solid var(--border);
  transition: all 0.15s;
}
.brand-hall .chrome-share:hover { color: var(--text); border-color: var(--text-dim); }
.brand-hall .chrome-cta {
  display: inline-flex; align-items: center; gap: 10px;
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.22em; text-transform: uppercase;
  padding: 9px 18px;
  border: 1px solid var(--primary);
  transition: all 0.15s;
}
.brand-hall .chrome-cta:hover { background: var(--primary); color: var(--bg); box-shadow: 0 0 18px rgba(255,97,0,0.4); }
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
.brand-hall .vh { position: relative; z-index: 1; padding-top: 0; min-height: 100vh; }

/* HERO */
.brand-hall .hero { position: relative; min-height: 100vh; max-height: 900px; padding: 48px 56px 48px; display: flex; align-items: center; overflow: hidden; }
.brand-hall .hero-banner { position: absolute; top: -80px; left: 0; right: 0; bottom: 0; z-index: 0; overflow: hidden; }
.brand-hall .hero-banner-img {
  position: absolute; inset: 0;
  background:
    linear-gradient(180deg, rgba(0,0,3,0) 0%, rgba(0,0,3,0) 40%, rgba(0,0,3,0.7) 80%, rgba(0,0,3,1) 100%),
    linear-gradient(90deg, rgba(0,0,3,0.5) 0%, rgba(0,0,3,0) 50%, rgba(0,0,3,0.3) 100%),
    radial-gradient(ellipse 800px 600px at 70% 40%, #4a1468 0%, #1e0824 40%, #000003 80%);
  filter: saturate(1.1) contrast(1.05);
}
.brand-hall .hero-banner-asset-slot {
  position: absolute; top: 0; right: 0; bottom: 0; width: 65%;
  opacity: 0.4;
  mask-image: linear-gradient(270deg, black 20%, transparent 80%);
  -webkit-mask-image: linear-gradient(270deg, black 20%, transparent 80%);
}
.brand-hall .hero-content { position: relative; z-index: 2; display: grid; grid-template-columns: 1.3fr 1fr; gap: 64px; width: 100%; max-width: 1400px; margin: 0 auto; align-items: center; }
.brand-hall .hero-left { min-width: 0; }
.brand-hall .hero-tag { display: inline-flex; align-items: center; gap: 10px; font-size: 10px; color: var(--primary); letter-spacing: 0.32em; text-transform: uppercase; margin-bottom: 24px; }
.brand-hall .hero-tag::before { content: ''; width: 32px; height: 1px; background: var(--primary); box-shadow: 0 0 6px var(--primary); }
.brand-hall .identity { display: flex; align-items: center; gap: 24px; margin-bottom: 28px; }
.brand-hall .identity-meta { flex: 1; min-width: 0; }
.brand-hall .hero-name { font-family: var(--display); font-size: 92px; line-height: 0.9; letter-spacing: 0.015em; color: var(--text); margin-bottom: 6px; text-shadow: 0 0 36px rgba(255,97,0,0.25); }
.brand-hall .hero-handle { display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--text-muted); letter-spacing: 0.08em; margin-bottom: 14px; }
.brand-hall .hero-tagline { font-size: 15px; color: var(--text); line-height: 1.6; max-width: 520px; font-style: italic; letter-spacing: 0.01em; }
.brand-hall .hero-tagline::before { content: '"'; color: var(--primary); margin-right: 4px; }
.brand-hall .hero-tagline::after { content: '"'; color: var(--primary); margin-left: 4px; }

/* Brand identity */
.brand-hall .brand-logo-wrap { position: relative; width: 120px; height: 120px; flex-shrink: 0; }
.brand-hall .brand-logo-wrap::before {
  content: ''; position: absolute; inset: -4px;
  border: 2px solid var(--brand-accent);
  border-radius: 8px;
  box-shadow: 0 0 20px var(--brand-accent-glow), inset 0 0 20px rgba(225,27,43,0.2);
  animation: brandHallRingPulse 3s ease-in-out infinite;
}
@keyframes brandHallRingPulse {
  0%, 100% { box-shadow: 0 0 20px var(--brand-accent-glow), inset 0 0 20px rgba(225,27,43,0.2); }
  50% { box-shadow: 0 0 34px var(--brand-accent-glow), inset 0 0 24px rgba(225,27,43,0.35); }
}
.brand-hall .brand-logo {
  position: relative; width: 100%; height: 100%;
  border-radius: 6px;
  background: linear-gradient(135deg, #1a1c1f 0%, #0e0f11 100%);
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.brand-hall .brand-logo-img { width: 100%; height: 100%; object-fit: cover; }
.brand-hall .brand-logo-fallback { font-family: var(--display); font-size: 72px; color: var(--brand-accent); text-shadow: 0 0 20px var(--brand-accent-glow); }
.brand-hall .verified-tick {
  position: absolute; bottom: -6px; right: -6px;
  width: 24px; height: 24px;
  border-radius: 50%;
  background: var(--accent); color: var(--bg);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 0 14px var(--accent-glow), 0 0 0 3px var(--bg);
  z-index: 2;
}
.brand-hall .brand-actions { display: flex; flex-direction: column; gap: 10px; align-self: flex-start; margin-left: auto; }
.brand-hall .btn-follow,
.brand-hall .btn-share {
  display: inline-flex; align-items: center; gap: 8px;
  font-family: var(--mono); font-size: 11px;
  letter-spacing: 0.18em; text-transform: uppercase;
  padding: 12px 16px;
  background: rgba(193,245,39,0.08);
  color: var(--accent);
  border: 1px solid rgba(193,245,39,0.35);
  cursor: pointer;
  transition: all 0.18s;
  white-space: nowrap;
}
.brand-hall .btn-follow:hover { background: var(--accent); color: var(--bg); box-shadow: 0 0 18px var(--accent-glow); }
.brand-hall .btn-follow.following {
  background: var(--accent); color: var(--bg); border-color: var(--accent);
}
.brand-hall .btn-share { padding: 12px; align-self: flex-end; }
.brand-hall .btn-share:hover { background: var(--accent); color: var(--bg); box-shadow: 0 0 18px var(--accent-glow); }
.brand-hall .brand-stats { border-color: rgba(225,27,43,0.25); }
.brand-hall .brand-stats .stat-cell.highlight { background: rgba(225,27,43,0.07); }
.brand-hall .brand-stats .stat-cell.highlight .stat-cell-num { color: var(--brand-accent); text-shadow: 0 0 16px var(--brand-accent-glow); }

/* Pursuit */
.brand-hall .btn-pursuit {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  width: 100%; margin-top: 18px;
  padding: 14px 20px;
  font-family: var(--mono); font-size: 11px;
  letter-spacing: 0.22em; text-transform: uppercase;
  background: var(--accent); color: var(--bg);
  border: 1px solid var(--accent);
  cursor: pointer; transition: all 0.18s;
  box-shadow: 0 0 24px var(--accent-glow);
}
.brand-hall .btn-pursuit:hover:not(:disabled) {
  background: transparent; color: var(--accent);
  box-shadow: 0 0 30px var(--accent-glow), inset 0 0 20px rgba(193,245,39,0.1);
}
.brand-hall .btn-pursuit.pursuing {
  background: transparent; color: var(--accent);
  border-color: rgba(193,245,39,0.4); box-shadow: none; cursor: default;
}

/* Stats bar */
.brand-hall .hero-stats {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 2px;
  background: rgba(42,44,46,0.5);
  border: 1px solid rgba(255,97,0,0.2);
  margin-top: 36px; max-width: 680px;
}
.brand-hall .stat-cell { padding: 18px 16px; background: rgba(14,15,17,0.85); text-align: left; position: relative; }
.brand-hall .stat-cell.highlight { background: rgba(255,97,0,0.06); }
.brand-hall .stat-cell-num { font-family: var(--display); font-size: 42px; line-height: 0.95; color: var(--text); letter-spacing: 0.02em; }
.brand-hall .stat-cell.highlight .stat-cell-num { color: var(--primary); text-shadow: 0 0 16px var(--primary-glow); }
.brand-hall .stat-cell-lbl { font-size: 9px; color: var(--text-dim); letter-spacing: 0.22em; text-transform: uppercase; margin-top: 4px; }

/* Showcase card */
.brand-hall .showcase-card {
  position: relative; padding: 32px;
  background: linear-gradient(135deg, rgba(193,245,39,0.06) 0%, transparent 50%), rgba(14,15,17,0.85);
  border: 1px solid rgba(193,245,39,0.25);
  box-shadow: 0 0 0 1px rgba(193,245,39,0.08), inset 0 0 80px rgba(193,245,39,0.04), 0 30px 80px rgba(0,0,0,0.6);
  overflow: hidden;
}
.brand-hall .showcase-card::after {
  content: ''; position: absolute; top: -50px; right: -50px;
  width: 300px; height: 300px;
  background: radial-gradient(circle, rgba(193,245,39,0.12), transparent 65%);
  filter: blur(40px); pointer-events: none;
}
.brand-hall .showcase-label { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid rgba(193,245,39,0.15); }
.brand-hall .showcase-label-text {
  font-size: 10px; color: var(--accent);
  letter-spacing: 0.3em; text-transform: uppercase;
  display: flex; align-items: center; gap: 10px;
}
.brand-hall .showcase-label-text::before {
  content: ''; width: 6px; height: 6px; background: var(--accent); border-radius: 50%;
  box-shadow: 0 0 8px var(--accent);
  animation: brandHallPulseDot 2s ease-in-out infinite;
}
@keyframes brandHallPulseDot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.2); }
}
.brand-hall .showcase-label-count { font-size: 10px; color: var(--text-dim); letter-spacing: 0.15em; text-transform: uppercase; }
.brand-hall .showcase-trophy { position: relative; display: flex; flex-direction: column; align-items: center; padding: 16px 0 4px; }
.brand-hall .showcase-trophy-asset {
  width: 140px; height: 140px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px;
  filter: drop-shadow(0 0 30px var(--accent-glow));
}
.brand-hall .showcase-trophy-img { max-width: 100%; max-height: 100%; object-fit: contain; }
.brand-hall .showcase-trophy-name { font-family: var(--display); font-size: 28px; color: var(--text); letter-spacing: 0.02em; margin-bottom: 6px; text-align: center; }
.brand-hall .showcase-trophy-sub { font-size: 11px; color: var(--text-muted); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 18px; text-align: center; }
.brand-hall .showcase-trophy-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border-top: 1px solid rgba(42,44,46,0.6); margin-top: 4px; }
.brand-hall .tmeta-cell { padding: 12px; text-align: center; border-right: 1px solid rgba(42,44,46,0.6); }
.brand-hall .tmeta-cell:last-child { border-right: none; }
.brand-hall .tmeta-val { font-family: var(--display); font-size: 22px; color: var(--primary); line-height: 1; text-shadow: 0 0 10px var(--primary-glow); }
.brand-hall .tmeta-lbl { font-size: 9px; color: var(--text-dim); letter-spacing: 0.2em; text-transform: uppercase; margin-top: 4px; }
.brand-hall .showcase-empty { padding: 40px 0; text-align: center; color: var(--text-muted); font-size: 12px; }

/* Scroll cue */
.brand-hall .scroll-cue {
  position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.3em; text-transform: uppercase;
  display: flex; flex-direction: column; align-items: center; gap: 10px;
  animation: brandHallBounce 2.5s ease-in-out infinite;
  z-index: 3;
}
.brand-hall .scroll-cue-line { width: 1px; height: 30px; background: linear-gradient(180deg, var(--primary), transparent); }
@keyframes brandHallBounce {
  0%, 100% { transform: translate(-50%, 0); }
  50% { transform: translate(-50%, 6px); }
}

/* SECTIONS */
.brand-hall .narrative { padding: 80px 56px 120px; max-width: 1280px; margin: 0 auto; position: relative; }
.brand-hall .section { margin-bottom: 96px; }
.brand-hall .section:last-child { margin-bottom: 0; }
.brand-hall .section-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 40px; gap: 24px; flex-wrap: wrap; }
.brand-hall .section-title { font-family: var(--display); font-size: 56px; line-height: 1; color: var(--text); letter-spacing: 0.02em; }
.brand-hall .section-title .accent-word { color: var(--primary); text-shadow: 0 0 20px var(--primary-glow); }
.brand-hall .section-meta { font-size: 10px; color: var(--text-dim); letter-spacing: 0.22em; text-transform: uppercase; padding-bottom: 12px; }

/* Active filters + grid */
.brand-hall .active-filters { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
.brand-hall .afilter {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 8px 16px;
  font-family: var(--mono); font-size: 11px;
  letter-spacing: 0.18em; text-transform: uppercase;
  background: transparent; color: var(--text-muted);
  border: 1px solid var(--border);
  border-radius: 20px;
  cursor: pointer; transition: all 0.15s;
}
.brand-hall .afilter:hover { color: var(--text); border-color: var(--text-muted); }
.brand-hall .afilter.active { background: var(--accent); color: var(--bg); border-color: var(--accent); box-shadow: 0 0 14px var(--accent-glow); }
.brand-hall .afilter-count { font-size: 10px; opacity: 0.7; padding: 2px 6px; background: rgba(0,0,0,0.2); border-radius: 8px; }
.brand-hall .afilter.active .afilter-count { background: rgba(0,0,0,0.25); }

.brand-hall .active-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.brand-hall .active-card { display: flex; background: var(--surface); border: 1px solid var(--border); border-radius: 6px; overflow: hidden; transition: all 0.18s; position: relative; }
.brand-hall .active-card:hover { border-color: rgba(225,27,43,0.35); transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,0,0,0.4); }
.brand-hall .active-card.pursuing { border-color: rgba(193,245,39,0.3); }
.brand-hall .active-card.conquered { border-color: rgba(193,245,39,0.45); background: linear-gradient(135deg, rgba(193,245,39,0.04) 0%, var(--surface) 60%); }
.brand-hall .active-card-asset { flex-shrink: 0; width: 96px; background: var(--surface-2); display: flex; align-items: center; justify-content: center; border-right: 1px solid var(--border); }
.brand-hall .active-card-asset.chest-asset { background: linear-gradient(135deg, rgba(225,27,43,0.06) 0%, var(--surface-2) 100%); }
.brand-hall .active-card-img { max-width: 100%; max-height: 100%; object-fit: contain; }
.brand-hall .active-card-body { flex: 1; padding: 14px 16px; display: flex; flex-direction: column; min-width: 0; }
.brand-hall .active-card-meta { display: flex; gap: 6px; margin-bottom: 10px; flex-wrap: wrap; }
.brand-hall .ac-tag { font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase; padding: 3px 7px; border-radius: 3px; white-space: nowrap; }
.brand-hall .tag-trophy { background: rgba(225,27,43,0.12); color: var(--brand-accent); border: 1px solid rgba(225,27,43,0.3); }
.brand-hall .tag-chest { background: rgba(255,97,0,0.12); color: var(--primary); border: 1px solid rgba(255,97,0,0.3); }
.brand-hall .tag-time { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
.brand-hall .tag-conquered { background: rgba(193,245,39,0.12); color: var(--accent); border: 1px solid rgba(193,245,39,0.3); }
.brand-hall .active-card-name { font-family: var(--display); font-size: 22px; line-height: 1; color: var(--text); margin-bottom: 6px; letter-spacing: 0.01em; }
.brand-hall .active-card-cond { font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 12px; }
.brand-hall .active-card-foot { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; margin-top: auto; }
.brand-hall .xp-pill { font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase; padding: 4px 8px; background: rgba(255,97,0,0.15); color: var(--primary); border-radius: 3px; }
.brand-hall .pursuing-count { font-size: 10px; color: var(--text-dim); letter-spacing: 0.1em; }
.brand-hall .btn-pursuit-sm {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; padding: 9px 12px;
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.2em; text-transform: uppercase;
  background: var(--accent); color: var(--bg);
  border: 1px solid var(--accent);
  cursor: pointer; transition: all 0.15s;
}
.brand-hall .btn-pursuit-sm:hover:not(:disabled) { background: transparent; color: var(--accent); box-shadow: 0 0 14px var(--accent-glow); }
.brand-hall .btn-pursuit-sm.pursuing { background: transparent; color: var(--accent); border-color: rgba(193,245,39,0.4); cursor: default; }
.brand-hall .btn-pursuit-sm.conquered { background: transparent; color: var(--text-muted); border-color: var(--border); cursor: default; }
.brand-hall .active-empty { padding: 40px 0; text-align: center; color: var(--text-muted); font-size: 12px; border: 1px dashed var(--border); border-radius: 6px; }

/* Wall */
.brand-hall .wall-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; }
.brand-hall .wall-col { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: 20px; }
.brand-hall .wall-col-head {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 18px; padding-bottom: 14px;
  border-bottom: 1px solid var(--border);
  font-size: 11px; letter-spacing: 0.22em; text-transform: uppercase; color: var(--text);
}
.brand-hall .wall-col-sub { color: var(--text-dim); font-size: 10px; }
.brand-hall .wall-col-sub.live-pulse { color: var(--accent); display: flex; align-items: center; gap: 6px; }
.brand-hall .wall-col-sub.live-pulse::before {
  content: ''; width: 6px; height: 6px;
  background: var(--accent); border-radius: 50%;
  box-shadow: 0 0 8px var(--accent);
  animation: brandHallPulseDot 2s ease-in-out infinite;
}
.brand-hall .wall-list { display: flex; flex-direction: column; gap: 10px; }
.brand-hall .wall-row {
  display: grid; grid-template-columns: 32px 36px 1fr 60px;
  align-items: center; gap: 12px;
  padding: 8px 4px;
  border-bottom: 1px solid rgba(42,44,46,0.4);
}
.brand-hall .wall-row:last-child { border-bottom: none; }
.brand-hall .wall-rank { font-family: var(--display); font-size: 20px; color: var(--text-dim); text-align: center; }
.brand-hall .wall-row:nth-child(-n+3) .wall-rank { color: var(--accent); text-shadow: 0 0 10px var(--accent-glow); }
.brand-hall .wall-avatar { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--display); font-size: 16px; color: var(--bg); flex-shrink: 0; }
.brand-hall .wall-avatar.accent-1 { background: linear-gradient(135deg, #f5c547 0%, #d98c3a 100%); }
.brand-hall .wall-avatar.accent-2 { background: linear-gradient(135deg, #c1f527 0%, #8ab81a 100%); }
.brand-hall .wall-avatar.accent-3 { background: linear-gradient(135deg, #ff6100 0%, #a94000 100%); }
.brand-hall .wall-avatar.accent-4 { background: linear-gradient(135deg, #66c0f4 0%, #2a6ba0 100%); }
.brand-hall .wall-avatar.accent-5 { background: linear-gradient(135deg, #e11b2b 0%, #7a1018 100%); }
.brand-hall .wall-info { min-width: 0; }
.brand-hall .wall-name { font-size: 13px; color: var(--text); letter-spacing: 0.02em; }
.brand-hall .wall-meta { font-size: 10px; color: var(--text-dim); letter-spacing: 0.08em; }
.brand-hall .wall-bar { width: 60px; height: 3px; background: var(--surface-3); border-radius: 2px; overflow: hidden; }
.brand-hall .wall-bar span { display: block; height: 100%; background: linear-gradient(90deg, var(--brand-accent) 0%, var(--accent) 100%); box-shadow: 0 0 6px rgba(193,245,39,0.4); }
.brand-hall .wall-empty { color: var(--text-muted); font-size: 12px; padding: 16px 0; text-align: center; }

.brand-hall .latest-list { display: flex; flex-direction: column; gap: 14px; }
.brand-hall .latest-row { display: flex; align-items: center; gap: 12px; padding-bottom: 14px; border-bottom: 1px solid rgba(42,44,46,0.4); }
.brand-hall .latest-row:last-child { border-bottom: none; padding-bottom: 0; }
.brand-hall .latest-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--display); font-size: 14px; color: var(--bg); flex-shrink: 0; }
.brand-hall .latest-avatar.accent-1 { background: linear-gradient(135deg, #f5c547 0%, #d98c3a 100%); }
.brand-hall .latest-avatar.accent-2 { background: linear-gradient(135deg, #c1f527 0%, #8ab81a 100%); }
.brand-hall .latest-avatar.accent-3 { background: linear-gradient(135deg, #ff6100 0%, #a94000 100%); }
.brand-hall .latest-avatar.accent-4 { background: linear-gradient(135deg, #66c0f4 0%, #2a6ba0 100%); }
.brand-hall .latest-avatar.accent-5 { background: linear-gradient(135deg, #e11b2b 0%, #7a1018 100%); }
.brand-hall .latest-info { flex: 1; min-width: 0; }
.brand-hall .latest-line { font-size: 12px; color: var(--text-muted); line-height: 1.5; }
.brand-hall .latest-line strong { color: var(--text); font-weight: 400; }
.brand-hall .latest-trophy { color: var(--brand-accent); text-shadow: 0 0 8px rgba(225,27,43,0.3); }
.brand-hall .latest-time { font-size: 10px; color: var(--text-dim); letter-spacing: 0.1em; margin-top: 2px; }

/* About */
.brand-hall .about-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
.brand-hall .about-block { background: var(--surface); border: 1px solid var(--border); border-radius: 6px; padding: 18px 20px; }
.brand-hall .about-label { font-size: 9px; letter-spacing: 0.25em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 8px; }
.brand-hall .about-value { font-size: 13px; color: var(--text); line-height: 1.5; }
.brand-hall .about-links { display: flex; flex-direction: column; gap: 6px; }
.brand-hall .about-link { font-size: 13px; color: var(--accent); letter-spacing: 0.04em; transition: color 0.15s; }
.brand-hall .about-link:hover { color: var(--text); text-shadow: 0 0 8px var(--accent-glow); }

/* Footer */
.brand-hall .vh-footer { padding: 40px 56px 60px; border-top: 1px solid rgba(255,97,0,0.1); background: rgba(5,5,8,0.6); backdrop-filter: blur(10px); position: relative; z-index: 1; }
.brand-hall .vh-footer-inner { max-width: 1280px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
.brand-hall .vh-footer-left { font-size: 11px; color: var(--text-muted); letter-spacing: 0.08em; line-height: 1.7; }
.brand-hall .vh-footer-left strong { color: var(--text); font-family: var(--display); font-size: 16px; letter-spacing: 0.04em; }
.brand-hall .vh-footer-actions { display: flex; gap: 10px; }
.brand-hall .footer-btn {
  display: inline-flex; align-items: center; gap: 10px;
  padding: 12px 20px; font-size: 10px;
  letter-spacing: 0.22em; text-transform: uppercase;
  border: 1px solid var(--primary); background: var(--primary); color: var(--bg);
  transition: all 0.15s; box-shadow: 0 0 16px rgba(255,97,0,0.25);
}
.brand-hall .footer-btn:hover { background: #ff7e2e; border-color: #ff7e2e; box-shadow: 0 0 28px rgba(255,97,0,0.5); }
.brand-hall .footer-btn.ghost { background: transparent; color: var(--text); border-color: rgba(254,237,223,0.3); box-shadow: none; }
.brand-hall .footer-btn.ghost:hover { border-color: var(--text); box-shadow: 0 0 14px rgba(254,237,223,0.15); }

/* Terminal strip */
.brand-hall .terminal-strip {
  padding: 18px 56px;
  border-top: 1px solid rgba(42,44,46,0.5);
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  display: flex; justify-content: space-between;
  max-width: 1400px; margin: 0 auto;
}
.brand-hall .cursor-blink {
  display: inline-block; width: 7px; height: 10px;
  background: var(--primary); margin-left: 4px; vertical-align: middle;
  animation: brandHallBlink 1s steps(1) infinite;
}
@keyframes brandHallBlink { 50% { opacity: 0; } }

/* RESPONSIVE */
@media (max-width: 1100px) {
  .brand-hall .hero { padding: 36px 32px; min-height: auto; max-height: none; }
  .brand-hall .hero-content { grid-template-columns: 1fr; gap: 40px; }
  .brand-hall .showcase-card { max-width: 480px; }
  .brand-hall .narrative { padding: 56px 32px 80px; }
  .brand-hall .section-title { font-size: 42px; }
  .brand-hall .hero-stats { max-width: 100%; }
}
@media (max-width: 840px) {
  .brand-hall .wall-grid { grid-template-columns: 1fr; }
  .brand-hall .brand-actions { flex-direction: row; margin-left: 0; margin-top: 14px; width: 100%; }
  .brand-hall .btn-share { align-self: auto; }
  .brand-hall .identity { flex-wrap: wrap; }
}
@media (max-width: 700px) {
  .brand-hall .public-chrome { padding: 12px 16px; }
  .brand-hall .chrome-share { display: none; }
  .brand-hall .chrome-name { font-size: 18px; }
  .brand-hall .hero { padding: 28px 16px 48px; }
  .brand-hall .identity { flex-direction: column; align-items: flex-start; gap: 16px; }
  .brand-hall .brand-logo-wrap { width: 88px; height: 88px; }
  .brand-hall .brand-logo-fallback { font-size: 50px; }
  .brand-hall .hero-name { font-size: 58px; }
  .brand-hall .hero-stats { grid-template-columns: repeat(2, 1fr); }
  .brand-hall .stat-cell-num { font-size: 32px; }
  .brand-hall .showcase-card { padding: 22px; }
  .brand-hall .narrative { padding: 40px 16px 60px; }
  .brand-hall .section { margin-bottom: 60px; }
  .brand-hall .section-title { font-size: 32px; }
  .brand-hall .section-header { align-items: flex-start; }
  .brand-hall .vh-footer { padding: 32px 16px; }
  .brand-hall .vh-footer-actions { width: 100%; flex-direction: column; }
  .brand-hall .footer-btn { justify-content: center; width: 100%; }
  .brand-hall .terminal-strip { padding: 16px; flex-direction: column; gap: 8px; text-align: left; }
  .brand-hall .scroll-cue { display: none; }
}
@media (max-width: 480px) {
  .brand-hall .active-grid { grid-template-columns: 1fr; }
  .brand-hall .hero-stats.brand-stats { grid-template-columns: repeat(2, 1fr); }
  .brand-hall .active-card-asset { width: 80px; }
  .brand-hall .wall-row { grid-template-columns: 24px 32px 1fr; }
  .brand-hall .wall-bar { display: none; }
}
</style>

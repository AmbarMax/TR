<template>
  <div v-if="halls.length" class="featured-halls">
    <div class="fh-head">
      <div class="fh-head-left">
        <span class="fh-tag">Featured this week</span>
        <h2 class="fh-title">Brand Halls <span class="accent-word">live now</span></h2>
      </div>
      <a href="#discover-halls" class="fh-head-link">Browse all halls →</a>
    </div>

    <div class="fh-scroller">
      <router-link
        v-for="hall in halls"
        :key="hall.username"
        :to="`/${hall.username}`"
        class="hall-card"
        :style="cardStyle(hall)"
      >
        <div class="hall-card-banner">
          <div class="hall-card-banner-overlay"></div>
        </div>
        <div class="hall-card-body">
          <div class="hall-card-id">
            <div class="hall-card-logo">
              <img v-if="hasCustomAvatar(hall)" :src="hall.avatar" :alt="hall.name" class="hall-card-logo-img" />
              <span v-else>{{ logoLetter(hall) }}</span>
            </div>
            <div class="hall-card-meta">
              <div class="hall-card-name">{{ hall.name || hall.username }}</div>
              <div class="hall-card-handle">
                @{{ hall.username }}<template v-if="hall.is_verified"> · Verified</template>
              </div>
            </div>
          </div>
          <div class="hall-card-stats">
            <div class="hcard-stat">
              <span class="hcard-stat-num">{{ hall.live_trophies ?? 0 }}</span>
              <span class="hcard-stat-lbl">Live trophies</span>
            </div>
            <div class="hcard-stat">
              <span class="hcard-stat-num">{{ formatBigNumber(hall.pursuing_now ?? 0) }}</span>
              <span class="hcard-stat-lbl">Pursuing now</span>
            </div>
          </div>
          <div class="hall-card-cta">
            <span>Visit Hall</span>
            <span>→</span>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: "FeaturedHallsStrip",
  props: {
    halls: { type: Array, default: () => [] },
  },
  methods: {
    cardStyle(hall) {
      const accent = hall.accent_color || "#ff6100";
      return {
        "--hcard-accent": accent,
        "--hcard-glow": this.hexToGlow(accent),
      };
    },
    hexToGlow(hex) {
      const m = /^#?([0-9a-f]{6})$/i.exec(hex || "");
      if (!m) return "rgba(255,97,0,0.45)";
      const r = parseInt(m[1].slice(0, 2), 16);
      const g = parseInt(m[1].slice(2, 4), 16);
      const b = parseInt(m[1].slice(4, 6), 16);
      return `rgba(${r},${g},${b},0.45)`;
    },
    hasCustomAvatar(hall) {
      return hall.avatar && !hall.avatar.includes("default-profile-img");
    },
    logoLetter(hall) {
      return (hall.name || hall.username || "?").trim().charAt(0).toUpperCase();
    },
    formatBigNumber(n) {
      const num = Number(n) || 0;
      if (num >= 1000) {
        const k = num / 1000;
        return (k >= 10 ? Math.round(k) : k.toFixed(1)) + "k";
      }
      return String(num);
    },
  },
};
</script>

<style>
/* Prefixed with .featured-halls so styles never leak. */
.featured-halls {
  margin: 0 auto 32px;
  max-width: 1240px;
  padding: 0 32px;
  font-family: 'Share Tech Mono', monospace;
  color: #feeddf;
}
.featured-halls .fh-head {
  display: flex; align-items: flex-end; justify-content: space-between;
  margin-bottom: 18px; flex-wrap: wrap; gap: 16px;
}
.featured-halls .fh-head-left { display: flex; flex-direction: column; gap: 4px; }
.featured-halls .fh-tag {
  font-size: 10px; letter-spacing: 0.28em; text-transform: uppercase;
  color: #c1f527;
  display: inline-flex; align-items: center; gap: 8px;
}
.featured-halls .fh-tag::before {
  content: ''; width: 6px; height: 6px;
  background: #c1f527; border-radius: 50%;
  box-shadow: 0 0 8px #c1f527;
  animation: featuredHallsPulseDot 2s ease-in-out infinite;
}
@keyframes featuredHallsPulseDot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.2); }
}
.featured-halls .fh-title {
  font-family: 'VT323', monospace;
  font-size: 36px; line-height: 1; color: #feeddf;
  letter-spacing: 0.015em; margin: 0;
}
.featured-halls .fh-title .accent-word {
  color: #ff6100; text-shadow: 0 0 18px rgba(255, 97, 0, 0.45);
}
.featured-halls .fh-head-link {
  font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase;
  color: #9a9590; text-decoration: none; transition: color 0.15s;
}
.featured-halls .fh-head-link:hover { color: #c1f527; }

.featured-halls .fh-scroller {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}
.featured-halls .hall-card {
  --hcard-accent: #ff6100;
  --hcard-glow: rgba(255, 97, 0, 0.45);
  position: relative;
  display: flex; flex-direction: column;
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  overflow: hidden;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
  text-decoration: none;
  color: inherit;
}
.featured-halls .hall-card:hover {
  border-color: var(--hcard-accent);
  transform: translateY(-3px);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5), 0 0 24px var(--hcard-glow);
}
.featured-halls .hall-card-banner {
  position: relative;
  height: 80px;
  background:
    linear-gradient(135deg, var(--hcard-accent) 0%, transparent 70%),
    linear-gradient(45deg, rgba(0, 0, 0, 0.6) 0%, transparent 100%),
    #1a1c1f;
  overflow: hidden;
}
.featured-halls .hall-card-banner::before {
  content: ''; position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
  background-size: 32px 32px;
  opacity: 0.6;
}
.featured-halls .hall-card-banner-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(180deg, transparent 50%, rgba(14, 15, 17, 0.9) 100%);
}
.featured-halls .hall-card-body {
  padding: 16px 18px 18px;
  display: flex; flex-direction: column; gap: 14px;
  flex: 1;
}
.featured-halls .hall-card-id {
  display: flex; align-items: center; gap: 12px;
  margin-top: -36px;
  position: relative; z-index: 2;
}
.featured-halls .hall-card-logo {
  width: 56px; height: 56px;
  border-radius: 6px;
  background: #1a1c1f;
  border: 2px solid var(--hcard-accent);
  display: flex; align-items: center; justify-content: center;
  font-family: 'VT323', monospace;
  font-size: 32px;
  color: var(--hcard-accent);
  text-shadow: 0 0 12px var(--hcard-glow);
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}
.featured-halls .hall-card-logo-img { width: 100%; height: 100%; object-fit: cover; }
.featured-halls .hall-card-meta { flex: 1; min-width: 0; padding-top: 18px; }
.featured-halls .hall-card-name {
  font-family: 'VT323', monospace;
  font-size: 24px; line-height: 1; color: #feeddf;
  margin-bottom: 2px; letter-spacing: 0.01em;
}
.featured-halls .hall-card-handle {
  font-size: 10px; letter-spacing: 0.15em; color: #5a5550;
}
.featured-halls .hall-card-stats {
  display: grid; grid-template-columns: 1fr 1fr; gap: 1px;
  background: #2a2c2e;
  border: 1px solid #2a2c2e;
}
.featured-halls .hcard-stat {
  padding: 10px 12px;
  background: rgba(14, 15, 17, 0.85);
  display: flex; flex-direction: column; gap: 2px;
}
.featured-halls .hcard-stat-num {
  font-family: 'VT323', monospace;
  font-size: 22px; line-height: 1; color: #feeddf; letter-spacing: 0.02em;
}
.featured-halls .hcard-stat-lbl {
  font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase; color: #5a5550;
}
.featured-halls .hall-card-cta {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 14px;
  background: transparent;
  border: 1px solid #2a2c2e;
  font-size: 10px; letter-spacing: 0.22em; text-transform: uppercase;
  color: #9a9590;
  transition: all 0.15s;
}
.featured-halls .hall-card:hover .hall-card-cta {
  background: var(--hcard-accent);
  color: #000003;
  border-color: var(--hcard-accent);
}

@media (max-width: 768px) {
  .featured-halls { padding: 0 16px; }
}
</style>

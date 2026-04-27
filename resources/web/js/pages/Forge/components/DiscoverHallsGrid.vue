<template>
  <section v-if="halls.length || meta" class="discover-halls" id="discover-halls">
    <div class="dh-head">
      <div>
        <div class="dh-tag">Discover</div>
        <h2 class="dh-title">All active <span class="accent-word">Brand Halls</span></h2>
      </div>
      <span class="dh-meta">
        {{ totalLabel }}
      </span>
    </div>

    <div v-if="halls.length" class="dh-grid">
      <router-link
        v-for="hall in halls"
        :key="hall.username"
        :to="`/${hall.username}`"
        class="dh-card"
        :style="cardStyle(hall)"
      >
        <div class="dh-card-logo">
          <img v-if="hasCustomAvatar(hall)" :src="hall.avatar" :alt="hall.name" class="dh-card-logo-img" />
          <span v-else>{{ logoLetter(hall) }}</span>
        </div>
        <div class="dh-card-body">
          <div class="dh-card-name">{{ hall.name || hall.username }}</div>
          <div class="dh-card-meta">
            {{ hall.live_trophies ?? 0 }} {{ (hall.live_trophies === 1) ? 'trophy' : 'trophies' }} live
            <template v-if="hall.pursuing_now"> · {{ formatBigNumber(hall.pursuing_now) }} pursuing</template>
          </div>
        </div>
      </router-link>
    </div>

    <p v-else class="dh-empty">No other halls live yet. Check back soon.</p>
  </section>
</template>

<script>
export default {
  name: "DiscoverHallsGrid",
  props: {
    halls: { type: Array, default: () => [] },
    meta: { type: Object, default: null },
  },
  computed: {
    totalLabel() {
      const total = this.meta?.total ?? this.halls.length;
      if (!total) return "Updated weekly";
      return `${total} ${total === 1 ? "brand" : "brands"} · Updated weekly`;
    },
  },
  methods: {
    cardStyle(hall) {
      return { "--dh-accent": hall.accent_color || "#ff6100" };
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
/* Prefixed with .discover-halls so styles never leak. */
.discover-halls {
  margin: 48px auto 0;
  max-width: 1240px;
  padding: 40px 32px 0;
  border-top: 1px solid #2a2c2e;
  font-family: 'Share Tech Mono', monospace;
  color: #feeddf;
}
.discover-halls .dh-head {
  display: flex; align-items: flex-end; justify-content: space-between;
  margin-bottom: 24px; flex-wrap: wrap; gap: 16px;
}
.discover-halls .dh-tag {
  font-size: 10px; letter-spacing: 0.28em; text-transform: uppercase;
  color: #9a9590; margin-bottom: 4px;
}
.discover-halls .dh-title {
  font-family: 'VT323', monospace;
  font-size: 32px; line-height: 1; color: #feeddf; letter-spacing: 0.015em; margin: 0;
}
.discover-halls .dh-title .accent-word {
  color: #ff6100; text-shadow: 0 0 16px rgba(255, 97, 0, 0.45);
}
.discover-halls .dh-meta {
  font-size: 11px; letter-spacing: 0.15em; color: #5a5550;
}

.discover-halls .dh-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}
.discover-halls .dh-card {
  --dh-accent: #ff6100;
  display: flex; align-items: center; gap: 14px;
  padding: 14px 16px;
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  text-decoration: none;
  color: inherit;
  transition: all 0.18s;
}
.discover-halls .dh-card:hover {
  border-color: var(--dh-accent);
  background: rgba(14, 15, 17, 1);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
}
.discover-halls .dh-card-logo {
  width: 42px; height: 42px;
  border-radius: 6px;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  display: flex; align-items: center; justify-content: center;
  font-family: 'VT323', monospace;
  font-size: 22px;
  color: var(--dh-accent);
  flex-shrink: 0;
  transition: all 0.15s;
  overflow: hidden;
}
.discover-halls .dh-card:hover .dh-card-logo {
  border-color: var(--dh-accent);
  box-shadow: 0 0 12px var(--dh-accent);
}
.discover-halls .dh-card-logo-img { width: 100%; height: 100%; object-fit: cover; }
.discover-halls .dh-card-body { min-width: 0; flex: 1; }
.discover-halls .dh-card-name {
  font-size: 14px; color: #feeddf; letter-spacing: 0.02em; margin-bottom: 2px;
}
.discover-halls .dh-card-meta {
  font-size: 10px; letter-spacing: 0.1em; color: #5a5550;
}
.discover-halls .dh-empty {
  color: #9a9590; font-size: 12px; padding: 24px 0; text-align: center;
  border: 1px dashed #2a2c2e; border-radius: 6px;
}

@media (max-width: 768px) {
  .discover-halls { padding: 32px 16px 0; }
}
</style>

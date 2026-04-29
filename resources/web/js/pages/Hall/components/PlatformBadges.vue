<template>
  <div v-if="platformGroups.length" class="platforms-section">
    <div
      v-for="group in platformGroups"
      :key="group.key"
      class="platform-block"
      :class="`platform-${group.key}`"
    >
      <div class="platform-header">
        <div class="platform-title">
          <div class="platform-title-icon" v-html="group.icon"></div>
          <div>
            <div class="platform-title-name">{{ group.name }}</div>
            <div class="platform-title-sub">Synced · {{ group.badges.length }} {{ group.badges.length === 1 ? 'badge' : 'badges' }}</div>
          </div>
        </div>
        <div class="platform-count" :style="countStyle(group.key)">{{ group.badges.length }}</div>
      </div>
      <div class="badges-grid">
        <div
          v-for="badge in group.badges"
          :key="badge.id"
          class="badge-tile"
          :class="badgeClass(badge)"
          :title="badge.name"
        >
          <img v-if="badgeImage(badge, group.key)" :src="badgeImage(badge, group.key)" :alt="badge.name" class="badge-img" />
          <span v-else class="badge-emoji">{{ badge.emoji || '⭐' }}</span>
        </div>
      </div>
    </div>
  </div>
  <p v-else class="player-empty">No platform badges synced yet.</p>
</template>

<script>
import { PLATFORM_ICONS } from "../../../constants/platform-icons.js";

// GitHub stays inline alongside the shared icon constants. The
// .platform-title-icon wrapper sizes child SVGs to 28px via CSS in
// PlayerHallView (the parent — this component has no scoped styles).
const PLATFORM_CONFIG = {
  ...PLATFORM_ICONS,
  github: {
    name: "GitHub",
    icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/></svg>',
  },
};

export default {
  name: "PlatformBadges",
  props: {
    badges: { type: Array, default: () => [] },
  },
  computed: {
    platformGroups() {
      const groups = {};
      this.badges.forEach(b => {
        const integration = b.integration;
        const rawKey = (typeof integration === "object" ? integration?.name : integration) || "unknown";
        const key = String(rawKey).toLowerCase();
        if (!groups[key]) groups[key] = [];
        groups[key].push(b);
      });
      return Object.entries(groups)
        .map(([key, badges]) => {
          const cfg = PLATFORM_CONFIG[key] || {
            name: key.charAt(0).toUpperCase() + key.slice(1),
            icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>',
          };
          return { key, name: cfg.name, icon: cfg.icon, badges };
        })
        .sort((a, b) => b.badges.length - a.badges.length);
    },
  },
  methods: {
    badgeImage(badge, platformKey) {
      if (!badge?.image) return null;
      const img = badge.image;
      if (img.startsWith("http") || img.startsWith("/")) return img;
      const integration = typeof badge.integration === "object"
        ? badge.integration?.name
        : (badge.integration || platformKey || "unknown");
      return `/storage/integrations/${integration}/${img}`;
    },
    badgeClass(badge) {
      return {
        legendary: badge.rarity === "legendary",
        rare: badge.rarity === "rare",
      };
    },
    countStyle(key) {
      if (key === "steam") {
        return { color: "#66c0f4", textShadow: "0 0 14px rgba(102,192,244,0.4)" };
      }
      return {};
    },
  },
};
</script>

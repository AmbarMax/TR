<template>
  <div v-if="achievements.length" class="achievements-list">
    <div
      v-for="ach in achievements"
      :key="ach.id"
      class="achievement-row"
    >
      <div class="ach-thumb">
        <img v-if="achImage(ach)" :src="achImage(ach)" :alt="ach.name" class="ach-thumb-img" />
        <div v-else class="ach-thumb-inner" :style="achThumbStyle(ach)">{{ achInitials(ach) }}</div>
      </div>
      <div class="ach-info">
        <div class="ach-name">{{ ach.name }}</div>
        <div v-if="ach.description" class="ach-desc">{{ ach.description }}</div>
      </div>
      <div v-if="isValidated(ach)" class="ach-badge">Validated</div>
    </div>
  </div>
  <p v-else class="player-empty">No custom achievements yet.</p>
</template>

<script>
export default {
  name: "CustomAchievements",
  props: {
    achievements: { type: Array, default: () => [] },
  },
  methods: {
    achImage(ach) {
      if (!ach?.image) return null;
      const img = ach.image;
      if (img.startsWith("http") || img.startsWith("/")) return img;
      return `/storage/achievements/${img}`;
    },
    isValidated(ach) {
      return ach.status === 1 || ach.status === "1" || ach.is_validated === true;
    },
    achThumbStyle(ach) {
      const src = ach?.name || "?";
      const hash = src.split("").reduce((h, c) => h + c.charCodeAt(0), 0);
      const hue = (hash * 37) % 360;
      return {
        background: `linear-gradient(135deg, hsl(${hue}, 40%, 22%), hsl(${(hue + 30) % 360}, 40%, 15%))`,
      };
    },
    achInitials(ach) {
      const src = (ach?.name || "?").trim();
      const parts = src.split(/\s+/);
      if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
      return src.slice(0, 2).toUpperCase();
    },
  },
};
</script>

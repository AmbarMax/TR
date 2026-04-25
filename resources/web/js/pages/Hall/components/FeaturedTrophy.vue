<template>
  <aside class="showcase-card">
    <div class="showcase-label">
      <div class="showcase-label-text">Featured this week</div>
      <div v-if="trophy" class="showcase-label-count">{{ subText }}</div>
    </div>

    <div v-if="trophy" class="showcase-trophy">
      <div class="showcase-trophy-asset">
        <img v-if="trophyImage" :src="trophyImage" :alt="trophy.name" class="showcase-trophy-img" />
        <svg v-else width="140" height="140" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
          <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="currentColor" stroke-width="2" style="color: var(--brand-accent)"/>
          <polygon points="50,22 75,35 50,48 25,35" fill="currentColor" style="color: var(--brand-accent)"/>
          <polygon points="50,48 75,35 75,65 50,78" fill="currentColor" style="color: var(--brand-accent); opacity: 0.7"/>
          <polygon points="50,48 25,35 25,65 50,78" fill="currentColor" style="color: var(--brand-accent); opacity: 0.5"/>
        </svg>
      </div>
      <h3 class="showcase-trophy-name">{{ trophy.name }}</h3>
      <p class="showcase-trophy-sub">{{ trophy.description || "Featured campaign" }}</p>
      <div class="showcase-trophy-meta">
        <div class="tmeta-cell">
          <div class="tmeta-val">+{{ trophy.receive ?? 0 }}</div>
          <div class="tmeta-lbl">XP reward</div>
        </div>
        <div class="tmeta-cell">
          <div class="tmeta-val">{{ trophy.pursuing_count ?? 0 }}</div>
          <div class="tmeta-lbl">Pursuing</div>
        </div>
      </div>
      <button
        class="btn-pursuit"
        :class="{ pursuing: isPursuing }"
        type="button"
        :disabled="isPursuing"
        @click="onPursuit"
      >
        <span>{{ isPursuing ? "In your pursuits" : "Add to my pursuits" }}</span>
        <span>{{ isPursuing ? "✓" : "→" }}</span>
      </button>
    </div>

    <div v-else class="showcase-empty">
      <p>No featured trophy yet.</p>
    </div>
  </aside>
</template>

<script>
export default {
  name: "FeaturedTrophy",
  props: {
    trophy: { type: Object, default: null },
    isPursuing: { type: Boolean, default: false },
    subText: { type: String, default: "Live campaign" },
  },
  emits: ["pursuit"],
  computed: {
    trophyImage() {
      const img = this.trophy?.image;
      if (!img) return null;
      if (img === "placeholder.jpg") return null;
      if (img.startsWith("http") || img.startsWith("/")) return img;
      return `/storage/${img}`;
    },
  },
  methods: {
    onPursuit() {
      if (this.isPursuing) return;
      this.$emit("pursuit", this.trophy);
    },
  },
};
</script>

<template>
  <aside class="showcase-card">
    <div class="showcase-label">
      <div class="showcase-label-text">Signature trophy</div>
      <div v-if="totalCount" class="showcase-label-count">1 of {{ totalCount }}</div>
    </div>

    <div v-if="trophy" class="showcase-trophy">
      <div class="showcase-trophy-asset">
        <img v-if="trophyImage" :src="trophyImage" :alt="trophy.name" class="showcase-trophy-img" />
        <svg v-else width="140" height="140" viewBox="0 0 100 100">
          <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
          <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
          <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
          <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
          <path d="M42 48 L42 68 M58 48 L58 68 M42 58 L58 58" stroke="#000003" stroke-width="3" fill="none"/>
        </svg>
      </div>
      <h3 class="showcase-trophy-name">{{ trophy.name }}</h3>
      <p class="showcase-trophy-sub">Forged · {{ forgedDate }}</p>
      <div class="showcase-trophy-meta">
        <div class="tmeta-cell">
          <div class="tmeta-val">+{{ trophy.receive ?? 0 }}</div>
          <div class="tmeta-lbl">XP</div>
        </div>
        <div class="tmeta-cell">
          <div class="tmeta-val">{{ seriesNumber }}</div>
          <div class="tmeta-lbl">Of series</div>
        </div>
      </div>
    </div>

    <div v-else class="showcase-empty">
      <p>No trophies forged yet.</p>
    </div>
  </aside>
</template>

<script>
export default {
  name: "SignatureTrophy",
  props: {
    trophy: { type: Object, default: null },
    totalCount: { type: Number, default: 0 },
  },
  computed: {
    trophyImage() {
      if (!this.trophy?.image) return null;
      const img = this.trophy.image;
      if (img === "placeholder.jpg") return null;
      if (img.startsWith("http") || img.startsWith("/")) return img;
      return `/storage/trophies/${img}`;
    },
    forgedDate() {
      const ts = this.trophy?.pivot?.created_at || this.trophy?.created_at;
      if (!ts) return "Recently";
      try {
        return new Date(ts).toLocaleDateString("en-US", { month: "long", year: "numeric" });
      } catch (e) { return "Recently"; }
    },
    seriesNumber() {
      const id = this.trophy?.pivot?.token_id;
      if (id == null) return "—";
      return String(id).padStart(3, "0");
    },
  },
};
</script>

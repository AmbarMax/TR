<template>
  <div class="trophies-featured">
    <div
      v-for="trophy in trophies"
      :key="trophy.id"
      class="trophy-piece"
    >
      <div class="trophy-piece-asset">
        <img v-if="trophyImage(trophy)" :src="trophyImage(trophy)" :alt="trophy.name" class="trophy-piece-img" />
        <svg v-else width="100" height="100" viewBox="0 0 100 100">
          <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
          <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
          <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
          <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
          <path d="M42 48 L42 68 M58 48 L58 68 M42 58 L58 58" stroke="#000003" stroke-width="3" fill="none"/>
        </svg>
      </div>
      <h3 class="trophy-piece-name">{{ trophy.name }}</h3>
      <p class="trophy-piece-desc">{{ trophy.description || "Forged trophy" }}</p>
      <div class="trophy-piece-footer">
        <span class="xp">+{{ trophy.receive ?? 0 }} XP</span>
        <span class="sep">·</span>
        <span>{{ forgedDate(trophy) }}</span>
      </div>
    </div>

    <div
      v-for="i in lockedSlotsToShow"
      :key="`locked-${i}`"
      class="trophy-piece locked"
    >
      <div class="trophy-piece-asset">
        <svg width="100" height="100" viewBox="0 0 100 100">
          <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#2a2c2e" stroke-width="2" stroke-dasharray="3 3"/>
          <text x="50" y="55" text-anchor="middle" font-family="VT323" font-size="36" fill="#5a5550">?</text>
        </svg>
      </div>
      <h3 class="trophy-piece-name locked-name">Locked slot</h3>
      <p class="trophy-piece-desc">Forge another trophy to unlock this showcase slot.</p>
      <div class="trophy-piece-footer">
        <span class="locked-text">Empty</span>
      </div>
    </div>

    <p v-if="!trophies.length && !lockedSlotsToShow" class="player-empty">
      No trophies forged yet.
    </p>
  </div>
</template>

<script>
export default {
  name: "ForgedTrophies",
  props: {
    trophies: { type: Array, default: () => [] },
  },
  computed: {
    lockedSlotsToShow() {
      const count = this.trophies.length;
      if (count === 0) return 3;
      if (count < 3) return 3 - count;
      return 0;
    },
  },
  methods: {
    trophyImage(trophy) {
      if (!trophy?.image) return null;
      const img = trophy.image;
      if (img === "placeholder.jpg") return null;
      if (img.startsWith("http") || img.startsWith("/")) return img;
      return `/storage/trophies/${img}`;
    },
    forgedDate(trophy) {
      const ts = trophy?.pivot?.created_at || trophy?.created_at;
      if (!ts) return "Recent";
      try {
        return new Date(ts).toLocaleDateString("en-US", { month: "short", year: "numeric" });
      } catch (e) { return "Recent"; }
    },
  },
};
</script>

<template>
  <div class="wall-grid">
    <div class="wall-col">
      <div class="wall-col-head">
        <span>Top conquerors</span>
        <span class="wall-col-sub">By trophies forged</span>
      </div>
      <ol v-if="top.length" class="wall-list">
        <li
          v-for="(c, idx) in top"
          :key="c.id || c.user_id"
          class="wall-row"
        >
          <span class="wall-rank">{{ rank(idx + 1) }}</span>
          <div class="wall-avatar" :class="accentClass(idx)">{{ initials(c) }}</div>
          <div class="wall-info">
            <div class="wall-name">{{ c.name || c.username }}</div>
            <div class="wall-meta">{{ c.conquest_count }} {{ c.conquest_count === 1 ? 'trophy' : 'trophies' }} forged</div>
          </div>
          <div class="wall-bar"><span :style="{ width: barWidth(c.conquest_count) + '%' }"></span></div>
        </li>
      </ol>
      <p v-else class="wall-empty">No conquerors yet. Be the first.</p>
    </div>

    <div class="wall-col">
      <div class="wall-col-head">
        <span>Latest conquests</span>
        <span class="wall-col-sub live-pulse">Live</span>
      </div>
      <ul v-if="latest.length" class="latest-list">
        <li v-for="(c, idx) in latest" :key="(c.user_id || idx) + '_' + (c.trophy_id || idx)" class="latest-row">
          <div class="latest-avatar" :class="accentClass(idx)">{{ initials(c) }}</div>
          <div class="latest-info">
            <div class="latest-line">
              <strong>{{ c.name || c.username }}</strong>
              conquered
              <span class="latest-trophy">{{ c.trophy_name }}</span>
            </div>
            <div class="latest-time">{{ relativeTime(c.created_at) }}</div>
          </div>
        </li>
      </ul>
      <p v-else class="wall-empty">No conquests yet.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: "WallOfConquerors",
  props: {
    top: { type: Array, default: () => [] },
    latest: { type: Array, default: () => [] },
  },
  computed: {
    maxConquest() {
      return Math.max(1, ...this.top.map(t => t.conquest_count || 0));
    },
  },
  methods: {
    rank(n) {
      return String(n).padStart(2, "0");
    },
    accentClass(idx) {
      return `accent-${(idx % 5) + 1}`;
    },
    initials(c) {
      const src = (c.name || c.username || "?").trim();
      const parts = src.split(/\s+/);
      if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
      }
      return src.slice(0, 2).toUpperCase();
    },
    barWidth(count) {
      const n = Number(count) || 0;
      return Math.round((n / this.maxConquest) * 100);
    },
    relativeTime(iso) {
      if (!iso) return "";
      const t = new Date(iso).getTime();
      const diffMs = Date.now() - t;
      if (diffMs < 0) return "just now";
      const min = Math.floor(diffMs / 60000);
      if (min < 1) return "just now";
      if (min < 60) return `${min}m ago`;
      const hrs = Math.floor(min / 60);
      if (hrs < 24) return `${hrs}h ago`;
      const days = Math.floor(hrs / 24);
      if (days < 7) return `${days}d ago`;
      return new Date(iso).toLocaleDateString();
    },
  },
};
</script>

<template>
  <div class="wof">

    <div class="wof-head">
      <div class="wof-title">Wall of Fame</div>
      <div class="wof-sub">Global leaderboard</div>
    </div>

    <!-- Section 1: Top by Trophies -->
    <div class="wof-section">
      <div class="wof-label wof-label--primary">Top by trophies</div>
      <div class="wof-list">
        <div
          v-for="(user, idx) in topByTrophies"
          :key="'t'+idx"
          class="wof-row"
          :class="{ 'wof-row--top': idx === 0 }"
        >
          <div class="wof-rank">{{ idx + 1 }}</div>
          <div class="wof-avatar">{{ user.initials }}</div>
          <div class="wof-info">
            <div class="wof-name">{{ user.username }}</div>
          </div>
          <div class="wof-val">{{ user.trophies }}</div>
        </div>
      </div>
    </div>

    <!-- Section 2: Top by Ambar -->
    <div class="wof-section">
      <div class="wof-label wof-label--accent">Top by Ambar</div>
      <div class="wof-list">
        <div
          v-for="(user, idx) in topByAmbar"
          :key="'a'+idx"
          class="wof-row"
          :class="{ 'wof-row--top-accent': idx === 0 }"
        >
          <div class="wof-rank">{{ idx + 1 }}</div>
          <div class="wof-avatar">{{ user.initials }}</div>
          <div class="wof-info">
            <div class="wof-name">{{ user.username }}</div>
          </div>
          <div class="wof-val wof-val--accent">{{ user.ambar }}</div>
        </div>
      </div>
    </div>

    <div class="wof-foot">Resets every Monday</div>
  </div>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
  data() {
    return {
      topByTrophies: [
        { initials: "KZ", username: "KazuXtreme", trophies: 4, ambar: 890 },
        { initials: "NV", username: "NovaPlays",   trophies: 3, ambar: 720 },
        { initials: "RL", username: "RuneLord",    trophies: 3, ambar: 650 },
        { initials: "MR", username: "MikeRush",    trophies: 2, ambar: 480 },
        { initials: "AX", username: "AxelForge",   trophies: 2, ambar: 410 },
      ],
      topByAmbar: [
        { initials: "NV", username: "NovaPlays",   ambar: 1240 },
        { initials: "KZ", username: "KazuXtreme",  ambar: 890  },
        { initials: "MR", username: "MikeRush",    ambar: 720  },
        { initials: "AX", username: "AxelForge",   ambar: 550  },
        { initials: "RL", username: "RuneLord",    ambar: 410  },
      ],
    };
  },
});
</script>

<style lang="scss" scoped>
.wof {
  background: rgba(14, 15, 17, 0.7);
  border: 1px solid rgba(42, 44, 46, 0.7);
  padding: 22px 22px 14px;
}

.wof-head {
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 1px solid rgba(42, 44, 46, 0.6);
}
.wof-title {
  font-family: var(--display);
  font-size: 24px;
  color: var(--text);
  line-height: 1;
  letter-spacing: 0.02em;
}
.wof-sub {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.22em;
  text-transform: uppercase;
  margin-top: 4px;
}

.wof-section { margin-bottom: 18px; }
.wof-section:last-of-type { margin-bottom: 10px; }

.wof-label {
  position: relative;
  font-size: 10px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.wof-label::after {
  content: '';
  flex: 1;
  height: 1px;
  background: linear-gradient(90deg, currentColor, transparent);
  opacity: 0.3;
}
.wof-label--primary { color: var(--primary); }
.wof-label--accent { color: var(--accent); }

.wof-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.wof-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 8px;
  transition: background 0.15s;
}
.wof-row:hover { background: rgba(255, 97, 0, 0.03); }

.wof-rank {
  font-family: var(--display);
  font-size: 22px;
  color: var(--text-dim);
  width: 24px;
  text-align: center;
  flex-shrink: 0;
  line-height: 1;
}
.wof-row--top .wof-rank {
  color: var(--primary);
  text-shadow: 0 0 10px var(--primary-glow);
}
.wof-row--top-accent .wof-rank {
  color: var(--accent);
  text-shadow: 0 0 10px var(--accent-glow);
}

.wof-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--surface-2);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--mono);
  font-size: 10px;
  color: var(--text-muted);
  flex-shrink: 0;
  letter-spacing: 0.04em;
}
.wof-row--top .wof-avatar {
  border-color: var(--primary);
  color: var(--primary);
  box-shadow: 0 0 8px rgba(255, 97, 0, 0.25);
}
.wof-row--top-accent .wof-avatar {
  border-color: var(--accent);
  color: var(--accent);
  box-shadow: 0 0 8px rgba(193, 245, 39, 0.25);
}

.wof-info {
  flex: 1;
  min-width: 0;
}
.wof-name {
  font-family: var(--mono);
  font-size: 11px;
  color: var(--text);
  letter-spacing: 0.03em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.wof-val {
  font-family: var(--display);
  font-size: 16px;
  color: var(--text-muted);
  line-height: 1;
  flex-shrink: 0;
}
.wof-row--top .wof-val { color: var(--primary); }
.wof-val--accent { color: var(--text-muted); }
.wof-row--top-accent .wof-val--accent { color: var(--accent); }

.wof-foot {
  padding-top: 12px;
  margin-top: 6px;
  border-top: 1px solid rgba(42, 44, 46, 0.6);
  font-family: var(--mono);
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  text-align: center;
}
</style>

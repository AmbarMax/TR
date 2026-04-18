<template>
  <div class="wof">
    <div class="wof-label">Wall of fame</div>
    <div class="wof-sub">Weekly top achievers</div>

    <div class="wof-entry" v-for="(user, idx) in topUsers" :key="idx">
      <div class="wof-rank" :class="{ gold: idx === 0 }">{{ idx + 1 }}</div>
      <div class="wof-avatar">{{ user.initials }}</div>
      <div class="wof-info">
        <div class="wof-name">{{ user.username }}</div>
        <div class="wof-stats">
          <span class="wof-trophies">{{ user.trophies }} troph.</span>
          <span class="wof-xp">{{ user.xp }} XP</span>
        </div>
      </div>
    </div>

    <div class="wof-divider"></div>
    <div class="wof-footer">Resets every Monday</div>
  </div>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
  data() {
    return {
      topUsers: [
        { initials: "KZ", username: "KazuXtreme", trophies: 4, xp: 890 },
        { initials: "NV", username: "NovaPlays", trophies: 3, xp: 720 },
        { initials: "RL", username: "RuneLord", trophies: 3, xp: 650 },
        { initials: "MR", username: "MikeRush", trophies: 2, xp: 480 },
        { initials: "AX", username: "AxelForge", trophies: 2, xp: 410 },
      ],
    };
  },
});
</script>

<style scoped>
.wof {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 14px;
  position: sticky;
  top: 20px;
}
.wof-label {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  color: #ff6100;
  letter-spacing: 0.12em;
  margin-bottom: 4px;
}
.wof-sub {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
  margin-bottom: 14px;
}
.wof-entry {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}
.wof-rank {
  font-family: 'Share Tech Mono', monospace;
  font-size: 18px;
  color: #2a2c2e;
  width: 20px;
  text-align: center;
}
.wof-rank.gold {
  color: #ff6100;
}
.wof-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px;
  color: #9a9590;
  flex-shrink: 0;
}
.wof-info {
  flex: 1;
  min-width: 0;
}
.wof-name {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: #feeddf;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.wof-stats {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
  display: flex;
  gap: 6px;
}
.wof-trophies {
  color: #ff6100;
}
.wof-xp {
  color: #c1f527;
}
.wof-divider {
  height: 1px;
  background: #2a2c2e;
  margin: 12px 0;
}
.wof-footer {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
  text-align: center;
}
</style>

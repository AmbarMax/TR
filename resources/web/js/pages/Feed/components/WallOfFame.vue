<template>
  <div class="wof">
    <!-- Header -->
    <div class="wof-head">
      <div class="wof-title">Wall of fame</div>
      <div class="wof-sub">Weekly top achievers</div>
    </div>

    <!-- Crown: #1 -->
    <div class="wof-crown">
      <div class="crown-rank">1</div>
      <div class="crown-av">{{ topUsers[0].initials }}</div>
      <div class="crown-info">
        <div class="crown-name">{{ topUsers[0].username }}</div>
        <div class="crown-stats">
          <span class="t">{{ topUsers[0].trophies }} troph.</span>
          <span class="a">{{ topUsers[0].ambar }} Ambar</span>
        </div>
      </div>
    </div>

    <!-- List: #2-5 -->
    <div class="wof-list">
      <div class="wof-entry" v-for="(user, idx) in topUsers.slice(1)" :key="idx">
        <div class="wof-rank">{{ idx + 2 }}</div>
        <div class="wof-av">{{ user.initials }}</div>
        <div class="wof-info">
          <div class="wof-name">{{ user.username }}</div>
          <div class="wof-stats">
            <span class="t">{{ user.trophies }} troph.</span>
            <span class="a">{{ user.ambar }} Ambar</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="wof-footer">Resets every Monday</div>
  </div>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
  data() {
    return {
      topUsers: [
        { initials: "KZ", username: "KazuXtreme", trophies: 4, ambar: 890 },
        { initials: "NV", username: "NovaPlays", trophies: 3, ambar: 720 },
        { initials: "RL", username: "RuneLord", trophies: 3, ambar: 650 },
        { initials: "MR", username: "MikeRush", trophies: 2, ambar: 480 },
        { initials: "AX", username: "AxelForge", trophies: 2, ambar: 410 },
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
  overflow: hidden;
  position: sticky;
  top: 20px;
}

/* Header */
.wof-head {
  padding: 16px 16px 14px;
  border-bottom: 1px solid #2a2c2e;
}
.wof-title {
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  text-transform: uppercase;
  color: #ff6100;
  letter-spacing: 0.14em;
  margin-bottom: 2px;
}
.wof-sub {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
}

/* Crown — #1 */
.wof-crown {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: rgba(255, 97, 0, 0.04);
  border-bottom: 1px solid #2a2c2e;
}
.crown-rank {
  font-family: 'Share Tech Mono', monospace;
  font-size: 24px;
  color: #ff6100;
  width: 24px;
  text-align: center;
  flex-shrink: 0;
}
.crown-av {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 97, 0, 0.12);
  border: 2px solid #ff6100;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  color: #ff6100;
  flex-shrink: 0;
}
.crown-info {
  flex: 1;
  min-width: 0;
}
.crown-name {
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  color: #feeddf;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 2px;
}
.crown-stats {
  display: flex;
  gap: 8px;
  font-family: 'Share Tech Mono', monospace;
}
.crown-stats .t {
  font-size: 11px;
  color: #ff6100;
}
.crown-stats .a {
  font-size: 11px;
  color: #c1f527;
}

/* List — #2-5 */
.wof-list {
  padding: 6px 0;
}
.wof-entry {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 16px;
}
.wof-rank {
  font-family: 'Share Tech Mono', monospace;
  font-size: 15px;
  color: #5a5550;
  width: 24px;
  text-align: center;
  flex-shrink: 0;
}
.wof-av {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #9a9590;
  flex-shrink: 0;
}
.wof-info {
  flex: 1;
  min-width: 0;
}
.wof-name {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  color: #feeddf;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 2px;
}
.wof-stats {
  display: flex;
  gap: 6px;
  font-family: 'Share Tech Mono', monospace;
}
.wof-stats .t {
  font-size: 10px;
  color: #ff6100;
}
.wof-stats .a {
  font-size: 10px;
  color: #c1f527;
}

/* Footer */
.wof-footer {
  padding: 10px 16px;
  border-top: 1px solid #2a2c2e;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
  text-align: center;
}
</style>

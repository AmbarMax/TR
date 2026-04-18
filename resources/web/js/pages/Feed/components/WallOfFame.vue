<template>
  <div class="wof">

    <!-- Header -->
    <div class="wof-header">
      <h2>Wall of Fame</h2>
    </div>

    <!-- Section 1: Top by Trophies -->
    <div class="wof-section">
      <div class="wof-section-label wof-section-label--orange">Top by trophies</div>

      <!-- Crown #1 -->
      <div class="wof-crown wof-crown--orange">
        <div class="crown-rank crown-rank--orange">1</div>
        <div class="crown-av crown-av--orange">{{ topByTrophies[0].initials }}</div>
        <div class="crown-info">
          <div class="crown-name">{{ topByTrophies[0].username }}</div>
          <div class="crown-stats">
            <span class="t">{{ topByTrophies[0].trophies }} troph.</span>
            <span class="a">{{ topByTrophies[0].ambar }} Ambar</span>
          </div>
        </div>
      </div>

      <!-- List #2-5 -->
      <div class="wof-list">
        <div class="wof-entry" v-for="(user, idx) in topByTrophies.slice(1)" :key="'t'+idx">
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
    </div>

    <!-- Divider -->
    <div class="wof-divider"></div>

    <!-- Section 2: Top by Ambar -->
    <div class="wof-section">
      <div class="wof-section-label wof-section-label--accent">Top by Ambar</div>

      <!-- Crown #1 -->
      <div class="wof-crown wof-crown--accent">
        <div class="crown-rank crown-rank--accent">1</div>
        <div class="crown-av crown-av--accent">{{ topByAmbar[0].initials }}</div>
        <div class="crown-info">
          <div class="crown-name">{{ topByAmbar[0].username }}</div>
          <div class="crown-stats">
            <span class="a">{{ topByAmbar[0].ambar }} Ambar</span>
          </div>
        </div>
      </div>

      <!-- List #2-5 -->
      <div class="wof-list">
        <div class="wof-entry" v-for="(user, idx) in topByAmbar.slice(1)" :key="'a'+idx">
          <div class="wof-rank">{{ idx + 2 }}</div>
          <div class="wof-av">{{ user.initials }}</div>
          <div class="wof-info">
            <div class="wof-name">{{ user.username }}</div>
            <div class="wof-stats">
              <span class="a">{{ user.ambar }} Ambar</span>
            </div>
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

<style scoped>
.wof {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  overflow: hidden;
  position: sticky;
  top: 84px;
  max-height: calc(100vh - 100px);
  overflow-y: auto;
  padding-right: 0;
  margin-right: 16px;
}

.wof-header {
  padding: 14px 16px 12px;
  border-bottom: 1px solid #2a2c2e;
}
.wof-header h2 {
  font-family: 'Share Tech Mono', monospace;
  font-size: 16px;
  font-weight: 400;
  color: #feeddf;
  margin: 0;
}

/* Section wrapper */
.wof-section {
  padding-bottom: 4px;
}

.wof-section-label {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  padding: 12px 16px 10px;
}
.wof-section-label--orange { color: #ff6100; }
.wof-section-label--accent { color: #c1f527; }

/* Crown */
.wof-crown {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid #2a2c2e;
}
.wof-crown--orange { background: rgba(255, 97, 0, 0.04); }
.wof-crown--accent { background: rgba(193, 245, 39, 0.04); }

.crown-rank {
  font-family: 'Share Tech Mono', monospace;
  font-size: 24px;
  width: 24px;
  text-align: center;
  flex-shrink: 0;
}
.crown-rank--orange { color: #ff6100; }
.crown-rank--accent { color: #c1f527; }

.crown-av {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  flex-shrink: 0;
  border: 2px solid;
}
.crown-av--orange {
  background: rgba(255, 97, 0, 0.12);
  border-color: #ff6100;
  color: #ff6100;
}
.crown-av--accent {
  background: rgba(193, 245, 39, 0.12);
  border-color: #c1f527;
  color: #c1f527;
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
.crown-stats .t { font-size: 11px; color: #ff6100; }
.crown-stats .a { font-size: 11px; color: #c1f527; }

/* List #2-5 */
.wof-list { padding: 4px 0; }

.wof-entry {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 16px;
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
  margin-bottom: 1px;
}
.wof-stats {
  display: flex;
  gap: 6px;
  font-family: 'Share Tech Mono', monospace;
}
.wof-stats .t { font-size: 10px; color: #ff6100; }
.wof-stats .a { font-size: 10px; color: #c1f527; }

/* Divider between sections */
.wof-divider {
  height: 1px;
  background: #2a2c2e;
  margin: 0;
}

/* Scrollbar */
.wof::-webkit-scrollbar { width: 4px; }
.wof::-webkit-scrollbar-thumb { background: #ff6100; border-radius: 4px; }

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

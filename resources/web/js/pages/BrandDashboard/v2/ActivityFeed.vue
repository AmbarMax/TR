<template>
  <section class="activity-section">
    <div class="activity-feed">
      <div class="activity-header">
        <span class="activity-header-title">◆ Activity feed</span>
        <span class="activity-header-meta"><span class="live-dot"></span>LIVE</span>
      </div>

      <div v-if="loading" class="state-msg">Loading activity…</div>

      <div v-else-if="error" class="state-msg state-error">
        <span>Failed to load activity feed.</span>
        <button class="retry-btn" @click="load(false)">Retry</button>
      </div>

      <div v-else-if="data && (!data.data || data.data.length === 0)" class="empty-state">
        <p class="empty-main">No activity yet.</p>
        <p class="empty-sub">Activity will appear as users interact with your trophies.</p>
      </div>

      <div v-else-if="data" class="activity-list">
        <div v-for="item in data.data" :key="item.id" class="activity-row">
          <span :class="['activity-icon', iconClass(item.type)]">{{ item.icon }}</span>
          <div>
            <div class="activity-text">
              <template v-for="(p, i) in formatEventParts(item)" :key="i">
                <span v-if="p.tag" :class="p.tag">{{ p.text }}</span>
                <template v-else>{{ p.text }}</template>
              </template>
            </div>
            <div class="activity-time">{{ item.human_time }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { fetchActivity } from '../../../services/brandAnalytics.js';

export default {
  name: 'ActivityFeed',
  data() {
    return {
      data: null,
      loading: true,
      error: null,
      refreshTimer: null,
    };
  },
  mounted() {
    this.load(false);
    this.refreshTimer = setInterval(() => this.load(true), 30000);
  },
  beforeUnmount() {
    if (this.refreshTimer) {
      clearInterval(this.refreshTimer);
      this.refreshTimer = null;
    }
  },
  methods: {
    async load(silent = false) {
      if (!silent) {
        this.loading = true;
        this.error = null;
      }
      try {
        const res = await fetchActivity(20);
        this.data = res.data;
      } catch (e) {
        if (!silent) {
          console.error('[ActivityFeed] load failed', e);
          this.error = e;
        } else {
          // Silent refresh failure: log only, keep current data displayed.
          console.error('[ActivityFeed] silent refresh failed', e);
        }
      } finally {
        if (!silent) this.loading = false;
      }
    },
    iconClass(type) {
      const map = {
        trophy_forged: 'forge',
        badge_granted: 'grant',
        pursuer_started: 'pursuit',
        cross_hall_hit: 'cross',
      };
      return map[type] || '';
    },
    formatEventParts(item) {
      const actor = item.actor?.username || 'someone';
      const target = item.target || {};
      switch (item.type) {
        case 'trophy_forged':
          return [
            { tag: 'strong', text: `@${actor}` },
            { text: ' forged ' },
            { tag: 'em', text: target.trophy_name || 'a trophy' },
          ];
        case 'badge_granted':
          return [
            { tag: 'strong', text: `@${actor}` },
            { text: ' earned ' },
            { tag: 'em', text: target.badge_name || 'a badge' },
            { text: target.platform ? ` (${target.platform})` : '' },
          ];
        case 'pursuer_started':
          return [
            { tag: 'strong', text: `@${actor}` },
            { text: ' started pursuing ' },
            { tag: 'em', text: target.trophy_name || 'a trophy' },
          ];
        case 'cross_hall_hit':
          return [
            { tag: 'strong', text: `@${actor}` },
            { text: ' also got a badge from ' },
            { tag: 'em', text: `@${target.other_brand || 'another brand'}` },
          ];
        default:
          return [{ tag: 'strong', text: `@${actor}` }, { text: ' interacted' }];
      }
    },
  },
};
</script>

<style scoped>
.activity-section {
  display: flex;
  flex-direction: column;
}

/* Feed container */
.activity-feed {
  border: 1px solid var(--line-strong, rgba(254, 237, 223, 0.15));
  background: var(--bg-elev, #0a0b0f);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.activity-header {
  padding: 10px 14px;
  border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--bg, #000003);
}
.activity-header-title {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--primary, #ff6100);
  letter-spacing: 2px;
  text-transform: uppercase;
}
.activity-header-meta {
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px;
  color: var(--accent, #c1f527);
  letter-spacing: 1.5px;
  display: flex;
  align-items: center;
}
.live-dot {
  display: inline-block;
  width: 6px;
  height: 6px;
  background: var(--accent, #c1f527);
  border-radius: 50%;
  margin-right: 6px;
  animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

/* List */
.activity-list {
  display: flex;
  flex-direction: column;
  padding: 6px 0;
  max-height: 600px;
  overflow-y: auto;
}
.activity-list::-webkit-scrollbar {
  width: 6px;
}
.activity-list::-webkit-scrollbar-track {
  background: var(--bg, #000003);
}
.activity-list::-webkit-scrollbar-thumb {
  background: var(--primary-line, rgba(255, 97, 0, 0.25));
}

/* Row */
.activity-row {
  display: grid;
  grid-template-columns: 22px 1fr;
  gap: 10px;
  padding: 10px 14px;
  border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}
.activity-row:last-child {
  border-bottom: none;
}

/* Icon — 4 type variants */
.activity-icon {
  width: 22px;
  height: 22px;
  border: 1px solid var(--primary-line, rgba(255, 97, 0, 0.25));
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'VT323', monospace;
  font-size: 14px;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
}
.activity-icon.forge {
  color: var(--accent, #c1f527);
  border-color: var(--accent, #c1f527);
}
.activity-icon.grant {
  color: var(--primary, #ff6100);
  border-color: var(--primary, #ff6100);
}
.activity-icon.pursuit {
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  border-color: var(--text-dim, rgba(254, 237, 223, 0.6));
}
.activity-icon.cross {
  color: var(--warn, #ffb800);
  border-color: var(--warn, #ffb800);
}

/* Text + time */
.activity-text {
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  line-height: 1.45;
}
.activity-text .strong {
  color: var(--accent, #c1f527);
  font-weight: normal;
}
.activity-text .em {
  color: var(--primary, #ff6100);
  font-style: normal;
}
.activity-time {
  font-family: 'VT323', monospace;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 1px;
  font-size: 12px;
  margin-top: 2px;
}

/* Empty state */
.empty-state {
  padding: 40px 20px;
  text-align: center;
}
.empty-main {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  letter-spacing: 0.5px;
  margin: 0 0 8px 0;
}
.empty-sub {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 0.5px;
  margin: 0;
}

/* Loading / error — same pattern as previous components */
.state-msg {
  padding: 40px;
  text-align: center;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  letter-spacing: 0.1em;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
}
.state-error {
  color: var(--danger, #ff3860);
}
.retry-btn {
  margin-left: 12px;
  padding: 4px 12px;
  font-family: inherit;
  font-size: inherit;
  letter-spacing: inherit;
  background: transparent;
  border: 1px solid currentColor;
  color: inherit;
  cursor: pointer;
  text-transform: uppercase;
}
.retry-btn:hover {
  background: rgba(255, 56, 96, 0.1);
}
</style>

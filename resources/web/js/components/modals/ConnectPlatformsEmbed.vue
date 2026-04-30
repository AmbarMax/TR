<template>
  <div class="cpe-list">
    <div
      v-for="p in platforms"
      :key="p.key"
      class="cpe-platform"
      :class="{ 'cpe-platform--connected': p.connected }"
    >
      <div class="cpe-platform-icon" v-html="p.icon"></div>
      <div class="cpe-platform-info">
        <div class="cpe-platform-name">{{ p.name }}</div>
        <div class="cpe-platform-status">
          {{ p.connected ? 'Connected' : 'Not connected' }}
        </div>
      </div>
      <button
        class="cpe-action"
        :class="{ 'cpe-action--connected': p.connected }"
        @click="handleConnect(p)"
        :disabled="p.connected"
      >
        {{ p.connected ? '✓' : 'Connect' }}
      </button>
    </div>
  </div>
</template>

<script>
import { PLATFORM_ICONS } from '../../constants/platform-icons.js';

export default {
  name: 'ConnectPlatformsEmbed',

  props: {
    connectedPlatforms: { type: Array, default: () => [] },
  },

  emits: ['connect'],

  computed: {
    platforms() {
      const order = ['steam', 'discord', 'riot', 'strava', 'overwolf'];
      return order.map((key) => ({
        key,
        name: PLATFORM_ICONS[key]?.name || key,
        icon: PLATFORM_ICONS[key]?.icon || '',
        connected: this.connectedPlatforms.includes(key),
      }));
    },
  },

  methods: {
    handleConnect(platform) {
      if (platform.connected) return;
      this.$emit('connect', platform.key);
    },
  },
};
</script>

<style scoped>
.cpe-list { display: flex; flex-direction: column; gap: 8px; }

.cpe-platform {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border: 1px solid var(--border, #2a2c2e);
  background: rgba(0, 0, 0, 0.2);
  transition: all 0.15s;
}
.cpe-platform:hover { background: rgba(255, 97, 0, 0.04); border-color: rgba(255, 97, 0, 0.3); }
.cpe-platform--connected { opacity: 0.7; }

.cpe-platform-icon {
  width: 28px; height: 28px;
  display: flex; align-items: center; justify-content: center;
  color: var(--text-muted);
  flex-shrink: 0;
}
.cpe-platform-icon :deep(svg) { width: 20px; height: 20px; }
.cpe-platform--connected .cpe-platform-icon { color: var(--primary); }

.cpe-platform-info { flex: 1; }
.cpe-platform-name { font-size: 14px; color: var(--text); }
.cpe-platform-status { font-size: 10px; color: var(--text-dim); letter-spacing: 0.1em; text-transform: uppercase; }
.cpe-platform--connected .cpe-platform-status { color: var(--accent, #c1f527); }

.cpe-action {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text);
  padding: 7px 14px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 11px;
  letter-spacing: 0.1em;
  cursor: pointer;
  text-transform: uppercase;
}
.cpe-action:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.cpe-action--connected {
  background: rgba(193, 245, 39, 0.1);
  border-color: var(--accent);
  color: var(--accent);
  cursor: default;
}
</style>

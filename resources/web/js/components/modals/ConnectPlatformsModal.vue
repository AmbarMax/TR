<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="open" class="cpm-overlay" @click.self="closeModal">
        <div class="cpm-modal" role="dialog" aria-labelledby="cpm-title">
          <button class="cpm-close" @click="closeModal" aria-label="Close">×</button>

          <header class="cpm-header">
            <h2 id="cpm-title" class="cpm-title">Connect platforms</h2>
            <p class="cpm-subtitle">Pick where you play. Achievements sync automatically.</p>
          </header>

          <div class="cpm-list">
            <div
              v-for="p in platforms"
              :key="p.key"
              class="cpm-platform"
              :class="{ 'cpm-platform--connected': p.connected }"
            >
              <div class="cpm-platform-icon" v-html="p.icon"></div>
              <div class="cpm-platform-info">
                <div class="cpm-platform-name">{{ p.name }}</div>
                <div class="cpm-platform-status">
                  {{ p.connected ? 'Connected' : 'Not connected' }}
                </div>
              </div>
              <button
                class="cpm-action"
                :class="{ 'cpm-action--connected': p.connected }"
                @click="handleConnect(p)"
                :disabled="p.connected"
              >
                {{ p.connected ? '✓' : 'Connect' }}
              </button>
            </div>
          </div>

          <footer class="cpm-footer">
            <button class="cpm-done" @click="closeModal">Done</button>
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { PLATFORM_ICONS } from '../../constants/platform-icons.js';

export default {
  name: 'ConnectPlatformsModal',

  props: {
    /** Controls visibility of the modal. Use v-model:open or :open + @close. */
    open: { type: Boolean, default: false },

    /** Connected platforms — array of platform keys, e.g. ['steam','discord'].
     *  Parent passes the user's connected platforms; the modal renders the
     *  appropriate state without fetching anything itself. */
    connectedPlatforms: { type: Array, default: () => [] },
  },

  emits: ['close', 'connect'],

  computed: {
    platforms() {
      // Order matters — user attention follows this order
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
    closeModal() {
      this.$emit('close');
    },

    handleConnect(platform) {
      if (platform.connected) return;

      // Tell parent so it can mark step done in onboarding context
      this.$emit('connect', platform.key);

      // OAuth redirect — same routes used elsewhere in the app
      const oauthRoutes = {
        steam:    '/login/steam',
        discord:  '/login/discord',
        riot:     '/api/riot/authorize',
        strava:   '/api/strava/authorize',
        overwolf: '/login/overwolf',
      };

      const url = oauthRoutes[platform.key];
      if (url) {
        window.location.href = url;
      }
    },
  },
};
</script>

<style scoped>
.cpm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.cpm-modal {
  background: var(--surface, #0e0f11);
  border: 1px solid var(--border, #2a2c2e);
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 0;
  position: relative;
  box-shadow: 0 0 60px rgba(255, 97, 0, 0.15);
}

.cpm-close {
  position: absolute;
  top: 12px;
  right: 12px;
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 28px;
  cursor: pointer;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}
.cpm-close:hover { color: var(--primary); }

.cpm-header {
  padding: 32px 28px 20px;
  border-bottom: 1px solid var(--border);
}
.cpm-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 28px;
  letter-spacing: 0.04em;
  color: var(--text);
  margin: 0 0 6px;
}
.cpm-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  margin: 0;
}

.cpm-list {
  padding: 12px 0;
}
.cpm-platform {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 28px;
  border-top: 1px solid transparent;
  border-bottom: 1px solid transparent;
  transition: background 0.15s;
}
.cpm-platform + .cpm-platform { border-top-color: var(--border); }
.cpm-platform:hover { background: rgba(255, 97, 0, 0.04); }
.cpm-platform--connected { opacity: 0.7; }

.cpm-platform-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  flex-shrink: 0;
}
.cpm-platform-icon :deep(svg) { width: 22px; height: 22px; }
.cpm-platform--connected .cpm-platform-icon { color: var(--primary); }

.cpm-platform-info {
  flex: 1;
  min-width: 0;
}
.cpm-platform-name {
  font-size: 14px;
  color: var(--text);
  font-weight: 500;
}
.cpm-platform-status {
  font-size: 11px;
  color: var(--text-dim);
  letter-spacing: 0.1em;
  text-transform: uppercase;
}
.cpm-platform--connected .cpm-platform-status { color: var(--accent, #c1f527); }

.cpm-action {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text);
  padding: 8px 16px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 12px;
  letter-spacing: 0.1em;
  cursor: pointer;
  transition: all 0.15s;
  text-transform: uppercase;
}
.cpm-action:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.cpm-action--connected {
  background: rgba(193, 245, 39, 0.1);
  border-color: var(--accent, #c1f527);
  color: var(--accent, #c1f527);
  cursor: default;
}

.cpm-footer {
  padding: 20px 28px 28px;
  display: flex;
  justify-content: flex-end;
  border-top: 1px solid var(--border);
}
.cpm-done {
  background: var(--primary);
  border: none;
  color: #000;
  padding: 10px 28px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 13px;
  letter-spacing: 0.1em;
  cursor: pointer;
  text-transform: uppercase;
}
.cpm-done:hover { background: #ff7e2e; }

.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.2s;
}
.modal-fade-enter-from, .modal-fade-leave-to {
  opacity: 0;
}
</style>

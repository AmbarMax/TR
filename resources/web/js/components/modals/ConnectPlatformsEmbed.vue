<template>
  <div class="cpe">
    <div class="cpe-bg" aria-hidden="true"></div>
    <div class="cpe-grid-bg" aria-hidden="true"></div>

    <div class="cpe-container">
      <div class="cpe-left">
        <div class="cpe-step-counter">
          <span class="cpe-step-num">02</span>
          <span class="cpe-step-of">/ 04</span>
        </div>

        <h2 class="cpe-title">
          Connect your<br>
          <span class="cpe-title-highlight">first platform</span>
        </h2>

        <p class="cpe-desc">
          TrophyRoom imports your achievements from where you actually play. Pick one to start—you can add more later.
        </p>

        <ul class="cpe-features">
          <li
            v-for="(feature, idx) in features"
            :key="`f-${idx}`"
            class="cpe-feature"
            :style="{ '--feature-i': idx }"
          >
            <span class="cpe-feature-bullet">✓</span>
            <span>{{ feature }}</span>
          </li>
        </ul>

        <div class="cpe-mascot">
          <div class="cpe-mascot-slot">
            <img :src="'/images/mascot-onboarding/trex_pointing.png'" alt="" class="cpe-mascot-img" />
          </div>
        </div>
      </div>

      <div class="cpe-right">
        <div class="cpe-platforms-list">
          <div
            v-for="(p, idx) in platforms"
            :key="p.key"
            class="cpe-platform-row"
            :class="{ 'cpe-platform-row--connected': p.connected }"
            :style="{ '--row-i': idx }"
            @click="handleConnect(p)"
          >
            <div class="cpe-platform-icon" v-html="p.icon"></div>
            <div class="cpe-platform-info">
              <div class="cpe-platform-name">{{ p.name }}</div>
              <div class="cpe-platform-status">
                {{ p.connected ? 'Connected' : 'Not connected' }}
              </div>
            </div>
            <button
              type="button"
              class="cpe-platform-action"
              :class="{ 'cpe-platform-action--connected': p.connected }"
              :disabled="p.connected"
              @click.stop="handleConnect(p)"
            >
              {{ p.connected ? '✓' : 'Connect' }}
            </button>
          </div>
        </div>

        <div class="cpe-footer">
          <span class="cpe-required">At least one platform recommended</span>
          <button type="button" class="cpe-skip" @click="$emit('skip')">Skip for now →</button>
        </div>
      </div>
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

  emits: ['connect', 'skip'],

  data() {
    return {
      features: [
        'Auto-sync, no manual entry',
        'Your data stays private',
        'Disconnect anytime',
      ],
    };
  },

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
.cpe {
  position: relative;
  width: 100%;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  color: var(--text, #feeddf);
  padding: 24px 0;
}

/* Backdrop layers (gradients + grid) — sit behind content */
.cpe-bg {
  position: absolute;
  inset: -40px -40px;
  z-index: 0;
  background:
    linear-gradient(180deg, transparent 0%, rgba(255,97,0,0.04) 50%, transparent 100%),
    radial-gradient(ellipse 1000px 400px at 30% 50%, rgba(77,208,225,0.06), transparent),
    radial-gradient(ellipse 800px 400px at 70% 70%, rgba(255,97,0,0.04), transparent);
  pointer-events: none;
}
.cpe-grid-bg {
  position: absolute;
  inset: -40px -40px;
  z-index: 0;
  background-image:
    linear-gradient(rgba(255,97,0,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,97,0,0.04) 1px, transparent 1px);
  background-size: 80px 80px;
  mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
  -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
  pointer-events: none;
}

.cpe-container {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: 80px;
  align-items: center;
  max-width: 1100px;
  margin: 0 auto;
}

/* LEFT — narrative */
.cpe-left { animation: cpe-in 0.8s both; }

.cpe-step-counter {
  font-size: 11px;
  letter-spacing: 0.3em;
  color: var(--primary, #ff6100);
  text-transform: uppercase;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.cpe-step-num {
  font-family: var(--display, 'VT323', monospace);
  font-size: 64px;
  line-height: 0.8;
  color: var(--primary, #ff6100);
  text-shadow: 0 0 20px rgba(255,97,0,0.5);
}
.cpe-step-of { color: var(--text-dim, #7a7570); }

.cpe-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 56px;
  line-height: 1;
  color: var(--text);
  margin: 24px 0;
}
.cpe-title-highlight {
  color: var(--primary, #ff6100);
  position: relative;
}
.cpe-title-highlight::after {
  content: '';
  position: absolute;
  left: 0; right: 0;
  bottom: -4px;
  height: 2px;
  background: var(--primary, #ff6100);
  box-shadow: 0 0 10px var(--primary, #ff6100);
}

.cpe-desc {
  font-size: 15px;
  color: var(--text-2, #b8b0a8);
  margin-bottom: 32px;
  max-width: 420px;
  line-height: 1.7;
}

.cpe-features {
  list-style: none;
  padding: 0;
  margin: 0 0 32px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.cpe-feature {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  font-size: 13px;
  color: var(--text-2, #b8b0a8);
  animation: cpe-feature-in 0.5s cubic-bezier(0.2,0.8,0.2,1) both;
  animation-delay: calc(0.3s + var(--feature-i, 0) * 0.1s);
}
.cpe-feature-bullet {
  flex-shrink: 0;
  width: 16px;
  height: 16px;
  background: rgba(193,245,39,0.1);
  border: 1px solid var(--accent, #c1f527);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  color: var(--accent, #c1f527);
  margin-top: 2px;
}

.cpe-mascot { margin-top: 24px; }
.cpe-mascot-slot {
  width: 120px;
  height: 120px;
  background:
    radial-gradient(circle at center, rgba(255,97,0,0.15), transparent 70%),
    repeating-linear-gradient(45deg, var(--surface-2, #14161a), var(--surface-2, #14161a) 4px, var(--surface, #0c0d0f) 4px, var(--surface, #0c0d0f) 8px);
  border: 1px dashed var(--border-2, #2a2e34);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.cpe-mascot-slot::before {
  content: '';
  position: absolute;
  inset: -8px;
  border: 1px solid rgba(255,97,0,0.2);
  pointer-events: none;
  animation: cpe-rotate-slow 30s linear infinite;
}
.cpe-mascot-img {
  width: 90%;
  height: 90%;
  object-fit: contain;
  image-rendering: pixelated;
}

/* RIGHT — list */
.cpe-right { animation: cpe-in 0.8s 0.2s both; }

.cpe-platforms-list {
  background: linear-gradient(180deg, rgba(20,22,26,0.7) 0%, rgba(12,13,15,0.9) 100%);
  border: 1px solid var(--border-2, #2a2e34);
  position: relative;
  padding: 8px;
}
.cpe-platforms-list::before {
  content: 'select.platform';
  position: absolute;
  top: -10px;
  left: 16px;
  font-size: 10px;
  letter-spacing: 0.2em;
  color: var(--primary, #ff6100);
  background: var(--bg, #07080a);
  padding: 2px 10px;
  text-transform: uppercase;
}

.cpe-platform-row {
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 18px 20px;
  border-bottom: 1px solid var(--border, #1f2226);
  position: relative;
  transition: all 0.2s;
  cursor: pointer;
  animation: cpe-row-in 0.5s cubic-bezier(0.2,0.8,0.2,1) both;
  animation-delay: calc(0.4s + var(--row-i, 0) * 0.1s);
}
.cpe-platform-row:last-child { border-bottom: none; }

.cpe-platform-row::before {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 2px;
  background: var(--primary, #ff6100);
  transform: scaleY(0);
  transition: transform 0.2s;
}
.cpe-platform-row:hover::before { transform: scaleY(1); }
.cpe-platform-row:hover {
  background: rgba(255,97,0,0.04);
  padding-left: 28px;
}

.cpe-platform-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--surface, #0c0d0f);
  border: 1px solid var(--border-2, #2a2e34);
  color: var(--text-2, #b8b0a8);
  flex-shrink: 0;
  transition: all 0.2s;
}
.cpe-platform-icon :deep(svg) {
  width: 22px;
  height: 22px;
}
.cpe-platform-row:hover .cpe-platform-icon {
  border-color: var(--primary, #ff6100);
  color: var(--primary, #ff6100);
}
.cpe-platform-row--connected .cpe-platform-icon {
  border-color: var(--accent, #c1f527);
  color: var(--accent, #c1f527);
  background: rgba(193,245,39,0.08);
}

.cpe-platform-info {
  flex: 1;
  min-width: 0;
}
.cpe-platform-name {
  font-size: 15px;
  color: var(--text);
  margin-bottom: 2px;
}
.cpe-platform-status {
  font-size: 10px;
  letter-spacing: 0.15em;
  color: var(--text-dim, #7a7570);
  text-transform: uppercase;
}
.cpe-platform-row--connected .cpe-platform-status {
  color: var(--accent, #c1f527);
}

.cpe-platform-action {
  background: transparent;
  border: 1px solid var(--border-2, #2a2e34);
  color: var(--text);
  padding: 8px 16px;
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.15s;
}
.cpe-platform-action:hover:not(:disabled) {
  background: var(--primary, #ff6100);
  color: #000;
  border-color: var(--primary, #ff6100);
}
.cpe-platform-action--connected {
  background: rgba(193,245,39,0.1);
  color: var(--accent, #c1f527);
  border-color: var(--accent, #c1f527);
  cursor: default;
}

/* Footer */
.cpe-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 24px;
  padding: 0 4px;
  animation: cpe-in 0.8s 0.9s both;
}
.cpe-required {
  font-size: 11px;
  letter-spacing: 0.15em;
  color: var(--text-dim, #7a7570);
  display: flex;
  align-items: center;
  gap: 8px;
  text-transform: uppercase;
}
.cpe-required::before {
  content: '⚠';
  color: var(--accent, #c1f527);
}
.cpe-skip {
  background: none;
  border: 1px solid var(--border, #1f2226);
  color: var(--text-dim, #7a7570);
  padding: 8px 14px;
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.15s;
}
.cpe-skip:hover {
  color: var(--text);
  border-color: var(--text-dim, #7a7570);
}

/* Animations */
@keyframes cpe-in {
  0% { opacity: 0; transform: translateY(20px) scale(0.97); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes cpe-feature-in {
  0% { opacity: 0; transform: translateX(-10px); }
  100% { opacity: 1; transform: translateX(0); }
}
@keyframes cpe-row-in {
  0% { opacity: 0; transform: translateX(20px); }
  100% { opacity: 1; transform: translateX(0); }
}
@keyframes cpe-rotate-slow { to { transform: rotate(360deg); } }

@media (max-width: 900px) {
  .cpe-container {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  .cpe-title { font-size: 40px; }
  .cpe-step-num { font-size: 48px; }
  .cpe-mascot { display: none; }
  .cpe-footer { flex-direction: column; gap: 12px; align-items: stretch; }
}
</style>

<template>
  <Teleport to="body">
    <Transition name="wtc-fade">
      <div v-if="open" class="wtc-overlay" @click.self="onClose">
        <div class="wtc-modal" :class="{ 'wtc-modal--revealed': revealed }">

          <div v-if="!revealed" class="wtc-pre">
            <div class="wtc-icon">🏆</div>
            <h2 class="wtc-title">Your first trophy is waiting</h2>
            <p class="wtc-subtitle">For taking the first step. Claim it to make it yours.</p>
            <button class="wtc-claim" :disabled="claiming" @click="claim">
              <span v-if="claiming">
                <span class="wtc-spinner"></span> Claiming...
              </span>
              <span v-else>Claim Welcome Trophy</span>
            </button>
          </div>

          <div v-else class="wtc-post">
            <div class="wtc-trophy-img">
              <img :src="trophyImage" :alt="trophyData.name" @error="onImgError" />
            </div>
            <h2 class="wtc-trophy-name">{{ trophyData.name }}</h2>
            <p class="wtc-trophy-desc">{{ trophyData.description }}</p>
            <div class="wtc-xp-badge">+{{ trophyData.receive }} XP</div>
            <button class="wtc-finish" @click="finish">See my Trophy Room →</button>
          </div>

          <!-- Confetti / sparkle effect overlay (CSS only) -->
          <div v-if="revealed" class="wtc-celebration">
            <div v-for="n in 20" :key="n" class="wtc-spark" :style="sparkStyle(n)"></div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import api from '../../api/api.js';

export default {
  name: 'WelcomeTrophyClaim',

  props: {
    open: { type: Boolean, default: false },
  },

  emits: ['close', 'finished'],

  data() {
    return {
      claiming: false,
      revealed: false,
      trophyData: { name: '', description: '', receive: 100, image: '' },
      imgErrored: false,
    };
  },

  computed: {
    trophyImage() {
      if (this.imgErrored || !this.trophyData.image) {
        return '/images/avatar/default-profile-img.png';
      }
      return `/storage/trophies/${this.trophyData.image}`;
    },
  },

  methods: {
    async claim() {
      this.claiming = true;
      try {
        const response = await api.post('/api/onboarding/claim-welcome-trophy');
        this.trophyData = response.data.trophy;

        setTimeout(() => {
          this.revealed = true;
        }, 600);
      } catch (e) {
        console.error('Claim failed:', e);
        this.claiming = false;
      }
    },

    finish() {
      this.$emit('finished');
      window.location.href = '/trophy-room';
    },

    onClose() {
      // Only allow close before claiming. After claiming, finish redirects.
      if (!this.revealed) {
        this.$emit('close');
      }
    },

    onImgError() {
      this.imgErrored = true;
    },

    sparkStyle(n) {
      const angle = (n / 20) * 360;
      const distance = 100 + Math.random() * 80;
      const delay = Math.random() * 0.5;
      const duration = 1.2 + Math.random() * 0.8;
      return {
        '--angle': `${angle}deg`,
        '--distance': `${distance}px`,
        '--delay': `${delay}s`,
        '--duration': `${duration}s`,
      };
    },
  },
};
</script>

<style scoped>
.wtc-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.92);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 20px;
}

.wtc-modal {
  background: var(--surface, #0e0f11);
  border: 2px solid var(--primary, #ff6100);
  width: 100%;
  max-width: 480px;
  padding: 48px 36px;
  text-align: center;
  position: relative;
  overflow: hidden;
  box-shadow: 0 0 80px rgba(255, 97, 0, 0.4);
}

.wtc-pre, .wtc-post { position: relative; z-index: 2; }

.wtc-icon {
  font-size: 56px;
  margin-bottom: 16px;
  filter: drop-shadow(0 0 20px rgba(255, 97, 0, 0.6));
}

.wtc-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 32px;
  color: var(--text);
  margin: 0 0 8px;
}

.wtc-subtitle {
  font-size: 14px;
  color: var(--text-muted);
  margin: 0 0 28px;
}

.wtc-claim {
  background: var(--primary);
  color: #000;
  border: none;
  padding: 16px 32px;
  font-family: var(--mono);
  font-size: 14px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
  font-weight: 600;
}
.wtc-claim:hover:not(:disabled) {
  background: #ff7e2e;
  box-shadow: 0 0 20px rgba(255, 97, 0, 0.6);
}
.wtc-claim:disabled { opacity: 0.6; cursor: not-allowed; }

.wtc-spinner {
  display: inline-block;
  width: 12px;
  height: 12px;
  border: 2px solid rgba(0,0,0,0.3);
  border-top-color: #000;
  border-radius: 50%;
  animation: wtc-spin 0.8s linear infinite;
}
@keyframes wtc-spin { to { transform: rotate(360deg); } }

.wtc-trophy-img {
  width: 120px;
  height: 120px;
  margin: 0 auto 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.wtc-trophy-img img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  animation: wtc-bounce 1s ease-out;
}
@keyframes wtc-bounce {
  0% { transform: scale(0.3); opacity: 0; }
  60% { transform: scale(1.15); }
  100% { transform: scale(1); opacity: 1; }
}

.wtc-trophy-name {
  font-family: var(--display);
  font-size: 28px;
  color: var(--accent, #c1f527);
  margin: 0 0 8px;
}

.wtc-trophy-desc {
  font-size: 13px;
  color: var(--text-muted);
  font-style: italic;
  margin: 0 0 20px;
}

.wtc-xp-badge {
  display: inline-block;
  padding: 6px 16px;
  background: rgba(193, 245, 39, 0.15);
  border: 1px solid var(--accent);
  color: var(--accent);
  font-size: 14px;
  letter-spacing: 0.1em;
  margin-bottom: 28px;
}

.wtc-finish {
  background: var(--primary);
  color: #000;
  border: none;
  padding: 12px 24px;
  font-family: var(--mono);
  font-size: 12px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
}
.wtc-finish:hover { background: #ff7e2e; }

.wtc-celebration {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
}
.wtc-spark {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 6px;
  height: 6px;
  background: var(--accent);
  border-radius: 50%;
  animation: wtc-spark var(--duration) ease-out var(--delay) forwards;
  opacity: 0;
}
@keyframes wtc-spark {
  0% { transform: translate(-50%, -50%) rotate(var(--angle)) translateY(0) scale(0); opacity: 1; }
  50% { opacity: 1; }
  100% { transform: translate(-50%, -50%) rotate(var(--angle)) translateY(calc(-1 * var(--distance))) scale(1.2); opacity: 0; }
}

.wtc-fade-enter-active, .wtc-fade-leave-active { transition: opacity 0.4s; }
.wtc-fade-enter-from, .wtc-fade-leave-to { opacity: 0; }
</style>

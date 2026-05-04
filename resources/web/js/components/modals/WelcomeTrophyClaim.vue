<template>
  <Teleport to="body">
    <Transition name="wtc-fade">
      <div v-if="open" class="wtc-overlay">
        <div class="wtc-bg" aria-hidden="true">
          <div class="wtc-glow"></div>
          <div class="wtc-rays"></div>
          <div class="wtc-spark wtc-spark--1"></div>
          <div class="wtc-spark wtc-spark--2"></div>
          <div class="wtc-spark wtc-spark--3"></div>
          <div class="wtc-spark wtc-spark--4"></div>
          <div class="wtc-spark wtc-spark--5"></div>
          <div class="wtc-spark wtc-spark--6"></div>
        </div>

        <div class="wtc-modal" role="dialog" aria-labelledby="wtc-title">

          <!-- PRE-CLAIM (small intro before user clicks "Claim") -->
          <div v-if="!revealed" class="wtc-pre">
            <div class="wtc-eyebrow">Welcome aboard</div>
            <h2 id="wtc-title" class="wtc-title">You made it.</h2>

            <div class="wtc-trophy">
              <div class="wtc-trophy-frame">
                <div class="wtc-trophy-icon wtc-trophy-icon--placeholder"></div>
              </div>
            </div>

            <p class="wtc-intro-desc">
              Your first trophy is waiting. Claim it to make it yours.
            </p>

            <button class="wtc-cta" :disabled="claiming" @click="claim">
              <span v-if="claiming"><span class="wtc-spinner"></span> Claiming…</span>
              <template v-else>
                <span>Claim Welcome Trophy</span>
                <span class="wtc-cta-arrow">→</span>
              </template>
            </button>
          </div>

          <!-- POST-CLAIM (full reveal — matches mockup Scene 06) -->
          <div v-else class="wtc-post">
            <div class="wtc-eyebrow">Welcome aboard</div>
            <h2 class="wtc-title">You made it.</h2>

            <div class="wtc-trophy wtc-trophy--revealed">
              <div class="wtc-trophy-frame">
                <div
                  class="wtc-trophy-icon"
                  :style="{ backgroundImage: `url('${trophyImageUrl}')` }"
                ></div>
              </div>
            </div>

            <h3 class="wtc-trophy-name">{{ trophyData.name || 'Welcome Trophy' }}</h3>
            <p class="wtc-trophy-desc">
              {{ trophyData.description || 'For taking the first step. Your hall is now alive.' }}
            </p>

            <div class="wtc-rewards">
              <div class="wtc-reward">
                <span class="wtc-reward-num">+{{ trophyData.receive || 100 }}</span>
                <span>XP</span>
              </div>
              <div class="wtc-reward">
                <span class="wtc-reward-num">01</span>
                <span>Trophy</span>
              </div>
            </div>

            <div class="wtc-mascot-celebration">
              <div class="wtc-mascot-slot">
                <img :src="'/images/mascot-onboarding/trex_victory.png'" alt="" class="wtc-mascot-img" />
              </div>
            </div>

            <button class="wtc-cta" @click="finish">
              <span>Enter Trophy Room</span>
              <span class="wtc-cta-arrow">→</span>
            </button>
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
    /**
     * Trophy artwork resolution order:
     *   1. backend-supplied image (under /storage/trophies/)
     *   2. local mockup asset (if backend image errors or is missing)
     */
    trophyImageUrl() {
      if (!this.imgErrored && this.trophyData.image) {
        return `/storage/trophies/${this.trophyData.image}`;
      }
      return '/images/mascot-onboarding/trophy_onboarding.png';
    },
  },

  methods: {
    async claim() {
      this.claiming = true;
      try {
        const response = await api.post('/api/onboarding/claim-welcome-trophy');
        this.trophyData = response.data.trophy;

        // Pre-resolve the trophy image so the reveal doesn't flash a broken state
        await this.preloadTrophyImage();

        setTimeout(() => {
          this.revealed = true;
        }, 400);
      } catch (e) {
        console.error('Claim failed:', e);
        this.claiming = false;
      }
    },

    preloadTrophyImage() {
      return new Promise((resolve) => {
        if (!this.trophyData.image) {
          resolve();
          return;
        }
        const img = new Image();
        img.onload = () => resolve();
        img.onerror = () => {
          this.imgErrored = true;
          resolve();
        };
        img.src = `/storage/trophies/${this.trophyData.image}`;
      });
    },

    finish() {
      this.$emit('finished');
      window.location.href = '/trophy-room';
    },
  },
};
</script>

<style scoped>
.wtc-overlay {
  position: fixed;
  inset: 0;
  background: var(--bg-deep, #050507);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 20px;
  overflow: hidden;
  font-family: var(--mono, 'Share Tech Mono', monospace);
}

/* Background — glow + rays + sparks */
.wtc-bg { position: absolute; inset: 0; pointer-events: none; }
.wtc-glow {
  position: absolute;
  top: 50%; left: 50%;
  width: 800px;
  height: 800px;
  transform: translate(-50%, -50%);
  background: radial-gradient(circle, rgba(255,97,0,0.15) 0%, transparent 50%);
  filter: blur(40px);
  animation: wtc-glow-breathe 4s ease-in-out infinite;
}
.wtc-rays {
  position: absolute;
  top: 50%; left: 50%;
  width: 1200px;
  height: 1200px;
  transform: translate(-50%, -50%);
  background-image:
    conic-gradient(
      from 0deg,
      transparent 0deg,
      rgba(255,97,0,0.1) 1deg,
      transparent 5deg,
      transparent 30deg,
      rgba(255,97,0,0.08) 31deg,
      transparent 35deg,
      transparent 60deg,
      rgba(193,245,39,0.06) 61deg,
      transparent 65deg,
      transparent 90deg,
      rgba(255,97,0,0.1) 91deg,
      transparent 95deg
    );
  animation: wtc-rotate-rays 60s linear infinite;
  opacity: 0.7;
}
.wtc-spark {
  position: absolute;
  width: 4px;
  height: 4px;
  background: var(--accent, #c1f527);
  border-radius: 50%;
  box-shadow: 0 0 8px var(--accent, #c1f527);
}
.wtc-spark--1 { top: 20%; left: 15%;  animation: wtc-spark-float 3s   -0.3s infinite; }
.wtc-spark--2 { top: 30%; right: 12%; animation: wtc-spark-float 4s   -0.7s infinite; }
.wtc-spark--3 { bottom: 30%; left: 20%; animation: wtc-spark-float 3.5s -1.2s infinite; }
.wtc-spark--4 { bottom: 40%; right: 18%; animation: wtc-spark-float 4.5s -1.8s infinite; }
.wtc-spark--5 {
  top: 50%; right: 8%;
  animation: wtc-spark-float 3s -0.5s infinite;
  background: var(--primary, #ff6100);
  box-shadow: 0 0 8px var(--primary, #ff6100);
}
.wtc-spark--6 {
  top: 60%; left: 8%;
  animation: wtc-spark-float 4s -1s infinite;
  background: var(--primary, #ff6100);
  box-shadow: 0 0 8px var(--primary, #ff6100);
}

/* Modal */
.wtc-modal {
  position: relative;
  z-index: 10;
  max-width: 540px;
  width: 100%;
  text-align: center;
  padding: 64px 48px 48px;
  background: linear-gradient(180deg, rgba(20,22,26,0.97) 0%, rgba(8,9,11,0.99) 100%);
  border: 2px solid var(--primary, #ff6100);
  box-shadow:
    0 0 100px rgba(255,97,0,0.4),
    0 0 0 1px rgba(255,97,0,0.1),
    inset 0 0 60px rgba(255,97,0,0.05);
  animation: wtc-claim-in 1s cubic-bezier(0.2, 0.8, 0.2, 1) both;
  color: var(--text, #feeddf);
}

.wtc-eyebrow {
  font-size: 11px;
  letter-spacing: 0.35em;
  color: var(--accent, #c1f527);
  text-transform: uppercase;
  margin-bottom: 8px;
  display: inline-flex;
  align-items: center;
  gap: 12px;
}
.wtc-eyebrow::before, .wtc-eyebrow::after {
  content: '';
  width: 32px;
  height: 1px;
  background: var(--accent, #c1f527);
}

.wtc-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 48px;
  line-height: 1;
  color: var(--text);
  margin: 0 0 36px;
}

/* Trophy display */
.wtc-trophy {
  width: 160px;
  height: 160px;
  margin: 0 auto 24px;
  position: relative;
}
.wtc-trophy--revealed {
  animation: wtc-trophy-reveal 1s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
.wtc-trophy-frame {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at center, rgba(255,97,0,0.15) 0%, transparent 60%);
  border: 2px solid var(--primary, #ff6100);
  display: flex;
  align-items: center;
  justify-content: center;
}
.wtc-trophy-frame::before, .wtc-trophy-frame::after {
  content: '';
  position: absolute;
  width: 16px; height: 16px;
  border: 2px solid var(--primary, #ff6100);
}
.wtc-trophy-frame::before {
  top: -2px; left: -2px;
  border-right: none; border-bottom: none;
}
.wtc-trophy-frame::after {
  bottom: -2px; right: -2px;
  border-left: none; border-top: none;
}
.wtc-trophy-icon {
  width: 110px;
  height: 110px;
  background-size: contain;
  background-position: center;
  background-repeat: no-repeat;
  filter: drop-shadow(0 0 20px rgba(255,97,0,0.6));
  animation: wtc-trophy-float 3s ease-in-out infinite;
  image-rendering: pixelated;
  image-rendering: -moz-crisp-edges;
}
.wtc-trophy-icon--placeholder {
  /* Pre-claim: show silhouette of trophy asset at 40% opacity */
  background-image: url('/images/mascot-onboarding/trophy_onboarding.png');
  opacity: 0.4;
  filter: drop-shadow(0 0 20px rgba(255,97,0,0.3)) grayscale(0.5);
}

.wtc-intro-desc {
  font-size: 14px;
  color: var(--text-2, #b8b0a8);
  margin: 0 0 32px;
}

.wtc-trophy-name {
  font-family: var(--display, 'VT323', monospace);
  font-size: 32px;
  color: var(--text);
  margin: 0 0 8px;
}
.wtc-trophy-desc {
  font-size: 13px;
  color: var(--text-2, #b8b0a8);
  font-style: italic;
  margin: 0 0 24px;
}

.wtc-rewards {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-bottom: 32px;
}
.wtc-reward {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(193,245,39,0.08);
  border: 1px solid var(--accent, #c1f527);
  font-size: 12px;
  letter-spacing: 0.1em;
  color: var(--accent, #c1f527);
  text-transform: uppercase;
}
.wtc-reward-num {
  font-family: var(--display, 'VT323', monospace);
  font-size: 18px;
}

.wtc-mascot-celebration {
  margin: 0 auto 32px;
  display: flex;
  justify-content: center;
}
.wtc-mascot-slot {
  width: 100px;
  height: 100px;
  background:
    radial-gradient(circle at center, rgba(193,245,39,0.2), transparent 70%),
    repeating-linear-gradient(45deg, var(--surface-2, #14161a), var(--surface-2, #14161a) 4px, var(--surface, #0c0d0f) 4px, var(--surface, #0c0d0f) 8px);
  border: 1px dashed var(--border-2, #2a2e34);
  display: flex;
  align-items: center;
  justify-content: center;
}
.wtc-mascot-img {
  width: 88%;
  height: 88%;
  object-fit: contain;
  image-rendering: pixelated;
}

.wtc-cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  background: var(--primary, #ff6100);
  color: #000;
  border: none;
  padding: 18px 36px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.2s;
}
.wtc-cta::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,0.4) 50%, transparent 70%);
  transform: translateX(-100%);
  animation: wtc-shine 3s infinite;
}
.wtc-cta:hover:not(:disabled) {
  background: var(--primary-2, #ff7e2e);
  box-shadow: 0 0 40px rgba(255,97,0,0.6);
}
.wtc-cta:disabled { opacity: 0.6; cursor: not-allowed; }
.wtc-cta-arrow { transition: transform 0.2s; }
.wtc-cta:hover:not(:disabled) .wtc-cta-arrow { transform: translateX(4px); }

.wtc-spinner {
  display: inline-block;
  width: 12px;
  height: 12px;
  border: 2px solid rgba(0,0,0,0.3);
  border-top-color: #000;
  border-radius: 50%;
  animation: wtc-spin 0.8s linear infinite;
  vertical-align: middle;
  margin-right: 8px;
}

/* Animations */
@keyframes wtc-glow-breathe {
  0%, 100% { opacity: 0.6; transform: translate(-50%,-50%) scale(0.95); }
  50%      { opacity: 1;   transform: translate(-50%,-50%) scale(1.05); }
}
@keyframes wtc-rotate-rays {
  to { transform: translate(-50%,-50%) rotate(360deg); }
}
@keyframes wtc-spark-float {
  0%, 100% { opacity: 0; transform: translateY(20px); }
  50%      { opacity: 1; transform: translateY(-20px); }
}
@keyframes wtc-claim-in {
  0%   { opacity: 0; transform: scale(0.8) translateY(40px); }
  100% { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes wtc-trophy-reveal {
  0%   { opacity: 0; transform: scale(0.3) rotate(-180deg); }
  100% { opacity: 1; transform: scale(1) rotate(0); }
}
@keyframes wtc-trophy-float {
  0%, 100% { transform: translateY(0); }
  50%      { transform: translateY(-6px); }
}
@keyframes wtc-shine {
  0%, 100% { transform: translateX(-100%); }
  50%      { transform: translateX(100%); }
}
@keyframes wtc-spin { to { transform: rotate(360deg); } }

.wtc-fade-enter-active, .wtc-fade-leave-active { transition: opacity 0.4s; }
.wtc-fade-enter-from, .wtc-fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
  .wtc-glow, .wtc-rays, .wtc-spark, .wtc-trophy-icon { animation: none; }
}

@media (max-width: 580px) {
  .wtc-modal { padding: 48px 28px 32px; }
  .wtc-title { font-size: 36px; }
  .wtc-rays  { width: 800px; height: 800px; }
  .wtc-glow  { width: 500px; height: 500px; }
}
</style>

<template>
  <Teleport to="body">
    <Transition name="ow-fade">
      <div v-if="open" class="ow-overlay" :class="`ow-overlay--step-${currentStep}`">
        <!-- Backdrop drift pattern lives behind everything -->
        <div class="ow-backdrop" aria-hidden="true"></div>

        <!-- Top progress pills (shared across steps) -->
        <div class="ow-progress" aria-hidden="true">
          <span
            v-for="n in 4"
            :key="`p-${n}`"
            class="ow-progress-pill"
            :class="{
              'ow-progress-pill--active': currentStep === n,
              'ow-progress-pill--done': currentStep > n,
            }"
          ></span>
        </div>

        <!-- ============================================================
             STEP 1: Welcome
             ============================================================ -->
        <div v-if="currentStep === 1" class="ow-welcome">
          <div class="ow-mascot-slot">
            <img src="/images/mascot-onboarding/trex_welcoming.png" alt="" class="ow-mascot-img" />
          </div>

          <div class="ow-eyebrow">Step 01 of 04</div>

          <h1 id="ow-title" class="ow-welcome-title">
            <span class="ow-word">Welcome</span>
            <span class="ow-word">to</span>
            <span class="ow-word ow-word--accent">TrophyRoom</span>
          </h1>

          <p class="ow-welcome-subtitle">
            Your gaming achievements, one place. Built by players, for players who want their progress to mean something.
          </p>

          <div class="ow-welcome-meta">
            <span class="ow-meta-item">3 Steps</span>
            <span class="ow-meta-item">≈ 2 minutes</span>
            <span class="ow-meta-item">No tricks</span>
          </div>

          <div class="ow-welcome-cta">
            <button class="ow-primary" @click="advanceFromWelcome">
              <span>Let's Begin</span>
              <span class="ow-primary-arrow">→</span>
            </button>
            <button class="ow-skip-link" @click="explore">I'll explore first</button>
          </div>
        </div>

        <!-- ============================================================
             STEP 2: Connect platforms
             ============================================================ -->
        <div v-if="currentStep === 2" class="ow-connect">
          <ConnectPlatformsEmbed
            :connected-platforms="connectedPlatforms"
            @connect="handlePlatformClick"
            @skip="skipStep2"
          />
        </div>

        <!-- ============================================================
             STEP 3: Sync result
             ============================================================ -->
        <div v-if="currentStep === 3" class="ow-sync">
          <div class="ow-sync-bg" aria-hidden="true">
            <span class="ow-ring ow-ring--1"></span>
            <span class="ow-ring ow-ring--2"></span>
            <span class="ow-ring ow-ring--3"></span>
          </div>

          <div class="ow-sync-modal">
            <div class="ow-sync-icon">✓</div>

            <div v-if="lastConnectedPlatform !== 'No platform'" class="ow-sync-tag">
              <span class="ow-sync-tag-dot"></span>
              <span>{{ lastConnectedPlatform }} · Synced</span>
            </div>
            <div v-else class="ow-sync-tag ow-sync-tag--skipped">
              <span class="ow-sync-tag-dot"></span>
              <span>Skipped for now</span>
            </div>

            <h2 v-if="lastConnectedPlatform !== 'No platform'" class="ow-sync-headline">
              <span v-if="syncing">
                <span class="ow-spinner"></span> Importing…
              </span>
              <template v-else>
                <span class="ow-sync-num">{{ achievementCount }}</span>
                achievements imported
              </template>
            </h2>
            <h2 v-else class="ow-sync-headline">
              <span class="ow-sync-num-skipped">—</span>
              achievements imported
            </h2>

            <p class="ow-sync-subtitle">
              <template v-if="lastConnectedPlatform !== 'No platform'">
                Welcome to your hall. Your collection is ready to be customized.
              </template>
              <template v-else>
                You can connect a platform anytime from your profile settings.
              </template>
            </p>

            <div v-if="lastConnectedPlatform !== 'No platform'" class="ow-sync-stats">
              <div class="ow-sync-stat">
                <div class="ow-sync-stat-num">—</div>
                <div class="ow-sync-stat-label">Games</div>
              </div>
              <div class="ow-sync-stat">
                <div class="ow-sync-stat-num">{{ achievementCount }}</div>
                <div class="ow-sync-stat-label">Achievements</div>
              </div>
              <div class="ow-sync-stat">
                <div class="ow-sync-stat-num">—</div>
                <div class="ow-sync-stat-label">Hours</div>
              </div>
            </div>

            <div class="ow-sync-mascot-row">
              <div class="ow-sync-mascot-slot">
                <img src="/images/mascot-onboarding/trex_celebrating.png" alt="" class="ow-mascot-img" />
              </div>
              <div class="ow-sync-quote">
                <div class="ow-sync-quote-label">Trex says</div>
                <div class="ow-sync-quote-text">
                  "Look at all that. Feels good, doesn't it?"
                </div>
              </div>
            </div>

            <div v-if="!syncing" class="ow-sync-cta">
              <button class="ow-primary" @click="advanceFromSync">
                <span>Continue</span>
                <span class="ow-primary-arrow">→</span>
              </button>
            </div>
          </div>
        </div>

        <!-- ============================================================
             STEP 4: Personalize
             ============================================================ -->
        <div v-if="currentStep === 4" class="ow-personalize">
          <AvatarBannerPicker
            :initial-avatar="userAvatar"
            :initial-banner="userBanner"
            @saved="handlePersonalizeSaved"
            @error="handlePersonalizeError"
          />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import api from '../../api/api.js';
import ConnectPlatformsEmbed from './ConnectPlatformsEmbed.vue';
import AvatarBannerPicker from './AvatarBannerPicker.vue';

export default {
  name: 'OnboardingWizard',

  components: { ConnectPlatformsEmbed, AvatarBannerPicker },

  props: {
    open: { type: Boolean, default: false },
    /** User's current avatar (string path, may be default) */
    userAvatar: { type: String, default: null },
    /** User's current banner (string path, may be default) */
    userBanner: { type: String, default: null },
    /** Array of platform keys the user has connected, e.g. ['steam', 'discord'] */
    connectedPlatforms: { type: Array, default: () => [] },
  },

  emits: ['close', 'completed'],

  data() {
    return {
      currentStep: 1,
      lastConnectedPlatform: '',
      syncing: false,
      achievementCount: 0,
    };
  },

  async mounted() {
    // Priority 1: explicit URL marker after OAuth round-trip — that wins
    // over backend state because it represents a fresh user action.
    const urlParams = new URLSearchParams(window.location.search);
    const resumeStep = parseInt(urlParams.get('onboarding_step'));

    if (resumeStep === 3) {
      this.lastConnectedPlatform = urlParams.get('connected') || 'Platform';
      this.currentStep = 3;
      this.startSync();
      window.history.replaceState({}, '', window.location.pathname);
      return;
    }
    if (resumeStep > 1 && resumeStep <= 4) {
      this.currentStep = resumeStep;
      window.history.replaceState({}, '', window.location.pathname);
      return;
    }

    // Priority 2: read backend onboarding_steps and resume at first incomplete.
    try {
      const resp = await api.get('/api/onboarding/state');
      const steps = resp.data?.steps || {};
      const completed = !!resp.data?.completed;

      if (completed) {
        this.$emit('close');
        return;
      }

      if (!steps.welcome_seen) {
        this.currentStep = 1;
      } else if (!steps.platform_connected && !steps.platform_connected_skipped) {
        this.currentStep = 2;
      } else if (!steps.sync_seen) {
        this.currentStep = 3;
        // No fresh OAuth, so just show the post-sync state without re-syncing
        this.lastConnectedPlatform = steps.platform_connected ? 'Platform' : 'No platform';
        this.syncing = false;
        this.achievementCount = '?';
      } else if (!steps.hall_personalized) {
        this.currentStep = 4;
      } else {
        this.$emit('close');
      }
    } catch (e) {
      // Backend unreachable — fall back to STEP 1 (default).
      console.warn('OnboardingWizard: could not fetch state, defaulting to step 1', e);
    }
  },

  methods: {
    goToStep(n) {
      this.currentStep = n;
    },

    async advanceFromWelcome() {
      try {
        await api.post('/api/onboarding/step', { step: 'welcome_seen' });
      } catch (e) { /* silent — non-critical */ }
      this.currentStep = 2;
    },

    async advanceFromSync() {
      try {
        await api.post('/api/onboarding/step', { step: 'sync_seen' });
      } catch (e) { /* silent */ }
      this.currentStep = 4;
    },

    async skipStep2() {
      // Provisional skip — marks step as skipped in backend so wizard
      // doesn't loop back here, then advances to STEP 3.
      try {
        await api.post('/api/onboarding/step', { step: 'platform_connected_skipped' });
      } catch (e) {
        console.warn('Could not mark skip:', e);
      }

      this.lastConnectedPlatform = 'No platform';
      this.achievementCount = 0;
      this.syncing = false;
      this.currentStep = 3;
    },

    handlePlatformClick(platformKey) {
      const platformName = this.platformDisplayName(platformKey);
      const returnUrl = `/dashboard?onboarding_step=3&connected=${encodeURIComponent(platformName)}`;

      // Riot has no OAuth flow — no redirectToRiot exists. Skip its entry
      // until that integration ships.
      const oauthRoutes = {
        steam:    '/login/steam',
        discord:  '/login/discord',
        strava:   '/api/strava/authorize',
        overwolf: '/login/overwolf',
      };

      const base = oauthRoutes[platformKey];
      if (!base) {
        console.warn('OnboardingWizard: no OAuth route for', platformKey);
        return;
      }

      const sep = base.includes('?') ? '&' : '?';
      window.location.href = `${base}${sep}onboarding_return=${encodeURIComponent(returnUrl)}`;
    },

    platformDisplayName(key) {
      const names = {
        steam: 'Steam',
        discord: 'Discord',
        riot: 'Riot Games',
        strava: 'Strava',
        overwolf: 'Overwolf',
      };
      return names[key] || key;
    },

    async startSync() {
      this.syncing = true;

      try {
        await api.post('/api/onboarding/step', { step: 'platform_connected' });

        // Brief simulated sync delay; real sync runs server-side after OAuth.
        await new Promise(r => setTimeout(r, 2000));

        // Best-effort fetch — endpoint name varies, fall back gracefully
        try {
          const resp = await api.get('/api/profile');
          this.achievementCount = resp.data?.user?.data?.achievements_count
            ?? resp.data?.achievements_count
            ?? '?';
        } catch (e) {
          this.achievementCount = '?';
        }
      } finally {
        this.syncing = false;
      }
    },

    async handlePersonalizeSaved() {
      // Step 4 done. The picker already saved avatar/banner and marked
      // the step. Close the wizard and route the user to their VHall
      // with the highlights flag so the in-page tour can fire.
      this.$emit('close');

      let username = null;
      try {
        const resp = await api.get('/api/profile');
        username = resp.data?.user?.data?.username
          ?? resp.data?.username
          ?? null;
      } catch (e) {
        // fall through
      }

      if (username) {
        window.location.href = `/${username}?onboarding=highlights`;
      } else {
        window.location.href = '/dashboard';
      }
    },

    handlePersonalizeError(err) {
      console.error('Personalize step failed:', err);
    },

    async explore() {
      try {
        await api.post('/api/onboarding/skip');
      } catch (e) { /* continue regardless */ }
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
/* ============================================================
   SHELL — full-screen overlay, sticky (no backdrop close handler).
   The backdrop drift pattern lives behind everything.
   ============================================================ */
.ow-overlay {
  position: fixed;
  inset: 0;
  background: var(--bg-deep, #050507);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 80px 24px 32px;
  overflow-y: auto;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  color: var(--text, #feeddf);
}

.ow-backdrop {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    radial-gradient(ellipse 800px 600px at 50% 50%, rgba(255,97,0,0.08), transparent 70%),
    radial-gradient(circle 200px at 50% 50%, rgba(193,245,39,0.04), transparent 80%);
}
.ow-backdrop::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle, rgba(255,97,0,0.4) 1px, transparent 1px);
  background-size: 60px 60px;
  opacity: 0.15;
  animation: ow-drift 20s linear infinite;
}

/* ============================================================
   PROGRESS PILLS (top-fixed, shared across all steps)
   ============================================================ */
.ow-progress {
  position: fixed;
  top: 32px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 6px;
  z-index: 50;
}
.ow-progress-pill {
  width: 44px;
  height: 4px;
  background: var(--border-2, #2a2e34);
  position: relative;
  overflow: hidden;
  transition: background 0.3s;
}
.ow-progress-pill--active {
  background: var(--primary, #ff6100);
  box-shadow: 0 0 10px rgba(255,97,0,0.6);
}
.ow-progress-pill--active::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
  animation: ow-progress-shine 2s infinite;
}
.ow-progress-pill--done { background: var(--accent, #c1f527); }

/* ============================================================
   STEP 1 — WELCOME
   ============================================================ */
.ow-welcome {
  position: relative;
  z-index: 10;
  max-width: 680px;
  width: 100%;
  text-align: center;
  padding: 60px 48px;
  background: linear-gradient(180deg, rgba(20,22,26,0.95) 0%, rgba(12,13,15,0.98) 100%);
  border: 1px solid var(--border-2, #2a2e34);
  box-shadow:
    0 0 80px rgba(255,97,0,0.12),
    0 0 0 1px rgba(255,97,0,0.06);
  animation: ow-scene-in 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both;
}
.ow-welcome::before,
.ow-welcome::after {
  content: '';
  position: absolute;
  width: 32px;
  height: 32px;
  border: 2px solid var(--primary, #ff6100);
}
.ow-welcome::before {
  top: -1px; left: -1px;
  border-right: none;
  border-bottom: none;
}
.ow-welcome::after {
  bottom: -1px; right: -1px;
  border-left: none;
  border-top: none;
}

.ow-mascot-slot {
  width: 180px;
  height: 180px;
  margin: 0 auto 32px;
  position: relative;
  background:
    radial-gradient(circle at center, rgba(255,97,0,0.15), transparent 70%),
    repeating-linear-gradient(45deg, var(--surface-2, #14161a), var(--surface-2, #14161a) 4px, var(--surface, #0c0d0f) 4px, var(--surface, #0c0d0f) 8px);
  border: 1px dashed var(--border-2, #2a2e34);
  display: flex;
  align-items: center;
  justify-content: center;
}
.ow-mascot-slot::before {
  content: '';
  position: absolute;
  inset: -8px;
  border: 1px solid rgba(255,97,0,0.2);
  pointer-events: none;
  animation: ow-rotate-slow 30s linear infinite;
}
.ow-mascot-img {
  width: 88%;
  height: 88%;
  object-fit: contain;
  image-rendering: pixelated;
  image-rendering: -moz-crisp-edges;
}

.ow-eyebrow {
  font-size: 11px;
  letter-spacing: 0.3em;
  color: var(--accent, #c1f527);
  text-transform: uppercase;
  margin-bottom: 16px;
  display: inline-flex;
  align-items: center;
  gap: 12px;
}
.ow-eyebrow::before, .ow-eyebrow::after {
  content: '';
  width: 24px;
  height: 1px;
  background: var(--accent, #c1f527);
}

.ow-welcome-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 64px;
  line-height: 1;
  letter-spacing: 0.01em;
  color: var(--text);
  margin: 0 0 16px;
  text-shadow: 0 0 30px rgba(255,97,0,0.3);
}
.ow-word {
  display: inline-block;
  animation: ow-word-in 0.6s cubic-bezier(0.2,0.8,0.2,1) both;
}
.ow-word:nth-child(1) { animation-delay: 0.3s; }
.ow-word:nth-child(2) { animation-delay: 0.4s; }
.ow-word:nth-child(3) { animation-delay: 0.5s; }
.ow-word--accent { color: var(--primary, #ff6100); }

.ow-welcome-subtitle {
  font-size: 16px;
  color: var(--text-2, #b8b0a8);
  max-width: 460px;
  margin: 0 auto 40px;
  line-height: 1.7;
  animation: ow-scene-in 0.8s 0.6s both;
}

.ow-welcome-meta {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 32px;
  margin-bottom: 40px;
  font-size: 10px;
  letter-spacing: 0.2em;
  color: var(--text-dim, #7a7570);
  text-transform: uppercase;
  animation: ow-scene-in 0.8s 0.7s both;
}
.ow-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.ow-meta-item::before {
  content: '';
  width: 6px;
  height: 6px;
  background: var(--accent, #c1f527);
  border-radius: 50%;
}

.ow-welcome-cta { animation: ow-scene-in 0.8s 0.8s both; }

/* Primary CTA shared across welcome + sync steps */
.ow-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  background: var(--primary, #ff6100);
  color: #000;
  border: none;
  padding: 18px 40px;
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
.ow-primary::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
  transform: translateX(-100%);
  transition: transform 0.6s;
}
.ow-primary:hover {
  background: var(--primary-2, #ff7e2e);
  box-shadow: 0 0 30px rgba(255,97,0,0.5);
}
.ow-primary:hover::before { transform: translateX(100%); }
.ow-primary-arrow { transition: transform 0.2s; }
.ow-primary:hover .ow-primary-arrow { transform: translateX(4px); }

.ow-skip-link {
  display: block;
  margin-top: 20px;
  background: none;
  border: none;
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.15em;
  color: var(--text-dim, #7a7570);
  text-decoration: underline;
  text-underline-offset: 4px;
  cursor: pointer;
  text-transform: uppercase;
  transition: color 0.15s;
}
.ow-skip-link:hover { color: var(--text); }

/* ============================================================
   STEP 2 — CONNECT (the embed handles its own layout)
   ============================================================ */
.ow-connect {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 1180px;
  /* The embed renders the entire connect-container scene */
}

/* ============================================================
   STEP 3 — SYNC RESULT
   ============================================================ */
.ow-sync {
  position: relative;
  z-index: 10;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ow-sync-bg {
  position: absolute;
  inset: -200px;
  pointer-events: none;
  z-index: 0;
}
.ow-ring {
  position: absolute;
  top: 50%; left: 50%;
  border-radius: 50%;
  border: 1px solid rgba(255,97,0,0.2);
  transform: translate(-50%, -50%);
}
.ow-ring--1 { width: 600px;  height: 600px;  animation: ow-ring-pulse 3s ease-out infinite;       }
.ow-ring--2 { width: 800px;  height: 800px;  animation: ow-ring-pulse 3s ease-out infinite 1s;    }
.ow-ring--3 { width: 1000px; height: 1000px; animation: ow-ring-pulse 3s ease-out infinite 2s;    }

.ow-sync-modal {
  position: relative;
  z-index: 1;
  max-width: 720px;
  width: 100%;
  text-align: center;
  padding: 56px 48px;
  background: linear-gradient(180deg, rgba(20,22,26,0.95) 0%, rgba(12,13,15,0.98) 100%);
  border: 1px solid var(--accent, #c1f527);
  box-shadow:
    0 0 60px rgba(193,245,39,0.15),
    inset 0 0 0 1px rgba(193,245,39,0.05);
  animation: ow-scene-in 0.8s cubic-bezier(0.2,0.8,0.2,1) both;
}

.ow-sync-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 24px;
  border: 2px solid var(--accent, #c1f527);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 42px;
  color: var(--accent, #c1f527);
  position: relative;
  animation: ow-success-in 0.6s 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
.ow-sync-icon::before {
  content: '';
  position: absolute;
  inset: -8px;
  border: 1px solid rgba(193,245,39,0.3);
  animation: ow-rotate-slow 8s linear infinite;
}

.ow-sync-tag {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 6px 14px;
  background: rgba(193,245,39,0.08);
  border: 1px solid rgba(193,245,39,0.3);
  font-size: 11px;
  letter-spacing: 0.2em;
  color: var(--accent, #c1f527);
  text-transform: uppercase;
  margin-bottom: 24px;
  animation: ow-scene-in 0.6s 0.5s both;
}
.ow-sync-tag-dot {
  width: 6px; height: 6px;
  background: var(--accent, #c1f527);
  border-radius: 50%;
  box-shadow: 0 0 8px var(--accent, #c1f527);
  animation: ow-pulse 1.5s infinite;
}
.ow-sync-tag--skipped {
  background: rgba(122,117,112,0.08);
  border-color: rgba(122,117,112,0.3);
  color: var(--text-dim, #7a7570);
}
.ow-sync-tag--skipped .ow-sync-tag-dot {
  background: var(--text-dim, #7a7570);
  box-shadow: none;
  animation: none;
}

.ow-sync-headline {
  font-family: var(--display, 'VT323', monospace);
  font-size: 42px;
  line-height: 1.05;
  color: var(--text);
  margin: 0 0 12px;
  animation: ow-scene-in 0.6s 0.6s both;
}
.ow-sync-num {
  color: var(--primary, #ff6100);
  font-size: 64px;
  text-shadow: 0 0 20px rgba(255,97,0,0.4);
  margin-right: 8px;
}
.ow-sync-num-skipped {
  color: var(--text-dim, #7a7570);
  font-size: 64px;
  margin-right: 8px;
}

.ow-sync-subtitle {
  font-size: 14px;
  color: var(--text-2, #b8b0a8);
  margin: 0 0 36px;
  animation: ow-scene-in 0.6s 0.7s both;
}

.ow-sync-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 40px;
  animation: ow-scene-in 0.6s 0.8s both;
}
.ow-sync-stat {
  background: var(--surface, #0c0d0f);
  border: 1px solid var(--border-2, #2a2e34);
  padding: 16px 12px;
  position: relative;
}
.ow-sync-stat::before {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 8px; height: 8px;
  border-top: 1px solid var(--primary, #ff6100);
  border-left: 1px solid var(--primary, #ff6100);
}
.ow-sync-stat::after {
  content: '';
  position: absolute;
  bottom: 0; right: 0;
  width: 8px; height: 8px;
  border-bottom: 1px solid var(--primary, #ff6100);
  border-right: 1px solid var(--primary, #ff6100);
}
.ow-sync-stat-num {
  font-family: var(--display, 'VT323', monospace);
  font-size: 32px;
  color: var(--text);
  line-height: 1;
  margin-bottom: 4px;
}
.ow-sync-stat-label {
  font-size: 9px;
  letter-spacing: 0.2em;
  color: var(--text-dim, #7a7570);
  text-transform: uppercase;
}

.ow-sync-mascot-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  margin-bottom: 36px;
  animation: ow-scene-in 0.6s 0.85s both;
}
.ow-sync-mascot-slot {
  width: 100px;
  height: 100px;
  background:
    radial-gradient(circle at center, rgba(255,97,0,0.15), transparent 70%),
    repeating-linear-gradient(45deg, var(--surface-2, #14161a), var(--surface-2, #14161a) 4px, var(--surface, #0c0d0f) 4px, var(--surface, #0c0d0f) 8px);
  border: 1px dashed var(--border-2, #2a2e34);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.ow-sync-quote { text-align: left; max-width: 240px; }
.ow-sync-quote-label {
  font-size: 9px;
  letter-spacing: 0.2em;
  color: var(--text-dim, #7a7570);
  text-transform: uppercase;
  margin-bottom: 6px;
}
.ow-sync-quote-text {
  font-family: var(--display, 'VT323', monospace);
  font-size: 18px;
  color: var(--text-2, #b8b0a8);
  line-height: 1.3;
}

.ow-sync-cta { animation: ow-scene-in 0.6s 0.95s both; }

.ow-spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid var(--border-2, #2a2e34);
  border-top-color: var(--primary, #ff6100);
  border-radius: 50%;
  animation: ow-spin 0.8s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}

/* ============================================================
   STEP 4 — PERSONALIZE (delegates to AvatarBannerPicker)
   ============================================================ */
.ow-personalize {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 920px;
  animation: ow-scene-in 0.8s both;
}

/* ============================================================
   ANIMATIONS
   ============================================================ */
@keyframes ow-drift {
  0% { transform: translate(0,0); }
  100% { transform: translate(60px, 60px); }
}
@keyframes ow-scene-in {
  0%   { opacity: 0; transform: translateY(20px) scale(0.97); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes ow-word-in {
  0%   { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}
@keyframes ow-rotate-slow { to { transform: rotate(360deg); } }
@keyframes ow-progress-shine {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
@keyframes ow-ring-pulse {
  0%   { opacity: 0.5; transform: translate(-50%,-50%) scale(0.8); }
  100% { opacity: 0;   transform: translate(-50%,-50%) scale(1.1); }
}
@keyframes ow-success-in {
  0%   { opacity: 0; transform: scale(0.3) rotate(-180deg); }
  100% { opacity: 1; transform: scale(1) rotate(0deg); }
}
@keyframes ow-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%      { opacity: 0.6; transform: scale(1.2); }
}
@keyframes ow-spin { to { transform: rotate(360deg); } }

.ow-fade-enter-active, .ow-fade-leave-active { transition: opacity 0.3s; }
.ow-fade-enter-from, .ow-fade-leave-to { opacity: 0; }

@media (prefers-reduced-motion: reduce) {
  .ow-backdrop::after,
  .ow-mascot-slot::before,
  .ow-progress-pill--active::after,
  .ow-ring,
  .ow-sync-icon::before,
  .ow-sync-tag-dot,
  .ow-spinner,
  .ow-word { animation: none; }
}

@media (max-width: 720px) {
  .ow-overlay { padding: 80px 16px 24px; }
  .ow-welcome { padding: 40px 24px; }
  .ow-welcome-title { font-size: 44px; }
  .ow-mascot-slot { width: 140px; height: 140px; }
  .ow-welcome-meta { flex-direction: column; gap: 12px; }
  .ow-sync-modal { padding: 36px 24px; }
  .ow-sync-headline { font-size: 28px; }
  .ow-sync-num, .ow-sync-num-skipped { font-size: 44px; }
  .ow-sync-mascot-row { flex-direction: column; }
  .ow-sync-quote { text-align: center; }
}
</style>

<template>
  <Teleport to="body">
    <Transition name="ow-fade">
      <div v-if="open" class="ow-overlay">
        <div class="ow-modal" role="dialog" aria-labelledby="ow-title">

          <!-- Progress indicator -->
          <div class="ow-progress">
            <div
              v-for="n in 4"
              :key="`step-${n}`"
              class="ow-progress-dot"
              :class="{
                'ow-progress-dot--active': currentStep === n,
                'ow-progress-dot--done': currentStep > n,
              }"
            ></div>
          </div>

          <!-- STEP 1: Welcome screen -->
          <div v-if="currentStep === 1" class="ow-step">
            <h1 id="ow-title" class="ow-title">Welcome to TrophyRoom</h1>
            <p class="ow-subtitle">Your gaming achievements, one place. Let's set you up.</p>
            <p class="ow-hint">Three quick steps. About 2 minutes.</p>
            <div class="ow-actions">
              <button class="ow-primary" @click="advanceFromWelcome">Let's start →</button>
            </div>
          </div>

          <!-- STEP 2: Connect platforms -->
          <div v-if="currentStep === 2" class="ow-step">
            <h2 class="ow-step-title">Connect your first platform</h2>
            <p class="ow-step-desc">
              TrophyRoom imports your achievements automatically. Pick where you play most.
            </p>
            <div class="ow-platforms-embed">
              <ConnectPlatformsEmbed
                :connected-platforms="connectedPlatforms"
                @connect="handlePlatformClick"
              />
            </div>
            <div class="ow-step-footer">
              <p class="ow-required-note">⚠ At least one platform is recommended.</p>
              <button class="ow-skip-provisional" @click="skipStep2">
                Skip for now →
              </button>
            </div>
          </div>

          <!-- STEP 3: Sync result -->
          <div v-if="currentStep === 3" class="ow-step">
            <h2 v-if="lastConnectedPlatform !== 'No platform'" class="ow-step-title">
              <span class="ow-check">✓</span> {{ lastConnectedPlatform }} connected
            </h2>
            <h2 v-else class="ow-step-title">
              Skipped for now
            </h2>
            <p v-if="syncing" class="ow-step-desc">
              <span class="ow-spinner"></span>
              Importing your achievements...
            </p>
            <div v-else class="ow-sync-result">
              <p v-if="lastConnectedPlatform !== 'No platform'" class="ow-step-desc">
                <strong>{{ achievementCount }}</strong> achievements imported.
              </p>
              <p v-else class="ow-step-desc">
                You can connect a platform anytime from your profile settings.
              </p>
              <p class="ow-hint">You're ready for the next step.</p>
            </div>
            <div v-if="!syncing" class="ow-actions">
              <button class="ow-primary" @click="advanceFromSync">Continue →</button>
            </div>
          </div>

          <!-- STEP 4: Personalize -->
          <div v-if="currentStep === 4" class="ow-step">
            <h2 class="ow-step-title">Make your hall yours</h2>
            <p class="ow-step-desc">Pick a look. You can change it anytime from your Profile.</p>
            <AvatarBannerPicker
              :initial-avatar="userAvatar"
              :initial-banner="userBanner"
              @saved="handlePersonalizeSaved"
              @error="handlePersonalizeError"
            />
          </div>

          <!-- Footer skip option (only on the welcome step) -->
          <div v-if="currentStep === 1" class="ow-footer">
            <button class="ow-skip-link" @click="explore">I'll explore first</button>
          </div>
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
      // Mark welcome_seen so resume logic skips this step on reopen.
      try {
        await api.post('/api/onboarding/step', { step: 'welcome_seen' });
      } catch (e) { /* silent — non-critical */ }
      this.currentStep = 2;
    },

    async advanceFromSync() {
      // Mark sync_seen so STEP 3 isn't shown again on resume.
      try {
        await api.post('/api/onboarding/step', { step: 'sync_seen' });
      } catch (e) { /* silent */ }
      this.currentStep = 4;
    },

    async skipStep2() {
      // Provisional skip — marks step as skipped in backend so wizard
      // doesn't loop back here, then advances to STEP 3 (sync result placeholder).
      // Proper fix coming: OAuth callbacks should preserve wizard return URL.
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
      // Append the onboarding_return query param so the OAuth callback
      // can redirect us back to the wizard's STEP 3 instead of the default
      // post-auth landing (which is /dashboard or /trophy-room depending
      // on the controller). Each backend controller stashes this in the
      // Laravel session before OAuth and pulls it on callback.
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
.ow-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.ow-modal {
  background: var(--surface, #0e0f11);
  border: 1px solid var(--border, #2a2c2e);
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 40px 36px 32px;
  box-shadow: 0 0 80px rgba(255, 97, 0, 0.2);
  position: relative;
}

.ow-progress {
  display: flex;
  gap: 8px;
  justify-content: center;
  margin-bottom: 28px;
}
.ow-progress-dot {
  width: 32px;
  height: 4px;
  background: var(--border, #2a2c2e);
  transition: all 0.3s;
}
.ow-progress-dot--active {
  background: var(--primary, #ff6100);
  box-shadow: 0 0 8px rgba(255, 97, 0, 0.6);
}
.ow-progress-dot--done {
  background: var(--accent, #c1f527);
}

.ow-step {
  text-align: left;
}

.ow-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 36px;
  letter-spacing: 0.02em;
  color: var(--text, #feeddf);
  margin: 0 0 12px;
  text-align: center;
}

.ow-subtitle {
  font-size: 15px;
  color: var(--text-muted, #b8b0a8);
  margin: 0 0 24px;
  text-align: center;
}

.ow-step-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 26px;
  color: var(--text);
  margin: 0 0 8px;
}

.ow-step-desc {
  font-size: 14px;
  color: var(--text-muted);
  margin: 0 0 20px;
  line-height: 1.6;
}

.ow-hint {
  font-size: 11px;
  letter-spacing: 0.15em;
  color: var(--text-dim, #7a7570);
  text-transform: uppercase;
  text-align: center;
  margin: 0 0 24px;
}

.ow-required-note {
  font-size: 11px;
  color: var(--text-dim);
  margin-top: 16px;
  text-align: right;
}

.ow-step-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
}
.ow-step-footer .ow-required-note {
  margin: 0;
  text-align: left;
}
.ow-skip-provisional {
  background: none;
  border: 1px solid var(--border, #2a2c2e);
  color: var(--text-dim, #7a7570);
  padding: 6px 14px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 11px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.15s;
}
.ow-skip-provisional:hover {
  border-color: var(--text-muted);
  color: var(--text);
}

.ow-check {
  color: var(--accent, #c1f527);
  margin-right: 6px;
}

.ow-spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: ow-spin 0.8s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}
@keyframes ow-spin { to { transform: rotate(360deg); } }

.ow-platforms-embed {
  margin: 8px 0 0;
}

.ow-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 24px;
}

.ow-primary {
  background: var(--primary, #ff6100);
  border: none;
  color: #000;
  padding: 12px 28px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 13px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
}
.ow-primary:hover { background: #ff7e2e; }

.ow-footer {
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid var(--border);
  text-align: center;
}

.ow-skip-link {
  background: none;
  border: none;
  color: var(--text-dim);
  font-size: 12px;
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 3px;
}
.ow-skip-link:hover { color: var(--primary); }

.ow-fade-enter-active, .ow-fade-leave-active {
  transition: opacity 0.3s;
}
.ow-fade-enter-from, .ow-fade-leave-to {
  opacity: 0;
}
</style>

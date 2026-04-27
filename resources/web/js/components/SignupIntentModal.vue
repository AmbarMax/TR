<template>
  <transition name="signup-intent-fade">
    <div v-if="show" class="signup-intent-modal" :style="modalStyle" @click.self="onOverlayClick">
      <div class="sim-card" role="dialog" aria-modal="true">
        <button class="sim-close" type="button" aria-label="Close" @click="onClose">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>

        <div class="sim-icon">
          <svg v-if="intent === 'follow'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 12V8a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v8a4 4 0 0 0 4 4h4"/>
            <line x1="16" y1="16" x2="22" y2="16"/>
            <line x1="19" y1="13" x2="19" y2="19"/>
          </svg>
          <svg v-else width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12,2 14.5,8 21,8.5 16,13 17.5,20 12,16.5 6.5,20 8,13 3,8.5 9.5,8"/>
          </svg>
        </div>

        <h2 class="sim-title">{{ headline }}</h2>
        <p class="sim-body">Join thousands of players forging trophies across platforms.</p>

        <div class="sim-actions">
          <button class="sim-btn sim-btn-primary" type="button" @click="proceed('signup')">
            <span>Sign up free</span>
            <span aria-hidden="true">→</span>
          </button>
          <button class="sim-btn sim-btn-secondary" type="button" @click="proceed('login')">
            I already have an account · Log in
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import { savePendingIntent } from "../services/pending-intent.js";

export default {
  name: "SignupIntentModal",
  props: {
    show: { type: Boolean, default: false },
    intent: { type: String, default: null },
    payload: { type: Object, default: () => ({}) },
    hallContext: { type: Object, default: () => ({}) },
  },
  emits: ["close", "proceed"],
  computed: {
    brandName() {
      return this.hallContext?.name || this.hallContext?.username || "this Hall";
    },
    headline() {
      if (this.intent === "follow") {
        return `Sign up to follow ${this.brandName}'s Hall`;
      }
      if (this.intent === "pursuit") {
        return `Sign up to start pursuing trophies from ${this.brandName}`;
      }
      return "Sign up to continue";
    },
    accentColor() {
      return this.hallContext?.accent_color || "#ff6100";
    },
    modalStyle() {
      return {
        "--sim-accent": this.accentColor,
        "--sim-accent-glow": this.hexToGlow(this.accentColor),
      };
    },
  },
  watch: {
    show(open) {
      if (typeof document !== "undefined") {
        document.body.style.overflow = open ? "hidden" : "";
      }
      if (open) {
        this.$nextTick(() => {
          window.addEventListener("keydown", this.handleKeydown);
        });
      } else {
        window.removeEventListener("keydown", this.handleKeydown);
      }
    },
  },
  beforeUnmount() {
    window.removeEventListener("keydown", this.handleKeydown);
    if (typeof document !== "undefined") document.body.style.overflow = "";
  },
  methods: {
    hexToGlow(hex) {
      const m = /^#?([0-9a-f]{6})$/i.exec(hex || "");
      if (!m) return "rgba(255,97,0,0.45)";
      const r = parseInt(m[1].slice(0, 2), 16);
      const g = parseInt(m[1].slice(2, 4), 16);
      const b = parseInt(m[1].slice(4, 6), 16);
      return `rgba(${r},${g},${b},0.45)`;
    },
    onClose() {
      this.$emit("close");
    },
    onOverlayClick() {
      this.$emit("close");
    },
    handleKeydown(e) {
      if (e.key === "Escape") this.onClose();
    },
    proceed(target) {
      if (this.intent && this.payload) {
        savePendingIntent({ type: this.intent, payload: this.payload });
      }
      this.$emit("proceed", { target });
      this.$router.push(target === "login" ? "/login" : "/sign-up");
    },
  },
};
</script>

<style>
/* All selectors prefixed with .signup-intent-modal so styles do not leak. */
.signup-intent-modal {
  --sim-bg: #000003;
  --sim-surface: #0e0f11;
  --sim-border: #2a2c2e;
  --sim-text: #feeddf;
  --sim-text-muted: #9a9590;
  --sim-text-dim: #5a5550;
  --sim-mono: 'Share Tech Mono', monospace;
  --sim-display: 'VT323', monospace;
  --sim-accent: #ff6100;
  --sim-accent-glow: rgba(255, 97, 0, 0.45);

  position: fixed;
  inset: 0;
  z-index: 1200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(0, 0, 3, 0.78);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  font-family: var(--sim-mono);
  color: var(--sim-text);
}
.signup-intent-modal *,
.signup-intent-modal *::before,
.signup-intent-modal *::after { box-sizing: border-box; margin: 0; padding: 0; }

.signup-intent-modal .sim-card {
  position: relative;
  width: 100%;
  max-width: 440px;
  background: linear-gradient(135deg, rgba(193, 245, 39, 0.04) 0%, transparent 50%), var(--sim-surface);
  border: 1px solid var(--sim-border);
  padding: 36px 32px 28px;
  box-shadow:
    0 0 0 1px var(--sim-accent-glow),
    inset 0 0 80px rgba(0, 0, 0, 0.4),
    0 30px 80px rgba(0, 0, 0, 0.6);
}
.signup-intent-modal .sim-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; height: 2px;
  background: var(--sim-accent);
  box-shadow: 0 0 12px var(--sim-accent-glow);
}

.signup-intent-modal .sim-close {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--sim-text-muted);
  background: transparent;
  border: 1px solid transparent;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}
.signup-intent-modal .sim-close:hover {
  color: var(--sim-text);
  border-color: var(--sim-border);
}

.signup-intent-modal .sim-icon {
  width: 56px;
  height: 56px;
  margin-bottom: 18px;
  border: 1px solid var(--sim-accent);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--sim-accent);
  box-shadow: 0 0 18px var(--sim-accent-glow), inset 0 0 18px rgba(0, 0, 0, 0.4);
}
.signup-intent-modal .sim-title {
  font-family: var(--sim-display);
  font-size: 32px;
  line-height: 1.05;
  letter-spacing: 0.015em;
  color: var(--sim-text);
  margin-bottom: 10px;
  text-shadow: 0 0 24px var(--sim-accent-glow);
}
.signup-intent-modal .sim-body {
  font-size: 13px;
  color: var(--sim-text-muted);
  line-height: 1.55;
  letter-spacing: 0.02em;
  margin-bottom: 28px;
}

.signup-intent-modal .sim-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.signup-intent-modal .sim-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 18px;
  font-family: var(--sim-mono);
  font-size: 11px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  border: 1px solid var(--sim-border);
  background: transparent;
  color: var(--sim-text);
  cursor: pointer;
  transition: all 0.18s;
}
.signup-intent-modal .sim-btn-primary {
  background: var(--sim-accent);
  color: var(--sim-bg);
  border-color: var(--sim-accent);
  box-shadow: 0 0 22px var(--sim-accent-glow);
}
.signup-intent-modal .sim-btn-primary:hover {
  background: transparent;
  color: var(--sim-accent);
  box-shadow: 0 0 28px var(--sim-accent-glow), inset 0 0 18px rgba(255, 255, 255, 0.05);
}
.signup-intent-modal .sim-btn-secondary {
  font-size: 10px;
  letter-spacing: 0.18em;
  color: var(--sim-text-muted);
  padding: 12px 16px;
}
.signup-intent-modal .sim-btn-secondary:hover {
  color: var(--sim-text);
  border-color: var(--sim-text-dim);
}

.signup-intent-fade-enter-active,
.signup-intent-fade-leave-active {
  transition: opacity 0.18s ease;
}
.signup-intent-fade-enter-active .sim-card,
.signup-intent-fade-leave-active .sim-card {
  transition: transform 0.22s ease;
}
.signup-intent-fade-enter-from,
.signup-intent-fade-leave-to {
  opacity: 0;
}
.signup-intent-fade-enter-from .sim-card,
.signup-intent-fade-leave-to .sim-card {
  transform: translateY(12px);
}

@media (max-width: 480px) {
  .signup-intent-modal { padding: 16px; }
  .signup-intent-modal .sim-card { padding: 28px 22px 22px; }
  .signup-intent-modal .sim-title { font-size: 26px; }
}
</style>

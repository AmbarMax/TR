<template>
  <div class="convert-overlay" @click.self="$emit('close')">
    <div class="convert-modal">
      <div class="convert-title">Convert Ambar to Uru</div>
      <div class="convert-rate">Rate: 10 Ambar = 1 Uru</div>

      <div class="convert-field">
        <label class="convert-label">Ambar to spend</label>
        <div class="convert-input-wrap">
          <div class="convert-input-dot"></div>
          <input
            type="number"
            v-model.number="ambarAmount"
            min="10"
            :max="userAmbar"
            step="10"
            class="convert-input"
            @input="validateAmount"
          />
          <span class="convert-unit">Ambar</span>
        </div>
        <div class="convert-available">Available: {{ userAmbar }} Ambar</div>
      </div>

      <div class="convert-arrow">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M12 5v14M12 19l-4-4M12 19l4-4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

      <div class="convert-field">
        <label class="convert-label">Uru you'll receive</label>
        <div class="convert-output-wrap">
          <span class="convert-output">{{ uruResult }}</span>
          <span class="convert-output-unit">Uru</span>
        </div>
      </div>

      <div class="convert-actions">
        <button class="convert-cancel" @click="$emit('close')">Cancel</button>
        <button class="convert-confirm" :disabled="ambarAmount < 10 || ambarAmount > userAmbar" @click="$emit('convert', ambarAmount)">
          Convert — {{ ambarAmount }} Ambar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
  props: {
    userAmbar: { type: Number, default: 0 },
  },
  emits: ["close", "convert"],
  data() {
    return {
      ambarAmount: 100,
    };
  },
  computed: {
    uruResult() {
      return Math.floor(this.ambarAmount / 10);
    },
  },
  methods: {
    validateAmount() {
      if (this.ambarAmount < 0) this.ambarAmount = 0;
      if (this.ambarAmount > this.userAmbar) this.ambarAmount = this.userAmbar;
    },
  },
});
</script>

<style lang="scss" scoped>
.convert-overlay {
  position: fixed; inset: 0; z-index: 1000;
  background: rgba(0,0,3,0.85);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.convert-modal {
  width: 100%; max-width: 380px;
  padding: 32px 28px 24px;
  background: rgba(14,15,17,0.95);
  border: 1px solid rgba(255,97,0,0.3);
  box-shadow: 0 30px 80px rgba(0,0,0,0.8);
  position: relative;
}
.convert-modal::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, transparent, var(--primary), transparent);
  box-shadow: 0 0 12px var(--primary-glow);
}
.convert-title {
  font-family: var(--display);
  font-size: 28px; color: var(--text);
  letter-spacing: 0.02em; margin-bottom: 6px;
}
.convert-rate {
  font-size: 11px; color: var(--text-muted);
  letter-spacing: 0.06em; margin-bottom: 20px;
}
.convert-field { margin-bottom: 8px; }
.convert-label {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  display: block; margin-bottom: 6px;
}
.convert-input-wrap {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 16px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  transition: border-color 0.15s;
}
.convert-input-wrap:focus-within { border-color: var(--primary); }
.convert-input-dot {
  width: 10px; height: 10px; border-radius: 50%;
  background: var(--primary);
  box-shadow: 0 0 8px var(--primary);
  flex-shrink: 0;
}
.convert-input {
  flex: 1;
  font-family: var(--display);
  font-size: 32px; color: var(--text);
  letter-spacing: 0.02em;
  background: none; border: none; outline: none;
  width: 100%;
  min-width: 0;
}
.convert-input::placeholder { color: var(--text-dim); }
.convert-unit {
  font-size: 11px; color: var(--text-muted);
  letter-spacing: 0.15em; text-transform: uppercase;
}
.convert-available {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.1em; margin-top: 6px;
}

.convert-arrow {
  display: flex; justify-content: center; padding: 12px 0;
  color: var(--accent);
}

.convert-output-wrap {
  display: flex; align-items: center; gap: 8px;
  padding: 14px 16px;
  background: var(--surface-2);
  border: 1px solid var(--border);
}
.convert-output {
  font-family: var(--display);
  font-size: 32px; color: var(--accent);
  text-shadow: 0 0 12px var(--accent-glow);
  letter-spacing: 0.02em;
}
.convert-output-unit {
  font-size: 11px; color: var(--text-muted);
  letter-spacing: 0.15em; text-transform: uppercase;
  margin-left: 8px;
}

.convert-actions {
  display: flex; gap: 10px; margin-top: 20px;
}
.convert-cancel {
  flex: 1; padding: 12px 16px;
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.2em; text-transform: uppercase;
  text-align: center; cursor: pointer;
  background: transparent; color: var(--text-muted);
  border: 1px solid var(--border);
  transition: all 0.15s;
}
.convert-cancel:hover { color: var(--text); border-color: var(--text-dim); }
.convert-confirm {
  flex: 1; padding: 12px 16px;
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.2em; text-transform: uppercase;
  text-align: center; cursor: pointer;
  background: var(--primary); color: var(--bg);
  border: 1px solid var(--primary);
  box-shadow: 0 0 14px rgba(255,97,0,0.3);
  transition: all 0.15s;
}
.convert-confirm:hover {
  background: #ff7e2e;
  box-shadow: 0 0 24px rgba(255,97,0,0.5);
}
.convert-confirm:disabled {
  opacity: 0.4; cursor: default;
  box-shadow: none;
}

/* Hide number input spinners */
.convert-input::-webkit-outer-spin-button,
.convert-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.convert-input {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>

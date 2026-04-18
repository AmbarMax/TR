<template>
  <div class="convert-overlay" @click.self="$emit('close')">
    <div class="convert-modal">
      <div class="convert-title">Convert Ambar to Uru</div>
      <div class="convert-rate">Rate: 10 Ambar = 1 Uru</div>

      <div class="convert-field">
        <label class="convert-label">Ambar to spend</label>
        <input
          type="number"
          v-model.number="ambarAmount"
          min="10"
          :max="userAmbar"
          step="10"
          class="convert-input"
          @input="validateAmount"
        />
        <div class="convert-available">Available: {{ userAmbar }} Ambar</div>
      </div>

      <div class="convert-arrow">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M12 5v14M12 19l-4-4M12 19l4-4" stroke="#c1f527" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

      <div class="convert-field">
        <label class="convert-label">Uru you'll receive</label>
        <div class="convert-output">{{ uruResult }}</div>
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

<style scoped>
.convert-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 3, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
}
.convert-modal {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 24px;
  width: 360px;
  max-width: 90vw;
}
.convert-title {
  font-family: "Share Tech Mono", monospace;
  font-size: 16px;
  color: #feeddf;
  margin-bottom: 4px;
}
.convert-rate {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  margin-bottom: 20px;
}
.convert-field {
  margin-bottom: 8px;
}
.convert-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #9a9590;
  display: block;
  margin-bottom: 6px;
}
.convert-input {
  width: 100%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 10px 14px;
  font-family: "Share Tech Mono", monospace;
  font-size: 14px;
  color: #feeddf;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s;
}
.convert-input:focus {
  border-color: #ff6100;
}
.convert-available {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  margin-top: 4px;
}
.convert-arrow {
  display: flex;
  justify-content: center;
  padding: 8px 0;
}
.convert-output {
  font-family: "Share Tech Mono", monospace;
  font-size: 22px;
  color: #c1f527;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 10px 14px;
}
.convert-actions {
  display: flex;
  gap: 8px;
  margin-top: 20px;
}
.convert-cancel {
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #9a9590;
  background: transparent;
  border: 1px solid #2a2c2e;
  border-radius: 4px;
  padding: 8px 16px;
  cursor: pointer;
  flex: 1;
  transition: border-color 0.2s;
}
.convert-cancel:hover {
  border-color: #5a5550;
}
.convert-confirm {
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #000003;
  background: #ff6100;
  border: none;
  border-radius: 4px;
  padding: 8px 16px;
  cursor: pointer;
  flex: 1;
  transition: opacity 0.2s;
}
.convert-confirm:hover {
  opacity: 0.85;
}
.convert-confirm:disabled {
  opacity: 0.4;
  cursor: default;
}
</style>

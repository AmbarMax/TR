<template>
  <div class="battlepass">
    <div class="bp-track">
      <div v-for="(level, index) in levels" :key="level.id" class="bp-tier" :style="{ opacity: tierOpacity(level) }">
        <div class="bp-node" :class="nodeClass(level)">{{ level.number }}</div>
        <div class="bp-card" :class="cardClass(level)">
          <div class="bp-card-left">
            <div class="bp-card-name" :class="{ 'bp-current-name': level.status === 'current' }">
              {{ level.name }}
              <span v-if="level.status === 'current'" class="bp-current-badge">Current level</span>
            </div>
            <div class="bp-card-cost">
              <template v-if="level.status === 'owned' && level.cost === 0">Free with signup</template>
              <template v-else-if="level.status === 'owned'">Purchased for {{ level.cost }} Uru</template>
              <template v-else>{{ level.cost }} Uru</template>
            </div>
          </div>
          <div class="bp-card-right">
            <div class="bp-rewards">
              <span v-for="(reward, ri) in level.rewards" :key="ri" class="bp-reward-pill" :class="rewardClass(reward)">
                {{ reward.label }}
              </span>
            </div>
            <div v-if="level.status === 'owned'" class="bp-status-owned">Owned</div>
            <button v-else-if="level.status === 'next'" class="bp-buy-btn" @click="$emit('buy-level', level)">
              Buy — {{ level.cost }} Uru
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import api from "../../api/api.js";

export default defineComponent({
  props: {
    userUru: { type: Number, default: 0 },
  },
  emits: ["buy-level"],
  data() {
    return {
      levels: [],
      loading: true,
    };
  },
  mounted() {
    this.fetchLevels();
  },
  methods: {
    async fetchLevels() {
      try {
        const resp = await api.get("/api/rewards/battle-pass");
        const raw = resp.data;
        const firstUnowned = raw.findIndex((l) => !l.owned);
        this.levels = raw.map((l, idx) => {
          let status = "locked";
          if (l.owned) {
            status = "owned";
          } else if (idx === firstUnowned) {
            status = idx > 0 && raw[idx - 1]?.owned ? "next" : "current";
          }
          return {
            id: l.id,
            number: l.number,
            name: l.name,
            cost: l.cost_uru,
            status,
            rewards: this.formatRewards(l.rewards || []),
          };
        });
      } catch (e) {
        this.levels = [];
      } finally {
        this.loading = false;
      }
    },
    formatRewards(rewards) {
      return rewards.map((r) => {
        if (r.type === "ambar") return { label: `+${r.amount} Ambar`, type: "ambar" };
        if (r.type === "key") return { label: `Key x${r.quantity || 1}`, type: "key" };
        return { label: r.type, type: "key" };
      });
    },
    tierOpacity(level) {
      if (level.status === "owned" || level.status === "current" || level.status === "next") return 1;
      const idx = this.levels.findIndex((l) => l.id === level.id);
      const nextIdx = this.levels.findIndex((l) => l.status === "next");
      const diff = idx - nextIdx;
      if (diff <= 1) return 0.6;
      if (diff <= 2) return 0.45;
      return 0.35;
    },
    nodeClass(level) {
      if (level.status === "owned" || level.status === "current") return "bp-node-done";
      if (level.status === "next") return "bp-node-next";
      return "bp-node-locked";
    },
    cardClass(level) {
      if (level.status === "current") return "bp-card-current";
      if (level.status === "next") return "bp-card-next";
      if (level.status === "owned") return "bp-card-owned";
      return "bp-card-locked";
    },
    rewardClass(reward) {
      return reward.type === "ambar" ? "bp-reward-ambar" : "bp-reward-key";
    },
  },
});
</script>

<style lang="scss" scoped>
.battlepass { padding-top: 4px; }

.bp-track {
  position: relative;
  padding-left: 60px;
  margin-left: 24px;
}
.bp-track::before {
  content: '';
  position: absolute; left: 21px; top: 0; bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, var(--accent), var(--accent) 45%, var(--border) 45%);
  box-shadow: 0 0 8px var(--accent-glow);
}

.bp-tier {
  position: relative;
  padding: 0 0 32px;
  transition: opacity 0.3s;
}
.bp-tier:last-child { padding-bottom: 0; }

/* Node — absolute positioned left of card */
.bp-node {
  position: absolute; left: -56px; top: 4px;
  width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mono); font-size: 13px;
  z-index: 2; transition: all 0.2s;
}
.bp-node-done {
  background: rgba(193,245,39,0.08);
  border: 2px solid var(--accent);
  color: var(--accent);
  box-shadow: 0 0 14px var(--accent-glow);
}
.bp-node-next {
  background: rgba(255,97,0,0.08);
  border: 2px solid var(--primary);
  color: var(--primary);
  box-shadow: 0 0 18px var(--primary-glow);
}
.bp-node-locked {
  background: var(--surface-2);
  border: 2px solid var(--border);
  border-style: dashed;
  color: var(--text-dim);
}

/* Card */
.bp-card {
  padding: 16px 20px;
  display: flex; justify-content: space-between; align-items: center;
  gap: 16px;
  transition: border-color 0.2s;
}
.bp-card-owned {
  background: rgba(14,15,17,0.7);
  border: 1px solid rgba(193,245,39,0.15);
}
.bp-card-current {
  background: rgba(14,15,17,0.8);
  border: 1px solid var(--accent);
  box-shadow: 0 0 20px rgba(193,245,39,0.08);
}
.bp-card-next {
  background: rgba(14,15,17,0.7);
  border: 1px solid rgba(255,97,0,0.3);
}
.bp-card-locked {
  background: rgba(14,15,17,0.5);
  border: 1px solid var(--border);
  border-style: dashed;
}

.bp-card-left { min-width: 0; }
.bp-card-name {
  font-size: 13px; color: var(--text);
  display: flex; align-items: center; gap: 8px;
}
.bp-card-locked .bp-card-name { color: var(--text-muted); }
.bp-current-name { color: var(--accent); }
.bp-current-badge {
  font-size: 9px;
  background: rgba(193,245,39,0.15);
  color: var(--accent);
  padding: 2px 6px;
  letter-spacing: 0.1em; text-transform: uppercase;
}
.bp-card-cost {
  font-size: 11px; color: var(--text-dim);
  margin-top: 2px;
}
.bp-card-next .bp-card-cost { color: var(--primary); }

.bp-card-right {
  display: flex; align-items: center; gap: 8px;
  flex-shrink: 0;
}
.bp-rewards { display: flex; gap: 6px; flex-wrap: wrap; }
.bp-reward-pill {
  font-size: 10px; padding: 3px 8px;
  letter-spacing: 0.08em;
  white-space: nowrap;
}
.bp-reward-ambar {
  background: rgba(193,245,39,0.15);
  color: var(--accent);
}
.bp-reward-key {
  background: rgba(255,97,0,0.15);
  color: var(--primary);
}

.bp-status-owned {
  font-size: 11px; color: var(--text-dim);
  letter-spacing: 0.1em; text-transform: uppercase;
}

.bp-buy-btn {
  font-family: var(--mono); font-size: 11px;
  color: var(--bg); background: var(--primary);
  border: 1px solid var(--primary);
  padding: 8px 16px;
  letter-spacing: 0.15em; text-transform: uppercase;
  cursor: pointer; white-space: nowrap;
  transition: all 0.15s;
  box-shadow: 0 0 12px rgba(255,97,0,0.25);
}
.bp-buy-btn:hover {
  background: #ff7e2e;
  box-shadow: 0 0 20px rgba(255,97,0,0.4);
}

@media (max-width: 640px) {
  .bp-card { flex-direction: column; align-items: flex-start; }
  .bp-card-right { width: 100%; justify-content: space-between; margin-top: 8px; }
  .bp-track { padding-left: 48px; margin-left: 16px; }
  .bp-node { left: -44px; width: 36px; height: 36px; font-size: 11px; }
}
</style>

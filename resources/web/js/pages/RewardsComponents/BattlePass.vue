<template>
  <div class="battlepass">
    <div class="bp-timeline">
      <div class="bp-line"></div>

      <div v-for="(level, index) in levels" :key="level.id" class="bp-tier" :style="{ opacity: tierOpacity(level) }">
        <div class="bp-node" :class="nodeClass(level)">{{ level.number }}</div>
        <div class="bp-connector" :class="{ 'bp-connector-done': level.status === 'owned' }"></div>
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

export default defineComponent({
  props: {
    userUru: { type: Number, default: 0 },
  },
  emits: ["buy-level"],
  data() {
    return {
      levels: [
        {
          id: 1, number: 1, name: "Rookie", cost: 0, status: "owned",
          rewards: [{ label: "+50 Ambar", type: "ambar" }],
        },
        {
          id: 2, number: 2, name: "Scout", cost: 50, status: "owned",
          rewards: [{ label: "Bronze Key x1", type: "key" }],
        },
        {
          id: 3, number: 3, name: "Explorer", cost: 100, status: "current",
          rewards: [{ label: "+200 Ambar", type: "ambar" }],
        },
        {
          id: 4, number: 4, name: "Warrior", cost: 200, status: "next",
          rewards: [{ label: "Silver Key x1", type: "key" }],
        },
        {
          id: 5, number: 5, name: "Hero", cost: 400, status: "locked",
          rewards: [
            { label: "Gold Key x1", type: "key" },
            { label: "+500 Ambar", type: "ambar" },
          ],
        },
        {
          id: 6, number: 6, name: "Legend", cost: 800, status: "locked",
          rewards: [
            { label: "Legendary Chest", type: "key" },
            { label: "+1000 Ambar", type: "ambar" },
          ],
        },
        {
          id: 7, number: 7, name: "Master", cost: 1500, status: "locked",
          rewards: [{ label: "Exclusive reward", type: "key" }],
        },
      ],
    };
  },
  methods: {
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

<style scoped>
.battlepass {
  padding-top: 4px;
}
.bp-timeline {
  position: relative;
  padding-left: 40px;
}
.bp-line {
  position: absolute;
  left: 17px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #2a2c2e;
}
.bp-tier {
  position: relative;
  margin-bottom: 8px;
  transition: opacity 0.3s;
}
.bp-node {
  position: absolute;
  left: -40px;
  top: 14px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  z-index: 1;
}
.bp-node-done {
  background: #c1f527;
  color: #000003;
}
.bp-node-next {
  background: #252729;
  border: 2px solid #ff6100;
  color: #ff6100;
}
.bp-node-locked {
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  color: #5a5550;
}
.bp-connector {
  position: absolute;
  left: -5px;
  top: 22px;
  width: 12px;
  height: 2px;
  background: #2a2c2e;
}
.bp-connector-done {
  background: #c1f527;
}
.bp-card {
  background: #0e0f11;
  border-radius: 6px;
  padding: 14px 16px;
  margin-left: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  transition: border-color 0.2s;
}
.bp-card-owned {
  border: 1px solid rgba(193, 245, 39, 0.2);
}
.bp-card-current {
  border: 1px solid #c1f527;
}
.bp-card-next {
  border: 1px solid rgba(255, 97, 0, 0.3);
}
.bp-card-locked {
  border: 1px solid #2a2c2e;
}
.bp-card-left {
  min-width: 0;
}
.bp-card-name {
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
  display: flex;
  align-items: center;
  gap: 8px;
}
.bp-card-locked .bp-card-name {
  color: #9a9590;
}
.bp-current-name {
  color: #c1f527;
}
.bp-current-badge {
  font-size: 10px;
  background: rgba(193, 245, 39, 0.15);
  color: #c1f527;
  padding: 2px 6px;
  border-radius: 4px;
}
.bp-card-cost {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  margin-top: 2px;
}
.bp-card-next .bp-card-cost {
  color: #ff6100;
}
.bp-card-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}
.bp-rewards {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.bp-reward-pill {
  font-family: "Share Tech Mono", monospace;
  font-size: 10px;
  padding: 3px 8px;
  border-radius: 4px;
  white-space: nowrap;
}
.bp-reward-ambar {
  background: rgba(193, 245, 39, 0.15);
  color: #c1f527;
}
.bp-reward-key {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
}
.bp-status-owned {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
}
.bp-buy-btn {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #000003;
  background: #ff6100;
  border: none;
  padding: 5px 14px;
  border-radius: 4px;
  cursor: pointer;
  white-space: nowrap;
  transition: opacity 0.2s;
}
.bp-buy-btn:hover {
  opacity: 0.85;
}
@media (max-width: 640px) {
  .bp-card {
    flex-direction: column;
    align-items: flex-start;
  }
  .bp-card-right {
    width: 100%;
    justify-content: space-between;
    margin-top: 8px;
  }
}
</style>

# CLAUDE_PHASE_5B_OPS.md — Rewards Page (Battle Pass + Chests + Shop)

> Read TROPHYROOM_WORKING_GUIDE.md and CLAUDE.md before starting.
> Execute ONE step at a time. Run `npm run dev` after each step to verify no errors.
> Vue 3 Options API ONLY. Share Tech Mono only. No Composition API, no `<script setup>`.

---

## Goal

Create a unified Rewards page that replaces both MyChests.vue and Exchange.vue. The page has 3 tabs: Battle Pass (timeline of purchasable levels), Chests (grid of openable chests), and Shop (direct item purchases). All data is hardcoded/mocked for now — backend integration comes later.

Also: update sidebar (remove Exchange, rename Reward Chest → Rewards), update router (redirect /exchange and /my-chests to /rewards).

---

## Step 1 — Create the Rewards page wrapper

**Create file:** `resources/web/js/pages/Rewards.vue`

This is the main page component with tabs and currency balances.

```vue
<template>
  <div class="rewards-page">
    <div class="rewards-header">
      <div class="rewards-label">Rewards</div>
      <p class="rewards-subtitle">Spend your Uru to level up, open chests, or buy from the shop</p>
    </div>

    <div class="rewards-balances">
      <div class="balance-card">
        <div class="balance-label">Your Ambar</div>
        <div class="balance-value balance-ambar">{{ userAmbar }}</div>
      </div>
      <div class="balance-card">
        <div class="balance-label">Your Uru</div>
        <div class="balance-value balance-uru">{{ userUru }}</div>
      </div>
      <div class="balance-card balance-card-action">
        <div class="balance-label">Convert</div>
        <button class="convert-btn" @click="showConvertModal = true">Ambar to Uru</button>
      </div>
    </div>

    <div class="rewards-tabs">
      <div :class="{ active: activeTab === 'battlepass' }" class="rewards-tab" @click="activeTab = 'battlepass'">Battle pass</div>
      <div :class="{ active: activeTab === 'chests' }" class="rewards-tab" @click="activeTab = 'chests'">Chests</div>
      <div :class="{ active: activeTab === 'shop' }" class="rewards-tab" @click="activeTab = 'shop'">Shop</div>
    </div>

    <BattlePass v-if="activeTab === 'battlepass'" :userUru="userUru" @buy-level="handleBuyLevel" />
    <RewardsChests v-if="activeTab === 'chests'" />
    <RewardsShop v-if="activeTab === 'shop'" :userUru="userUru" />

    <ConvertModal v-if="showConvertModal" :userAmbar="userAmbar" @close="showConvertModal = false" @convert="handleConvert" />
  </div>
</template>

<script>
import { defineComponent } from "vue";
import store from "../store/store.js";
import BattlePass from "./RewardsComponents/BattlePass.vue";
import RewardsChests from "./RewardsComponents/RewardsChests.vue";
import RewardsShop from "./RewardsComponents/RewardsShop.vue";
import ConvertModal from "./RewardsComponents/ConvertModal.vue";

export default defineComponent({
  components: {
    BattlePass,
    RewardsChests,
    RewardsShop,
    ConvertModal,
  },
  data() {
    return {
      activeTab: "battlepass",
      showConvertModal: false,
    };
  },
  computed: {
    userAmbar() {
      const balances = store.state.user?.balances;
      if (balances && balances.ambar !== undefined) {
        return balances.ambar < 0 ? 0 : balances.ambar;
      }
      return 0;
    },
    userUru() {
      const balances = store.state.user?.balances;
      if (balances && balances.uru !== undefined) {
        return balances.uru < 0 ? 0 : balances.uru;
      }
      return 0;
    },
  },
  methods: {
    handleBuyLevel(level) {
      // TODO: backend integration — POST to buy battle pass level
      console.log("Buy level:", level);
      store.state.notification = {
        message: "Level purchased! (mock — backend not connected yet)",
        type: "success",
        show: true,
      };
    },
    handleConvert(amount) {
      // TODO: backend integration — POST to convert Ambar to Uru
      console.log("Convert Ambar to Uru:", amount);
      this.showConvertModal = false;
      store.state.notification = {
        message: "Conversion complete! (mock — backend not connected yet)",
        type: "success",
        show: true,
      };
    },
  },
  mounted() {
    if (this.$route.query.tab === "chests") {
      this.activeTab = "chests";
    } else if (this.$route.query.tab === "shop") {
      this.activeTab = "shop";
    }
  },
});
</script>

<style scoped>
.rewards-page {
  padding-top: 20px;
  padding-left: 32px;
  max-width: 780px;
}
.rewards-header {
  margin-bottom: 16px;
}
.rewards-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #ff6100;
  margin-bottom: 6px;
}
.rewards-subtitle {
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #9a9590;
  margin: 0;
}
.rewards-balances {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}
.balance-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 10px 16px;
  flex: 1;
}
.balance-card-action {
  flex: 0 0 auto;
}
.balance-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.balance-value {
  font-family: "Share Tech Mono", monospace;
  font-size: 18px;
  margin-top: 4px;
}
.balance-ambar {
  color: #ff6100;
}
.balance-uru {
  color: #c1f527;
}
.convert-btn {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #000003;
  background: #c1f527;
  border: none;
  padding: 5px 14px;
  border-radius: 4px;
  margin-top: 6px;
  cursor: pointer;
  transition: opacity 0.2s;
}
.convert-btn:hover {
  opacity: 0.85;
}
.rewards-tabs {
  display: flex;
  border-bottom: 1px solid #2a2c2e;
  margin-bottom: 20px;
  gap: 0;
}
.rewards-tab {
  padding: 10px 16px;
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #9a9590;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: color 0.2s, border-color 0.2s;
}
.rewards-tab:hover {
  color: #feeddf;
}
.rewards-tab.active {
  color: #c1f527;
  border-bottom-color: #c1f527;
}
@media (max-width: 968px) {
  .rewards-page {
    padding-left: 16px;
    padding-right: 16px;
  }
  .rewards-balances {
    flex-wrap: wrap;
  }
  .balance-card {
    min-width: 120px;
  }
}
@media (max-width: 520px) {
  .rewards-balances {
    flex-direction: column;
  }
}
</style>
```

**After creating, run:** `npm run dev`

---

## Step 2 — Create BattlePass.vue component

**Create directory:** `resources/web/js/pages/RewardsComponents/`

**Create file:** `resources/web/js/pages/RewardsComponents/BattlePass.vue`

```vue
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
```

**After creating, run:** `npm run dev`

---

## Step 3 — Create RewardsChests.vue component

**Create file:** `resources/web/js/pages/RewardsComponents/RewardsChests.vue`

```vue
<template>
  <div class="rchests">
    <div class="rchests-filters">
      <div :class="['rchests-pill', { active: filter === 'all' }]" @click="filter = 'all'">All chests</div>
      <div :class="['rchests-pill', { active: filter === 'mine' }]" @click="filter = 'mine'">My chests</div>
    </div>

    <div class="rchests-grid">
      <div v-for="chest in filteredChests" :key="chest.id" class="rchest-card" :style="{ opacity: chest.state === 'locked' ? 0.6 : 1 }">
        <div class="rchest-image">
          <div class="rchest-placeholder">?</div>
          <div class="rchest-badge" :class="badgeClass(chest)">{{ badgeText(chest) }}</div>
        </div>
        <div class="rchest-body">
          <div class="rchest-name">{{ chest.name }}</div>
          <div class="rchest-desc">{{ chest.description }}</div>
          <div class="rchest-req-label">Requires</div>
          <div class="rchest-reqs">
            <span v-for="(req, ri) in chest.requirements" :key="ri" class="rchest-req" :class="reqClass(req)">
              {{ req.label }}
            </span>
          </div>
          <button class="rchest-btn" :class="btnClass(chest)" :disabled="chest.state !== 'available'" @click="openChest(chest)">
            {{ btnText(chest) }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="filteredChests.length === 0" class="rchests-empty">
      <span v-if="filter === 'mine'">You haven't opened any chests yet</span>
      <span v-else>No chests available right now</span>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import store from "../../store/store.js";

export default defineComponent({
  data() {
    return {
      filter: "all",
      chests: [
        {
          id: 1, name: "Starter chest", description: "Contains random gaming reward",
          state: "available", owned: false,
          requirements: [{ label: "Bronze Key x1", type: "key", met: true }],
        },
        {
          id: 2, name: "Brand X campaign chest", description: "Exclusive merch + in-game items",
          state: "partial", owned: false,
          requirements: [
            { label: "Silver Key x1", type: "key", met: true },
            { label: "Trophy: DS Completionist", type: "trophy", met: false },
            { label: "Trophy: LoL Master", type: "trophy", met: false },
          ],
        },
        {
          id: 3, name: "Legendary chest", description: "Unlocked at Battle Pass Level 6",
          state: "locked", owned: false,
          requirements: [
            { label: "Gold Key x1", type: "key", met: false },
            { label: "Battle Pass Lvl 6", type: "trophy", met: false },
          ],
        },
      ],
    };
  },
  computed: {
    filteredChests() {
      if (this.filter === "mine") return this.chests.filter((c) => c.owned);
      return this.chests;
    },
  },
  methods: {
    badgeClass(chest) {
      if (chest.state === "available") return "rchest-badge-available";
      if (chest.state === "partial") return "rchest-badge-partial";
      return "rchest-badge-locked";
    },
    badgeText(chest) {
      if (chest.state === "available") return "Available";
      if (chest.state === "partial") {
        const met = chest.requirements.filter((r) => r.met).length;
        return met + " of " + chest.requirements.length;
      }
      return "Locked";
    },
    reqClass(req) {
      return req.met ? "rchest-req-met" : "rchest-req-unmet";
    },
    btnClass(chest) {
      if (chest.state === "available") return "rchest-btn-open";
      return "rchest-btn-disabled";
    },
    btnText(chest) {
      if (chest.state === "available") return "Open chest";
      if (chest.state === "partial") return "Missing requirements";
      return "Locked";
    },
    openChest(chest) {
      if (chest.state !== "available") return;
      store.state.notification = {
        message: chest.name + " opened! (mock)",
        type: "success",
        show: true,
      };
    },
  },
});
</script>

<style scoped>
.rchests {
  padding-top: 4px;
}
.rchests-filters {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
}
.rchests-pill {
  padding: 6px 16px;
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #9a9590;
  border: 1px solid #2a2c2e;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.2s;
}
.rchests-pill:hover {
  color: #feeddf;
  border-color: #5a5550;
}
.rchests-pill.active {
  background: #c1f527;
  color: #000003;
  border-color: #c1f527;
}
.rchests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}
.rchest-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  overflow: hidden;
  transition: border-color 0.2s;
}
.rchest-card:hover {
  border-color: rgba(255, 97, 0, 0.3);
}
.rchest-image {
  background: #1a1c1f;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.rchest-placeholder {
  width: 64px;
  height: 64px;
  background: #252729;
  border: 1px solid #2a2c2e;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Share Tech Mono", monospace;
  font-size: 28px;
  color: #5a5550;
}
.rchest-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  font-family: "Share Tech Mono", monospace;
  font-size: 10px;
  padding: 2px 8px;
  border-radius: 4px;
}
.rchest-badge-available {
  background: rgba(193, 245, 39, 0.15);
  color: #c1f527;
}
.rchest-badge-partial {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
}
.rchest-badge-locked {
  background: #252729;
  color: #5a5550;
}
.rchest-body {
  padding: 12px;
}
.rchest-name {
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
  margin-bottom: 4px;
}
.rchest-desc {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  margin-bottom: 10px;
}
.rchest-req-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #ff6100;
  margin-bottom: 8px;
}
.rchest-reqs {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 10px;
}
.rchest-req {
  font-family: "Share Tech Mono", monospace;
  font-size: 10px;
  padding: 2px 8px;
  border-radius: 4px;
}
.rchest-req-met {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
}
.rchest-req-unmet {
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  color: #9a9590;
}
.rchest-btn {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  width: 100%;
  padding: 6px 0;
  border-radius: 4px;
  text-align: center;
  cursor: pointer;
  border: none;
  transition: opacity 0.2s;
}
.rchest-btn-open {
  background: #c1f527;
  color: #000003;
}
.rchest-btn-open:hover {
  opacity: 0.85;
}
.rchest-btn-disabled {
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
  cursor: default;
}
.rchests-empty {
  padding: 40px 0;
  text-align: center;
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #5a5550;
}
@media (max-width: 520px) {
  .rchests-grid {
    grid-template-columns: 1fr;
  }
}
</style>
```

**After creating, run:** `npm run dev`

---

## Step 4 — Create RewardsShop.vue component

**Create file:** `resources/web/js/pages/RewardsComponents/RewardsShop.vue`

```vue
<template>
  <div class="rshop">
    <div class="rshop-curated">Curated by TrophyRoom</div>

    <div class="rshop-grid">
      <div v-for="item in shopItems" :key="item.id" class="rshop-card">
        <div class="rshop-image">
          <div class="rshop-image-placeholder">{{ item.icon }}</div>
        </div>
        <div class="rshop-body">
          <div class="rshop-name">{{ item.name }}</div>
          <div class="rshop-desc">{{ item.description }}</div>
          <div class="rshop-footer">
            <div class="rshop-price">{{ item.price }} Uru</div>
            <button class="rshop-buy-btn" :disabled="userUru < item.price" @click="buyItem(item)">Buy</button>
          </div>
        </div>
      </div>
    </div>

    <div class="rshop-history">
      <div class="rshop-history-label">Purchase history</div>
      <div v-if="purchaseHistory.length === 0" class="rshop-history-empty">No purchases yet</div>
      <div v-else class="rshop-history-list">
        <div v-for="purchase in purchaseHistory" :key="purchase.id" class="rshop-history-row">
          <div class="rshop-history-name">{{ purchase.name }}</div>
          <div class="rshop-history-price">{{ purchase.price }} Uru</div>
          <div class="rshop-history-status" :class="purchase.status === 'delivered' ? 'rshop-status-delivered' : 'rshop-status-pending'">
            {{ purchase.status === 'delivered' ? 'Delivered' : 'Pending' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import store from "../../store/store.js";

export default defineComponent({
  props: {
    userUru: { type: Number, default: 0 },
  },
  data() {
    return {
      shopItems: [
        { id: 1, name: "Steam $10 gift card", description: "Delivered via email within 24h", price: 500, icon: "GC" },
        { id: 2, name: "TrophyRoom enamel pin", description: "Limited edition collector pin", price: 300, icon: "Pin" },
        { id: 3, name: "Exclusive profile frame", description: "Gold animated border for your Virtual Hall", price: 150, icon: "Frame" },
      ],
      purchaseHistory: [
        { id: 1, name: "TrophyRoom sticker pack", price: 100, status: "delivered" },
        { id: 2, name: "Discord Nitro 1 month", price: 800, status: "pending" },
      ],
    };
  },
  methods: {
    buyItem(item) {
      if (this.userUru < item.price) return;
      store.state.notification = {
        message: item.name + " purchased! (mock)",
        type: "success",
        show: true,
      };
    },
  },
});
</script>

<style scoped>
.rshop {
  padding-top: 4px;
}
.rshop-curated {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 12px;
}
.rshop-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}
.rshop-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  overflow: hidden;
  transition: border-color 0.2s;
}
.rshop-card:hover {
  border-color: rgba(255, 97, 0, 0.3);
}
.rshop-image {
  background: #1a1c1f;
  height: 110px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.rshop-image-placeholder {
  width: 64px;
  height: 48px;
  background: #252729;
  border: 1px solid #2a2c2e;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
}
.rshop-body {
  padding: 12px;
}
.rshop-name {
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
  margin-bottom: 4px;
}
.rshop-desc {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  margin-bottom: 10px;
}
.rshop-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.rshop-price {
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #c1f527;
}
.rshop-buy-btn {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #000003;
  background: #ff6100;
  border: none;
  padding: 5px 14px;
  border-radius: 4px;
  cursor: pointer;
  transition: opacity 0.2s;
}
.rshop-buy-btn:hover {
  opacity: 0.85;
}
.rshop-buy-btn:disabled {
  opacity: 0.4;
  cursor: default;
}
.rshop-history {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px;
}
.rshop-history-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 12px;
}
.rshop-history-empty {
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #5a5550;
  text-align: center;
  padding: 16px 0;
}
.rshop-history-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.rshop-history-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #1a1c1f;
}
.rshop-history-row:last-child {
  border-bottom: none;
}
.rshop-history-name {
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #feeddf;
  flex: 1;
}
.rshop-history-price {
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #9a9590;
  margin: 0 16px;
}
.rshop-history-status {
  font-family: "Share Tech Mono", monospace;
  font-size: 10px;
  padding: 2px 8px;
  border-radius: 4px;
}
.rshop-status-delivered {
  background: rgba(193, 245, 39, 0.15);
  color: #c1f527;
}
.rshop-status-pending {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
}
@media (max-width: 520px) {
  .rshop-grid {
    grid-template-columns: 1fr;
  }
  .rshop-history-row {
    flex-wrap: wrap;
    gap: 4px;
  }
}
</style>
```

**After creating, run:** `npm run dev`

---

## Step 5 — Create ConvertModal.vue component

**Create file:** `resources/web/js/pages/RewardsComponents/ConvertModal.vue`

```vue
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
```

**After creating, run:** `npm run dev`

---

## Step 6 — Update routes.js

**Edit file:** `resources/web/js/router/routes.js`

**6a.** Add import at the top (after the MyChests import):

```js
import Rewards from "../pages/Rewards.vue";
```

**6b.** Add the rewards route inside the Main children array, right after the `/my-chests` route:

```js
            {
                path: '/rewards',
                component: Rewards,
                name: 'rewards'
            },
```

**6c.** Replace the `/exchange` route:

Find:
```js
            {
                path: '/exchange',
                component: Exchange,
                name: 'exchange'
            },
```
Replace with:
```js
            {
                path: '/exchange',
                redirect: { name: 'rewards', query: { tab: 'shop' } }
            },
```

**6d.** Replace the `/my-chests` route:

Find:
```js
            {
                path: '/my-chests',
                component: MyChests,
                name: 'my-chests'
            },
```
Replace with:
```js
            {
                path: '/my-chests',
                redirect: { name: 'rewards', query: { tab: 'chests' } }
            },
```

**6e.** Remove the imports that are no longer needed (but keep the files — they're dead code now):

Find and delete these two lines:
```js
import Exchange from "../pages/Exchange.vue";
import MyChests from "../pages/MyChests.vue";
```

**After editing, run:** `npm run dev`

---

## Step 7 — Update sidebar.vue

**Edit file:** `resources/web/js/components/sidebar.vue`

**7a.** Find the Reward Chest `<li>` block — it's the one with `to="/my-chests"`. Change the route and label:

Find:
```html
                <router-link to="/my-chests" :class="{ active_item: $route.path === '/my-chests' }">
```
Replace with:
```html
                <router-link to="/rewards" :class="{ active_item: $route.path === '/rewards' }">
```

Find:
```html
                    <span>
                        Reward Chest
                    </span>
```
Replace with:
```html
                    <span>
                        Rewards
                    </span>
```

**7b.** Find the ENTIRE Exchange `<li>` block and DELETE it. It starts with:
```html
            <li>
                <router-link to="/exchange" :class="{ active_item: $route.path === '/exchange' }">
```
And ends with:
```html
                </router-link>
            </li>
```
Delete everything from that `<li>` to its closing `</li>`, including both SVG variants.

**After editing, run:** `npm run dev`

---

## Step 8 — Verify and commit

Run these checks:

1. `npm run dev` — should compile with zero errors
2. Navigate to `/rewards` — should show balances, 3 tabs, Battle Pass timeline
3. Click "Chests" tab — should show grid with 3 mock chests, filter pills
4. Click "Shop" tab — should show grid with 3 mock items, purchase history
5. Click "Ambar to Uru" button — should open convert modal
6. Navigate to `/my-chests` — should redirect to `/rewards?tab=chests`
7. Navigate to `/exchange` — should redirect to `/rewards?tab=shop`
8. Sidebar: should show "Rewards" (not "Reward Chest"), no "Exchange" item

**If all checks pass, commit:**

```bash
git add -A && git commit -m "feat: unified Rewards page with Battle Pass, Chests, and Shop tabs"
```

**Do NOT push yet. Wait for Max to review.**

---

## Files changed summary

| File | Action |
|------|--------|
| `resources/web/js/pages/Rewards.vue` | **NEW** — main rewards page |
| `resources/web/js/pages/RewardsComponents/BattlePass.vue` | **NEW** — battle pass timeline |
| `resources/web/js/pages/RewardsComponents/RewardsChests.vue` | **NEW** — chests grid |
| `resources/web/js/pages/RewardsComponents/RewardsShop.vue` | **NEW** — shop + purchase history |
| `resources/web/js/pages/RewardsComponents/ConvertModal.vue` | **NEW** — Ambar to Uru conversion modal |
| `resources/web/js/router/routes.js` | **MODIFIED** — add /rewards, redirect /exchange and /my-chests |
| `resources/web/js/components/sidebar.vue` | **MODIFIED** — rename Reward Chest → Rewards, remove Exchange |

## Files NOT touched (preserved as-is)

| File | Reason |
|------|--------|
| `resources/web/js/pages/MyChests.vue` | Keep — dead code, safe to leave |
| `resources/web/js/pages/Exchange.vue` | Keep — dead code, safe to leave |
| `resources/web/js/parts/chest-card.vue` | Keep — legacy, not used by new page |
| Backend files | No backend changes — all data mocked |

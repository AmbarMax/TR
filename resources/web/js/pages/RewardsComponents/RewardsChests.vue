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

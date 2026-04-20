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

<style lang="scss" scoped>
.rchests { padding-top: 4px; }

.rchests-filters { display: flex; gap: 8px; margin-bottom: 20px; }
.rchests-pill {
  padding: 8px 16px; font-size: 11px;
  font-family: var(--mono);
  letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--text-muted);
  border: 1px solid var(--border);
  cursor: pointer; transition: all 0.15s;
  background: none;
}
.rchests-pill:hover { color: var(--text); border-color: var(--text-dim); }
.rchests-pill.active {
  color: var(--bg); background: var(--accent);
  border-color: var(--accent);
  box-shadow: 0 0 12px var(--accent-glow);
}

.rchests-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.rchest-card {
  background: rgba(14,15,17,0.8);
  border: 1px solid rgba(42,44,46,0.8);
  overflow: hidden;
  transition: all 0.25s;
}
.rchest-card:hover {
  border-color: rgba(255,97,0,0.3);
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.4);
}

.rchest-image {
  height: 140px;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(180deg, rgba(26,28,31,0.6), rgba(14,15,17,0.8));
  position: relative;
}
.rchest-placeholder {
  width: 80px; height: 80px;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--display); font-size: 28px;
  color: var(--text-dim);
}
.rchest-badge {
  position: absolute; top: 10px; right: 10px;
  font-size: 9px; padding: 3px 8px;
  letter-spacing: 0.15em; text-transform: uppercase;
}
.rchest-badge-available {
  background: rgba(193,245,39,0.15); color: var(--accent);
  border: 1px solid rgba(193,245,39,0.3);
}
.rchest-badge-partial {
  background: rgba(255,97,0,0.15); color: var(--primary);
  border: 1px solid rgba(255,97,0,0.3);
}
.rchest-badge-locked {
  background: var(--surface-3); color: var(--text-dim);
  border: 1px solid var(--border);
}

.rchest-body { padding: 18px 22px; }
.rchest-name {
  font-family: var(--display); font-size: 22px;
  color: var(--text); letter-spacing: 0.02em;
  margin-bottom: 4px;
}
.rchest-desc {
  font-size: 11px; color: var(--text-muted);
  letter-spacing: 0.04em; margin-bottom: 14px; line-height: 1.5;
}
.rchest-req-label {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.25em; text-transform: uppercase;
  margin-bottom: 8px;
}
.rchest-reqs { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
.rchest-req {
  padding: 4px 10px; font-size: 10px;
  letter-spacing: 0.1em;
}
.rchest-req-met {
  background: rgba(255,97,0,0.12); color: var(--primary);
  border: 1px solid rgba(255,97,0,0.3);
}
.rchest-req-unmet {
  background: rgba(42,44,46,0.6); color: var(--text-muted);
  border: 1px solid var(--border);
}

.rchest-btn {
  width: 100%; padding: 11px 16px;
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.2em; text-transform: uppercase;
  text-align: center; cursor: pointer;
  transition: all 0.15s; border: none;
}
.rchest-btn-open {
  background: var(--accent); color: var(--bg);
  border: 1px solid var(--accent);
  box-shadow: 0 0 14px var(--accent-glow);
}
.rchest-btn-open:hover {
  background: #d4ff4a;
  box-shadow: 0 0 24px var(--accent-glow);
}
.rchest-btn-disabled {
  background: transparent; color: var(--text-muted);
  border: 1px solid var(--border);
  cursor: default;
}

.rchests-empty {
  padding: 40px 0; text-align: center;
  font-size: 13px; color: var(--text-dim);
  letter-spacing: 0.06em;
}

@media (max-width: 520px) {
  .rchests-grid { grid-template-columns: 1fr; }
}
</style>

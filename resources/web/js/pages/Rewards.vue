<template>
  <div class="rewards-page">

    <!-- PAGE HERO -->
    <div class="page-hero">
      <div class="page-hero-bg"></div>
      <div class="page-hero-inner">
        <div class="page-tag">Reward Chest</div>
        <div class="hero-title-row">
          <h1 class="page-title"><span class="accent-word">Rewards</span></h1>
          <button class="convert-btn" @click="showConvertModal = true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            Ambar → Uru
          </button>
        </div>
        <p class="page-subtitle">Spend your Uru to level up, open chests, or buy from the shop. Convert Ambar to Uru when you need more.</p>
      </div>
    </div>

    <!-- TABS BAR (sticky) -->
    <div class="tabs-bar">
      <button
        class="tab"
        :class="{ active: activeTab === 'battlepass' }"
        @click="activeTab = 'battlepass'"
      >Battle pass</button>
      <button
        class="tab"
        :class="{ active: activeTab === 'chests' }"
        @click="activeTab = 'chests'"
      >Chests</button>
      <button
        class="tab"
        :class="{ active: activeTab === 'shop' }"
        @click="activeTab = 'shop'"
      >Shop</button>
    </div>

    <!-- TAB CONTENT -->
    <div class="content">
      <BattlePass
        v-if="activeTab === 'battlepass'"
        :userUru="userUru"
        @buy-level="handleBuyLevel"
      />
      <RewardsChests
        v-if="activeTab === 'chests'"
      />
      <RewardsShop
        v-if="activeTab === 'shop'"
        :userUru="userUru"
        @refresh-balances="refreshBalances"
      />
    </div>

    <!-- TERMINAL STRIP -->
    <div class="terminal-strip">
      <div>rewards · {{ activeTab }}<span class="cursor-blink"></span></div>
      <div>balance · nominal</div>
    </div>

    <!-- CONVERT MODAL -->
    <ConvertModal
      v-if="showConvertModal"
      :userAmbar="userAmbar"
      @close="showConvertModal = false"
      @convert="handleConvert"
    />

  </div>
</template>

<script>
import { defineComponent } from "vue";
import api from "../api/api.js";
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
    async handleBuyLevel(level) {
      try {
        const resp = await api.post("/api/rewards/buy-level", { level_id: level.id });
        if (resp.data.success) {
          store.state.notification = { message: level.name + " unlocked!", type: "success", show: true };
          this.refreshBalances();
        } else {
          store.state.notification = { message: resp.data.message || "Purchase failed", type: "error", show: true };
        }
      } catch (e) {
        store.state.notification = { message: "Something went wrong", type: "error", show: true };
      }
    },
    async handleConvert(amount) {
      this.showConvertModal = false;
      try {
        const resp = await api.post("/api/rewards/convert", { ambar_amount: amount });
        if (resp.data.success) {
          store.state.notification = {
            message: "Converted " + amount + " Ambar → " + resp.data.uru_received + " Uru",
            type: "success",
            show: true,
          };
          this.refreshBalances();
        } else {
          store.state.notification = { message: resp.data.message || "Conversion failed", type: "error", show: true };
        }
      } catch (e) {
        store.state.notification = { message: "Something went wrong", type: "error", show: true };
      }
    },
    async refreshBalances() {
      try {
        const resp = await api.get("/api/profile/balances");
        if (resp.data.userBalances) {
          for (const bal of resp.data.userBalances) {
            if (store.state.user?.balances && bal.currency?.name) {
              store.state.user.balances[bal.currency.name] = Math.floor(bal.amount);
            }
          }
        }
      } catch (e) {}
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

<style lang="scss" scoped>
.rewards-page {
  min-width: 0;
  max-width: 100%;
}

/* Page Hero */
.page-hero {
  position: relative;
  padding: 40px 48px 0;
  overflow: hidden;
}
.page-hero-bg {
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 700px 400px at 90% 40%, rgba(255,97,0,0.1), transparent 60%),
    radial-gradient(ellipse 400px 300px at 90% 40%, rgba(193,245,39,0.03), transparent 65%);
}
.page-hero-inner {
  position: relative; z-index: 2;
}
.page-tag {
  display: inline-flex; align-items: center; gap: 10px;
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.32em; text-transform: uppercase;
  margin-bottom: 16px;
}
.page-tag::before {
  content: ''; width: 28px; height: 1px; background: var(--primary);
  box-shadow: 0 0 6px var(--primary);
}
.hero-title-row {
  display: flex; align-items: flex-end; justify-content: space-between;
  gap: 20px; flex-wrap: wrap;
}
.page-title {
  font-family: var(--display);
  font-size: 64px; line-height: 0.95;
  color: var(--text); letter-spacing: 0.015em;
  margin-bottom: 10px;
  text-shadow: 0 0 30px rgba(255,97,0,0.18);
}
.page-title .accent-word {
  color: var(--primary);
  text-shadow: 0 0 22px var(--primary-glow);
}
.page-subtitle {
  font-size: 13px; color: var(--text-muted);
  letter-spacing: 0.04em; line-height: 1.6;
  max-width: 580px; margin-bottom: 28px;
}

/* Convert button in hero */
.convert-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 10px 18px; font-size: 10px;
  font-family: var(--mono);
  letter-spacing: 0.18em; text-transform: uppercase;
  border: 1px solid rgba(255,97,0,0.3);
  color: var(--primary);
  background: rgba(14,15,17,0.6);
  cursor: pointer;
  transition: all 0.15s;
  margin-bottom: 10px;
}
.convert-btn:hover {
  border-color: var(--primary);
  background: rgba(255,97,0,0.08);
  box-shadow: 0 0 14px rgba(255,97,0,0.2);
}
.convert-btn svg { flex-shrink: 0; }

/* Tabs Bar — sticky below header */
.tabs-bar {
  padding: 0 48px;
  border-bottom: 1px solid rgba(255,97,0,0.1);
  display: flex; gap: 32px;
  background: rgba(5,5,8,0.5);
  position: sticky; top: 64px; z-index: 30;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}
.tab {
  padding: 18px 0;
  font-family: var(--mono);
  font-size: 11px; letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--text-muted);
  border: none; background: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: all 0.15s;
  cursor: pointer;
}
.tab:hover { color: var(--text); }
.tab.active {
  color: var(--primary);
  border-bottom-color: var(--primary);
  text-shadow: 0 0 10px var(--primary-glow);
}

/* Content area */
.content {
  padding: 48px;
  max-width: 1100px;
}

/* Terminal Strip */
.terminal-strip {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  display: flex; justify-content: space-between;
  padding: 20px 48px;
  border-top: 1px solid rgba(42,44,46,0.5);
}
.cursor-blink {
  display: inline-block; width: 8px; height: 11px;
  background: var(--primary);
  margin-left: 4px; vertical-align: middle;
  animation: blink 1s steps(1) infinite;
}
@keyframes blink { 50% { opacity: 0; } }

/* Responsive */
@media (max-width: 700px) {
  .page-hero { padding: 28px 20px 0; }
  .page-title { font-size: 44px; }
  .tabs-bar { padding: 0 20px; gap: 20px; top: 56px; }
  .content { padding: 24px 20px; }
  .terminal-strip { padding: 20px; }
  .hero-title-row { flex-direction: column; align-items: flex-start; }
}
</style>

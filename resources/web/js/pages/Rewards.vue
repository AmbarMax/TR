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
      console.log("Buy level:", level);
      store.state.notification = {
        message: "Level purchased! (mock — backend not connected yet)",
        type: "success",
        show: true,
      };
    },
    handleConvert(amount) {
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

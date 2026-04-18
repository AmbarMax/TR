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
import api from "../../api/api.js";
import store from "../../store/store.js";

export default defineComponent({
  props: {
    userUru: { type: Number, default: 0 },
  },
  emits: ["refresh-balances"],
  data() {
    return {
      shopItems: [],
      purchaseHistory: [],
    };
  },
  mounted() {
    this.fetchItems();
    this.fetchHistory();
  },
  methods: {
    async fetchItems() {
      try {
        const resp = await api.get("/api/rewards/shop-items");
        this.shopItems = (resp.data || []).map((i) => ({
          id: i.id,
          name: i.name,
          description: i.description,
          price: i.price_uru,
          icon: i.name.slice(0, 3).toUpperCase(),
        }));
      } catch (e) {
        this.shopItems = [];
      }
    },
    async fetchHistory() {
      try {
        const resp = await api.get("/api/rewards/purchase-history");
        this.purchaseHistory = (resp.data || []).map((p) => ({
          id: p.id,
          name: p.item_name,
          price: p.price_paid,
          status: p.status,
        }));
      } catch (e) {
        this.purchaseHistory = [];
      }
    },
    async buyItem(item) {
      if (this.userUru < item.price) return;
      try {
        const resp = await api.post("/api/rewards/buy-shop-item", { item_id: item.id });
        if (resp.data.success) {
          store.state.notification = { message: item.name + " purchased!", type: "success", show: true };
          this.fetchHistory();
          this.$emit("refresh-balances");
        } else {
          store.state.notification = { message: resp.data.message || "Purchase failed", type: "error", show: true };
        }
      } catch (e) {
        store.state.notification = { message: "Something went wrong", type: "error", show: true };
      }
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

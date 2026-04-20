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

<style lang="scss" scoped>
.rshop { padding-top: 4px; }
.rshop-curated {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.25em; text-transform: uppercase;
  margin-bottom: 24px;
}

.rshop-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px; margin-bottom: 48px;
}
.rshop-card {
  background: rgba(14,15,17,0.8);
  border: 1px solid rgba(42,44,46,0.8);
  overflow: hidden; transition: all 0.25s;
}
.rshop-card:hover {
  border-color: rgba(255,97,0,0.3);
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.4);
}

.rshop-image {
  height: 160px;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(180deg, rgba(26,28,31,0.6), rgba(14,15,17,0.8));
}
.rshop-image-placeholder {
  font-size: 12px; color: var(--text-dim);
  letter-spacing: 0.12em;
  padding: 12px 20px;
  background: rgba(42,44,46,0.4);
  border: 1px solid var(--border);
}

.rshop-body { padding: 18px 22px; }
.rshop-name {
  font-family: var(--display); font-size: 22px;
  color: var(--text); letter-spacing: 0.02em;
  margin-bottom: 3px;
}
.rshop-desc {
  font-size: 11px; color: var(--text-muted);
  letter-spacing: 0.04em; margin-bottom: 14px; line-height: 1.5;
}
.rshop-footer {
  display: flex; align-items: center; justify-content: space-between;
}
.rshop-price {
  font-family: var(--display); font-size: 22px;
  color: var(--accent);
  text-shadow: 0 0 10px var(--accent-glow);
  letter-spacing: 0.02em;
}
.rshop-buy-btn {
  font-family: var(--mono); font-size: 10px;
  color: var(--bg); background: var(--primary);
  border: 1px solid var(--primary);
  padding: 7px 16px;
  letter-spacing: 0.18em; text-transform: uppercase;
  cursor: pointer; transition: all 0.15s;
  box-shadow: 0 0 10px rgba(255,97,0,0.2);
}
.rshop-buy-btn:hover {
  background: #ff7e2e;
  box-shadow: 0 0 18px rgba(255,97,0,0.4);
}
.rshop-buy-btn:disabled {
  opacity: 0.4; cursor: default;
  box-shadow: none;
}

/* Purchase History */
.rshop-history {
  background: rgba(14,15,17,0.7);
  border: 1px solid rgba(42,44,46,0.7);
  padding: 24px;
}
.rshop-history-label {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.25em; text-transform: uppercase;
  margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}
.rshop-history-label::after {
  content: ''; flex: 1; height: 1px;
  background: linear-gradient(90deg, rgba(255,97,0,0.2), transparent);
}
.rshop-history-empty {
  font-size: 13px; color: var(--text-dim);
  text-align: center; padding: 20px 0;
}
.rshop-history-list { display: flex; flex-direction: column; gap: 4px; }
.rshop-history-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 10px 12px;
  background: rgba(26,28,31,0.5);
  border: 1px solid transparent;
  transition: border-color 0.15s;
}
.rshop-history-row:hover { border-color: rgba(255,97,0,0.1); }
.rshop-history-name { font-size: 12px; color: var(--text); flex: 1; }
.rshop-history-price { font-size: 12px; color: var(--text-muted); margin: 0 16px; }
.rshop-history-status {
  font-size: 9px; padding: 2px 8px;
  letter-spacing: 0.15em; text-transform: uppercase;
}
.rshop-status-delivered {
  background: rgba(193,245,39,0.15); color: var(--accent);
}
.rshop-status-pending {
  background: rgba(255,97,0,0.15); color: var(--primary);
}

@media (max-width: 520px) {
  .rshop-grid { grid-template-columns: 1fr; }
  .rshop-history-row { flex-wrap: wrap; gap: 4px; }
}
</style>

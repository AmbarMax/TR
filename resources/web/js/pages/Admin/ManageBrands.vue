<template>
  <div class="manage-brands">
    <div class="mb-toolbar">
      <div class="mb-toolbar-left">
        <h2 class="mb-section-title">Brands</h2>
        <p class="mb-section-meta">{{ totalLabel }}</p>
      </div>
      <div class="mb-toolbar-right">
        <input
          v-model="searchQuery"
          type="text"
          class="mb-search-input"
          placeholder="Search username or name…"
          @input="onSearchInput"
        />
        <button class="mb-btn mb-btn-primary" type="button" @click="openPromote">
          + Promote user
        </button>
      </div>
    </div>

    <div class="mb-status-tabs" role="tablist">
      <button
        v-for="opt in statusOptions"
        :key="opt.value"
        type="button"
        role="tab"
        :class="['mb-status-tab', { 'is-active': selectedStatus === opt.value }]"
        :aria-selected="selectedStatus === opt.value"
        @click="selectStatus(opt.value)"
      >
        <span class="mb-status-tab-label">{{ opt.label }}</span>
        <span
          v-if="opt.value === 'pending' && pendingCount > 0"
          class="mb-status-badge"
        >{{ pendingCount }}</span>
      </button>
    </div>

    <div v-if="error" class="mb-error">{{ error }}</div>

    <BrandsTable
      :brands="brands"
      :loading="loading"
      :search-query="searchQuery"
      :sort-key="sort.key"
      :sort-order="sort.order"
      @sort="onSort"
      @edit="openEdit"
      @demote="onDemote"
      @approve="onApprove"
      @reject="onReject"
      @reapprove="onReapprove"
    />

    <PromoteUserModal
      :show="promoteModalOpen"
      @close="promoteModalOpen = false"
      @promoted="onPromoted"
    />

    <EditBrandModal
      :show="editModalOpen"
      :brand="editingBrand"
      @close="closeEdit"
      @saved="onSaved"
    />

    <transition name="mb-toast-fade">
      <div v-if="toast" class="mb-toast" :class="`mb-toast--${toastType}`">{{ toast }}</div>
    </transition>
  </div>
</template>

<script>
import api from "../../api/api.js";
import BrandsTable from "./components/BrandsTable.vue";
import PromoteUserModal from "./components/PromoteUserModal.vue";
import EditBrandModal from "./components/EditBrandModal.vue";

export default {
  name: "ManageBrands",
  components: { BrandsTable, PromoteUserModal, EditBrandModal },
  data() {
    return {
      brands: [],
      meta: null,
      loading: false,
      error: "",

      searchQuery: "",
      searchDebounceId: null,

      sort: { key: "created_at", order: "desc" },

      // null = first load not yet resolved. Decided in loadBrands() based on
      // meta.status_counts.pending: 'pending' if any pending brand exists,
      // otherwise 'all'. Once resolved, never null again.
      selectedStatus: null,
      statusOptions: [
        { value: "all",      label: "All" },
        { value: "pending",  label: "Pending" },
        { value: "active",   label: "Active" },
        { value: "rejected", label: "Rejected" },
      ],

      promoteModalOpen: false,
      editModalOpen: false,
      editingBrand: null,

      toast: "",
      toastType: "info",
      toastTimer: null,
    };
  },
  computed: {
    totalLabel() {
      const total = this.meta?.total ?? this.brands.length;
      if (!total) return "No brands yet";
      return `${total} ${total === 1 ? "brand" : "brands"} total`;
    },
    pendingCount() {
      return this.meta?.status_counts?.pending ?? 0;
    },
  },
  mounted() {
    this.loadBrands();
  },
  beforeUnmount() {
    clearTimeout(this.searchDebounceId);
    clearTimeout(this.toastTimer);
  },
  methods: {
    async loadBrands() {
      this.loading = true;
      this.error = "";
      try {
        const params = {
          q: this.searchQuery || undefined,
          sort: this.sort.key,
          order: this.sort.order,
        };
        // Only forward status when an explicit non-'all' filter is selected.
        // null means "first load — don't filter, decide afterwards from counts".
        if (this.selectedStatus && this.selectedStatus !== "all") {
          params.status = this.selectedStatus;
        }

        const { data } = await api.get("/api/admin/brands", { params });
        this.brands = data?.data ?? [];
        this.meta = data?.meta ?? null;

        // First-load default landing: if any pending brand exists, jump to the
        // Pending tab automatically (admin queue first). Otherwise default to All.
        // The pending re-fetch is intentional — server-side pagination needs the
        // filter applied to return the correct slice.
        if (this.selectedStatus === null) {
          const pending = this.meta?.status_counts?.pending ?? 0;
          if (pending > 0) {
            this.selectedStatus = "pending";
            this.loading = false;
            return this.loadBrands();
          }
          this.selectedStatus = "all";
        }
      } catch (e) {
        this.error = e.response?.data?.message || "Failed to load brands.";
        this.brands = [];
        if (this.selectedStatus === null) {
          this.selectedStatus = "all";
        }
      } finally {
        this.loading = false;
      }
    },

    selectStatus(value) {
      if (this.selectedStatus === value) return;
      this.selectedStatus = value;
      this.loadBrands();
    },

    onSearchInput() {
      clearTimeout(this.searchDebounceId);
      this.searchDebounceId = setTimeout(() => this.loadBrands(), 300);
    },

    onSort(key) {
      if (this.sort.key === key) {
        this.sort.order = this.sort.order === "asc" ? "desc" : "asc";
      } else {
        this.sort.key = key;
        this.sort.order = "desc";
      }
      this.loadBrands();
    },

    openPromote() {
      this.promoteModalOpen = true;
    },

    onPromoted(brand) {
      this.promoteModalOpen = false;
      this.flashToast(`Promoted @${brand?.username} to brand`, "success");
      this.loadBrands();
    },

    openEdit(brand) {
      this.editingBrand = brand;
      this.editModalOpen = true;
    },

    closeEdit() {
      this.editModalOpen = false;
      this.editingBrand = null;
    },

    onSaved(updated) {
      const idx = this.brands.findIndex(b => b.id === updated?.id);
      if (idx >= 0 && updated) {
        this.brands.splice(idx, 1, updated);
      }
      this.flashToast(`Saved @${updated?.username}`, "success");
      this.closeEdit();
    },

    async onDemote(brand) {
      try {
        await api.delete(`/api/admin/brands/${encodeURIComponent(brand.username)}/demote`);
        this.flashToast(`Demoted @${brand.username} back to player`, "success");
        this.loadBrands();
      } catch (e) {
        this.flashToast(e.response?.data?.message || "Demote failed", "error");
      }
    },

    async onApprove(brand) {
      try {
        await api.post(`/api/admin/brands/${encodeURIComponent(brand.username)}/approve`);
        this.flashToast(`Approved @${brand.username}`, "success");
        this.loadBrands();
      } catch (e) {
        this.flashToast(e.response?.data?.message || "Approve failed", "error");
      }
    },

    async onReject(brand) {
      if (!window.confirm(`Reject @${brand.username}? Their public hall will stay locked and trophy creation remains disabled.`)) return;
      try {
        await api.post(`/api/admin/brands/${encodeURIComponent(brand.username)}/reject`);
        this.flashToast(`Rejected @${brand.username}`, "success");
        this.loadBrands();
      } catch (e) {
        this.flashToast(e.response?.data?.message || "Reject failed", "error");
      }
    },

    async onReapprove(brand) {
      if (!window.confirm(`Re-approve @${brand.username}? Their account will become active and unlock the brand features.`)) return;
      try {
        await api.post(`/api/admin/brands/${encodeURIComponent(brand.username)}/reapprove`);
        this.flashToast(`Re-approved @${brand.username}`, "success");
        this.loadBrands();
      } catch (e) {
        this.flashToast(e.response?.data?.message || "Re-approve failed", "error");
      }
    },

    flashToast(msg, type = "info") {
      this.toast = msg;
      this.toastType = type;
      clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => { this.toast = ""; }, 2400);
    },
  },
};
</script>

<style>
/* All selectors prefixed with .manage-brands so styles never leak. The
   wrapper itself is .manage-brands, modals/toasts mount as descendants. */
.manage-brands {
  --mb-bg: #000003;
  --mb-surface: #0e0f11;
  --mb-surface-2: #1a1c1f;
  --mb-border: #2a2c2e;
  --mb-text: #feeddf;
  --mb-text-muted: #9a9590;
  --mb-text-dim: #5a5550;
  --mb-primary: #ff6100;
  --mb-primary-glow: rgba(255, 97, 0, 0.45);
  --mb-accent: #c1f527;
  --mb-accent-glow: rgba(193, 245, 39, 0.35);
  --mb-danger: #e11b2b;
  --mb-mono: 'Share Tech Mono', monospace;
  --mb-display: 'VT323', monospace;
  font-family: var(--mb-mono);
  color: var(--mb-text);
  font-size: 13px;
}

.manage-brands .mb-toolbar {
  display: flex; align-items: flex-end; justify-content: space-between;
  gap: 16px; flex-wrap: wrap;
  margin-bottom: 24px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--mb-border);
}
.manage-brands .mb-toolbar-left { display: flex; flex-direction: column; gap: 4px; }
.manage-brands .mb-toolbar-right { display: flex; gap: 10px; align-items: center; }
.manage-brands .mb-section-title {
  font-family: var(--mb-display);
  font-size: 28px; line-height: 1; letter-spacing: 0.015em;
  color: var(--mb-text); margin: 0;
}
.manage-brands .mb-section-meta {
  font-size: 10px; color: var(--mb-text-dim);
  letter-spacing: 0.18em; text-transform: uppercase; margin: 0;
}

.manage-brands .mb-search-input {
  padding: 9px 14px;
  font-family: var(--mb-mono); font-size: 12px;
  background: var(--mb-surface); color: var(--mb-text);
  border: 1px solid var(--mb-border);
  width: 280px; max-width: 100%;
  transition: border-color 0.15s;
}
.manage-brands .mb-search-input:focus { outline: none; border-color: var(--mb-primary); }

/* Status filter — segmented control row below the toolbar */
.manage-brands .mb-status-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 16px;
  border-bottom: 1px solid var(--mb-border);
}
.manage-brands .mb-status-tab {
  display: inline-flex; align-items: center; gap: 8px;
  appearance: none;
  background: transparent;
  border: none;
  border-bottom: 1px solid transparent;
  padding: 10px 16px;
  margin-bottom: -1px;
  font-family: var(--mb-mono);
  font-size: 12px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--mb-text-dim);
  cursor: pointer;
  transition: color 0.15s, border-color 0.15s;
}
.manage-brands .mb-status-tab:hover { color: var(--mb-text); }
.manage-brands .mb-status-tab.is-active {
  color: var(--mb-text);
  border-bottom-color: var(--mb-primary);
}
.manage-brands .mb-status-badge {
  display: inline-block;
  padding: 2px 6px;
  font-size: 10px;
  letter-spacing: 0.1em;
  background: rgba(255, 184, 0, 0.15);
  color: #ffb800;
  border-radius: 2px;
}

.manage-brands .mb-error {
  padding: 12px 16px;
  background: rgba(225, 27, 43, 0.08);
  border: 1px solid rgba(225, 27, 43, 0.4);
  color: var(--mb-danger);
  margin-bottom: 16px;
  font-size: 12px;
}

/* Table */
.manage-brands .brands-table-wrap { overflow-x: auto; }
.manage-brands .brands-loading,
.manage-brands .brands-empty {
  padding: 40px 20px; text-align: center;
  color: var(--mb-text-muted); font-size: 12px;
  border: 1px dashed var(--mb-border);
}
.manage-brands .brands-table {
  width: 100%; border-collapse: collapse;
  font-size: 12px;
  background: var(--mb-surface);
  border: 1px solid var(--mb-border);
}
.manage-brands .brands-table th,
.manage-brands .brands-table td {
  padding: 12px 14px; text-align: left;
  border-bottom: 1px solid var(--mb-border);
  white-space: nowrap;
}
.manage-brands .brands-table th {
  font-size: 10px;
  letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--mb-text-dim);
  background: rgba(14, 15, 17, 0.6);
  font-weight: 400;
}
.manage-brands .brands-table .th-sortable { cursor: pointer; user-select: none; transition: color 0.15s; }
.manage-brands .brands-table .th-sortable:hover { color: var(--mb-text); }
.manage-brands .brands-table .sort-arrow { opacity: 0.55; margin-left: 2px; }
.manage-brands .brands-table tbody tr { transition: background 0.15s; }
.manage-brands .brands-table tbody tr:hover { background: rgba(255, 97, 0, 0.04); }
.manage-brands .brands-table tbody tr:last-child td { border-bottom: none; }
.manage-brands .th-avatar { width: 48px; padding: 0 0 0 14px; }
.manage-brands .td-avatar { padding: 8px 0 8px 14px; width: 48px; }
.manage-brands .avatar-cell {
  --row-accent: #ff6100;
  width: 32px; height: 32px;
  border-radius: 4px;
  background: var(--mb-surface-2);
  border: 1px solid var(--row-accent);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mb-display); font-size: 16px;
  color: var(--row-accent);
  overflow: hidden;
}
.manage-brands .avatar-cell img { width: 100%; height: 100%; object-fit: cover; }
.manage-brands .td-username { color: var(--mb-text); letter-spacing: 0.04em; }
.manage-brands .td-name { color: var(--mb-text-muted); }
.manage-brands .td-num { font-family: var(--mb-display); font-size: 16px; color: var(--mb-text); }
.manage-brands .accent-chip {
  display: inline-block;
  width: 14px; height: 14px;
  border-radius: 3px;
  border: 1px solid var(--mb-border);
  vertical-align: middle;
  margin-right: 6px;
}
.manage-brands .accent-hex { font-size: 11px; color: var(--mb-text-muted); vertical-align: middle; }
.manage-brands .pill-yes,
.manage-brands .pill-no {
  display: inline-flex; align-items: center;
  padding: 3px 8px; border-radius: 3px;
  font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
}
.manage-brands .pill-yes { background: rgba(193, 245, 39, 0.12); color: var(--mb-accent); border: 1px solid rgba(193, 245, 39, 0.3); }
.manage-brands .pill-no  { background: transparent; color: var(--mb-text-dim); border: 1px solid var(--mb-border); }
.manage-brands .muted { color: var(--mb-text-dim); }
.manage-brands .td-actions { text-align: right; padding-right: 14px; }
.manage-brands .row-btn {
  display: inline-flex; align-items: center;
  padding: 6px 12px; margin-left: 6px;
  font-family: var(--mb-mono); font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  background: transparent; color: var(--mb-text-muted);
  border: 1px solid var(--mb-border);
  cursor: pointer; transition: all 0.15s;
}
.manage-brands .row-btn:hover { color: var(--mb-text); border-color: var(--mb-text-dim); }
.manage-brands .row-btn--danger { color: var(--mb-danger); border-color: rgba(225, 27, 43, 0.35); }
.manage-brands .row-btn--danger:hover { background: var(--mb-danger); color: var(--mb-bg); border-color: var(--mb-danger); }
.manage-brands .row-btn--primary { color: var(--mb-accent); border-color: rgba(193, 245, 39, 0.45); }
.manage-brands .row-btn--primary:hover { background: var(--mb-accent); color: var(--mb-bg); border-color: var(--mb-accent); }

/* Buttons */
.manage-brands .mb-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 9px 16px;
  font-family: var(--mb-mono); font-size: 11px;
  letter-spacing: 0.2em; text-transform: uppercase;
  border: 1px solid var(--mb-border);
  background: transparent; color: var(--mb-text);
  cursor: pointer; transition: all 0.18s;
}
.manage-brands .mb-btn-primary {
  background: var(--mb-primary); color: var(--mb-bg);
  border-color: var(--mb-primary);
  box-shadow: 0 0 18px var(--mb-primary-glow);
}
.manage-brands .mb-btn-primary:hover:not(:disabled) {
  background: transparent; color: var(--mb-primary);
}
.manage-brands .mb-btn-ghost { color: var(--mb-text-muted); }
.manage-brands .mb-btn-ghost:hover:not(:disabled) { color: var(--mb-text); border-color: var(--mb-text-dim); }
.manage-brands .mb-btn:disabled { opacity: 0.5; cursor: default; }

/* Modals — shared shell for promote + edit */
.manage-brands .mb-modal {
  position: fixed; inset: 0; z-index: 1300;
  display: flex; align-items: center; justify-content: center;
  padding: 24px;
  background: rgba(0, 0, 3, 0.78);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.manage-brands .mb-card {
  position: relative;
  width: 100%; max-width: 460px;
  background: var(--mb-surface);
  border: 1px solid var(--mb-border);
  padding: 32px 28px 24px;
  box-shadow: 0 0 0 1px var(--mb-primary-glow), 0 30px 80px rgba(0, 0, 0, 0.6);
}
.manage-brands .mb-card--wide { max-width: 560px; }
.manage-brands .mb-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: var(--mb-primary); box-shadow: 0 0 12px var(--mb-primary-glow);
}
.manage-brands .mb-close {
  position: absolute; top: 12px; right: 12px;
  width: 30px; height: 30px;
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--mb-text-muted); background: transparent;
  border: 1px solid transparent; cursor: pointer;
  transition: all 0.15s;
}
.manage-brands .mb-close:hover { color: var(--mb-text); border-color: var(--mb-border); }
.manage-brands .mb-title {
  font-family: var(--mb-display);
  font-size: 26px; line-height: 1.05; letter-spacing: 0.015em;
  margin-bottom: 12px;
}
.manage-brands .mb-body {
  font-size: 12px; color: var(--mb-text-muted);
  line-height: 1.55; margin-bottom: 20px;
}
.manage-brands .mb-body code {
  background: var(--mb-surface-2);
  padding: 1px 5px; border-radius: 2px;
  font-family: var(--mb-mono); font-size: 11px;
  color: var(--mb-accent);
}

/* Promote search */
.manage-brands .mb-search {
  width: 100%;
  padding: 10px 14px;
  font-family: var(--mb-mono); font-size: 13px;
  background: var(--mb-bg); color: var(--mb-text);
  border: 1px solid var(--mb-border);
  margin-bottom: 12px;
  transition: border-color 0.15s;
}
.manage-brands .mb-search:focus { outline: none; border-color: var(--mb-primary); }
.manage-brands .mb-results {
  max-height: 280px; overflow-y: auto;
  border: 1px solid var(--mb-border);
  background: var(--mb-bg);
  margin-bottom: 16px;
}
.manage-brands .mb-state {
  padding: 24px 16px; text-align: center;
  color: var(--mb-text-muted); font-size: 12px;
}
.manage-brands .mb-state-hint { color: var(--mb-text-dim); }
.manage-brands .mb-result-row {
  display: flex; align-items: center; gap: 12px;
  width: 100%; padding: 10px 14px;
  background: transparent; border: none;
  border-bottom: 1px solid rgba(42, 44, 46, 0.4);
  text-align: left; cursor: pointer;
  transition: background 0.15s;
  color: var(--mb-text);
}
.manage-brands .mb-result-row:last-child { border-bottom: none; }
.manage-brands .mb-result-row:hover { background: rgba(255, 97, 0, 0.04); }
.manage-brands .mb-result-row.selected { background: rgba(255, 97, 0, 0.12); }
.manage-brands .mb-result-avatar {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, #f5c547, #d98c3a);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mb-display); font-size: 14px; color: var(--mb-bg);
  flex-shrink: 0;
}
.manage-brands .mb-result-info { flex: 1; min-width: 0; }
.manage-brands .mb-result-name { font-size: 13px; color: var(--mb-text); }
.manage-brands .mb-result-meta { font-size: 11px; color: var(--mb-text-dim); }
.manage-brands .mb-result-check {
  font-size: 16px; color: var(--mb-accent);
  text-shadow: 0 0 8px var(--mb-accent);
}

/* Edit form */
.manage-brands .mb-form { display: flex; flex-direction: column; gap: 16px; }
.manage-brands .mb-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.manage-brands .mb-field { display: flex; flex-direction: column; gap: 6px; }
.manage-brands .mb-label {
  font-size: 10px; letter-spacing: 0.22em; text-transform: uppercase;
  color: var(--mb-text-dim);
}
.manage-brands .mb-hint { color: var(--mb-text-dim); font-size: 9px; letter-spacing: 0.15em; margin-left: 6px; }
.manage-brands .mb-input {
  padding: 9px 12px;
  font-family: var(--mb-mono); font-size: 13px;
  background: var(--mb-bg); color: var(--mb-text);
  border: 1px solid var(--mb-border);
  transition: border-color 0.15s;
}
.manage-brands .mb-input:focus { outline: none; border-color: var(--mb-primary); }
.manage-brands .mb-accent-input { display: flex; align-items: center; gap: 8px; }
.manage-brands .mb-color-picker {
  width: 36px; height: 36px;
  padding: 0; border: 1px solid var(--mb-border);
  background: transparent; cursor: pointer;
}
.manage-brands .mb-toggle {
  display: inline-flex; align-items: center; gap: 10px;
  cursor: pointer;
}
.manage-brands .mb-toggle input { position: absolute; opacity: 0; pointer-events: none; }
.manage-brands .mb-toggle-track {
  position: relative;
  width: 36px; height: 20px;
  background: var(--mb-surface-2);
  border: 1px solid var(--mb-border);
  border-radius: 12px;
  transition: background 0.15s, border-color 0.15s;
}
.manage-brands .mb-toggle-track::after {
  content: '';
  position: absolute; top: 2px; left: 2px;
  width: 14px; height: 14px;
  border-radius: 50%;
  background: var(--mb-text-muted);
  transition: transform 0.18s, background 0.15s;
}
.manage-brands .mb-toggle input:checked + .mb-toggle-track {
  background: rgba(193, 245, 39, 0.2);
  border-color: var(--mb-accent);
}
.manage-brands .mb-toggle input:checked + .mb-toggle-track::after {
  transform: translateX(16px);
  background: var(--mb-accent);
  box-shadow: 0 0 6px var(--mb-accent);
}
.manage-brands .mb-toggle-text { font-size: 12px; color: var(--mb-text); letter-spacing: 0.04em; }
.manage-brands .mb-field-errors {
  background: rgba(225, 27, 43, 0.08);
  border: 1px solid rgba(225, 27, 43, 0.3);
  padding: 10px 12px;
  font-size: 11px; color: var(--mb-danger);
}
.manage-brands .mb-field-errors ul { margin: 0; padding-left: 18px; }
.manage-brands .mb-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }

/* Toast */
.manage-brands .mb-toast {
  position: fixed; bottom: 32px; right: 32px;
  padding: 12px 20px;
  background: var(--mb-surface);
  border: 1px solid var(--mb-border);
  font-size: 12px; letter-spacing: 0.04em;
  z-index: 1400;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
}
.manage-brands .mb-toast--success { border-color: rgba(193, 245, 39, 0.4); color: var(--mb-accent); }
.manage-brands .mb-toast--error { border-color: rgba(225, 27, 43, 0.4); color: var(--mb-danger); }

.manage-brands .mb-fade-enter-active,
.manage-brands .mb-fade-leave-active { transition: opacity 0.18s ease; }
.manage-brands .mb-fade-enter-from,
.manage-brands .mb-fade-leave-to { opacity: 0; }
.manage-brands .mb-toast-fade-enter-active,
.manage-brands .mb-toast-fade-leave-active { transition: opacity 0.18s, transform 0.18s; }
.manage-brands .mb-toast-fade-enter-from,
.manage-brands .mb-toast-fade-leave-to { opacity: 0; transform: translateY(10px); }

@media (max-width: 720px) {
  .manage-brands .mb-toolbar-right { width: 100%; }
  .manage-brands .mb-search-input { flex: 1; }
  .manage-brands .mb-field-row { grid-template-columns: 1fr; }
}
</style>

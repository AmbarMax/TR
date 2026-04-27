<template>
  <div class="manage-roles">
    <div class="mr-toolbar">
      <div class="mr-toolbar-left">
        <h2 class="mr-section-title">Roles</h2>
        <p class="mr-section-meta">{{ totalLabel }}</p>
      </div>
      <div class="mr-toolbar-right">
        <input
          v-model="searchQuery"
          type="text"
          class="mr-search-input"
          placeholder="Search staff by username or name…"
          @input="onSearchInput"
        />
        <button class="mr-btn mr-btn-primary" type="button" @click="openAssign(null)">+ Assign role</button>
      </div>
    </div>

    <div class="mr-filter-pills">
      <button
        v-for="f in filters"
        :key="f.key"
        class="mr-pill"
        :class="{ 'mr-pill--active': roleFilter === f.key }"
        type="button"
        @click="setFilter(f.key)"
      >
        {{ f.label }}
      </button>
    </div>

    <div v-if="error" class="mr-error">{{ error }}</div>

    <RolesTable
      :users="users"
      :loading="loading"
      :search-query="searchQuery"
      :role-filter="roleFilter"
      :caller-is-super="callerIsSuper"
      @revoke="onRevoke"
      @assign-to="openAssign"
    />

    <AssignRoleModal
      :show="assignModalOpen"
      :locked-user="lockedUser"
      :caller-is-super="callerIsSuper"
      @close="closeAssign"
      @assigned="onAssigned"
    />

    <transition name="mr-toast-fade">
      <div v-if="toast" class="mr-toast" :class="`mr-toast--${toastType}`">{{ toast }}</div>
    </transition>
  </div>
</template>

<script>
import api from "../../api/api.js";
import RolesTable from "./components/RolesTable.vue";
import AssignRoleModal from "./components/AssignRoleModal.vue";

const FILTERS = [
  { key: "staff",         label: "All staff" },
  { key: "tr_moderator",  label: "Moderators" },
  { key: "tr_admin",      label: "Admins" },
  { key: "tr_superadmin", label: "Super Admins" },
];

export default {
  name: "ManageRoles",
  components: { RolesTable, AssignRoleModal },
  data() {
    return {
      users: [],
      loading: false,
      error: "",

      searchQuery: "",
      searchDebounceId: null,
      roleFilter: "staff",
      filters: FILTERS,

      assignModalOpen: false,
      lockedUser: null,

      toast: "",
      toastType: "info",
      toastTimer: null,
    };
  },
  computed: {
    callerIsSuper() {
      return this.$store?.getters?.isSuperAdmin ?? false;
    },
    totalLabel() {
      const total = this.users.length;
      const role = this.filters.find(f => f.key === this.roleFilter);
      const noun = role ? role.label.toLowerCase() : "staff";
      if (!total) return `No ${noun} yet`;
      return `${total} ${total === 1 ? "user" : "users"} · ${noun}`;
    },
  },
  mounted() {
    this.loadUsers();
  },
  beforeUnmount() {
    clearTimeout(this.searchDebounceId);
    clearTimeout(this.toastTimer);
  },
  methods: {
    async loadUsers() {
      this.loading = true;
      this.error = "";
      try {
        const { data } = await api.get("/api/admin/users/search-for-role", {
          params: {
            q: this.searchQuery || undefined,
            role: this.roleFilter,
          },
        });
        this.users = data?.data ?? [];
      } catch (e) {
        this.error = e.response?.data?.message || "Failed to load staff.";
        this.users = [];
      } finally {
        this.loading = false;
      }
    },

    onSearchInput() {
      clearTimeout(this.searchDebounceId);
      this.searchDebounceId = setTimeout(() => this.loadUsers(), 300);
    },

    setFilter(key) {
      this.roleFilter = key;
      this.loadUsers();
    },

    openAssign(user) {
      this.lockedUser = user || null;
      this.assignModalOpen = true;
    },

    closeAssign() {
      this.assignModalOpen = false;
      this.lockedUser = null;
    },

    onAssigned({ user, role }) {
      this.flashToast(`Assigned ${role} to @${user?.username}`, "success");
      this.closeAssign();
      this.loadUsers();
    },

    async onRevoke({ user, role }) {
      try {
        await api.delete(
          `/api/admin/users/${encodeURIComponent(user.username)}/roles/${encodeURIComponent(role)}`
        );
        this.flashToast(`Revoked ${role} from @${user.username}`, "success");
        this.loadUsers();
      } catch (e) {
        const msg = e.response?.data?.message || "Revoke failed.";
        this.flashToast(msg, "error");
      }
    },

    flashToast(msg, type = "info") {
      this.toast = msg;
      this.toastType = type;
      clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => { this.toast = ""; }, 2800);
    },
  },
};
</script>

<style>
/* All selectors prefixed with .manage-roles so styles never leak. */
.manage-roles {
  --mr-bg: #000003;
  --mr-surface: #0e0f11;
  --mr-surface-2: #1a1c1f;
  --mr-border: #2a2c2e;
  --mr-text: #feeddf;
  --mr-text-muted: #9a9590;
  --mr-text-dim: #5a5550;
  --mr-primary: #ff6100;
  --mr-primary-glow: rgba(255, 97, 0, 0.45);
  --mr-accent: #c1f527;
  --mr-accent-glow: rgba(193, 245, 39, 0.35);
  --mr-danger: #e11b2b;
  --mr-mod: #66c0f4;
  --mr-admin: #ff6100;
  --mr-super: #c1f527;
  --mr-mono: 'Share Tech Mono', monospace;
  --mr-display: 'VT323', monospace;
  font-family: var(--mr-mono);
  color: var(--mr-text);
  font-size: 13px;
}

.manage-roles .mr-toolbar {
  display: flex; align-items: flex-end; justify-content: space-between;
  gap: 16px; flex-wrap: wrap;
  margin-bottom: 18px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--mr-border);
}
.manage-roles .mr-toolbar-left { display: flex; flex-direction: column; gap: 4px; }
.manage-roles .mr-toolbar-right { display: flex; gap: 10px; align-items: center; }
.manage-roles .mr-section-title {
  font-family: var(--mr-display);
  font-size: 28px; line-height: 1; letter-spacing: 0.015em;
  color: var(--mr-text); margin: 0;
}
.manage-roles .mr-section-meta {
  font-size: 10px; color: var(--mr-text-dim);
  letter-spacing: 0.18em; text-transform: uppercase; margin: 0;
}

.manage-roles .mr-search-input {
  padding: 9px 14px;
  font-family: var(--mr-mono); font-size: 12px;
  background: var(--mr-surface); color: var(--mr-text);
  border: 1px solid var(--mr-border);
  width: 280px; max-width: 100%;
  transition: border-color 0.15s;
}
.manage-roles .mr-search-input:focus { outline: none; border-color: var(--mr-primary); }

/* Filter pills */
.manage-roles .mr-filter-pills {
  display: flex; gap: 8px; flex-wrap: wrap;
  margin-bottom: 18px;
}
.manage-roles .mr-pill {
  padding: 7px 14px;
  font-family: var(--mr-mono); font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  background: transparent;
  color: var(--mr-text-muted);
  border: 1px solid var(--mr-border);
  cursor: pointer; transition: all 0.15s;
}
.manage-roles .mr-pill:hover { color: var(--mr-text); border-color: var(--mr-text-dim); }
.manage-roles .mr-pill--active {
  background: var(--mr-accent); color: var(--mr-bg); border-color: var(--mr-accent);
  box-shadow: 0 0 12px var(--mr-accent-glow);
}

.manage-roles .mr-error {
  padding: 12px 16px;
  background: rgba(225, 27, 43, 0.08);
  border: 1px solid rgba(225, 27, 43, 0.4);
  color: var(--mr-danger);
  margin-bottom: 16px;
  font-size: 12px;
}

/* Table */
.manage-roles .roles-table-wrap { overflow-x: auto; }
.manage-roles .roles-loading,
.manage-roles .roles-empty {
  padding: 40px 20px; text-align: center;
  color: var(--mr-text-muted); font-size: 12px;
  border: 1px dashed var(--mr-border);
}
.manage-roles .roles-table {
  width: 100%; border-collapse: collapse;
  font-size: 12px;
  background: var(--mr-surface);
  border: 1px solid var(--mr-border);
}
.manage-roles .roles-table th,
.manage-roles .roles-table td {
  padding: 12px 14px; text-align: left;
  border-bottom: 1px solid var(--mr-border);
  white-space: nowrap;
}
.manage-roles .roles-table th {
  font-size: 10px;
  letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--mr-text-dim);
  background: rgba(14, 15, 17, 0.6);
  font-weight: 400;
}
.manage-roles .roles-table tbody tr { transition: background 0.15s; }
.manage-roles .roles-table tbody tr:hover { background: rgba(255, 97, 0, 0.04); }
.manage-roles .roles-table tbody tr:last-child td { border-bottom: none; }
.manage-roles .th-avatar { width: 48px; padding: 0 0 0 14px; }
.manage-roles .td-avatar { padding: 8px 0 8px 14px; width: 48px; }
.manage-roles .avatar-cell {
  width: 32px; height: 32px;
  border-radius: 4px;
  background: var(--mr-surface-2);
  border: 1px solid var(--mr-border);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mr-display); font-size: 14px;
  color: var(--mr-text);
  overflow: hidden;
}
.manage-roles .avatar-cell img { width: 100%; height: 100%; object-fit: cover; }
.manage-roles .td-username { color: var(--mr-text); letter-spacing: 0.04em; }
.manage-roles .td-name { color: var(--mr-text-muted); }
.manage-roles .account-pill {
  display: inline-flex;
  padding: 3px 8px; border-radius: 3px;
  font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
  background: rgba(42, 44, 46, 0.5);
  color: var(--mr-text-muted);
  border: 1px solid var(--mr-border);
}
.manage-roles .td-roles { display: flex; gap: 6px; flex-wrap: wrap; padding: 12px 14px; }
.manage-roles .role-chip {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 4px 8px; border-radius: 3px;
  font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
  border: 1px solid var(--mr-border);
}
.manage-roles .role-chip--tr_moderator {
  background: rgba(102, 192, 244, 0.1); color: var(--mr-mod); border-color: rgba(102, 192, 244, 0.35);
}
.manage-roles .role-chip--tr_admin {
  background: rgba(255, 97, 0, 0.12); color: var(--mr-admin); border-color: rgba(255, 97, 0, 0.35);
}
.manage-roles .role-chip--tr_superadmin {
  background: rgba(193, 245, 39, 0.12); color: var(--mr-super); border-color: rgba(193, 245, 39, 0.35);
}
.manage-roles .role-chip-revoke {
  display: inline-flex; align-items: center; justify-content: center;
  width: 14px; height: 14px;
  background: transparent; border: none;
  color: inherit; opacity: 0.55;
  cursor: pointer;
  font-size: 14px; line-height: 1;
  padding: 0;
  transition: opacity 0.15s;
}
.manage-roles .role-chip-revoke:hover { opacity: 1; }
.manage-roles .muted { color: var(--mr-text-dim); }
.manage-roles .td-actions { text-align: right; padding-right: 14px; }
.manage-roles .row-btn {
  display: inline-flex; align-items: center;
  padding: 6px 12px;
  font-family: var(--mr-mono); font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  background: transparent; color: var(--mr-text-muted);
  border: 1px solid var(--mr-border);
  cursor: pointer; transition: all 0.15s;
}
.manage-roles .row-btn:hover { color: var(--mr-text); border-color: var(--mr-text-dim); }

/* Buttons */
.manage-roles .mr-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 9px 16px;
  font-family: var(--mr-mono); font-size: 11px;
  letter-spacing: 0.2em; text-transform: uppercase;
  border: 1px solid var(--mr-border);
  background: transparent; color: var(--mr-text);
  cursor: pointer; transition: all 0.18s;
}
.manage-roles .mr-btn-primary {
  background: var(--mr-primary); color: var(--mr-bg);
  border-color: var(--mr-primary);
  box-shadow: 0 0 18px var(--mr-primary-glow);
}
.manage-roles .mr-btn-primary:hover:not(:disabled) {
  background: transparent; color: var(--mr-primary);
}
.manage-roles .mr-btn-ghost { color: var(--mr-text-muted); }
.manage-roles .mr-btn-ghost:hover:not(:disabled) { color: var(--mr-text); border-color: var(--mr-text-dim); }
.manage-roles .mr-btn:disabled { opacity: 0.5; cursor: default; }

/* Modal shell — reused for AssignRoleModal */
.manage-roles .mr-modal {
  position: fixed; inset: 0; z-index: 1300;
  display: flex; align-items: center; justify-content: center;
  padding: 24px;
  background: rgba(0, 0, 3, 0.78);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.manage-roles .mr-card {
  position: relative;
  width: 100%; max-width: 520px;
  background: var(--mr-surface);
  border: 1px solid var(--mr-border);
  padding: 32px 28px 24px;
  box-shadow: 0 0 0 1px var(--mr-primary-glow), 0 30px 80px rgba(0, 0, 0, 0.6);
}
.manage-roles .mr-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: var(--mr-primary); box-shadow: 0 0 12px var(--mr-primary-glow);
}
.manage-roles .mr-close {
  position: absolute; top: 12px; right: 12px;
  width: 30px; height: 30px;
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--mr-text-muted); background: transparent;
  border: 1px solid transparent; cursor: pointer;
  transition: all 0.15s;
}
.manage-roles .mr-close:hover { color: var(--mr-text); border-color: var(--mr-border); }
.manage-roles .mr-title {
  font-family: var(--mr-display);
  font-size: 26px; line-height: 1.05; letter-spacing: 0.015em;
  margin-bottom: 12px;
}
.manage-roles .mr-body {
  font-size: 12px; color: var(--mr-text-muted);
  line-height: 1.55; margin-bottom: 20px;
}
.manage-roles .mr-body code {
  background: var(--mr-surface-2);
  padding: 1px 5px; border-radius: 2px;
  font-family: var(--mr-mono); font-size: 11px;
  color: var(--mr-accent);
}
.manage-roles .mr-field { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; }
.manage-roles .mr-label {
  font-size: 10px; letter-spacing: 0.22em; text-transform: uppercase;
  color: var(--mr-text-dim);
}
.manage-roles .mr-input {
  padding: 10px 14px;
  font-family: var(--mr-mono); font-size: 13px;
  background: var(--mr-bg); color: var(--mr-text);
  border: 1px solid var(--mr-border);
  transition: border-color 0.15s;
}
.manage-roles .mr-input:focus { outline: none; border-color: var(--mr-primary); }
.manage-roles .mr-locked-user {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 14px;
  background: var(--mr-bg);
  border: 1px solid var(--mr-border);
}
.manage-roles .mr-locked-avatar {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, #f5c547, #d98c3a);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mr-display); font-size: 14px; color: var(--mr-bg);
  flex-shrink: 0;
}
.manage-roles .mr-locked-info { flex: 1; min-width: 0; }
.manage-roles .mr-locked-name { font-size: 13px; color: var(--mr-text); }
.manage-roles .mr-locked-meta { font-size: 11px; color: var(--mr-text-dim); }

.manage-roles .mr-results {
  max-height: 240px; overflow-y: auto;
  border: 1px solid var(--mr-border);
  background: var(--mr-bg);
  margin-bottom: 18px;
}
.manage-roles .mr-state {
  padding: 24px 16px; text-align: center;
  color: var(--mr-text-muted); font-size: 12px;
}
.manage-roles .mr-state-hint { color: var(--mr-text-dim); }
.manage-roles .mr-result-row {
  display: flex; align-items: center; gap: 12px;
  width: 100%; padding: 10px 14px;
  background: transparent; border: none;
  border-bottom: 1px solid rgba(42, 44, 46, 0.4);
  text-align: left; cursor: pointer;
  transition: background 0.15s;
  color: var(--mr-text);
}
.manage-roles .mr-result-row:last-child { border-bottom: none; }
.manage-roles .mr-result-row:hover { background: rgba(255, 97, 0, 0.04); }
.manage-roles .mr-result-row.selected { background: rgba(255, 97, 0, 0.12); }
.manage-roles .mr-result-avatar {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, #f5c547, #d98c3a);
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mr-display); font-size: 14px; color: var(--mr-bg);
  flex-shrink: 0;
}
.manage-roles .mr-result-info { flex: 1; min-width: 0; }
.manage-roles .mr-result-name { font-size: 13px; color: var(--mr-text); }
.manage-roles .mr-result-meta { font-size: 11px; color: var(--mr-text-dim); }
.manage-roles .mr-result-roles { color: var(--mr-text-muted); }
.manage-roles .mr-result-check {
  font-size: 16px; color: var(--mr-accent);
  text-shadow: 0 0 8px var(--mr-accent);
}

.manage-roles .mr-role-options {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px;
}
.manage-roles .mr-role-option {
  display: flex; flex-direction: column; align-items: flex-start; gap: 4px;
  padding: 12px 14px;
  background: var(--mr-surface);
  border: 1px solid var(--mr-border);
  cursor: pointer; transition: all 0.15s;
  text-align: left;
}
.manage-roles .mr-role-option:hover { border-color: var(--mr-primary); }
.manage-roles .mr-role-option.selected {
  background: rgba(255, 97, 0, 0.08);
  border-color: var(--mr-primary);
  box-shadow: 0 0 12px var(--mr-primary-glow);
}
.manage-roles .mr-role-option-name {
  font-family: var(--mr-display); font-size: 18px;
  color: var(--mr-text); letter-spacing: 0.02em;
}
.manage-roles .mr-role-option-id {
  font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
  color: var(--mr-text-dim);
}
.manage-roles .mr-role-option-locked {
  font-size: 9px; color: var(--mr-danger);
  letter-spacing: 0.1em;
}
.manage-roles .mr-hint {
  font-size: 11px; color: var(--mr-text-dim);
  letter-spacing: 0.04em; margin-top: 6px;
}

.manage-roles .mr-field-errors {
  background: rgba(225, 27, 43, 0.08);
  border: 1px solid rgba(225, 27, 43, 0.3);
  padding: 10px 12px;
  font-size: 11px; color: var(--mr-danger);
  margin-bottom: 16px;
}
.manage-roles .mr-field-errors ul { margin: 0; padding-left: 18px; }
.manage-roles .mr-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 8px; }

/* Toast */
.manage-roles .mr-toast {
  position: fixed; bottom: 32px; right: 32px;
  padding: 12px 20px;
  background: var(--mr-surface);
  border: 1px solid var(--mr-border);
  font-size: 12px; letter-spacing: 0.04em;
  z-index: 1400;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
}
.manage-roles .mr-toast--success { border-color: rgba(193, 245, 39, 0.4); color: var(--mr-accent); }
.manage-roles .mr-toast--error { border-color: rgba(225, 27, 43, 0.4); color: var(--mr-danger); }

.manage-roles .mr-fade-enter-active,
.manage-roles .mr-fade-leave-active { transition: opacity 0.18s ease; }
.manage-roles .mr-fade-enter-from,
.manage-roles .mr-fade-leave-to { opacity: 0; }
.manage-roles .mr-toast-fade-enter-active,
.manage-roles .mr-toast-fade-leave-active { transition: opacity 0.18s, transform 0.18s; }
.manage-roles .mr-toast-fade-enter-from,
.manage-roles .mr-toast-fade-leave-to { opacity: 0; transform: translateY(10px); }

@media (max-width: 720px) {
  .manage-roles .mr-toolbar-right { width: 100%; }
  .manage-roles .mr-search-input { flex: 1; }
}
</style>

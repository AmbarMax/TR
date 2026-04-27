<template>
  <div class="roles-table-wrap">
    <div v-if="loading" class="roles-loading">Loading staff…</div>
    <div v-else-if="!users.length" class="roles-empty">
      <p v-if="searchQuery">No staff users match "{{ searchQuery }}".</p>
      <p v-else-if="roleFilter !== 'staff'">No users with this role yet.</p>
      <p v-else>No staff users yet. Use "+ Assign role" to grant the first one.</p>
    </div>

    <table v-else class="roles-table">
      <thead>
        <tr>
          <th class="th-avatar"></th>
          <th>Username</th>
          <th>Name</th>
          <th>Account</th>
          <th>Staff roles</th>
          <th class="th-actions"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id" class="roles-row">
          <td class="td-avatar">
            <div class="avatar-cell">
              <img v-if="hasCustomAvatar(user)" :src="user.avatar" :alt="user.name" />
              <span v-else>{{ initials(user) }}</span>
            </div>
          </td>
          <td class="td-username">@{{ user.username }}</td>
          <td class="td-name">{{ user.name || "—" }}</td>
          <td class="td-account">
            <span class="account-pill">{{ user.account_type || "player" }}</span>
          </td>
          <td class="td-roles">
            <span
              v-for="role in staffRolesOf(user)"
              :key="`${user.id}-${role}`"
              class="role-chip"
              :class="`role-chip--${role}`"
            >
              {{ shortRoleLabel(role) }}
              <button
                v-if="canRevoke(user, role)"
                class="role-chip-revoke"
                type="button"
                :title="`Revoke ${role}`"
                @click="onRevoke(user, role)"
              >×</button>
            </span>
            <span v-if="!staffRolesOf(user).length" class="muted">—</span>
          </td>
          <td class="td-actions">
            <button class="row-btn" type="button" @click="$emit('assign-to', user)">+ Add role</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
const STAFF_ROLES = ["tr_moderator", "tr_admin", "tr_superadmin"];
const SHORT_LABELS = {
  tr_moderator: "Moderator",
  tr_admin: "Admin",
  tr_superadmin: "Super Admin",
};

export default {
  name: "RolesTable",
  props: {
    users: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    searchQuery: { type: String, default: "" },
    roleFilter: { type: String, default: "staff" },
    callerIsSuper: { type: Boolean, default: false },
  },
  emits: ["revoke", "assign-to"],
  methods: {
    hasCustomAvatar(user) {
      return user.avatar && !user.avatar.includes("default-profile-img");
    },
    initials(user) {
      const src = (user.username || user.name || "?").trim();
      return src.slice(0, 2).toUpperCase();
    },
    staffRolesOf(user) {
      return (user.roles || []).filter(r => STAFF_ROLES.includes(r));
    },
    shortRoleLabel(role) {
      return SHORT_LABELS[role] || role;
    },
    canRevoke(user, role) {
      // Defense-in-depth UI hint: only super can revoke super. Backend
      // also enforces this — UI hide is purely cosmetic.
      if (role === "tr_superadmin" && !this.callerIsSuper) return false;
      return true;
    },
    onRevoke(user, role) {
      const label = this.shortRoleLabel(role);
      if (!window.confirm(`Revoke ${label} from @${user.username}?`)) return;
      this.$emit("revoke", { user, role });
    },
  },
};
</script>

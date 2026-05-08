<template>
  <div class="brands-table-wrap">
    <div v-if="loading" class="brands-loading">Loading brands…</div>
    <div v-else-if="!brands.length" class="brands-empty">
      <p v-if="searchQuery">No brands match "{{ searchQuery }}".</p>
      <p v-else>No brands yet. Use "+ Promote user" to create the first one.</p>
    </div>

    <table v-else class="brands-table">
      <thead>
        <tr>
          <th class="th-avatar"></th>
          <th class="th-sortable" @click="$emit('sort', 'username')">
            Username <span class="sort-arrow">{{ sortArrow('username') }}</span>
          </th>
          <th>Name</th>
          <th>Accent</th>
          <th class="th-sortable" @click="$emit('sort', 'verified_at')">
            Verified <span class="sort-arrow">{{ sortArrow('verified_at') }}</span>
          </th>
          <th class="th-sortable" @click="$emit('sort', 'is_featured')">
            Featured <span class="sort-arrow">{{ sortArrow('is_featured') }}</span>
          </th>
          <th>Status</th>
          <th>Trophies</th>
          <th>Followers</th>
          <th class="th-actions"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="brand in brands" :key="brand.id" class="brand-row">
          <td class="td-avatar">
            <div class="avatar-cell" :style="{ '--row-accent': brand.accent_color || '#ff6100' }">
              <img v-if="hasCustomAvatar(brand)" :src="brand.avatar" :alt="brand.name" />
              <span v-else>{{ logoLetter(brand) }}</span>
            </div>
          </td>
          <td class="td-username">@{{ brand.username }}</td>
          <td class="td-name">{{ brand.name || "—" }}</td>
          <td class="td-accent">
            <span v-if="brand.accent_color" class="accent-chip" :style="{ background: brand.accent_color }" :title="brand.accent_color"></span>
            <span v-if="brand.accent_color" class="accent-hex">{{ brand.accent_color }}</span>
            <span v-else class="muted">—</span>
          </td>
          <td>
            <span :class="brand.is_verified ? 'pill-yes' : 'pill-no'">{{ brand.is_verified ? 'Yes' : 'No' }}</span>
          </td>
          <td>
            <span :class="brand.is_featured ? 'pill-yes' : 'pill-no'">{{ brand.is_featured ? 'Yes' : 'No' }}</span>
          </td>
          <td>
            <span :class="['status-pill', `status-pill--${brand.account_status || 'unknown'}`]">
              {{ statusLabel(brand.account_status) }}
            </span>
          </td>
          <td class="td-num">{{ brand.active_trophies ?? 0 }}</td>
          <td class="td-num">{{ brand.followers ?? 0 }}</td>
          <td class="td-actions">
            <template v-if="brand.account_status === 'pending'">
              <button class="row-btn row-btn--primary" type="button" @click="$emit('approve', brand)">Approve</button>
              <button class="row-btn" type="button" @click="$emit('reject', brand)">Reject</button>
            </template>
            <template v-else-if="brand.account_status === 'rejected'">
              <button class="row-btn" type="button" @click="$emit('reapprove', brand)">Re-approve</button>
            </template>
            <template v-else>
              <button class="row-btn" type="button" @click="$emit('edit', brand)">Edit</button>
              <button class="row-btn row-btn--danger" type="button" @click="onDemote(brand)">Demote</button>
            </template>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "BrandsTable",
  props: {
    brands: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    searchQuery: { type: String, default: "" },
    sortKey: { type: String, default: "created_at" },
    sortOrder: { type: String, default: "desc" },
  },
  emits: ["sort", "edit", "demote", "approve", "reject", "reapprove"],
  methods: {
    sortArrow(key) {
      if (this.sortKey !== key) return "↕";
      return this.sortOrder === "asc" ? "↑" : "↓";
    },
    hasCustomAvatar(brand) {
      return brand.avatar && !brand.avatar.includes("default-profile-img");
    },
    logoLetter(brand) {
      return (brand.name || brand.username || "?").trim().charAt(0).toUpperCase();
    },
    statusLabel(status) {
      switch (status) {
        case "pending":  return "Pending";
        case "active":   return "Active";
        case "rejected": return "Rejected";
        case "suspended": return "Suspended";
        default:         return "—";
      }
    },
    onDemote(brand) {
      if (!window.confirm(`Demote @${brand.username} back to player? This revokes the brand_admin role.`)) return;
      this.$emit("demote", brand);
    },
  },
};
</script>

<style scoped>
.status-pill {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 2px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
}
.status-pill--pending {
  background: rgba(255, 184, 0, 0.12);
  color: #ffb800;
}
.status-pill--active {
  background: rgba(193, 245, 39, 0.12);
  color: #c1f527;
}
.status-pill--rejected {
  background: rgba(255, 80, 80, 0.12);
  color: rgba(255, 120, 120, 0.9);
}
.status-pill--suspended {
  background: rgba(154, 149, 144, 0.12);
  color: rgba(154, 149, 144, 0.9);
}
.status-pill--unknown {
  background: rgba(154, 149, 144, 0.08);
  color: rgba(154, 149, 144, 0.6);
}
</style>

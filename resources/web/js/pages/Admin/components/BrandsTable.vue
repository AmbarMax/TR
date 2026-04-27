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
          <td class="td-num">{{ brand.active_trophies ?? 0 }}</td>
          <td class="td-num">{{ brand.followers ?? 0 }}</td>
          <td class="td-actions">
            <button class="row-btn" type="button" @click="$emit('edit', brand)">Edit</button>
            <button class="row-btn row-btn--danger" type="button" @click="onDemote(brand)">Demote</button>
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
  emits: ["sort", "edit", "demote"],
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
    onDemote(brand) {
      if (!window.confirm(`Demote @${brand.username} back to player? This revokes the brand_admin role.`)) return;
      this.$emit("demote", brand);
    },
  },
};
</script>

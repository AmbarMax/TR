<template>
  <div>
    <div class="active-filters">
      <button
        v-for="f in filters"
        :key="f.key"
        class="afilter"
        :class="{ active: activeFilter === f.key }"
        type="button"
        @click="activeFilter = f.key"
      >
        {{ f.label }} <span class="afilter-count">{{ f.count }}</span>
      </button>
    </div>

    <div v-if="visibleItems.length" class="active-grid">
      <div
        v-for="item in visibleItems"
        :key="item.id"
        class="active-card"
        :class="cardClass(item)"
      >
        <div class="active-card-asset" :class="{ 'chest-asset': item.type === 'chest' }">
          <img v-if="itemImage(item)" :src="itemImage(item)" :alt="item.name" class="active-card-img" />
          <svg v-else viewBox="0 0 100 100" width="80" height="80">
            <template v-if="item.type === 'chest'">
              <rect x="15" y="40" width="70" height="48" fill="#0e0f11" stroke="currentColor" stroke-width="2" style="color: var(--brand-accent)"/>
              <path d="M15 40 Q15 22 50 22 Q85 22 85 40" fill="#0e0f11" stroke="currentColor" stroke-width="2" style="color: var(--brand-accent)"/>
              <rect x="15" y="55" width="70" height="6" fill="currentColor" style="color: var(--brand-accent)"/>
              <rect x="44" y="50" width="12" height="16" fill="#feeddf"/>
              <circle cx="50" cy="58" r="3" fill="#000003"/>
            </template>
            <template v-else>
              <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="currentColor" stroke-width="2" style="color: var(--brand-accent)"/>
              <polygon points="50,22 75,35 50,48 25,35" fill="currentColor" style="color: var(--brand-accent)"/>
              <polygon points="50,48 75,35 75,65 50,78" fill="currentColor" style="color: var(--brand-accent); opacity: 0.7"/>
              <polygon points="50,48 25,35 25,65 50,78" fill="currentColor" style="color: var(--brand-accent); opacity: 0.5"/>
            </template>
          </svg>
        </div>
        <div class="active-card-body">
          <div class="active-card-meta">
            <span class="ac-tag" :class="item.type === 'chest' ? 'tag-chest' : 'tag-trophy'">
              {{ item.type === 'chest' ? 'Chest' : 'Trophy' }}
            </span>
            <span v-if="isConquered(item)" class="ac-tag tag-conquered">Conquered</span>
            <span v-else class="ac-tag tag-time">Active</span>
          </div>
          <h3 class="active-card-name">{{ item.name }}</h3>
          <p class="active-card-cond">{{ item.description || "Live this week." }}</p>
          <div class="active-card-foot">
            <span class="xp-pill">{{ item.type === 'chest' ? 'Loot' : `+${item.receive ?? 0} XP` }}</span>
            <span class="pursuing-count">{{ pursuingLabel(item) }}</span>
          </div>
          <button
            class="btn-pursuit-sm"
            :class="cardButtonClass(item)"
            type="button"
            :disabled="isPursuing(item) || isConquered(item)"
            @click="onPursuit(item)"
          >
            <template v-if="isConquered(item)">
              <span>In your trophy room</span><span>★</span>
            </template>
            <template v-else-if="isPursuing(item)">
              <span>In your pursuits</span><span>✓</span>
            </template>
            <template v-else>
              <span>Add to pursuits</span><span>+</span>
            </template>
          </button>
        </div>
      </div>
    </div>

    <div v-else class="active-empty">
      <p>No active items right now. Check back soon.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: "ActiveItemsGrid",
  props: {
    items: { type: Array, required: true },
    pursuingIds: { type: Array, default: () => [] },
    conqueredIds: { type: Array, default: () => [] },
  },
  emits: ["pursuit"],
  data() {
    return {
      activeFilter: "all",
    };
  },
  computed: {
    counts() {
      return {
        all: this.items.length,
        trophy: this.items.filter(i => i.type === "trophy").length,
        chest: this.items.filter(i => i.type === "chest").length,
      };
    },
    filters() {
      return [
        { key: "all", label: "All", count: this.counts.all },
        { key: "trophy", label: "Trophies", count: this.counts.trophy },
        { key: "chest", label: "Chests", count: this.counts.chest },
      ];
    },
    visibleItems() {
      if (this.activeFilter === "all") return this.items;
      return this.items.filter(i => i.type === this.activeFilter);
    },
  },
  methods: {
    isPursuing(item) {
      return this.pursuingIds.includes(item.id);
    },
    isConquered(item) {
      return this.conqueredIds.includes(item.id);
    },
    cardClass(item) {
      return {
        trophy: item.type === "trophy",
        chest: item.type === "chest",
        pursuing: this.isPursuing(item),
        conquered: this.isConquered(item),
      };
    },
    cardButtonClass(item) {
      return {
        pursuing: this.isPursuing(item),
        conquered: this.isConquered(item),
      };
    },
    itemImage(item) {
      const img = item.image;
      if (!img || img === "placeholder.jpg") return null;
      if (img.startsWith("http") || img.startsWith("/")) return img;
      return `/storage/${img}`;
    },
    pursuingLabel(item) {
      if (this.isConquered(item)) return "In your trophy room";
      if (item.type === "chest") return `${item.unlocked_count ?? 0} unlocked`;
      return `${item.pursuing_count ?? 0} pursuing`;
    },
    onPursuit(item) {
      if (this.isPursuing(item) || this.isConquered(item)) return;
      this.$emit("pursuit", item);
    },
  },
};
</script>

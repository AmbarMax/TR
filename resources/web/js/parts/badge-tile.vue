<template>
  <div class="badge-tile" :title="badge.name" @click="openDetail">
    <img
      v-if="badge.image"
      :src="imageUrl"
      :alt="badge.name"
      class="badge-tile__img"
    />
    <div v-else class="badge-tile__placeholder">?</div>
  </div>
</template>

<script>
import store from '../store/store.js';

export default {
  props: {
    badge: { type: Object, required: true },
    service: { type: String, default: 'discord' }
  },
  computed: {
    imageUrl() {
      return `/storage/integrations/${this.service}/${this.badge.image}`;
    }
  },
  methods: {
    openDetail() {
      store.state.modals.trophyRoomBadge.show = true;
      store.state.modals.trophyRoomBadge.data.imageUrl = this.imageUrl;
      store.state.modals.trophyRoomBadge.data.name = this.badge.name;
    }
  }
}
</script>

<style scoped>
.badge-tile {
  width: 56px;
  height: 56px;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: border-color 0.15s;
  overflow: hidden;
}

.badge-tile:hover {
  border-color: #9a9590;
}

.badge-tile__img {
  width: 40px;
  height: 40px;
  object-fit: contain;
}

.badge-tile__placeholder {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 18px;
}
</style>

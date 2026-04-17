<template>
  <div class="ach-row">
    <div class="ach-row__icon">
      <img v-if="achievement.image" :src="imageUrl" :alt="achievement.name" />
      <div v-else class="ach-row__icon-placeholder"></div>
    </div>
    <div class="ach-row__info">
      <div class="ach-row__name">{{ achievement.name }}</div>
      <div class="ach-row__status" v-if="achievement.status === 1">
        <span class="ach-row__validated">✓ Validated</span>
        <span class="ach-row__vouches" v-if="achievement.validations_count"> · {{ achievement.validations_count }} vouches</span>
      </div>
      <div class="ach-row__status" v-else-if="achievement.status === 2">
        <span class="ach-row__pending">Not validated</span>
      </div>
      <div class="ach-row__status" v-else-if="achievement.status === 3">
        <span class="ach-row__rejected">Rejected</span>
      </div>
      <div class="ach-row__status" v-else>
        <span class="ach-row__pending">Pending review</span>
      </div>
    </div>
    <div class="ach-row__actions">
      <button
        v-if="!achievement.display"
        class="ach-row__btn"
        @click="showcase"
      >Showcase</button>
      <button
        v-else
        class="ach-row__btn ach-row__btn--active"
        @click="removeShowcase"
      >Remove</button>
    </div>
  </div>
</template>

<script>
import store from '../store/store.js';
import api from '../api/api.js';

export default {
  props: {
    achievement: { type: Object, required: true }
  },
  computed: {
    imageUrl() {
      return `/storage/achievements/${this.achievement.image}`;
    }
  },
  methods: {
    showcase() {
      api.get(`/api/achievement/${this.achievement.id}/showcase`).then(resp => {
        if (resp.status === 200) {
          this.achievement.display = true;
          store.state.notification = {
            message: `${this.achievement.name} has been added to the virtual hall`,
            type: 'success',
            show: true
          };
        }
      });
    },
    removeShowcase() {
      api.get(`/api/achievement/${this.achievement.id}/removeShowcase`).then(resp => {
        if (resp.status === 200) {
          this.achievement.display = false;
          store.state.notification = {
            message: `${this.achievement.name} has been removed from the virtual hall`,
            type: 'success',
            show: true
          };
        }
      });
    }
  }
}
</script>

<style scoped>
.ach-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  transition: border-color 0.15s;
}

.ach-row:hover {
  border-color: #3a3d40;
}

.ach-row__icon {
  width: 44px;
  height: 44px;
  min-width: 44px;
  background: #1a1c1f;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.ach-row__icon img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.ach-row__icon-placeholder {
  width: 32px;
  height: 32px;
  background: #252729;
  border-radius: 4px;
}

.ach-row__info {
  flex: 1;
  min-width: 0;
}

.ach-row__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ach-row__status {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  margin-top: 2px;
}

.ach-row__validated {
  color: #c1f527;
}

.ach-row__vouches {
  color: #9a9590;
}

.ach-row__pending {
  color: #9a9590;
}

.ach-row__rejected {
  color: #e24b4a;
}

.ach-row__actions {
  flex-shrink: 0;
}

.ach-row__btn {
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
  border-radius: 4px;
  padding: 6px 12px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}

.ach-row__btn:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.ach-row__btn--active {
  border-color: #ff6100;
  color: #ff6100;
}

@media (max-width: 480px) {
  .ach-row__actions {
    display: none;
  }
}
</style>

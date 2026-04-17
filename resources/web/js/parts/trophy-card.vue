<template>
  <div class="trophy-card" :class="{ 'trophy-card--ready': isReady }">
    <div class="trophy-card__header">
      <div class="trophy-card__icon">
        <img v-if="trophy.image" :src="imageUrl" :alt="trophy.name" />
      </div>
      <div class="trophy-card__xp" v-if="trophy.weight || trophy.price">
        {{ Math.floor(trophy.weight || trophy.price) }} XP
      </div>
    </div>

    <div class="trophy-card__name">{{ trophy.name }}</div>
    <div class="trophy-card__brand" v-if="trophy.description">{{ trophy.description }}</div>

    <div class="trophy-card__progress" v-if="trophy.badges_required !== undefined">
      <div class="trophy-card__progress-info">
        <span>{{ trophy.badges_owned || 0 }}/{{ trophy.badges_required }} badges</span>
        <span>{{ progressPercent }}%</span>
      </div>
      <div class="trophy-card__progress-bar">
        <div class="trophy-card__progress-fill" :style="{ width: progressPercent + '%' }"></div>
      </div>
    </div>

    <div class="trophy-card__action" v-if="isReady && showForgeButton">
      <button class="trophy-card__forge-btn" @click="forgeTrophy">Forge now</button>
    </div>
    <div class="trophy-card__action" v-else-if="!isReady && trophy.badges_required">
      <button class="trophy-card__missing-btn" disabled>
        Missing {{ (trophy.badges_required || 0) - (trophy.badges_owned || 0) }} badge{{ ((trophy.badges_required || 0) - (trophy.badges_owned || 0)) !== 1 ? 's' : '' }}
      </button>
    </div>

    <div class="trophy-card__showcase" v-if="showShowcase">
      <button
        v-if="!trophy.pivot || !trophy.pivot.display"
        class="trophy-card__showcase-btn"
        @click="showcase"
      >Showcase</button>
      <button
        v-else
        class="trophy-card__showcase-btn trophy-card__showcase-btn--active"
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
    trophy: { type: Object, required: true },
    showForgeButton: { type: Boolean, default: false },
    showShowcase: { type: Boolean, default: true }
  },
  computed: {
    imageUrl() {
      return `/storage/trophies/${this.trophy.image}`;
    },
    progressPercent() {
      if (!this.trophy.badges_required) return 0;
      return Math.round(((this.trophy.badges_owned || 0) / this.trophy.badges_required) * 100);
    },
    isReady() {
      return this.trophy.badges_required && (this.trophy.badges_owned || 0) >= this.trophy.badges_required;
    }
  },
  methods: {
    forgeTrophy() {
      store.state.modals.claimTrophyModal.show = true;
      store.state.modals.claimTrophyModal.data = this.trophy;
    },
    showcase() {
      api.get(`/api/forge/${this.trophy.id}/showcase`).then(resp => {
        if (resp.status === 200) {
          this.trophy.pivot.display = true;
          store.state.notification = {
            message: `${this.trophy.name} has been added to the virtual hall`,
            type: 'success',
            show: true
          };
        }
      });
    },
    removeShowcase() {
      api.get(`/api/forge/${this.trophy.id}/remove`).then(resp => {
        if (resp.status === 200) {
          this.trophy.pivot.display = false;
          store.state.notification = {
            message: `${this.trophy.name} has been removed from the virtual hall`,
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
.trophy-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.trophy-card--ready {
  border-color: #ff6100;
}

.trophy-card__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.trophy-card__icon {
  width: 56px;
  height: 56px;
  background: #1a1c1f;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.trophy-card__icon img {
  width: 44px;
  height: 44px;
  object-fit: contain;
}

.trophy-card__xp {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 4px;
  letter-spacing: 0.05em;
}

.trophy-card__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 15px;
  font-weight: 400;
}

.trophy-card__brand {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.trophy-card__progress {
  margin-top: 4px;
}

.trophy-card__progress-info {
  display: flex;
  justify-content: space-between;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: #9a9590;
  margin-bottom: 4px;
}

.trophy-card__progress-bar {
  width: 100%;
  height: 4px;
  background: #252729;
  border-radius: 2px;
  overflow: hidden;
}

.trophy-card__progress-fill {
  height: 100%;
  border-radius: 2px;
  background: #c1f527;
  transition: width 0.3s ease;
}

.trophy-card--ready .trophy-card__progress-fill {
  background: #ff6100;
}

.trophy-card__forge-btn {
  width: 100%;
  background: #ff6100;
  color: #000003;
  border: none;
  border-radius: 4px;
  padding: 10px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  cursor: pointer;
  letter-spacing: 0.05em;
  transition: filter 0.15s;
}

.trophy-card__forge-btn:hover {
  filter: brightness(1.1);
}

.trophy-card__missing-btn {
  width: 100%;
  background: #252729;
  color: #5a5550;
  border: none;
  border-radius: 4px;
  padding: 10px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  cursor: default;
}

.trophy-card__showcase-btn {
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

.trophy-card__showcase-btn:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.trophy-card__showcase-btn--active {
  border-color: #ff6100;
  color: #ff6100;
}
</style>

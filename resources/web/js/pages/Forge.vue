<template>
  <div class="main_block">
    <!-- Header -->
    <div class="forge-header">
      <h1 class="forge-title">Forge</h1>
      <p class="forge-subtitle">Trophies created by brands and the community. Meet the requirements and forge them to earn XP and showcase your achievements.</p>
    </div>

    <!-- Filter Pills + Stats -->
    <div class="forge-controls">
      <div class="forge-pills">
        <button class="forge-pill" :class="{ 'forge-pill--active': activeFilter === 'all' }" @click="setFilter('all')">All</button>
        <button class="forge-pill" :class="{ 'forge-pill--active': activeFilter === 'available' }" @click="setFilter('available')">Available</button>
        <button class="forge-pill" :class="{ 'forge-pill--active': activeFilter === 'almost' }" @click="setFilter('almost')">Almost ready</button>
        <button class="forge-pill" :class="{ 'forge-pill--active': activeFilter === 'completed' }" @click="setFilter('completed')">Completed</button>
      </div>
      <div class="forge-stats">
        <span class="forge-stats__total">{{ achievements.length }} trophies</span>
        <span class="forge-stats__ready" v-if="readyToForgeCount">{{ readyToForgeCount }} ready to forge</span>
      </div>
    </div>

    <!-- Trophy Grid -->
    <div class="forge-grid">
      <TrophyCard
        v-for="trophy in filteredTrophies"
        :key="trophy.id"
        :trophy="trophy"
        :show-forge-button="true"
        :show-showcase="false"
        :show-description="true"
        :required-badges="trophy.badges || []"
        :user-badge-ids="userBadgeIds"
      />
    </div>

    <!-- Empty State -->
    <div class="forge-empty" v-if="!filteredTrophies.length && achievements.length">
      <p>No trophies match this filter.</p>
    </div>
  </div>
</template>

<script>

import {defineComponent} from "vue";
import TrophyCard from "../parts/trophy-card.vue";
import buttonWhite from "../parts/button.vue";
import store from "../store/store.js";
import api from "../api/api.js";

export default defineComponent({
    components: {
        TrophyCard,
        buttonWhite,
        store
    },
    data() {
        return {
            achievements: [],
            trophies: [],
            activeFilter: 'all',
            userBadges: [],
            button_text: "Forge Trophy",
            icon_type: "Ambar",
            currentPage: 1,
            totalPages: 3,
        }
    },
    methods: {
        OpenModalClaimTrophy() {
            store.state.claimTrophyModal = true;
        },
        changePage(newPage) {
            this.currentPage = newPage;
        },
        getAllForges() {
            api.get(`/api/forge`).then(resp => {
                this.achievements = resp.data.trophies;
            });
        },
        getUserTrophies() {
            api.get('/api/forge/available-trophies').then(resp => {
                if (resp.status === 200) {
                    this.trophies = resp.data.trophies;
                }
            });
        },
        getUserBadges() {
            api.get('/api/badges').then(resp => {
                if (resp.status === 200) {
                    this.userBadges = resp.data.data;
                }
            }).catch(e => {
                console.log('badges error', e);
            });
        },
        setFilter(filter) {
            this.activeFilter = filter;
        },
    },
    mounted() {
        this.totalPages = Math.ceil(this.achievements.length / 3);
        this.getAllForges();
        this.getUserTrophies();
        this.getUserBadges();
    },
    computed: {
        displayedAchievements() {
            const startIndex = (this.currentPage - 1) * 3;
            const endIndex = startIndex + 3;
            return this.achievements.slice(startIndex, endIndex);
        },
        trophiesChanged() {
            return store.state.modals.claimTrophyModal.show;
        },
        userBadgeIds() {
            return this.userBadges.map(b => b.id);
        },
        filteredTrophies() {
            if (this.activeFilter === 'all') return this.achievements;
            return this.achievements.filter(trophy => {
                const required = trophy.badges ? trophy.badges.length : 0;
                if (required === 0) return this.activeFilter === 'available';
                const owned = trophy.badges.filter(b => this.userBadgeIds.includes(b.id)).length;
                if (this.activeFilter === 'available') return owned === 0;
                if (this.activeFilter === 'almost') return owned > 0 && owned < required;
                if (this.activeFilter === 'completed') return owned >= required;
                return true;
            });
        },
        readyToForgeCount() {
            return this.achievements.filter(trophy => {
                if (!trophy.badges || !trophy.badges.length) return false;
                const owned = trophy.badges.filter(b => this.userBadgeIds.includes(b.id)).length;
                return owned >= trophy.badges.length;
            }).length;
        },
    },
    watch: {
        trophiesChanged() {
            this.getUserTrophies();
        }
    }
})
</script>

<style scoped>
.forge-header {
  margin-bottom: 24px;
  padding-top: 32px;
}

.forge-title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 28px;
  font-weight: 400;
  margin: 0 0 6px;
}

.forge-subtitle {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  margin: 0;
  max-width: 600px;
}

.forge-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 24px;
}

.forge-pills {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.forge-pill {
  padding: 6px 16px;
  border-radius: 20px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.15s;
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
}

.forge-pill--active {
  background: #c1f527;
  color: #000003;
  border-color: #c1f527;
}

.forge-pill:hover:not(.forge-pill--active) {
  border-color: #9a9590;
  color: #feeddf;
}

.forge-stats {
  display: flex;
  gap: 16px;
  align-items: center;
}

.forge-stats__total {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}

.forge-stats__ready {
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}

.forge-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.forge-empty {
  text-align: center;
  padding: 40px;
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
}

@media (max-width: 768px) {
  .forge-grid {
    grid-template-columns: 1fr;
  }

  .forge-controls {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

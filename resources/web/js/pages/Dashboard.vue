<template>
  <div class="main_block">
    <!-- Header -->
    <div class="dash-header">
      <div>
        <h1 class="dash-title">Welcome back, {{ $store.state.userUsername }}</h1>
        <p class="dash-tagline">Keep forging. Keep earning.</p>
      </div>
      <div class="dash-currencies">
        <div class="dash-currency">
          <span class="dash-currency__value dash-currency__value--ambar">{{ Math.floor($store.state.user.balances.ambar || 0) }}</span>
          <span class="dash-currency__label">Ambar</span>
        </div>
        <div class="dash-currency__sep"></div>
        <div class="dash-currency">
          <span class="dash-currency__value dash-currency__value--uru">{{ Math.floor($store.state.user.balances.uru || 0) }}</span>
          <span class="dash-currency__label">Uru</span>
        </div>
      </div>
    </div>

    <!-- Daily Missions -->
    <div class="dash-section">
      <div class="dash-section__header">
        <span class="dash-section__label">Daily missions</span>
        <span class="dash-section__timer" id="dash-timer"></span>
      </div>
      <div class="dash-missions">
        <div
          v-for="mission in missions"
          :key="mission.id"
          class="dash-mission"
          :class="{ 'dash-mission--done': mission.done }"
          @click="mission.action"
        >
          <div class="dash-mission__info">
            <div class="dash-mission__name">
              <span v-if="mission.done" class="dash-mission__check">&#10003;</span>
              {{ mission.name }}
            </div>
            <div class="dash-mission__desc">{{ mission.description }}</div>
          </div>
          <div class="dash-mission__reward" :class="mission.done ? 'dash-mission__reward--done' : ''">
            {{ mission.done ? 'Done' : mission.reward }}
          </div>
        </div>
      </div>
    </div>

    <!-- Trophies in Progress -->
    <div class="dash-section" v-if="trophiesInProgress.length">
      <div class="dash-section__header">
        <span class="dash-section__label">Trophies in progress</span>
        <span class="dash-section__meta">{{ trophiesInProgress.length }} in progress<span v-if="readyToForge"> · {{ readyToForge }} ready</span></span>
      </div>
      <div class="dash-trophy-grid">
        <div
          v-for="trophy in trophiesInProgress.slice(0, 6)"
          :key="trophy.id"
          class="dash-trophy"
          :class="{ 'dash-trophy--ready': isTrophyReady(trophy) }"
          @click="goToForge"
        >
          <div class="dash-trophy__top">
            <div class="dash-trophy__icon">
              <img v-if="trophy.image" :src="'/storage/trophies/' + trophy.image" alt="" />
              <span v-else>{{ trophy.name.charAt(0) }}</span>
            </div>
            <div class="dash-trophy__xp">{{ Math.floor(trophy.price || 0) }} XP</div>
          </div>
          <div class="dash-trophy__name">{{ trophy.name }}</div>
          <div class="dash-trophy__progress-text">{{ getTrophyBadgesOwned(trophy) }}/{{ getTrophyBadgesRequired(trophy) }} badges</div>
          <div class="dash-trophy__bar">
            <div class="dash-trophy__fill" :style="{ width: getTrophyPercent(trophy) + '%' }"></div>
          </div>
          <div class="dash-trophy__action" v-if="isTrophyReady(trophy)">Forge now</div>
          <div class="dash-trophy__missing" v-else>Missing {{ getTrophyBadgesRequired(trophy) - getTrophyBadgesOwned(trophy) }} badge{{ (getTrophyBadgesRequired(trophy) - getTrophyBadgesOwned(trophy)) !== 1 ? 's' : '' }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="dash-section" v-if="notifications.length">
      <div class="dash-section__header">
        <span class="dash-section__label">Recent activity</span>
      </div>
      <div class="dash-activity">
        <div class="dash-activity-row" v-for="notif in notifications.slice(0, 5)" :key="notif.id">
          <div class="dash-activity-row__dot"></div>
          <div class="dash-activity-row__text">{{ notif.message }}</div>
          <div class="dash-activity-row__time">{{ formatTime(notif.created_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Connected Platforms -->
    <div class="dash-section">
      <div class="dash-section__header">
        <span class="dash-section__label">Connected platforms</span>
      </div>
      <div class="dash-platforms">
        <div
          v-for="platform in platforms"
          :key="platform.name"
          class="dash-platform"
          :class="platform.connected ? 'dash-platform--connected' : ''"
          @click="!platform.connected && connectPlatform(platform.type)"
        >
          <div class="dash-platform__dot" :class="platform.connected ? 'dash-platform__dot--on' : ''"></div>
          <span class="dash-platform__name">{{ platform.name }}</span>
          <span v-if="platform.connected" class="dash-platform__status">synced</span>
          <span v-else class="dash-platform__connect">connect</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import store from "../store/store.js";
import api from "../api/api.js";

export default defineComponent({
  data() {
    return {
      allTrophies: [],
      userTrophyIds: [],
      userBadges: [],
      userBadgeIds: [],
      notifications: [],
      timerInterval: null
    }
  },
  computed: {
    missions() {
      const hasDiscord = this.hasPlatformBadges('discord');
      return [
        {
          id: 1,
          name: hasDiscord ? 'Import more Discord badges' : 'Connect Discord',
          description: hasDiscord ? 'Sync your latest achievements' : 'Link your Discord account',
          reward: '+250',
          done: false,
          action: () => this.connectPlatform('discord')
        },
        {
          id: 2,
          name: 'Forge a trophy',
          description: this.readyToForge ? `You have ${this.readyToForge} ready to forge` : 'Complete badge requirements first',
          reward: '+Uru',
          done: false,
          action: () => this.$router.push('/forge')
        },
        {
          id: 3,
          name: 'Share an achievement',
          description: 'Post to the feed and earn Ambar',
          reward: '+50',
          done: false,
          action: () => this.$router.push('/feed')
        },
        {
          id: 4,
          name: 'Connect Discord',
          description: 'Completed',
          reward: 'Done',
          done: !!hasDiscord,
          action: () => {}
        }
      ];
    },
    platforms() {
      return [
        { name: 'Discord', type: 'discord', connected: this.hasPlatformBadges('discord') },
        { name: 'Steam', type: 'steam', connected: this.hasPlatformBadges('steam') },
        { name: 'GitHub', type: 'github', connected: this.hasPlatformBadges('github') },
        { name: 'Riot Games', type: 'riot', connected: false },
        { name: 'Strava', type: 'strava', connected: false },
      ];
    },
    trophiesInProgress() {
      return this.allTrophies.filter(t => !this.userTrophyIds.includes(t.id));
    },
    readyToForge() {
      return this.trophiesInProgress.filter(t => this.isTrophyReady(t)).length;
    }
  },
  methods: {
    hasPlatformBadges(platform) {
      return this.userBadges.some(b => b.integration === platform);
    },
    isTrophyReady(trophy) {
      if (!trophy.badges || !trophy.badges.length) return false;
      const owned = trophy.badges.filter(b => this.userBadgeIds.includes(b.id)).length;
      return owned >= trophy.badges.length;
    },
    getTrophyBadgesOwned(trophy) {
      if (!trophy.badges) return 0;
      return trophy.badges.filter(b => this.userBadgeIds.includes(b.id)).length;
    },
    getTrophyBadgesRequired(trophy) {
      return trophy.badges ? trophy.badges.length : 0;
    },
    getTrophyPercent(trophy) {
      const req = this.getTrophyBadgesRequired(trophy);
      if (!req) return 0;
      return Math.round((this.getTrophyBadgesOwned(trophy) / req) * 100);
    },
    connectPlatform(type) {
      const token = localStorage.getItem('access_token');
      window.location.href = '/login/' + type + '?token=' + encodeURIComponent(token);
    },
    goToForge() {
      this.$router.push('/forge');
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      const now = new Date();
      const diff = Math.floor((now - date) / 1000);
      if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
      if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
      return Math.floor(diff / 86400) + 'd ago';
    },
    startTimer() {
      const updateTimer = () => {
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setUTCHours(24, 0, 0, 0);
        const diff = Math.floor((tomorrow - now) / 1000);
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const el = document.getElementById('dash-timer');
        if (el) el.textContent = 'Resets in ' + h + 'h ' + m + 'm';
      };
      updateTimer();
      this.timerInterval = setInterval(updateTimer, 60000);
    },
    async fetchData() {
      try {
        const [badgesResp, forgeResp, trophiesResp] = await Promise.all([
          api.get('/api/badges'),
          api.get('/api/forge'),
          api.get('/api/forge/available-trophies')
        ]);

        if (badgesResp.status === 200) {
          this.userBadges = badgesResp.data.data;
          this.userBadgeIds = this.userBadges.map(b => b.id);
        }
        if (forgeResp.status === 200) {
          this.allTrophies = forgeResp.data.trophies || [];
        }
        if (trophiesResp.status === 200) {
          this.userTrophyIds = (trophiesResp.data.trophies || []).map(t => t.id);
        }
      } catch (e) {
        console.log('Dashboard fetch error:', e);
      }

      try {
        const notifResp = await api.get('/api/notifications');
        if (notifResp.status === 200) {
          this.notifications = notifResp.data.data || notifResp.data || [];
        }
      } catch (e) {
        console.log('Notifications fetch error:', e);
      }
    }
  },
  mounted() {
    this.fetchData();
    this.startTimer();
  },
  beforeUnmount() {
    if (this.timerInterval) clearInterval(this.timerInterval);
  }
});
</script>

<style scoped>
.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
  padding-top: 32px;
  gap: 20px;
}

.dash-title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 24px;
  font-weight: 400;
  margin: 0;
}

.dash-tagline {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  margin: 4px 0 0;
}

.dash-currencies {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-shrink: 0;
}

.dash-currency {
  text-align: right;
}

.dash-currency__value {
  display: block;
  font-family: 'Share Tech Mono', monospace;
  font-size: 20px;
}

.dash-currency__value--ambar { color: #ff6100; }
.dash-currency__value--uru { color: #c1f527; }

.dash-currency__label {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.dash-currency__sep {
  width: 1px;
  height: 28px;
  background: #2a2c2e;
}

/* Sections */
.dash-section {
  margin-bottom: 32px;
}

.dash-section__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.dash-section__label {
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.dash-section__timer {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

.dash-section__meta {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

/* Missions */
.dash-missions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.dash-mission {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 14px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: border-color 0.15s;
}

.dash-mission:hover {
  border-color: #3a3d40;
}

.dash-mission--done {
  border-color: rgba(193, 245, 39, 0.2);
}

.dash-mission__info {
  flex: 1;
  min-width: 0;
}

.dash-mission__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}

.dash-mission__check {
  color: #c1f527;
  margin-right: 4px;
}

.dash-mission__desc {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  margin-top: 2px;
}

.dash-mission__reward {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 4px;
  white-space: nowrap;
  margin-left: 12px;
  flex-shrink: 0;
}

.dash-mission__reward--done {
  background: rgba(193, 245, 39, 0.1);
  color: #c1f527;
}

/* Trophy Grid */
.dash-trophy-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.dash-trophy {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px;
  cursor: pointer;
  transition: border-color 0.15s;
}

.dash-trophy:hover {
  border-color: #3a3d40;
}

.dash-trophy--ready {
  border-color: #ff6100;
}

.dash-trophy__top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
}

.dash-trophy__icon {
  width: 40px;
  height: 40px;
  background: #1a1c1f;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.dash-trophy__icon img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.dash-trophy__icon span {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 16px;
}

.dash-trophy__xp {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 3px;
}

.dash-trophy__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dash-trophy__progress-text {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  margin-top: 4px;
}

.dash-trophy__bar {
  width: 100%;
  height: 3px;
  background: #252729;
  border-radius: 2px;
  margin-top: 6px;
  overflow: hidden;
}

.dash-trophy__fill {
  height: 100%;
  border-radius: 2px;
  background: #c1f527;
  transition: width 0.3s;
}

.dash-trophy--ready .dash-trophy__fill {
  background: #ff6100;
}

.dash-trophy__action {
  margin-top: 8px;
  background: #ff6100;
  color: #000003;
  text-align: center;
  padding: 6px;
  border-radius: 4px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

.dash-trophy__missing {
  margin-top: 8px;
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-align: center;
}

/* Activity */
.dash-activity {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.dash-activity-row {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.dash-activity-row__dot {
  width: 8px;
  height: 8px;
  min-width: 8px;
  background: #c1f527;
  border-radius: 50%;
}

.dash-activity-row__text {
  flex: 1;
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dash-activity-row__time {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  flex-shrink: 0;
}

/* Platforms */
.dash-platforms {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.dash-platform {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: border-color 0.15s;
}

.dash-platform--connected {
  border-color: rgba(193, 245, 39, 0.2);
  cursor: default;
}

.dash-platform:not(.dash-platform--connected):hover {
  border-color: #ff6100;
}

.dash-platform__dot {
  width: 8px;
  height: 8px;
  background: #5a5550;
  border-radius: 50%;
}

.dash-platform__dot--on {
  background: #c1f527;
}

.dash-platform__name {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

.dash-platform__status {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

.dash-platform__connect {
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

/* Responsive */
@media (max-width: 768px) {
  .dash-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .dash-currencies {
    width: 100%;
    justify-content: flex-start;
  }

  .dash-missions {
    grid-template-columns: 1fr;
  }

  .dash-trophy-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 480px) {
  .dash-trophy-grid {
    grid-template-columns: 1fr;
  }
}
</style>

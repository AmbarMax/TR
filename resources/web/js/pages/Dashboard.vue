<template>
  <div class="dashboard-page">
    <!-- HERO -->
    <section class="dash-hero">
      <div class="dash-hero-bg"></div>

      <div class="dash-hero-content">
        <!-- LEFT: copy + stats + CTAs -->
        <div class="dash-hero-left">
          <div class="hero-tag">System online · Player profile</div>
          <h1 class="hero-name">
            Welcome back,<br>
            <span class="hero-name-accent">{{ username }}</span>
          </h1>
          <p class="hero-tagline">{{ tagline }}</p>

          <div class="hero-stats">
            <div class="stat-block">
              <div class="stat-num stat-num--accent">{{ stats.badges }}</div>
              <div class="stat-lbl">Badges</div>
            </div>
            <div class="stat-block">
              <div class="stat-num">{{ stats.trophies }}</div>
              <div class="stat-lbl">Trophies</div>
            </div>
            <div class="stat-block">
              <div class="stat-num">{{ stats.achievements }}</div>
              <div class="stat-lbl">Achievements</div>
            </div>
            <div class="stat-block">
              <div class="stat-num">{{ stats.platforms }}</div>
              <div class="stat-lbl">Platforms</div>
            </div>
          </div>

          <div class="hero-actions">
            <router-link to="/forge" class="btn-hero">
              <span>Forge trophy</span>
              <span class="btn-arrow">→</span>
            </router-link>
            <router-link :to="virtualHallPath" class="btn-hero btn-hero--ghost">
              View virtual hall
            </router-link>
          </div>
        </div>

        <!-- RIGHT: mascot -->
        <div class="dash-hero-mascot">
          <div class="mascot-glow"></div>
          <img
            :src="trexHero"
            alt="TrophyRoom T-Rex"
            class="mascot-img"
          >
        </div>
      </div>
    </section>

    <!-- CONTENT SECTIONS -->
    <div class="dash-content">

      <!-- READY TO FORGE -->
      <section v-if="readyTrophy" class="dash-section">
        <div class="section-label">
          <span class="label-text">Ready to forge</span>
          <span class="section-meta">
            1 available · +{{ readyTrophy.xp || 0 }} XP · +{{ readyTrophy.receive || 0 }} Uru
          </span>
        </div>

        <div class="trophy-hero">
          <div class="trophy-hero-left">
            <div class="trophy-status">
              <span class="pulse-dot"></span>
              <span>{{ readyTrophy.badges_count }}/{{ readyTrophy.badges_count }} badges collected — ready</span>
            </div>
            <h2 class="trophy-hero-name">{{ readyTrophy.name }}</h2>
            <p class="trophy-hero-desc">{{ readyTrophy.description }}</p>
            <div class="trophy-hero-meta">
              <div class="meta-item">
                <div class="meta-val">+{{ readyTrophy.xp || 0 }}</div>
                <div class="meta-lbl">XP</div>
              </div>
              <div class="meta-item">
                <div class="meta-val">+{{ readyTrophy.receive || 0 }}</div>
                <div class="meta-lbl">Uru</div>
              </div>
              <div class="meta-item">
                <div class="meta-val meta-val--white">{{ readyTrophy.price || 0 }}</div>
                <div class="meta-lbl">Ambar cost</div>
              </div>
            </div>
            <router-link :to="{ path: '/forge', query: { trophy: readyTrophy.id } }" class="btn-hero">
              <span>Forge now</span>
              <span class="btn-arrow">→</span>
            </router-link>
          </div>

          <div class="trophy-hero-right">
            <div class="trophy-badge-hero">
              <img
                v-if="readyTrophy.image"
                :src="readyTrophy.image"
                :alt="readyTrophy.name"
              >
              <svg v-else viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
                <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
                <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
                <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
              </svg>
            </div>
          </div>
        </div>
      </section>

      <!-- TROPHIES IN PROGRESS -->
      <section v-if="progressTrophies.length" class="dash-section">
        <div class="section-label">
          <span class="label-text">Trophies in progress</span>
          <span class="section-meta">{{ progressTrophies.length }} queued</span>
        </div>

        <div class="trophy-thumbs">
          <router-link
            v-for="trophy in progressTrophies"
            :key="trophy.id"
            :to="{ path: '/forge', query: { trophy: trophy.id } }"
            class="trophy-thumb"
          >
            <div class="trophy-thumb-icon">
              <img v-if="trophy.image" :src="trophy.image" :alt="trophy.name">
              <svg v-else width="22" height="22" viewBox="0 0 24 24" :fill="trophyThumbColor(trophy)">
                <path d="M12 2l2.5 5 5.5.5-4 4 1 5.5L12 14l-5 3 1-5.5-4-4 5.5-.5z"/>
              </svg>
            </div>
            <div class="trophy-thumb-name">{{ trophy.name }}</div>
            <div class="trophy-thumb-meta">
              {{ trophy.badges_owned || 0 }}/{{ trophy.badges_count }} badges · {{ trophy.xp || 0 }} XP
            </div>
            <div class="progress-bar">
              <div
                class="progress-fill"
                :class="progressFillClass(trophy)"
                :style="{ width: progressPercent(trophy) + '%' }"
              ></div>
            </div>
          </router-link>
        </div>
      </section>

      <!-- DAILY MISSIONS -->
      <section class="dash-section">
        <div class="section-label">
          <span class="label-text">Daily missions</span>
          <span class="section-meta">Resets in {{ resetCountdown }}</span>
        </div>

        <div class="missions-terminal">
          <div class="missions-header">
            <div class="mission-count">
              {{ completedMissions }}/{{ missions.length }}<span> completed</span>
            </div>
            <div class="missions-pending">
              +{{ pendingReward }} Ambar pending
            </div>
          </div>
          <div class="missions-list">
            <div
              v-for="mission in missions"
              :key="mission.id"
              class="mission-row"
              :class="{ 'mission-row--done': mission.done }"
            >
              <div class="mission-check">{{ mission.done ? '✓' : '' }}</div>
              <div class="mission-info">
                <div class="mission-title">{{ mission.title }}</div>
                <div class="mission-sub">{{ mission.sub }}</div>
              </div>
              <div
                class="mission-reward"
                :class="{ 'mission-reward--green': mission.done }"
              >
                {{ mission.reward }}
              </div>
              <button
                v-if="!mission.done"
                class="mission-action"
                @click="handleMission(mission)"
              >
                {{ mission.actionLabel }} →
              </button>
              <div v-else class="mission-action mission-action--done">Done</div>
            </div>
          </div>
        </div>
      </section>

      <!-- RECENT ACTIVITY -->
      <section class="dash-section">
        <div class="section-label">
          <span class="label-text">Recent activity</span>
          <span v-if="notifications.length" class="section-meta">
            {{ notifications.length }} recent
          </span>
        </div>

        <div v-if="notifications.length" class="activity-list">
          <div
            v-for="notif in notifications.slice(0, 6)"
            :key="notif.id"
            class="activity-row"
          >
            <span class="activity-dot"></span>
            <div class="activity-content">
              <div class="activity-text">{{ notif.description }}</div>
              <div class="activity-time">{{ formatTime(notif.created_at) }}</div>
            </div>
          </div>
        </div>

        <div v-else class="activity-empty">
          <img
            :src="trexHero"
            alt=""
            class="activity-mascot"
          >
          <div class="activity-empty-text">
            <h3>The chest is empty</h3>
            <p>Forge your first trophy, share an achievement, or donate to a friend's post. Activity shows up here the moment something moves — rankings shift, badges unlock, Uru lands.</p>
            <router-link to="/forge" class="activity-cta">→ Go to Forge</router-link>
          </div>
        </div>
      </section>

      <!-- CONNECTED PLATFORMS -->
      <section class="dash-section">
        <div class="section-label">
          <span class="label-text">Connected platforms</span>
          <span class="section-meta">
            {{ syncedPlatforms.length }} synced · {{ availablePlatforms.length }} available
          </span>
        </div>

        <div class="platforms-strip">
          <a
            v-for="platform in platforms"
            :key="platform.key"
            href="#"
            class="platform-cell"
            :class="{ 'platform-cell--synced': platform.synced, 'platform-cell--off': !platform.synced }"
            @click.prevent="handlePlatformClick(platform)"
          >
            <div class="platform-logo" v-html="platform.svg"></div>
            <div class="platform-cell-name">{{ platform.name }}</div>
            <div class="platform-cell-status">
              {{ platform.synced ? `Synced · ${platform.count}` : 'Connect' }}
            </div>
          </a>
        </div>
      </section>

      <!-- TERMINAL FOOTER -->
      <div class="terminal-footer">
        <div>trophyroom.v2.0 · build 4.7<span class="cursor-blink"></span></div>
        <div>session · {{ sessionTime }} · uptime nominal</div>
      </div>

    </div>
  </div>
</template>

<script>
import trexHero from '../../../web/images/web/img/mascot/trex-voxel-hero.png';
import api from '../api/api.js';

export default {
  name: 'Dashboard',
  data() {
    return {
      trexHero,

      // Real data from APIs
      readyTrophy: null,
      progressTrophies: [],
      notifications: [],
      userBadges: [],
      userBadgeIds: [],
      userTrophyIds: [],
      allTrophies: [],

      // Missions — placeholder until backend has this concept
      // TODO: wire to backend when daily missions API exists
      missions: [
        { id: 1, title: 'Connect Discord', sub: 'Integration complete', reward: '+250', done: false, actionLabel: 'Connect' },
        { id: 2, title: 'Import more Discord badges', sub: 'Sync your latest achievements', reward: '+250', done: false, actionLabel: 'Sync' },
        { id: 3, title: 'Forge a trophy', sub: 'You have 1 ready to forge', reward: '+Uru', done: false, actionLabel: 'Forge' },
        { id: 4, title: 'Share an achievement', sub: 'Post to the feed and earn Ambar', reward: '+50', done: false, actionLabel: 'Post' }
      ],

      // Platforms — placeholder config; sync counts derived from userBadges.integration
      platformsConfig: [
        { key: 'discord', name: 'Discord', svg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4C2.9 4 2 4.9 2 6v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM9.5 16c-1.4 0-2.5-1.1-2.5-2.5S8.1 11 9.5 11s2.5 1.1 2.5 2.5S10.9 16 9.5 16zm5 0c-1.4 0-2.5-1.1-2.5-2.5S13.1 11 14.5 11s2.5 1.1 2.5 2.5S15.9 16 14.5 16z"/></svg>' },
        { key: 'steam', name: 'Steam', svg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>' },
        { key: 'github', name: 'GitHub', svg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.9 2.9 1.3 3.6 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.2-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.7.2 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3"/></svg>' },
        { key: 'riot', name: 'Riot Games', svg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm0 22C6.48 22 2 17.52 2 12S6.48 2 12 2s10 4.48 10 10-4.48 10-10 10zm-1-16v4H7v2h4v4h2v-4h4v-2h-4V6h-2z"/></svg>' },
        { key: 'strava', name: 'Strava', svg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M15.4 17.9l-2.1-4.1-3.1 6.1c-.3.6-.9 1.1-1.6 1.1H4.5c-.35 0-.7-.1-1-.3-.6-.3-.9-.9-.9-1.5 0-.25.05-.5.2-.75L11.6.7C11.85.05 12.5-.4 13.2-.4c.7 0 1.3.45 1.6 1.1l6.8 13.75c.1.25.2.5.2.75 0 .6-.35 1.2-.9 1.5-.3.2-.65.3-1 .3l-4.5-.1z"/></svg>' }
      ],

      // Session tracking
      sessionStart: Date.now(),
      sessionTime: '0h 0m',
      sessionTimer: null,

      // Reset countdown
      resetCountdown: '00:00:00',
      resetTimer: null
    };
  },
  computed: {
    username() {
      return this.$store?.state?.userUsername || 'User';
    },
    tagline() {
      const badges = this.userBadges.length;
      const platforms = this.syncedPlatforms.length;
      if (badges === 0) {
        return 'Fresh start. Connect your first platform and begin forging your legacy.';
      }
      return `A fullmetal heart. ${badges} badges forged across ${platforms} ${platforms === 1 ? 'platform' : 'platforms'}. Your virtual hall is taking shape — keep forging, keep earning.`;
    },
    stats() {
      const forgedCount = this.allTrophies.filter(t => t.forged).length;
      return {
        badges: this.userBadges.length,
        trophies: forgedCount,
        achievements: this.allTrophies.length,
        platforms: this.syncedPlatforms.length
      };
    },
    virtualHallPath() {
      return `/virtual-hall/${this.username}`;
    },
    syncedPlatforms() {
      // Derive from userBadges.integration
      const counts = {};
      this.userBadges.forEach(b => {
        const key = (b.integration || '').toLowerCase();
        if (key) counts[key] = (counts[key] || 0) + 1;
      });
      return this.platformsConfig
        .filter(p => counts[p.key] && counts[p.key] > 0)
        .map(p => ({ ...p, synced: true, count: counts[p.key] }));
    },
    availablePlatforms() {
      const synced = new Set(this.syncedPlatforms.map(p => p.key));
      return this.platformsConfig.filter(p => !synced.has(p.key));
    },
    platforms() {
      return [
        ...this.syncedPlatforms,
        ...this.availablePlatforms.map(p => ({ ...p, synced: false, count: 0 }))
      ];
    },
    completedMissions() {
      return this.missions.filter(m => m.done).length;
    },
    pendingReward() {
      return this.missions
        .filter(m => !m.done)
        .reduce((sum, m) => {
          const match = String(m.reward).match(/\d+/);
          return sum + (match ? parseInt(match[0]) : 0);
        }, 0);
    }
  },
  mounted() {
    this.loadDashboardData();
    this.startSessionTimer();
    this.startResetCountdown();
  },
  beforeUnmount() {
    if (this.sessionTimer) clearInterval(this.sessionTimer);
    if (this.resetTimer) clearInterval(this.resetTimer);
  },
  methods: {
    async loadDashboardData() {
      try {
        // Real endpoints use /api/ prefix (see Forge.vue/TrophyRoom.vue patterns).
        // Shapes: badges->data.data, forge->data.trophies, available-trophies->data.trophies, notification->data[0].data
        const [badgesRes, forgeRes, availableRes, notifRes] = await Promise.all([
          api.get('/api/badges').catch(() => ({ data: { data: [] } })),
          api.get('/api/forge').catch(() => ({ data: { trophies: [] } })),
          api.get('/api/forge/available-trophies').catch(() => ({ data: { trophies: [] } })),
          api.get('/api/notification').catch(() => ({ data: [] }))
        ]);

        this.userBadges = badgesRes.data?.data || [];
        this.userBadgeIds = this.userBadges.map(b => b.id);

        this.userTrophyIds = (availableRes.data?.trophies || []).map(t => t.id);

        // Immutable enrichment — do NOT mutate raw API objects
        const rawTrophies = forgeRes.data?.trophies || [];
        this.allTrophies = rawTrophies.map(t => ({
          ...t,
          badges_count: t.badges?.length || 0,
          badges_owned: t.badges?.filter(b => this.userBadgeIds.includes(b.id)).length || 0,
          forged: this.userTrophyIds.includes(t.id),
          xp: t.weight || t.price || 0,
          image: t.image ? `/storage/trophies/${t.image}` : null
        }));

        // Ready to forge: all badges collected AND not yet forged
        this.readyTrophy = this.allTrophies.find(t =>
          t.badges_count > 0 && t.badges_owned >= t.badges_count && !t.forged
        ) || null;

        // In progress: partial badges, not complete, not forged, not the ready one
        this.progressTrophies = this.allTrophies
          .filter(t =>
            t.id !== this.readyTrophy?.id &&
            t.badges_count > 0 &&
            t.badges_owned > 0 &&
            t.badges_owned < t.badges_count &&
            !t.forged
          )
          .slice(0, 4);

        // Notifications shape: resp.data is an array-wrapped paginator [{data: [...]}]
        const notifWrapper = Array.isArray(notifRes.data) ? notifRes.data[0] : notifRes.data;
        this.notifications = (notifWrapper?.data || (Array.isArray(notifWrapper) ? notifWrapper : [])) || [];

        this.updateMissionStates();
      } catch (err) {
        console.error('[Dashboard] Failed to load data', err);
      }
    },
    updateMissionStates() {
      // Mission 1: Connect Discord → done if any discord badge exists
      const hasDiscord = this.userBadges.some(b =>
        (b.integration || '').toLowerCase() === 'discord'
      );
      const m1 = this.missions.find(m => m.id === 1);
      if (m1) m1.done = hasDiscord;

      // Mission 3: Forge a trophy → done if any trophy is forged
      const hasForged = this.allTrophies.some(t => t.forged);
      const m3 = this.missions.find(m => m.id === 3);
      if (m3) m3.done = hasForged;
    },
    handleMission(mission) {
      // Discord connect (mission 1 or 2 when Discord is involved): preserve existing OAuth flow
      if (mission.id === 1 || mission.id === 2) {
        this.connectPlatform('discord');
        return;
      }
      const routes = {
        3: '/forge',
        4: '/feed'
      };
      const path = routes[mission.id];
      if (path) this.$router.push(path);
    },
    handlePlatformClick(platform) {
      if (platform.synced) return;
      this.connectPlatform(platform.key);
    },
    connectPlatform(type) {
      // Preserve legacy OAuth flow — redirects to Laravel's /login/{provider}?token=... endpoint
      const token = localStorage.getItem('access_token');
      window.location.href = '/login/' + type + '?token=' + encodeURIComponent(token || '');
    },
    progressPercent(trophy) {
      const total = trophy.badges_count || 1;
      const owned = trophy.badges_owned || 0;
      return Math.round((owned / total) * 100);
    },
    progressFillClass(trophy) {
      const p = this.progressPercent(trophy);
      if (p === 0) return 'progress-fill--empty';
      if (p === 100) return 'progress-fill--full';
      return 'progress-fill--in';
    },
    trophyThumbColor(trophy) {
      const owned = trophy.badges_owned || 0;
      return owned > 0 ? '#ff6100' : '#5a5550';
    },
    formatTime(timestamp) {
      if (!timestamp) return '';
      const date = new Date(timestamp);
      const now = Date.now();
      const diffMin = Math.floor((now - date.getTime()) / 60000);
      if (diffMin < 1) return 'just now';
      if (diffMin < 60) return `${diffMin}m ago`;
      const diffHr = Math.floor(diffMin / 60);
      if (diffHr < 24) return `${diffHr}h ago`;
      const diffDay = Math.floor(diffHr / 24);
      return `${diffDay}d ago`;
    },
    startSessionTimer() {
      const update = () => {
        const elapsed = Date.now() - this.sessionStart;
        const mins = Math.floor(elapsed / 60000);
        const hours = Math.floor(mins / 60);
        this.sessionTime = `${hours}h ${mins % 60}m`;
      };
      update();
      this.sessionTimer = setInterval(update, 30000);
    },
    startResetCountdown() {
      const update = () => {
        const now = new Date();
        const midnight = new Date(now);
        midnight.setHours(24, 0, 0, 0);
        const diff = midnight - now;
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        this.resetCountdown = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
      };
      update();
      this.resetTimer = setInterval(update, 1000);
    }
  }
};
</script>

<style lang="scss" scoped>
.dashboard-page {
  min-width: 0;
  max-width: 100%;
  padding-top: 0;
}

/* ========== HERO ========== */
.dash-hero {
  position: relative;
  padding: 48px 48px 64px;
  border-bottom: 1px solid rgba(255, 97, 0, 0.1);
  overflow: hidden;
  min-height: 440px;
}
.dash-hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background:
    radial-gradient(ellipse 700px 500px at 85% 50%, rgba(255, 97, 0, 0.18), transparent 60%),
    radial-gradient(ellipse 500px 400px at 85% 50%, rgba(193, 245, 39, 0.06), transparent 65%);
}
.dash-hero-content {
  position: relative;
  z-index: 2;
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 48px;
  align-items: center;
  min-height: 380px;
}

.dash-hero-left {
  min-width: 0;
}

.hero-tag {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-bottom: 18px;
}
.hero-name {
  font-family: var(--display);
  font-size: 56px;
  line-height: 1;
  letter-spacing: 0.03em;
  color: var(--text);
  margin-bottom: 18px;
}
.hero-name-accent {
  color: var(--primary);
  text-shadow: 0 0 24px rgba(255, 97, 0, 0.35);
}
.hero-tagline {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.7;
  max-width: 520px;
  margin-bottom: 28px;
}

.hero-stats {
  display: flex;
  gap: 36px;
  margin-bottom: 32px;
  flex-wrap: wrap;
}
.stat-block {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.stat-num {
  font-family: var(--display);
  font-size: 36px;
  line-height: 1;
  color: var(--text);
}
.stat-num--accent {
  color: var(--accent);
  text-shadow: 0 0 12px rgba(193, 245, 39, 0.3);
}
.stat-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

.hero-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.btn-hero {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: var(--primary);
  color: var(--bg);
  font-size: 12px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.15s;
  font-family: var(--mono);
  border: 1px solid var(--primary);
}
.btn-hero:hover {
  box-shadow: 0 0 20px rgba(255, 97, 0, 0.5);
}
.btn-hero--ghost {
  background: transparent;
  color: var(--text-muted);
  border-color: var(--border);
}
.btn-hero--ghost:hover {
  color: var(--text);
  border-color: var(--primary);
  box-shadow: none;
}
.btn-arrow {
  font-size: 14px;
}

/* Mascot */
.dash-hero-mascot {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.mascot-glow {
  position: absolute;
  inset: -20%;
  background: radial-gradient(circle, rgba(255, 97, 0, 0.25), transparent 60%);
  filter: blur(20px);
  pointer-events: none;
  animation: mascot-glow-pulse 3s ease-in-out infinite;
}
.mascot-img {
  position: relative;
  z-index: 2;
  width: 340px;
  height: 340px;
  object-fit: contain;
  animation: mascot-float 4s ease-in-out infinite;
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.5));
}

@keyframes mascot-glow-pulse {
  0%, 100% { opacity: 0.7; }
  50% { opacity: 1; }
}
@keyframes mascot-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}
@media (prefers-reduced-motion: reduce) {
  .mascot-img, .mascot-glow { animation: none; }
}

/* ========== CONTENT ========== */
.dash-content {
  padding: 32px 48px 48px;
  display: flex;
  flex-direction: column;
  gap: 36px;
}

.dash-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-label {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.label-text {
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
.section-meta {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

/* ========== READY TO FORGE ========== */
.trophy-hero {
  display: grid;
  grid-template-columns: 1fr 220px;
  gap: 40px;
  padding: 28px 32px;
  background: var(--surface);
  border: 1px solid rgba(193, 245, 39, 0.2);
  border-radius: 6px;
  align-items: center;
  position: relative;
  overflow: hidden;
}
.trophy-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 80% 50%, rgba(193, 245, 39, 0.08), transparent 70%);
  pointer-events: none;
}
.trophy-hero-left { position: relative; z-index: 1; min-width: 0; }
.trophy-hero-right { position: relative; z-index: 1; display: flex; justify-content: center; }

.trophy-status {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 10px;
  color: var(--accent);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  margin-bottom: 14px;
}
.pulse-dot {
  width: 6px;
  height: 6px;
  background: var(--accent);
  border-radius: 50%;
  box-shadow: 0 0 10px var(--accent);
  animation: pulse-dot 1.4s ease-in-out infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.3); }
}

.trophy-hero-name {
  font-family: var(--display);
  font-size: 36px;
  line-height: 1.05;
  color: var(--text);
  margin-bottom: 12px;
}
.trophy-hero-desc {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.7;
  margin-bottom: 20px;
  max-width: 560px;
}
.trophy-hero-meta {
  display: flex;
  gap: 28px;
  margin-bottom: 22px;
  flex-wrap: wrap;
}
.meta-item { display: flex; flex-direction: column; gap: 4px; }
.meta-val {
  font-family: var(--display);
  font-size: 22px;
  color: var(--accent);
  line-height: 1;
}
.meta-val--white { color: var(--text); }
.meta-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.trophy-badge-hero {
  width: 200px;
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 24px rgba(193, 245, 39, 0.25));
}
.trophy-badge-hero img,
.trophy-badge-hero svg {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

/* ========== TROPHY THUMBS ========== */
.trophy-thumbs {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px;
}
.trophy-thumb {
  padding: 18px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-decoration: none;
  color: inherit;
  transition: border-color 0.15s;
}
.trophy-thumb:hover {
  border-color: rgba(255, 97, 0, 0.3);
}
.trophy-thumb-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}
.trophy-thumb-icon img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.trophy-thumb-name {
  font-size: 13px;
  color: var(--text);
  letter-spacing: 0.02em;
}
.trophy-thumb-meta {
  font-size: 10px;
  color: var(--text-muted);
  letter-spacing: 0.1em;
  text-transform: uppercase;
}
.progress-bar {
  height: 3px;
  background: var(--surface-3);
  overflow: hidden;
  border-radius: 2px;
  margin-top: 4px;
}
.progress-fill {
  height: 100%;
  transition: width 0.4s ease;
}
.progress-fill--empty { background: var(--surface-3); }
.progress-fill--in { background: var(--accent); }
.progress-fill--full { background: var(--primary); }

/* ========== MISSIONS TERMINAL ========== */
.missions-terminal {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  overflow: hidden;
}
.missions-header {
  padding: 14px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--surface-2);
  border-bottom: 1px solid var(--border);
}
.mission-count {
  font-family: var(--display);
  font-size: 18px;
  color: var(--text);
}
.mission-count span {
  font-family: var(--mono);
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-left: 6px;
}
.missions-pending {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

.missions-list {
  display: flex;
  flex-direction: column;
}
.mission-row {
  padding: 14px 20px;
  display: grid;
  grid-template-columns: 28px 1fr auto auto;
  gap: 14px;
  align-items: center;
  border-bottom: 1px solid var(--border);
  transition: background 0.15s;
}
.mission-row:last-child { border-bottom: none; }
.mission-row:hover { background: rgba(255, 97, 0, 0.02); }
.mission-row--done { opacity: 0.6; }

.mission-check {
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  border-radius: 50%;
  font-size: 12px;
  color: var(--accent);
}
.mission-row--done .mission-check {
  border-color: var(--accent);
  background: rgba(193, 245, 39, 0.1);
}

.mission-info { min-width: 0; }
.mission-title {
  font-size: 13px;
  color: var(--text);
  letter-spacing: 0.02em;
  margin-bottom: 2px;
}
.mission-sub {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.08em;
}

.mission-reward {
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.1em;
  padding: 4px 10px;
  background: rgba(255, 97, 0, 0.1);
  border-radius: 4px;
  font-family: var(--mono);
}
.mission-reward--green {
  color: var(--accent);
  background: rgba(193, 245, 39, 0.12);
}

.mission-action {
  font-size: 10px;
  color: var(--primary);
  background: none;
  border: 1px solid var(--primary);
  padding: 6px 12px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  cursor: pointer;
  font-family: var(--mono);
  transition: all 0.15s;
}
.mission-action:hover {
  background: var(--primary);
  color: var(--bg);
}
.mission-action--done {
  border: 1px dashed var(--border);
  color: var(--text-dim);
  cursor: default;
}

/* ========== RECENT ACTIVITY ========== */
.activity-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 6px 0;
}
.activity-row {
  padding: 12px 20px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  transition: background 0.15s;
}
.activity-row:hover { background: rgba(255, 97, 0, 0.02); }
.activity-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
  flex-shrink: 0;
  margin-top: 5px;
}
.activity-content { flex: 1; min-width: 0; }
.activity-text {
  font-size: 13px;
  color: var(--text);
  line-height: 1.5;
}
.activity-time {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  margin-top: 4px;
}

.activity-empty {
  padding: 40px 32px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 28px;
}
.activity-mascot {
  width: 140px;
  height: 140px;
  object-fit: contain;
  opacity: 0.9;
  filter: drop-shadow(0 0 20px rgba(255, 97, 0, 0.2));
  flex-shrink: 0;
}
.activity-empty-text { flex: 1; min-width: 0; }
.activity-empty-text h3 {
  font-family: var(--display);
  font-size: 22px;
  color: var(--text);
  margin-bottom: 10px;
}
.activity-empty-text p {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.7;
  margin-bottom: 14px;
  max-width: 520px;
}
.activity-cta {
  display: inline-block;
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  text-decoration: none;
  border-bottom: 1px solid var(--primary);
  padding-bottom: 2px;
}

/* ========== PLATFORMS ========== */
.platforms-strip {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 10px;
}
.platform-cell {
  padding: 18px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: inherit;
  transition: all 0.15s;
  cursor: pointer;
}
.platform-cell:hover { border-color: rgba(255, 97, 0, 0.3); }
.platform-logo {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
}
.platform-cell--synced .platform-logo { color: var(--primary); }
.platform-cell--off .platform-logo { color: var(--text-dim); }

.platform-cell-name {
  font-size: 12px;
  color: var(--text);
  letter-spacing: 0.04em;
}
.platform-cell-status {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
.platform-cell--synced .platform-cell-status { color: var(--accent); }

/* ========== TERMINAL FOOTER ========== */
.terminal-footer {
  display: flex;
  justify-content: space-between;
  padding: 20px 0;
  border-top: 1px dashed rgba(255, 97, 0, 0.1);
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
  flex-wrap: wrap;
  gap: 12px;
}
.cursor-blink {
  display: inline-block;
  width: 7px;
  height: 10px;
  background: var(--primary);
  margin-left: 4px;
  animation: cursor-blink 1s step-end infinite;
  vertical-align: middle;
}
@keyframes cursor-blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

/* ========== RESPONSIVE ========== */
@media (max-width: 900px) {
  .dash-hero {
    padding: 32px 24px 48px;
  }
  .dash-hero-content {
    grid-template-columns: 1fr;
    gap: 28px;
  }
  .dash-hero-mascot { justify-content: flex-start; }
  .mascot-img { width: 220px; height: 220px; }

  .hero-name { font-size: 36px; }

  .dash-content { padding: 24px 24px 40px; }

  .trophy-hero { grid-template-columns: 1fr; gap: 24px; }
  .trophy-hero-right { justify-content: flex-start; }
  .trophy-badge-hero { width: 140px; height: 140px; }

  .activity-empty { flex-direction: column; align-items: flex-start; padding: 24px; }
  .activity-mascot { width: 100px; height: 100px; }
}

@media (max-width: 600px) {
  .hero-name { font-size: 28px; }
  .hero-stats { gap: 20px; }
  .stat-num { font-size: 28px; }
  .trophy-hero-name { font-size: 26px; }
}
</style>

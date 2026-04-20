<template>
  <div class="trophy-room-page">
    <!-- HERO -->
    <section class="tr-hero">
      <div class="tr-hero-bg"></div>

      <div class="tr-hero-content">
        <div class="tr-hero-main">
          <div class="page-tag">Your private vault</div>
          <h1 class="page-title">Trophy <span class="page-title-accent">Room</span></h1>
          <p class="page-subtitle">
            Every badge, trophy, and achievement you've earned — forged, imported, or created.
            Manage what appears on your Virtual Hall and curate what the world sees.
          </p>
        </div>

        <div class="tr-hero-aside">
          <router-link :to="virtualHallPath" class="vhall-preview">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
            <span>Virtual Hall preview</span>
          </router-link>

          <div class="quick-stats">
            <div class="qstat qstat--highlight">
              <div class="qstat-val">{{ badges.length }}</div>
              <div class="qstat-lbl">Badges</div>
            </div>
            <div class="qstat">
              <div class="qstat-val">{{ forgedTrophies.length }}</div>
              <div class="qstat-lbl">Trophies</div>
            </div>
            <div class="qstat">
              <div class="qstat-val">{{ customAchievements.length }}</div>
              <div class="qstat-lbl">Custom</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="tr-content">

      <!-- FORGED TROPHIES -->
      <section class="tr-section">
        <div class="section-header">
          <div class="section-label">Forged trophies</div>
          <div class="section-toolbar">
            <span class="section-count">{{ forgedTrophies.length }} total</span>
          </div>
        </div>

        <div v-if="forgedTrophies.length" class="trophies-vault">
          <div
            v-for="trophy in forgedTrophies"
            :key="trophy.id"
            class="vault-trophy"
            :class="{ 'vault-trophy--showcased': trophy.in_hall }"
          >
            <div v-if="trophy.in_hall" class="hall-flag">
              <span class="hall-flag-dot"></span>
              <span>In Virtual Hall</span>
            </div>

            <div class="vt-header">
              <div class="vt-asset">
                <img v-if="trophy.image" :src="trophyImage(trophy)" :alt="trophy.name">
                <svg v-else viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                  <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
                  <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
                  <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
                  <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
                </svg>
              </div>
              <div class="vt-info">
                <div class="vt-name">{{ trophy.name }}</div>
                <div class="vt-sub">Forged · {{ formatDate(trophy.pivot && trophy.pivot.created_at || trophy.created_at) }}</div>
              </div>
            </div>

            <div class="vt-desc">{{ trophy.description || '' }}</div>

            <div class="vt-meta">
              <div class="vt-meta-stats">
                <span class="vt-xp">+{{ Math.floor(trophy.weight || trophy.price || 0) }} XP</span>
                <span v-if="trophy.series" class="vt-series">{{ trophy.series }}</span>
              </div>
              <div class="vt-actions">
                <button
                  class="vt-action vt-action--toggle"
                  :class="{ 'vt-action--remove': trophy.in_hall }"
                  :title="trophy.in_hall ? 'Remove from Virtual Hall' : 'Add to Virtual Hall'"
                  :disabled="togglingId === 'trophy-' + trophy.id"
                  @click="toggleShowcase('trophy', trophy)"
                >
                  <svg v-if="trophy.in_hall" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L6 18M6 6l12 12"/>
                  </svg>
                  <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="empty-section">
          <p>No forged trophies yet. Head to the Forge when you have enough badges.</p>
          <router-link to="/forge" class="empty-cta">→ Go to Forge</router-link>
        </div>

        <div v-if="readyToForge" class="promo-hint">
          <div class="promo-text">
            <strong>You have {{ readyToForge.name }} ready to forge.</strong>
            {{ readyBadgesCount }}/{{ readyBadgesCount }} badges collected.
          </div>
          <router-link :to="{ path: '/forge', query: { trophy: readyToForge.id } }" class="promo-cta">
            <span>Go to Forge</span>
            <span>→</span>
          </router-link>
        </div>
      </section>

      <!-- CUSTOM ACHIEVEMENTS -->
      <section class="tr-section">
        <div class="section-header">
          <div class="section-label">Custom achievements</div>
          <div class="section-toolbar">
            <span class="section-count">
              {{ customAchievements.length }} total
              <span v-if="validatedAchievements.length">
                · {{ validatedAchievements.length }} validated
              </span>
            </span>
          </div>
        </div>

        <div v-if="customAchievements.length" class="achievements-list">
          <div
            v-for="ach in customAchievements"
            :key="ach.id"
            class="ach-row"
          >
            <div class="ach-thumb">
              <img v-if="ach.image" :src="achievementImage(ach)" :alt="ach.name">
              <div v-else class="ach-thumb-inner" :style="achThumbStyle(ach)">
                {{ achThumbLabel(ach) }}
              </div>
            </div>
            <div class="ach-info">
              <div class="ach-name">{{ ach.name }}</div>
              <div class="ach-desc">{{ ach.description || '' }}</div>
            </div>
            <div
              v-if="ach.validated"
              class="ach-status-badge"
            >Validated</div>
            <button
              class="ach-cta"
              :class="{ 'ach-cta--active': ach.in_hall }"
              :disabled="togglingId === 'ach-' + ach.id"
              @click="toggleShowcase('achievement', ach)"
            >
              {{ ach.in_hall ? 'In hall' : 'Add to hall' }}
            </button>
          </div>
        </div>

        <div v-else class="empty-section">
          <p>No custom achievements yet. Create one from the Feed composer.</p>
          <router-link to="/feed" class="empty-cta">→ Go to Feed</router-link>
        </div>
      </section>

      <!-- PLATFORM BADGES -->
      <section class="tr-section">
        <div class="section-header">
          <div class="section-label">Platform badges</div>
          <div class="section-toolbar">
            <span class="section-count">
              {{ badges.length }} synced · {{ platformGroups.length }} {{ platformGroups.length === 1 ? 'platform' : 'platforms' }}
            </span>
            <div class="filter-pills">
              <button
                class="filter-pill"
                :class="{ 'filter-pill--active': activePlatformFilter === 'all' }"
                @click="activePlatformFilter = 'all'"
              >
                All
              </button>
              <button
                v-for="group in platformGroups"
                :key="group.key"
                class="filter-pill"
                :class="{ 'filter-pill--active': activePlatformFilter === group.key }"
                @click="activePlatformFilter = group.key"
              >
                {{ group.name }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="filteredPlatformGroups.length" class="platform-sections">
          <div
            v-for="group in filteredPlatformGroups"
            :key="group.key"
            class="platform-section"
          >
            <div class="platform-header-row">
              <div class="ph-title">
                <div class="ph-icon" v-html="group.icon"></div>
                <div>
                  <div class="ph-name">{{ group.name }}</div>
                  <div class="ph-sub">Synced · {{ group.lastSyncLabel }}</div>
                </div>
              </div>
              <div class="ph-count">{{ group.badges.length }}</div>
            </div>

            <div class="badges-grid">
              <div
                v-for="badge in group.badges"
                :key="badge.id"
                class="badge-tile"
                :class="badgeRarity(badge)"
                :title="badge.name || ''"
              >
                <img v-if="badge.image" :src="badgeImage(badge, group.key)" :alt="badge.name">
                <span v-else class="badge-emoji">{{ badge.emoji || '🏆' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="empty-section">
          <p>No platform badges yet. Connect your gaming accounts to start importing.</p>
          <router-link to="/profile" class="empty-cta">→ Connect platforms</router-link>
        </div>

        <div v-if="availablePlatforms.length" class="promo-hint">
          <div class="promo-text">
            <strong>Connect more platforms to grow your vault.</strong>
            {{ availablePlatforms.join(', ') }} {{ availablePlatforms.length === 1 ? 'is' : 'are' }} waiting — each connection earns Ambar per imported badge.
          </div>
          <router-link to="/profile" class="promo-cta">
            <span>Connect platforms</span>
            <span>→</span>
          </router-link>
        </div>
      </section>

    </div>

    <!-- TERMINAL FOOTER -->
    <div class="terminal-strip">
      <div>
        trophy_room · vault · {{ totalItems }} items indexed<span class="cursor-blink"></span>
      </div>
      <div>last indexed · now · nominal</div>
    </div>
  </div>
</template>

<script>
import api from '../api/api.js';

export default {
  name: 'TrophyRoom',
  data() {
    return {
      badges: [],
      // User's forged trophies (from /api/forge/trophies — includes pivot.display)
      userTrophies: [],
      // All trophy templates (from /api/forge) — used to compute readyToForge
      catalogTrophies: [],
      // Custom user achievements (from /api/achievement?status=1)
      achievements: [],

      activePlatformFilter: 'all',
      togglingId: null, // 'trophy-{id}' or 'ach-{id}' during in-flight toggle

      platformConfig: {
        discord: {
          name: 'Discord',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4C2.9 4 2 4.9 2 6v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM9.5 16c-1.4 0-2.5-1.1-2.5-2.5S8.1 11 9.5 11s2.5 1.1 2.5 2.5S10.9 16 9.5 16zm5 0c-1.4 0-2.5-1.1-2.5-2.5S13.1 11 14.5 11s2.5 1.1 2.5 2.5S15.9 16 14.5 16z"/></svg>'
        },
        steam: {
          name: 'Steam',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>'
        },
        github: {
          name: 'GitHub',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.9 2.9 1.3 3.6 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.2-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.7.2 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3"/></svg>'
        },
        riot: {
          name: 'Riot Games',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm0 22C6.48 22 2 17.52 2 12S6.48 2 12 2s10 4.48 10 10-4.48 10-10 10z"/></svg>'
        },
        strava: {
          name: 'Strava',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M15.4 17.9l-2.1-4.1-3.1 6.1c-.3.6-.9 1.1-1.6 1.1H4.5c-.35 0-.7-.1-1-.3-.6-.3-.9-.9-.9-1.5 0-.25.05-.5.2-.75L11.6.7C11.85.05 12.5-.4 13.2-.4c.7 0 1.3.45 1.6 1.1l6.8 13.75c.1.25.2.5.2.75 0 .6-.35 1.2-.9 1.5-.3.2-.65.3-1 .3l-4.5-.1z"/></svg>'
        }
      }
    };
  },
  computed: {
    username() {
      return this.$store?.state?.userUsername || 'user';
    },
    virtualHallPath() {
      return `/virtual-hall/${this.username}`;
    },

    // Forged trophies — from /api/forge/trophies (includes pivot.display). Exclude NFTs for safety.
    forgedTrophies() {
      return this.userTrophies
        .filter(t => t.is_nft !== 1)
        .map(t => ({
          ...t,
          in_hall: !!(t.pivot && t.pivot.display)
        }));
    },

    // Ready to forge: trophy from catalog with all badges collected but not yet forged
    readyToForge() {
      const userBadgeIds = new Set(this.badges.map(b => b.id));
      const forgedIds = new Set(this.userTrophies.map(t => t.id));
      return this.catalogTrophies.find(t => {
        if (forgedIds.has(t.id)) return false;
        const trophyBadges = t.badges || [];
        if (!trophyBadges.length) return false;
        return trophyBadges.every(b => userBadgeIds.has(b.id));
      });
    },
    readyBadgesCount() {
      return this.readyToForge?.badges?.length || 0;
    },

    // Custom achievements — use .display as in_hall, derive validated from status === 1
    customAchievements() {
      return this.achievements.map(a => ({
        ...a,
        in_hall: !!a.display,
        validated: a.status === 1
      }));
    },
    validatedAchievements() {
      return this.customAchievements.filter(a => a.validated);
    },

    // Group badges by integration (only non-empty platforms)
    platformGroups() {
      const groups = {};
      this.badges.forEach(b => {
        const key = (typeof b.integration === 'string' ? b.integration : b.integration?.name || '').toLowerCase();
        if (!key) return;
        if (!groups[key]) groups[key] = [];
        groups[key].push(b);
      });

      return Object.entries(groups)
        .map(([key, badges]) => {
          const config = this.platformConfig[key] || {
            name: key.charAt(0).toUpperCase() + key.slice(1),
            icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>'
          };
          return {
            key,
            name: config.name,
            icon: config.icon,
            badges,
            lastSyncLabel: 'recently'
          };
        })
        .sort((a, b) => b.badges.length - a.badges.length);
    },

    filteredPlatformGroups() {
      if (this.activePlatformFilter === 'all') return this.platformGroups;
      return this.platformGroups.filter(g => g.key === this.activePlatformFilter);
    },

    availablePlatforms() {
      const syncedKeys = new Set(this.platformGroups.map(g => g.key));
      return Object.entries(this.platformConfig)
        .filter(([key]) => !syncedKeys.has(key))
        .map(([, config]) => config.name);
    },

    totalItems() {
      return this.badges.length + this.forgedTrophies.length + this.customAchievements.length;
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    async loadData() {
      try {
        const [badgesRes, catalogRes, userTrophiesRes, achRes] = await Promise.all([
          api.get('/api/badges').catch(() => ({ data: { data: [] } })),
          api.get('/api/forge').catch(() => ({ data: { trophies: [] } })),
          api.get('/api/forge/trophies').catch(() => ({ data: { trophies: [] } })),
          api.get('/api/achievement?status=1').catch(() => ({ data: { data: [] } }))
        ]);

        this.badges = badgesRes.data?.data || [];
        this.catalogTrophies = catalogRes.data?.trophies || [];
        this.userTrophies = userTrophiesRes.data?.trophies || [];
        this.achievements = achRes.data?.data || [];
      } catch (err) {
        console.error('[TrophyRoom] Failed to load data', err);
      }
    },

    async toggleShowcase(type, item) {
      const togglingKey = type === 'trophy' ? `trophy-${item.id}` : `ach-${item.id}`;
      if (this.togglingId === togglingKey) return;
      this.togglingId = togglingKey;

      const wasInHall = item.in_hall;
      // Optimistic update
      this.applyShowcaseChange(type, item.id, !wasInHall);

      try {
        // Real endpoints are GET, separate add/remove routes (per parts/*.vue legacy components):
        //   trophy add:        GET /api/forge/{id}/showcase
        //   trophy remove:     GET /api/forge/{id}/remove
        //   achievement add:   GET /api/achievement/{id}/showcase
        //   achievement remove:GET /api/achievement/{id}/removeShowcase
        let endpoint;
        if (type === 'trophy') {
          endpoint = wasInHall
            ? `/api/forge/${item.id}/remove`
            : `/api/forge/${item.id}/showcase`;
        } else {
          endpoint = wasInHall
            ? `/api/achievement/${item.id}/removeShowcase`
            : `/api/achievement/${item.id}/showcase`;
        }

        await api.get(endpoint);
      } catch (err) {
        // Revert on failure
        this.applyShowcaseChange(type, item.id, wasInHall);
        console.error('[TrophyRoom] Toggle showcase failed', err);
      } finally {
        this.togglingId = null;
      }
    },

    applyShowcaseChange(type, id, value) {
      if (type === 'trophy') {
        const idx = this.userTrophies.findIndex(x => x.id === id);
        if (idx >= 0) {
          // Immutable update — write into pivot.display (real field)
          const existing = this.userTrophies[idx];
          this.userTrophies.splice(idx, 1, {
            ...existing,
            pivot: { ...(existing.pivot || {}), display: value }
          });
        }
      } else {
        const idx = this.achievements.findIndex(x => x.id === id);
        if (idx >= 0) {
          const existing = this.achievements[idx];
          this.achievements.splice(idx, 1, { ...existing, display: value });
        }
      }
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
      } catch {
        return '';
      }
    },

    trophyImage(trophy) {
      if (!trophy.image) return '';
      if (trophy.image.startsWith('http') || trophy.image.startsWith('/')) return trophy.image;
      return `/storage/trophies/${trophy.image}`;
    },
    achievementImage(ach) {
      if (!ach.image) return '';
      if (ach.image.startsWith('http') || ach.image.startsWith('/')) return ach.image;
      return `/storage/achievements/${ach.image}`;
    },
    badgeImage(badge, platformKey) {
      if (!badge.image) return '';
      if (badge.image.startsWith('http') || badge.image.startsWith('/')) return badge.image;
      const integration = (typeof badge.integration === 'string'
        ? badge.integration
        : badge.integration?.name || platformKey || '').toLowerCase();
      return `/storage/integrations/${integration}/${badge.image}`;
    },

    badgeRarity(badge) {
      if (!badge.rarity) return '';
      const r = String(badge.rarity).toLowerCase();
      if (['legendary', 'epic', 'rare'].includes(r)) return `badge-tile--${r}`;
      return '';
    },

    achThumbStyle(ach) {
      if (!ach.name) return {};
      const hash = ach.name.split('').reduce((h, c) => h + c.charCodeAt(0), 0);
      const hue = (hash * 37) % 360;
      return {
        background: `linear-gradient(135deg, hsl(${hue}, 40%, 22%), hsl(${(hue + 30) % 360}, 40%, 15%))`
      };
    },
    achThumbLabel(ach) {
      if (!ach.name) return '??';
      return ach.name
        .split(' ')
        .slice(0, 2)
        .map(w => w[0])
        .join('')
        .toUpperCase();
    }
  }
};
</script>

<style lang="scss" scoped>
.trophy-room-page {
  min-width: 0;
  max-width: 100%;
}

/* ========== HERO ========== */
.tr-hero {
  position: relative;
  padding: 48px 48px 40px;
  border-bottom: 1px solid rgba(255, 97, 0, 0.1);
  overflow: hidden;
}
.tr-hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background:
    radial-gradient(ellipse 900px 500px at 15% 50%, rgba(255, 97, 0, 0.14), transparent 65%),
    radial-gradient(ellipse 700px 400px at 90% 50%, rgba(193, 245, 39, 0.06), transparent 65%);
}
.tr-hero-content {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 48px;
  align-items: center;
}
.tr-hero-main { min-width: 0; }
.page-tag {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-bottom: 14px;
}
.page-title {
  font-family: var(--display);
  font-size: 64px;
  line-height: 1;
  letter-spacing: 0.03em;
  color: var(--text);
  margin-bottom: 16px;
}
.page-title-accent {
  color: var(--primary);
  text-shadow: 0 0 24px rgba(255, 97, 0, 0.35);
  border-bottom: 3px solid var(--primary);
  padding-bottom: 2px;
}
.page-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.7;
  max-width: 560px;
}

.tr-hero-aside {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.vhall-preview {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  font-size: 11px;
  color: var(--text-muted);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  transition: all 0.15s;
  text-decoration: none;
  width: fit-content;
  align-self: flex-end;
}
.vhall-preview:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.quick-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.qstat {
  padding: 14px 12px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  text-align: center;
}
.qstat--highlight {
  border-color: rgba(193, 245, 39, 0.3);
  background: linear-gradient(180deg, rgba(193, 245, 39, 0.06), transparent);
}
.qstat-val {
  font-family: var(--display);
  font-size: 28px;
  color: var(--text);
  line-height: 1;
}
.qstat--highlight .qstat-val {
  color: var(--accent);
  text-shadow: 0 0 12px rgba(193, 245, 39, 0.35);
}
.qstat-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  margin-top: 4px;
}

/* ========== CONTENT ========== */
.tr-content {
  padding: 40px 48px;
  display: flex;
  flex-direction: column;
  gap: 44px;
}
.tr-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.section-label {
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
.section-toolbar {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}
.section-count {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.filter-pills {
  display: flex;
  gap: 6px;
}
.filter-pill {
  padding: 6px 14px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 20px;
  color: var(--text-muted);
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.15s;
}
.filter-pill:hover { border-color: rgba(255, 97, 0, 0.4); color: var(--text); }
.filter-pill--active {
  background: var(--accent);
  color: var(--bg);
  border-color: var(--accent);
}

/* ========== VAULT TROPHIES ========== */
.trophies-vault {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 14px;
}
.vault-trophy {
  position: relative;
  padding: 20px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: border-color 0.15s;
}
.vault-trophy:hover { border-color: rgba(255, 97, 0, 0.3); }
.vault-trophy--showcased {
  border-color: rgba(193, 245, 39, 0.35);
  box-shadow: 0 0 0 1px rgba(193, 245, 39, 0.08) inset;
}
.vault-trophy--showcased::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(193, 245, 39, 0.04), transparent 60%);
  pointer-events: none;
  border-radius: 6px;
}

.hall-flag {
  position: absolute;
  top: -1px;
  right: -1px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: var(--accent);
  color: var(--bg);
  font-size: 9px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-weight: bold;
  border-radius: 0 6px 0 4px;
  z-index: 2;
}
.hall-flag-dot {
  width: 5px;
  height: 5px;
  background: var(--bg);
  border-radius: 50%;
}

.vt-header {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}
.vt-asset {
  width: 64px;
  height: 64px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 14px rgba(193, 245, 39, 0.15));
}
.vt-asset img, .vt-asset svg {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.vt-info { min-width: 0; flex: 1; }
.vt-name {
  font-size: 15px;
  color: var(--text);
  letter-spacing: 0.02em;
  line-height: 1.2;
  margin-bottom: 4px;
}
.vt-sub {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.vt-desc {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.6;
  min-height: 2.4em;
}

.vt-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px dashed var(--border);
}
.vt-meta-stats {
  display: flex;
  gap: 12px;
  align-items: center;
}
.vt-xp {
  font-size: 11px;
  color: var(--accent);
  padding: 3px 8px;
  background: rgba(193, 245, 39, 0.12);
  border-radius: 3px;
  letter-spacing: 0.08em;
}
.vt-series {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.vt-actions { display: flex; gap: 6px; }
.vt-action {
  width: 28px;
  height: 28px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  border-radius: 3px;
}
.vt-action:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.vt-action--remove:hover:not(:disabled) {
  border-color: #e24b4a;
  color: #e24b4a;
}
.vt-action:disabled { opacity: 0.5; cursor: wait; }

/* ========== PROMO HINT ========== */
.promo-hint {
  margin-top: 8px;
  padding: 18px 24px;
  background: linear-gradient(90deg, rgba(193, 245, 39, 0.06), transparent);
  border: 1px dashed rgba(193, 245, 39, 0.25);
  border-radius: 6px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}
.promo-text {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.6;
  flex: 1;
  min-width: 220px;
}
.promo-text strong { color: var(--accent); }
.promo-cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  background: var(--accent);
  color: var(--bg);
  font-size: 11px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 3px;
}

/* ========== ACHIEVEMENTS LIST ========== */
.achievements-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.ach-row {
  display: grid;
  grid-template-columns: 56px 1fr auto auto;
  gap: 16px;
  align-items: center;
  padding: 14px 18px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  transition: border-color 0.15s;
}
.ach-row:hover { border-color: rgba(255, 97, 0, 0.3); }

.ach-thumb {
  width: 56px;
  height: 56px;
  border-radius: 4px;
  overflow: hidden;
  flex-shrink: 0;
}
.ach-thumb img { width: 100%; height: 100%; object-fit: cover; }
.ach-thumb-inner {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  font-size: 14px;
  font-family: var(--display);
  letter-spacing: 0.04em;
}

.ach-info { min-width: 0; }
.ach-name {
  font-size: 13px;
  color: var(--text);
  margin-bottom: 3px;
}
.ach-desc {
  font-size: 11px;
  color: var(--text-muted);
  line-height: 1.5;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.ach-status-badge {
  font-size: 9px;
  padding: 3px 8px;
  background: rgba(193, 245, 39, 0.12);
  color: var(--accent);
  border-radius: 3px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.ach-cta {
  padding: 8px 14px;
  font-size: 10px;
  background: transparent;
  color: var(--text-muted);
  border: 1px solid var(--border);
  border-radius: 3px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.15s;
  font-family: var(--mono);
}
.ach-cta:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.ach-cta--active {
  background: var(--accent);
  color: var(--bg);
  border-color: var(--accent);
}
.ach-cta--active:hover:not(:disabled) {
  background: rgba(193, 245, 39, 0.8);
  border-color: rgba(193, 245, 39, 0.8);
  color: var(--bg);
}
.ach-cta:disabled { opacity: 0.5; cursor: wait; }

/* ========== PLATFORM SECTIONS ========== */
.platform-sections {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.platform-section {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 20px;
}
.platform-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px dashed var(--border);
}
.ph-title {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}
.ph-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  flex-shrink: 0;
}
.ph-name {
  font-size: 14px;
  color: var(--text);
  letter-spacing: 0.04em;
  margin-bottom: 2px;
}
.ph-sub {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}
.ph-count {
  font-family: var(--display);
  font-size: 28px;
  color: var(--primary);
  line-height: 1;
}

.badges-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(56px, 1fr));
  gap: 8px;
}
.badge-tile {
  aspect-ratio: 1;
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
  cursor: default;
  overflow: hidden;
}
.badge-tile:hover {
  border-color: rgba(255, 97, 0, 0.4);
  transform: translateY(-1px);
}
.badge-tile img {
  width: 70%;
  height: 70%;
  object-fit: contain;
}
.badge-tile--rare { border-color: rgba(193, 245, 39, 0.35); }
.badge-tile--legendary { border-color: rgba(255, 97, 0, 0.45); box-shadow: 0 0 14px rgba(255, 97, 0, 0.15); }
.badge-emoji { font-size: 22px; }

/* ========== EMPTY STATES ========== */
.empty-section {
  padding: 32px 24px;
  background: var(--surface);
  border: 1px dashed var(--border);
  border-radius: 6px;
  text-align: center;
}
.empty-section p {
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 10px;
}
.empty-cta {
  display: inline-block;
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  text-decoration: none;
  border-bottom: 1px solid var(--primary);
  padding-bottom: 2px;
}

/* ========== TERMINAL ========== */
.terminal-strip {
  padding: 18px 48px;
  border-top: 1px dashed rgba(255, 97, 0, 0.1);
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
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
  .tr-hero { padding: 28px 24px 32px; }
  .tr-hero-content { grid-template-columns: 1fr; gap: 24px; }
  .page-title { font-size: 42px; }
  .tr-hero-aside { align-items: flex-start; }
  .vhall-preview { align-self: flex-start; }
  .tr-content { padding: 28px 24px; gap: 36px; }
  .terminal-strip { padding: 14px 24px; }

  .ach-row {
    grid-template-columns: 48px 1fr auto;
    gap: 12px;
    padding: 12px 14px;
  }
  .ach-row .ach-status-badge { display: none; }
}

@media (max-width: 600px) {
  .page-title { font-size: 32px; }
  .section-header { flex-direction: column; align-items: flex-start; }
  .trophies-vault { grid-template-columns: 1fr; }
}
</style>

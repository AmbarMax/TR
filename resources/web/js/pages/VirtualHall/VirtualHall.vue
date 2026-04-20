<template>
  <div class="virtual-hall-page">

    <!-- PUBLIC CHROME HEADER -->
    <header class="public-chrome">
      <router-link to="/" class="chrome-brand" aria-label="TrophyRoom home">
        <svg class="chrome-logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
          <polygon points="50,5 93,30 93,75 50,95 7,75 7,30" fill="#000003" stroke="#ff6100" stroke-width="1.5" opacity="0.8"/>
          <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
          <polygon points="50,48 75,35 75,65 50,78" fill="#ff6100"/>
          <polygon points="50,48 25,35 25,65 50,78" fill="#d4500c"/>
        </svg>
        <span class="chrome-name">Trophy<span class="chrome-name-dot">Room</span></span>
      </router-link>

      <div class="chrome-right">
        <button class="chrome-share" @click="handleShare" :disabled="sharing">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8M16 6l-4-4-4 4M12 2v13"/>
          </svg>
          <span>{{ shareLabel }}</span>
        </button>
        <router-link :to="ctaTarget" class="chrome-cta">
          <span>{{ ctaLabel }}</span>
          <span>→</span>
        </router-link>
      </div>
    </header>

    <!-- LOADING STATE -->
    <div v-if="loading" class="vh-loading">
      <div class="vh-loading-pulse"></div>
      <p>Loading profile...</p>
    </div>

    <!-- NOT FOUND -->
    <div v-else-if="notFound" class="vh-not-found">
      <h2>Profile not found</h2>
      <p>No user with username <strong>{{ routeUsername }}</strong> exists, or their profile is private.</p>
      <router-link to="/" class="vh-not-found-cta">← Back to TrophyRoom</router-link>
    </div>

    <!-- PROFILE CONTENT -->
    <div v-else class="vh">

      <!-- HERO -->
      <section class="hero">
        <div class="hero-banner">
          <div class="hero-banner-img" :style="bannerStyle"></div>
          <div class="hero-banner-overlay"></div>
        </div>

        <div class="hero-content">
          <!-- LEFT: identity + stats -->
          <div class="hero-left">
            <div class="hero-tag">Public profile · trophyroom.gg/{{ profile.username }}</div>

            <div class="identity">
              <div class="avatar-ring">
                <img v-if="avatarUrl" :src="avatarUrl" :alt="profile.username" class="avatar-img">
                <div v-else class="avatar-img avatar-img--initials">{{ initials }}</div>
              </div>

              <div class="identity-meta">
                <h1 class="hero-name">{{ profile.username }}</h1>
                <div class="hero-handle" v-if="profile.name || connectedPlatforms.length">
                  <span v-if="profile.name">{{ profile.name }}</span>
                  <span v-if="profile.name && connectedPlatforms.length" class="hero-handle-sep" aria-hidden="true">·</span>
                  <div v-if="connectedPlatforms.length" class="platforms">
                    <span
                      v-for="platform in connectedPlatforms"
                      :key="platform.key"
                      class="plat-icon"
                      :title="`${platform.name} connected`"
                      v-html="platform.icon"
                    ></span>
                  </div>
                </div>
                <p v-if="profile.description" class="hero-tagline">
                  {{ profile.description }}
                </p>
              </div>
            </div>

            <div class="hero-stats">
              <div class="stat-cell stat-cell--highlight">
                <div class="stat-cell-num">{{ stats.badges }}</div>
                <div class="stat-cell-lbl">Badges</div>
              </div>
              <div class="stat-cell">
                <div class="stat-cell-num">{{ stats.trophies }}</div>
                <div class="stat-cell-lbl">Trophies</div>
              </div>
              <div class="stat-cell">
                <div class="stat-cell-num">{{ stats.achievements }}</div>
                <div class="stat-cell-lbl">Achievements</div>
              </div>
              <div class="stat-cell">
                <div class="stat-cell-num">{{ stats.platforms }}</div>
                <div class="stat-cell-lbl">Platforms</div>
              </div>
            </div>
          </div>

          <!-- RIGHT: signature trophy -->
          <aside v-if="signatureTrophy" class="showcase-card">
            <div class="showcase-label">
              <div class="showcase-label-text">Signature trophy</div>
              <div class="showcase-label-count">1 of {{ showcasedTrophies.length }}</div>
            </div>
            <div class="showcase-trophy">
              <div class="showcase-trophy-asset">
                <img v-if="signatureTrophy.image" :src="trophyImage(signatureTrophy)" :alt="signatureTrophy.name">
                <svg v-else viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                  <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
                  <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
                  <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
                  <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
                </svg>
              </div>
              <h3 class="showcase-trophy-name">{{ signatureTrophy.name }}</h3>
              <p class="showcase-trophy-sub">Forged · {{ formatDate(signatureTrophy.pivot && signatureTrophy.pivot.created_at || signatureTrophy.created_at) }}</p>
              <div v-if="signatureTrophy.weight || signatureTrophy.price || signatureTrophy.series" class="showcase-trophy-meta">
                <div v-if="signatureTrophy.weight || signatureTrophy.price" class="tmeta-cell">
                  <div class="tmeta-val">+{{ Math.floor(signatureTrophy.weight || signatureTrophy.price) }}</div>
                  <div class="tmeta-lbl">XP</div>
                </div>
                <div v-if="signatureTrophy.series" class="tmeta-cell">
                  <div class="tmeta-val">{{ signatureTrophy.series }}</div>
                  <div class="tmeta-lbl">Series</div>
                </div>
              </div>
            </div>
          </aside>
        </div>
      </section>

      <!-- NARRATIVE SECTIONS -->
      <section class="narrative">

        <!-- FORGED TROPHIES -->
        <div v-if="showcasedTrophies.length" class="vh-section">
          <div class="section-header">
            <h2 class="section-title">
              Forged <span class="section-title-accent">trophies</span>
            </h2>
            <div class="section-meta">{{ showcasedTrophies.length }} featured · curated by player</div>
          </div>
          <div class="trophies-featured">
            <div
              v-for="trophy in showcasedTrophies"
              :key="trophy.id"
              class="trophy-piece"
            >
              <div class="trophy-piece-asset">
                <img v-if="trophy.image" :src="trophyImage(trophy)" :alt="trophy.name">
                <svg v-else viewBox="0 0 100 100">
                  <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
                  <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
                  <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
                  <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
                </svg>
              </div>
              <h3 class="trophy-piece-name">{{ trophy.name }}</h3>
              <p class="trophy-piece-desc">{{ trophy.description || '' }}</p>
              <div class="trophy-piece-footer">
                <span v-if="trophy.weight || trophy.price" class="trophy-piece-xp">
                  +{{ Math.floor(trophy.weight || trophy.price) }} XP
                </span>
                <span v-if="trophy.weight || trophy.price" class="trophy-piece-sep" aria-hidden="true">·</span>
                <span>{{ formatDate(trophy.pivot && trophy.pivot.created_at || trophy.created_at) }}</span>
              </div>
            </div>

            <!-- Locked slots for motivation (only if few trophies) -->
            <div
              v-for="n in lockedSlotsToShow"
              :key="`locked-${n}`"
              class="trophy-piece trophy-piece--locked"
            >
              <div class="trophy-piece-asset trophy-piece-asset--locked">
                <svg viewBox="0 0 100 100">
                  <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#2a2c2e" stroke-width="2" stroke-dasharray="3 3"/>
                  <text x="50" y="60" text-anchor="middle" font-family="VT323" font-size="36" fill="#5a5550">?</text>
                </svg>
              </div>
              <h3 class="trophy-piece-name trophy-piece-name--locked">Locked slot</h3>
              <p class="trophy-piece-desc">Forge another trophy to unlock this showcase.</p>
              <div class="trophy-piece-footer">
                <span class="trophy-piece-empty">Empty</span>
              </div>
            </div>
          </div>
        </div>

        <!-- CUSTOM ACHIEVEMENTS -->
        <div v-if="showcasedAchievements.length" class="vh-section">
          <div class="section-header">
            <h2 class="section-title">
              Custom <span class="section-title-accent">achievements</span>
            </h2>
            <div class="section-meta">{{ showcasedAchievements.length }} featured{{ validatedCount ? ` · ${validatedCount} community verified` : '' }}</div>
          </div>
          <div class="achievements-list">
            <div
              v-for="ach in showcasedAchievements"
              :key="ach.id"
              class="achievement-row"
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
              <div v-if="ach.status === 1" class="ach-badge">Validated</div>
            </div>
          </div>
        </div>

        <!-- PLATFORM BADGES -->
        <div v-if="platformGroups.length" class="vh-section">
          <div class="section-header">
            <h2 class="section-title">
              Platform <span class="section-title-accent">badges</span>
            </h2>
            <div class="section-meta">{{ totalBadges }} earned · {{ platformGroups.length }} {{ platformGroups.length === 1 ? 'platform' : 'platforms' }}</div>
          </div>
          <div class="platforms-section">
            <div
              v-for="group in platformGroups"
              :key="group.key"
              class="platform-block"
            >
              <div class="platform-header">
                <div class="platform-title">
                  <div class="platform-title-icon" v-html="group.icon"></div>
                  <div>
                    <div class="platform-title-name">{{ group.name }}</div>
                    <div class="platform-title-sub">Synced · {{ group.badges.length }} badges</div>
                  </div>
                </div>
                <div class="platform-count">{{ group.badges.length }}</div>
              </div>
              <div class="badges-grid">
                <div
                  v-for="badge in group.badges"
                  :key="badge.id"
                  class="badge-tile"
                  :title="badge.name || ''"
                >
                  <img v-if="badge.image" :src="badgeImage(badge, group.key)" :alt="badge.name">
                  <span v-else class="badge-emoji">🏆</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- EMPTY PROFILE -->
        <div v-if="isEmptyProfile" class="vh-section">
          <div class="vh-empty">
            <p class="vh-empty-title">This profile is still being forged.</p>
            <p class="vh-empty-desc">{{ profile.username }} hasn't added any achievements to their Virtual Hall yet. Check back soon.</p>
          </div>
        </div>

      </section>

      <!-- CONVERSION FOOTER -->
      <footer class="vh-footer">
        <div class="vh-footer-inner">
          <div class="vh-footer-left">
            <strong>Inspired by {{ profile.username }}?</strong>
            <span>Build your own Virtual Hall. Connect Discord, Steam, and more — forge trophies across every platform you play.</span>
          </div>
          <div class="vh-footer-actions">
            <router-link :to="ctaTarget" class="footer-btn footer-btn--primary">
              <span>{{ footerCtaLabel }}</span>
              <span>→</span>
            </router-link>
            <router-link to="/" class="footer-btn footer-btn--ghost">Learn more</router-link>
          </div>
        </div>
      </footer>

    </div>

    <!-- SHARE TOAST -->
    <transition name="toast">
      <div v-if="shareToast" class="share-toast">{{ shareToast }}</div>
    </transition>

  </div>
</template>

<script>
import api from '../../api/api.js';

export default {
  name: 'VirtualHall',
  data() {
    return {
      profile: null,
      badges: [],
      trophies: [],
      achievements: [],

      loading: true,
      notFound: false,
      sharing: false,
      shareToast: '',

      platformConfig: {
        discord: {
          name: 'Discord',
          icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4C2.9 4 2 4.9 2 6v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM9.5 16c-1.4 0-2.5-1.1-2.5-2.5S8.1 11 9.5 11s2.5 1.1 2.5 2.5S10.9 16 9.5 16zm5 0c-1.4 0-2.5-1.1-2.5-2.5S13.1 11 14.5 11s2.5 1.1 2.5 2.5S15.9 16 14.5 16z"/></svg>'
        },
        steam: {
          name: 'Steam',
          icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>'
        },
        github: {
          name: 'GitHub',
          icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.9 2.9 1.3 3.6 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.2-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.7.2 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3"/></svg>'
        },
        riot: {
          name: 'Riot Games',
          icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/></svg>'
        },
        strava: {
          name: 'Strava',
          icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M15.4 17.9l-2.1-4.1-3.1 6.1c-.3.6-.9 1.1-1.6 1.1H4.5c-.35 0-.7-.1-1-.3-.6-.3-.9-.9-.9-1.5 0-.25.05-.5.2-.75L11.6.7C11.85.05 12.5-.4 13.2-.4c.7 0 1.3.45 1.6 1.1l6.8 13.75c.1.25.2.5.2.75 0 .6-.35 1.2-.9 1.5-.3.2-.65.3-1 .3l-4.5-.1z"/></svg>'
        }
      }
    };
  },
  computed: {
    routeUsername() {
      return this.$route.params.username;
    },

    isAuthenticated() {
      return !!this.$store?.state?.authorized;
    },
    isOwner() {
      if (!this.isAuthenticated) return false;
      const loggedUsername = this.$store?.state?.userUsername;
      return !!(loggedUsername && loggedUsername.toLowerCase() === (this.routeUsername || '').toLowerCase());
    },
    ctaLabel() {
      return this.isOwner ? 'Edit profile' : 'Create your own';
    },
    ctaTarget() {
      if (this.isOwner) return '/trophy-room';
      return this.isAuthenticated ? '/dashboard' : '/sign-up';
    },
    footerCtaLabel() {
      return this.isOwner ? 'Edit my profile' : 'Create my profile';
    },
    shareLabel() {
      return this.shareToast || 'Share profile';
    },

    stats() {
      return {
        badges: this.badges.length,
        trophies: this.trophies.length,
        achievements: this.achievements.length,
        platforms: this.platformGroups.length
      };
    },
    totalBadges() {
      return this.badges.length;
    },

    // Server already pre-filters to display=1; these are safety nets in case shape changes.
    showcasedTrophies() {
      return this.trophies.filter(t => t.is_nft !== 1);
    },
    showcasedAchievements() {
      return this.achievements;
    },
    signatureTrophy() {
      return this.showcasedTrophies[0] || null;
    },

    validatedCount() {
      return this.showcasedAchievements.filter(a => a.status === 1).length;
    },

    lockedSlotsToShow() {
      const count = this.showcasedTrophies.length;
      if (count === 0) return 0;
      if (count < 3) return 1;
      return 0;
    },

    connectedPlatforms() {
      return this.platformGroups.slice(0, 3).map(g => ({
        key: g.key,
        name: g.name,
        icon: g.icon
      }));
    },
    platformGroups() {
      const groups = {};
      this.badges.forEach(b => {
        const integration = b.integration;
        const key = (typeof integration === 'object' ? integration?.name : integration || '').toLowerCase();
        if (!key) return;
        if (!groups[key]) groups[key] = [];
        groups[key].push(b);
      });

      return Object.entries(groups)
        .map(([key, badges]) => {
          const config = this.platformConfig[key] || {
            name: key.charAt(0).toUpperCase() + key.slice(1),
            icon: '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>'
          };
          return { key, name: config.name, icon: config.icon, badges };
        })
        .sort((a, b) => b.badges.length - a.badges.length);
    },

    initials() {
      if (!this.profile?.username) return '??';
      return this.profile.username.substring(0, 2).toUpperCase();
    },
    avatarUrl() {
      // Backend returns a full URL already (via getAvatarFile) or a default path starting with "/".
      // Defaults '/images/avatar/default-profile-img.png' should be treated as "no avatar" so we show initials instead.
      const a = this.profile?.avatar;
      if (!a) return '';
      if (a.includes('default-profile-img')) return '';
      return a;
    },
    bannerStyle() {
      const bg = this.profile?.background;
      if (bg && !bg.includes('default-background-img')) {
        return { backgroundImage: `url(${bg})` };
      }
      return {};
    },

    isEmptyProfile() {
      return !this.showcasedTrophies.length && !this.showcasedAchievements.length && !this.platformGroups.length;
    },

    pageTitle() {
      if (!this.profile) return 'Profile — TrophyRoom';
      return `${this.profile.username} — Virtual Hall · TrophyRoom`;
    },
    pageDescription() {
      if (!this.profile) return 'A unified achievement showcase across every gaming platform.';
      const badges = this.badges.length;
      const trophies = this.trophies.length;
      return `${this.profile.username}'s Virtual Hall · ${badges} badges · ${trophies} trophies forged across ${this.platformGroups.length} platforms.`;
    }
  },
  mounted() {
    this.loadProfile();
  },
  watch: {
    '$route.params.username'() {
      this.loadProfile();
    },
    pageTitle() {
      if (typeof document !== 'undefined') document.title = this.pageTitle;
    }
  },
  methods: {
    async loadProfile() {
      this.loading = true;
      this.notFound = false;

      try {
        const response = await api.get(`/api/virtual-hall/${encodeURIComponent(this.routeUsername)}`);
        // Real shape: { user: { data: {...} }, followStatus, socialLinks, featuredSlots }
        const userData = response.data?.user?.data || null;

        if (!userData) {
          this.notFound = true;
          return;
        }

        this.profile = userData;
        this.badges = userData.badges?.data || [];
        this.trophies = Array.isArray(userData.trophies) ? userData.trophies : [];
        this.achievements = Array.isArray(userData.achievements) ? userData.achievements : [];
        this.updateMetaTags();
      } catch (err) {
        if (err.response?.status === 404) {
          this.notFound = true;
        } else {
          console.error('[VirtualHall] Failed to load profile', err);
          this.notFound = true;
        }
      } finally {
        this.loading = false;
      }
    },

    async handleShare() {
      if (this.sharing) return;
      this.sharing = true;
      const url = window.location.href;

      try {
        if (navigator.share) {
          await navigator.share({
            title: this.pageTitle,
            text: this.pageDescription,
            url
          });
          this.showToast('Shared');
        } else if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(url);
          this.showToast('Link copied');
        } else {
          window.prompt('Copy this link to share:', url);
        }
      } catch (err) {
        // user cancelled or API unavailable — silent fail
      } finally {
        this.sharing = false;
      }
    },

    showToast(msg) {
      this.shareToast = msg;
      setTimeout(() => { this.shareToast = ''; }, 2000);
    },

    updateMetaTags() {
      if (typeof document === 'undefined') return;
      document.title = this.pageTitle;
      this.setMetaTag('description', this.pageDescription);
      this.setMetaTag('og:title', this.pageTitle, 'property');
      this.setMetaTag('og:description', this.pageDescription, 'property');
      this.setMetaTag('og:type', 'profile', 'property');
      this.setMetaTag('og:url', window.location.href, 'property');
      this.setMetaTag('twitter:card', 'summary_large_image');
      this.setMetaTag('twitter:title', this.pageTitle);
      this.setMetaTag('twitter:description', this.pageDescription);
    },
    setMetaTag(name, content, attr = 'name') {
      let tag = document.querySelector(`meta[${attr}="${name}"]`);
      if (!tag) {
        tag = document.createElement('meta');
        tag.setAttribute(attr, name);
        document.head.appendChild(tag);
      }
      tag.setAttribute('content', content);
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
      } catch {
        return '';
      }
    },

    trophyImage(trophy) {
      if (!trophy?.image) return '';
      const img = trophy.image;
      if (img.startsWith('http') || img.startsWith('/')) return img;
      return `/storage/trophies/${img}`;
    },
    achievementImage(ach) {
      if (!ach?.image) return '';
      const img = ach.image;
      if (img.startsWith('http') || img.startsWith('/')) return img;
      return `/storage/achievements/${img}`;
    },
    badgeImage(badge, platformKey) {
      if (!badge?.image) return '';
      const img = badge.image;
      if (img.startsWith('http') || img.startsWith('/')) return img;
      const integration = typeof badge.integration === 'object'
        ? badge.integration?.name
        : (badge.integration || platformKey || 'unknown');
      return `/storage/integrations/${integration}/${img}`;
    },

    achThumbStyle(ach) {
      if (!ach?.name) return {};
      const hash = ach.name.split('').reduce((h, c) => h + c.charCodeAt(0), 0);
      const hue = (hash * 37) % 360;
      return {
        background: `linear-gradient(135deg, hsl(${hue}, 40%, 22%), hsl(${(hue + 30) % 360}, 40%, 15%))`
      };
    },
    achThumbLabel(ach) {
      if (!ach?.name) return '??';
      return ach.name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
    }
  }
};
</script>

<style lang="scss" scoped>
.virtual-hall-page {
  min-height: 100vh;
  position: relative;
}

/* ========== PUBLIC CHROME ========== */
.public-chrome {
  position: sticky;
  top: 0;
  z-index: 50;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 32px;
  background: rgba(0, 0, 3, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 97, 0, 0.08);
}
.chrome-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: var(--text);
}
.chrome-logo {
  width: 28px;
  height: 28px;
}
.chrome-name {
  font-family: var(--display);
  font-size: 20px;
  letter-spacing: 0.04em;
}
.chrome-name-dot {
  color: var(--primary);
}
.chrome-right {
  display: flex;
  align-items: center;
  gap: 12px;
}
.chrome-share {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.15s;
  border-radius: 3px;
}
.chrome-share:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.chrome-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: var(--primary);
  color: var(--bg);
  font-size: 10px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.15s;
  border-radius: 3px;
}
.chrome-cta:hover {
  box-shadow: 0 0 16px rgba(255, 97, 0, 0.4);
}

/* ========== LOADING / NOT FOUND ========== */
.vh-loading, .vh-not-found {
  min-height: calc(100vh - 80px);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  text-align: center;
}
.vh-loading-pulse {
  width: 48px;
  height: 48px;
  border: 2px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 18px;
}
.vh-loading p, .vh-not-found p {
  color: var(--text-muted);
  font-size: 13px;
  max-width: 400px;
  line-height: 1.6;
}
.vh-not-found h2 {
  font-family: var(--display);
  font-size: 36px;
  color: var(--text);
  margin-bottom: 12px;
}
.vh-not-found-cta {
  margin-top: 22px;
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  text-decoration: none;
  border-bottom: 1px solid var(--primary);
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ========== HERO ========== */
.hero {
  position: relative;
  padding-bottom: 60px;
}
.hero-banner {
  position: relative;
  height: 320px;
  overflow: hidden;
}
.hero-banner-img {
  position: absolute;
  inset: 0;
  background-color: var(--surface);
  background-size: cover;
  background-position: center;
  background-image: radial-gradient(ellipse 1200px 600px at 50% 40%, rgba(255, 97, 0, 0.3), transparent 60%),
                    linear-gradient(180deg, #1a0d05 0%, #000003 100%);
}
.hero-banner-overlay {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, transparent 0%, rgba(0, 0, 3, 0.3) 60%, rgba(0, 0, 3, 0.95) 100%),
    radial-gradient(ellipse 600px 400px at 80% 30%, rgba(193, 245, 39, 0.12), transparent 65%);
}

.hero-content {
  position: relative;
  margin-top: -120px;
  padding: 0 48px;
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 48px;
  align-items: start;
  z-index: 2;
}

.hero-left {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.hero-tag {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

.identity {
  display: flex;
  gap: 24px;
  align-items: flex-start;
}
.avatar-ring {
  width: 100px;
  height: 100px;
  flex-shrink: 0;
  padding: 3px;
  background: linear-gradient(135deg, var(--primary), var(--accent));
  border-radius: 50%;
}
.avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: var(--surface);
  object-fit: cover;
  display: flex;
  align-items: center;
  justify-content: center;
}
.avatar-img--initials {
  font-family: var(--display);
  font-size: 36px;
  color: var(--primary);
}

.identity-meta { min-width: 0; flex: 1; }
.hero-name {
  font-family: var(--display);
  font-size: 52px;
  line-height: 1;
  color: var(--text);
  margin-bottom: 8px;
  text-shadow: 0 0 24px rgba(255, 97, 0, 0.2);
}
.hero-handle {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  color: var(--text-muted);
  flex-wrap: wrap;
  margin-bottom: 10px;
}
.platforms { display: inline-flex; gap: 8px; }
.plat-icon {
  width: 24px;
  height: 24px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  background: var(--surface-2);
  border-radius: 4px;
  transition: all 0.15s;
}
.plat-icon:hover {
  transform: translateY(-1px);
  color: var(--accent);
}
.hero-tagline {
  font-size: 13px;
  color: var(--text);
  font-style: italic;
  line-height: 1.5;
  opacity: 0.85;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  max-width: 480px;
}
.stat-cell {
  padding: 14px 10px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  text-align: center;
}
.stat-cell--highlight {
  border-color: rgba(193, 245, 39, 0.3);
  background: linear-gradient(180deg, rgba(193, 245, 39, 0.06), transparent);
}
.stat-cell-num {
  font-family: var(--display);
  font-size: 28px;
  color: var(--text);
  line-height: 1;
}
.stat-cell--highlight .stat-cell-num {
  color: var(--accent);
  text-shadow: 0 0 12px rgba(193, 245, 39, 0.35);
}
.stat-cell-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  margin-top: 4px;
}

/* Showcase trophy card */
.showcase-card {
  background: var(--surface);
  border: 1px solid rgba(193, 245, 39, 0.25);
  border-radius: 6px;
  padding: 24px;
  position: relative;
  overflow: hidden;
}
.showcase-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(193, 245, 39, 0.1), transparent 60%);
  pointer-events: none;
}
.showcase-label {
  position: relative;
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 18px;
  padding-bottom: 12px;
  border-bottom: 1px dashed var(--border);
}
.showcase-label-text {
  font-size: 10px;
  color: var(--accent);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}
.showcase-label-count {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
.showcase-trophy {
  position: relative;
  text-align: center;
}
.showcase-trophy-asset {
  width: 180px;
  height: 180px;
  margin: 0 auto 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 28px rgba(193, 245, 39, 0.3));
}
.showcase-trophy-asset img, .showcase-trophy-asset svg {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.showcase-trophy-name {
  font-family: var(--display);
  font-size: 26px;
  color: var(--text);
  margin-bottom: 4px;
}
.showcase-trophy-sub {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
  margin-bottom: 16px;
}
.showcase-trophy-meta {
  display: flex;
  justify-content: center;
  gap: 20px;
  padding-top: 12px;
  border-top: 1px dashed var(--border);
}
.tmeta-cell { text-align: center; }
.tmeta-val {
  font-family: var(--display);
  font-size: 20px;
  color: var(--accent);
  line-height: 1;
}
.tmeta-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
  margin-top: 4px;
}

/* ========== NARRATIVE ========== */
.narrative {
  padding: 60px 48px 80px;
  display: flex;
  flex-direction: column;
  gap: 64px;
}
.vh-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 16px;
  flex-wrap: wrap;
}
.section-title {
  font-family: var(--display);
  font-size: 40px;
  color: var(--text);
  line-height: 1;
}
.section-title-accent {
  color: var(--primary);
  text-shadow: 0 0 18px rgba(255, 97, 0, 0.3);
}
.section-meta {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

/* Forged trophies */
.trophies-featured {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 16px;
}
.trophy-piece {
  padding: 24px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  text-align: center;
  transition: all 0.2s;
}
.trophy-piece:hover {
  border-color: rgba(193, 245, 39, 0.35);
  transform: translateY(-2px);
}
.trophy-piece--locked {
  opacity: 0.5;
}
.trophy-piece--locked:hover {
  border-color: var(--border);
  transform: none;
}
.trophy-piece-asset {
  width: 120px;
  height: 120px;
  margin: 0 auto 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 18px rgba(193, 245, 39, 0.15));
}
.trophy-piece-asset--locked {
  filter: none;
}
.trophy-piece-asset img, .trophy-piece-asset svg {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.trophy-piece-name {
  font-family: var(--display);
  font-size: 20px;
  color: var(--text);
  margin-bottom: 6px;
}
.trophy-piece-name--locked {
  color: var(--text-dim);
}
.trophy-piece-desc {
  font-size: 11px;
  color: var(--text-muted);
  line-height: 1.5;
  margin-bottom: 14px;
  min-height: 3em;
}
.trophy-piece-footer {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  font-size: 10px;
  color: var(--text-muted);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  padding-top: 12px;
  border-top: 1px dashed var(--border);
}
.trophy-piece-xp {
  color: var(--accent);
}
.trophy-piece-sep {
  color: var(--text-dim);
}
.trophy-piece-empty {
  color: var(--text-dim);
}

/* Achievements */
.achievements-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.achievement-row {
  display: grid;
  grid-template-columns: 56px 1fr auto;
  gap: 16px;
  align-items: center;
  padding: 14px 18px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  transition: border-color 0.15s;
}
.achievement-row:hover { border-color: rgba(255, 97, 0, 0.3); }
.ach-thumb {
  width: 56px;
  height: 56px;
  border-radius: 4px;
  overflow: hidden;
}
.ach-thumb img { width: 100%; height: 100%; object-fit: cover; }
.ach-thumb-inner {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  font-family: var(--display);
  font-size: 14px;
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
}
.ach-badge {
  font-size: 9px;
  padding: 3px 8px;
  background: rgba(193, 245, 39, 0.12);
  color: var(--accent);
  border-radius: 3px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  white-space: nowrap;
}

/* Platform badges */
.platforms-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.platform-block {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 22px;
}
.platform-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px dashed var(--border);
}
.platform-title { display: flex; align-items: center; gap: 14px; min-width: 0; }
.platform-title-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  flex-shrink: 0;
}
.platform-title-name {
  font-size: 15px;
  color: var(--text);
  margin-bottom: 2px;
}
.platform-title-sub {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}
.platform-count {
  font-family: var(--display);
  font-size: 34px;
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
  overflow: hidden;
}
.badge-tile:hover {
  border-color: rgba(255, 97, 0, 0.4);
}
.badge-tile img {
  width: 70%;
  height: 70%;
  object-fit: contain;
}
.badge-emoji { font-size: 22px; }

/* Empty profile */
.vh-empty {
  padding: 48px 32px;
  background: var(--surface);
  border: 1px dashed var(--border);
  border-radius: 6px;
  text-align: center;
}
.vh-empty-title {
  font-family: var(--display);
  font-size: 24px;
  color: var(--text);
  margin-bottom: 10px;
}
.vh-empty-desc {
  font-size: 13px;
  color: var(--text-muted);
  max-width: 500px;
  margin: 0 auto;
}

/* ========== FOOTER ========== */
.vh-footer {
  padding: 48px 48px 72px;
  border-top: 1px dashed rgba(255, 97, 0, 0.15);
  background: linear-gradient(180deg, transparent, rgba(255, 97, 0, 0.03));
}
.vh-footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 32px;
  align-items: center;
}
.vh-footer-left {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.7;
}
.vh-footer-left strong {
  font-family: var(--display);
  font-size: 24px;
  color: var(--text);
  font-weight: normal;
}
.vh-footer-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.footer-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  font-size: 11px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 3px;
  transition: all 0.15s;
  font-family: var(--mono);
}
.footer-btn--primary {
  background: var(--primary);
  color: var(--bg);
}
.footer-btn--primary:hover {
  box-shadow: 0 0 18px rgba(255, 97, 0, 0.4);
}
.footer-btn--ghost {
  color: var(--text-muted);
  border: 1px solid var(--border);
}
.footer-btn--ghost:hover {
  border-color: var(--primary);
  color: var(--primary);
}

/* Share toast */
.share-toast {
  position: fixed;
  bottom: 32px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  background: var(--surface);
  border: 1px solid var(--accent);
  border-radius: 4px;
  color: var(--accent);
  font-size: 12px;
  letter-spacing: 0.12em;
  z-index: 100;
  box-shadow: 0 0 24px rgba(193, 245, 39, 0.15);
}
.toast-enter-active, .toast-leave-active { transition: all 0.25s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(10px); }

/* ========== RESPONSIVE ========== */
@media (max-width: 900px) {
  .public-chrome { padding: 12px 20px; }
  .chrome-name { font-size: 16px; }
  .chrome-cta span:first-child { display: none; }

  .hero-banner { height: 220px; }
  .hero-content {
    grid-template-columns: 1fr;
    gap: 28px;
    margin-top: -80px;
    padding: 0 24px;
  }

  .identity { flex-direction: column; gap: 16px; }
  .avatar-ring { width: 80px; height: 80px; }
  .hero-name { font-size: 40px; }

  .hero-stats { grid-template-columns: repeat(2, 1fr); }

  .showcase-card { max-width: 400px; }
  .showcase-trophy-asset { width: 140px; height: 140px; }
  .showcase-trophy-name { font-size: 22px; }

  .narrative { padding: 40px 24px 56px; gap: 48px; }
  .section-title { font-size: 30px; }

  .vh-footer { padding: 40px 24px 56px; }
  .vh-footer-inner { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
  .chrome-share span { display: none; }
  .hero-name { font-size: 32px; }
  .section-title { font-size: 24px; }
  .achievement-row { grid-template-columns: 48px 1fr; }
  .achievement-row .ach-badge { grid-column: 2; justify-self: start; }
}
</style>

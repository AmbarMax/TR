<template>
  <div class="forge-page">

    <!-- PAGE HERO -->
    <section class="forge-hero">
      <div class="forge-hero-bg"></div>
      <div class="forge-hero-content">
        <div class="forge-hero-left">
          <div class="page-tag">The Forge</div>
          <h1 class="page-title">
            Forge your <span class="page-title-accent">trophies</span>
          </h1>
          <p class="page-subtitle">
            Collect badges from your connected platforms, then spend Ambar to forge them into trophies.
            Each trophy earns Uru — the currency that unlocks real rewards.
          </p>
        </div>
        <aside class="forge-hero-aside">
          <div class="quick-stats">
            <div class="qstat">
              <div class="qstat-val">{{ availableCount }}</div>
              <div class="qstat-lbl">Available</div>
            </div>
            <div class="qstat qstat--ready">
              <div class="qstat-val">{{ readyCount }}</div>
              <div class="qstat-lbl">Ready</div>
            </div>
            <div class="qstat qstat--forged">
              <div class="qstat-val">{{ forgedCount }}</div>
              <div class="qstat-lbl">Forged</div>
            </div>
          </div>
        </aside>
      </div>
    </section>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <span class="filter-bar-label">Filter</span>
      <button
        v-for="filter in filterOptions"
        :key="filter.key"
        class="filter-pill"
        :class="{ 'filter-pill--active': activeFilter === filter.key }"
        @click="activeFilter = filter.key"
      >
        {{ filter.label }}
        <span class="filter-count">{{ filter.count }}</span>
      </button>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="forge-loading">
      <div class="forge-loading-pulse"></div>
      <p>Loading the forge...</p>
    </div>

    <!-- CONTENT -->
    <div v-else class="forge-content">

      <section
        v-for="section in visibleSections"
        :key="section.key"
        class="forge-section"
      >
        <div class="section-label">
          <span class="label-text">{{ section.title }}</span>
          <span class="section-meta">
            {{ section.trophies.length }} {{ section.trophies.length === 1 ? 'trophy' : 'trophies' }}{{ section.metaSuffix || '' }}
          </span>
        </div>

        <div class="forge-grid">
          <article
            v-for="trophy in section.trophies"
            :key="trophy.id"
            class="forge-card"
            :class="[
              `forge-card--${trophy._status}`,
              { 'forge-card--deep-link': trophy._status === 'ready' && trophy.id === deepLinkedTrophyId }
            ]"
          >
            <div v-if="trophy._status === 'ready'" class="ready-flag">
              <span class="ready-flag-dot"></span>
              <span>Ready</span>
            </div>
            <div v-if="trophy._status === 'forged'" class="forged-flag">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              <span>Forged</span>
            </div>

            <div class="fc-header">
              <div class="fc-asset">
                <img v-if="trophy.image" :src="trophyImage(trophy)" :alt="trophy.name">
                <svg v-else viewBox="0 0 100 100">
                  <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" :stroke="trophyStrokeColor(trophy)" stroke-width="2" :stroke-dasharray="trophy._status === 'locked' ? '4 3' : ''"/>
                  <text v-if="trophy._status === 'locked'" x="50" y="58" text-anchor="middle" font-family="VT323" font-size="36" fill="#5a5550">?</text>
                  <g v-else>
                    <polygon points="50,22 75,35 50,48 25,35" :fill="trophyFillColor(trophy, 0)"/>
                    <polygon points="50,48 75,35 75,65 50,78" :fill="trophyFillColor(trophy, 1)"/>
                    <polygon points="50,48 25,35 25,65 50,78" :fill="trophyFillColor(trophy, 2)"/>
                  </g>
                </svg>
              </div>
              <div class="fc-info">
                <div class="fc-name">{{ trophy.name }}</div>
                <div class="fc-sub">
                  <span v-if="trophy.series">Series {{ trophy.series }}</span>
                  <span v-else-if="trophy.is_nft">NFT · Exclusive</span>
                  <span v-else>Standard</span>
                </div>
              </div>
            </div>

            <p class="fc-desc">{{ trophy.description || 'No description available.' }}</p>

            <div v-if="trophy.badges && trophy.badges.length" class="fc-badges">
              <div
                v-for="(badge, i) in trophy.badges.slice(0, 6)"
                :key="`${trophy.id}-b${i}`"
                class="fc-badge"
                :class="{
                  'fc-badge--owned': isOwnedBadge(badge),
                  'fc-badge--missing': !isOwnedBadge(badge)
                }"
                :title="badge.name || ''"
              >
                <img v-if="badge.image" :src="badgeImage(badge)" :alt="badge.name">
                <span v-else class="fc-badge-emoji">{{ badgeEmoji(badge) }}</span>
              </div>
              <div v-if="trophy.badges.length > 6" class="fc-badge fc-badge--more">
                +{{ trophy.badges.length - 6 }}
              </div>
            </div>

            <div class="fc-progress">
              <div class="fc-progress-bar">
                <div
                  class="fc-progress-fill"
                  :class="progressFillClass(trophy)"
                  :style="{ width: trophy._progressPercent + '%' }"
                ></div>
              </div>
              <div class="fc-progress-text">
                <span>{{ trophy._badgesOwned }}/{{ trophy._badgesTotal }} badges</span>
                <span>{{ progressLabel(trophy) }}</span>
              </div>
            </div>

            <div class="fc-meta">
              <span class="fc-pill fc-pill--xp">+{{ Math.floor(trophy.weight || trophy.price || 0) }} XP</span>
              <span class="fc-pill fc-pill--cost">
                <span class="pill-dot"></span>{{ trophy.price || 0 }} Ambar
              </span>
              <span class="fc-pill fc-pill--reward">
                <span class="pill-dot"></span>+{{ trophy.receive || 0 }} Uru
              </span>
            </div>

            <div class="fc-cta">
              <button
                v-if="trophy._status === 'ready'"
                class="fc-btn fc-btn--forge"
                @click="openForgeModal(trophy)"
              >
                Forge now →
              </button>
              <button
                v-else-if="trophy._status === 'in_progress' || trophy._status === 'almost'"
                class="fc-btn fc-btn--missing"
                disabled
              >
                Missing {{ trophy._badgesMissing }} {{ trophy._badgesMissing === 1 ? 'badge' : 'badges' }}
              </button>
              <button
                v-else-if="trophy._status === 'locked'"
                class="fc-btn fc-btn--locked"
                disabled
              >
                Locked · 0 badges
              </button>
              <button
                v-else-if="trophy._status === 'forged'"
                class="fc-btn fc-btn--forged"
                disabled
              >
                ✓ Already forged
              </button>
            </div>
          </article>
        </div>
      </section>

      <div v-if="!visibleSections.length" class="forge-empty">
        <p>No trophies match this filter.</p>
        <button class="forge-empty-cta" @click="activeFilter = 'all'">Show all</button>
      </div>

    </div>

    <div class="terminal-strip">
      <div>forge.catalog · {{ allTrophies.length }} trophies<span class="cursor-blink"></span></div>
      <div>balance · {{ userAmbar }} ambar · {{ userUru }} uru</div>
    </div>

    <!-- MODAL -->
    <transition name="modal">
      <div
        v-if="modalTrophy"
        class="forge-modal-overlay"
        @click.self="closeModal"
      >
        <div class="forge-modal" role="dialog" aria-modal="true">
          <button class="fm-close" @click="closeModal" aria-label="Close">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>

          <div class="fm-trophy-art">
            <img v-if="modalTrophy.image" :src="trophyImage(modalTrophy)" :alt="modalTrophy.name">
            <svg v-else viewBox="0 0 100 100">
              <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
              <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
              <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
              <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
            </svg>
          </div>

          <h2 class="fm-title">{{ modalTrophy.name }}</h2>
          <p class="fm-question">Forge this trophy? This action is permanent.</p>

          <div class="fm-costs">
            <div class="fm-cost-cell">
              <div class="fm-cost-val fm-cost-val--spend">-{{ modalTrophy.price || 0 }}</div>
              <div class="fm-cost-lbl">Ambar spent</div>
            </div>
            <div class="fm-cost-cell">
              <div class="fm-cost-val fm-cost-val--earn">+{{ modalTrophy.receive || 0 }}</div>
              <div class="fm-cost-lbl">Uru earned</div>
            </div>
          </div>

          <div v-if="modalError" class="fm-error">{{ modalError }}</div>
          <div v-if="insufficientFunds && !modalError" class="fm-error">
            You don't have enough Ambar. Need {{ modalTrophy.price }}, have {{ userAmbar }}.
          </div>

          <div class="fm-actions">
            <button
              class="fm-btn fm-btn--cancel"
              @click="closeModal"
              :disabled="forging"
            >Cancel</button>
            <button
              class="fm-btn fm-btn--confirm"
              @click="confirmForge"
              :disabled="forging || insufficientFunds"
            >
              {{ forging ? 'Forging...' : 'Confirm forge' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <transition name="toast">
      <div v-if="toastMsg" class="forge-toast" :class="`forge-toast--${toastType}`">
        {{ toastMsg }}
      </div>
    </transition>

  </div>
</template>

<script>
import api from '../api/api.js';
import store from '../store/store.js';

const FILTER_KEYS = {
  ALL: 'all',
  READY: 'ready',
  ALMOST: 'almost',
  IN_PROGRESS: 'in_progress',
  FORGED: 'forged'
};

export default {
  name: 'Forge',
  data() {
    return {
      allTrophies: [],
      userBadgeIds: new Set(),
      userForgedIds: new Set(),

      loading: true,
      activeFilter: FILTER_KEYS.ALL,

      modalTrophy: null,
      modalError: '',
      forging: false,

      toastMsg: '',
      toastType: 'info',
      toastTimer: null,

      deepLinkedTrophyId: null
    };
  },
  computed: {
    userAmbar() {
      const v = store.state.user?.balances?.ambar || 0;
      return Math.floor(v);
    },
    userUru() {
      const v = store.state.user?.balances?.uru || 0;
      return Math.floor(v);
    },
    insufficientFunds() {
      if (!this.modalTrophy) return false;
      return this.userAmbar < (this.modalTrophy.price || 0);
    },

    enrichedTrophies() {
      return this.allTrophies.map(t => {
        const trophyBadges = t.badges || [];
        const total = trophyBadges.length;
        const owned = trophyBadges.filter(b => this.userBadgeIds.has(b.id)).length;
        const missing = Math.max(0, total - owned);
        const percent = total === 0 ? 0 : Math.round((owned / total) * 100);
        const isForged = this.userForgedIds.has(t.id);

        let status;
        if (isForged) status = 'forged';
        else if (total === 0 || owned === 0) status = 'locked';
        else if (owned >= total) status = 'ready';
        else if (owned === total - 1) status = 'almost';
        else status = 'in_progress';

        return {
          ...t,
          _badgesTotal: total,
          _badgesOwned: owned,
          _badgesMissing: missing,
          _progressPercent: percent,
          _status: status
        };
      });
    },

    availableCount() {
      return this.enrichedTrophies.filter(t => t._status !== 'forged').length;
    },
    readyCount() {
      return this.enrichedTrophies.filter(t => t._status === 'ready').length;
    },
    almostCount() {
      return this.enrichedTrophies.filter(t => t._status === 'almost').length;
    },
    inProgressCount() {
      return this.enrichedTrophies.filter(t => t._status === 'in_progress').length;
    },
    forgedCount() {
      return this.enrichedTrophies.filter(t => t._status === 'forged').length;
    },

    filterOptions() {
      return [
        { key: FILTER_KEYS.ALL, label: 'All', count: this.allTrophies.length },
        { key: FILTER_KEYS.READY, label: 'Ready to forge', count: this.readyCount },
        { key: FILTER_KEYS.ALMOST, label: 'Almost ready', count: this.almostCount },
        { key: FILTER_KEYS.IN_PROGRESS, label: 'In progress', count: this.inProgressCount },
        { key: FILTER_KEYS.FORGED, label: 'Forged', count: this.forgedCount }
      ].filter(f => f.count > 0 || f.key === FILTER_KEYS.ALL);
    },

    visibleSections() {
      const sections = [];
      const include = (key) => {
        if (this.activeFilter === FILTER_KEYS.ALL) return true;
        return this.activeFilter === key;
      };

      const ready = this.enrichedTrophies.filter(t => t._status === 'ready');
      const almost = this.enrichedTrophies.filter(t => t._status === 'almost');
      const inProgress = this.enrichedTrophies.filter(t => t._status === 'in_progress');
      const locked = this.enrichedTrophies.filter(t => t._status === 'locked');
      const forged = this.enrichedTrophies.filter(t => t._status === 'forged');

      if (include(FILTER_KEYS.READY) && ready.length) {
        sections.push({ key: 'ready', title: 'Ready to forge', trophies: ready, metaSuffix: ' awaiting' });
      }
      if (include(FILTER_KEYS.ALMOST) && almost.length) {
        sections.push({ key: 'almost', title: 'Almost ready', trophies: almost });
      }
      if (include(FILTER_KEYS.IN_PROGRESS) && inProgress.length) {
        sections.push({ key: 'in_progress', title: 'In progress', trophies: inProgress });
      }
      if (this.activeFilter === FILTER_KEYS.ALL && locked.length) {
        sections.push({ key: 'locked', title: 'Locked', trophies: locked });
      }
      if (include(FILTER_KEYS.FORGED) && forged.length) {
        sections.push({ key: 'forged', title: 'Forged', trophies: forged, metaSuffix: ' completed' });
      }

      return sections;
    }
  },
  mounted() {
    this.loadData().then(() => {
      this.handleDeepLink();
    });
  },
  watch: {
    '$route.query.trophy'() {
      this.handleDeepLink();
    }
  },
  beforeUnmount() {
    if (this.toastTimer) clearTimeout(this.toastTimer);
  },
  methods: {
    async loadData() {
      this.loading = true;
      try {
        const [forgeRes, badgesRes, trophiesRes] = await Promise.all([
          api.get('/api/forge').catch(() => ({ data: { trophies: [] } })),
          api.get('/api/badges').catch(() => ({ data: { data: [] } })),
          api.get('/api/forge/available-trophies').catch(() => ({ data: { trophies: [] } }))
        ]);

        this.allTrophies = forgeRes.data?.trophies || [];
        const badges = badgesRes.data?.data || [];
        this.userBadgeIds = new Set(badges.map(b => b.id));

        const forgedTrophies = trophiesRes.data?.trophies || [];
        this.userForgedIds = new Set(forgedTrophies.map(t => t.id));
      } catch (err) {
        console.error('[Forge] Load failed', err);
        this.showToast('Failed to load forge catalog', 'error');
      } finally {
        this.loading = false;
      }
    },

    async refreshUserBalances() {
      // Mirror main-header's getUserBalances pattern — the claim endpoint doesn't return balances.
      try {
        const resp = await api.get('/api/profile');
        if (resp?.status === 200 && resp.data?.user?.data?.balances) {
          for (const balance of resp.data.user.data.balances) {
            if (balance.currency?.name) {
              store.state.user.balances[balance.currency.name] = Math.floor(balance.amount);
            }
          }
        }
      } catch (e) {
        // Silent — balances will sync via Centrifuge subscription in main-header eventually.
      }
    },

    handleDeepLink() {
      const trophyId = this.$route.query.trophy;
      if (!trophyId) {
        this.deepLinkedTrophyId = null;
        return;
      }
      const id = parseInt(trophyId);
      this.deepLinkedTrophyId = id;
      const trophy = this.enrichedTrophies.find(t => t.id === id);
      if (!trophy) {
        this.showToast('Trophy not found', 'error');
        return;
      }

      if (trophy._status === 'ready') {
        this.openForgeModal(trophy);
      } else if (trophy._status === 'forged') {
        this.showToast('You already forged this trophy', 'info');
      } else if (trophy._status === 'locked') {
        this.showToast('This trophy is locked — collect badges first', 'info');
      } else {
        this.showToast(`Not ready yet — missing ${trophy._badgesMissing} ${trophy._badgesMissing === 1 ? 'badge' : 'badges'}`, 'info');
      }
    },

    openForgeModal(trophy) {
      this.modalError = '';
      this.modalTrophy = trophy;
    },
    closeModal() {
      if (this.forging) return;
      this.modalTrophy = null;
      this.modalError = '';
      if (this.$route.query.trophy) {
        const q = { ...this.$route.query };
        delete q.trophy;
        this.$router.replace({ query: q });
      }
    },

    async confirmForge() {
      if (!this.modalTrophy || this.forging || this.insufficientFunds) return;
      this.forging = true;
      this.modalError = '';

      const trophyId = this.modalTrophy.id;
      const trophyName = this.modalTrophy.name;

      try {
        // Backend always returns HTTP 200; inspect resp.data.type for actual outcome.
        const response = await api.post(`/api/forge/claim/${trophyId}`);
        const respType = response.data?.type;
        const respMsg = response.data?.message || '';

        if (respType === 'success') {
          this.userForgedIds = new Set([...this.userForgedIds, trophyId]);
          this.showToast(`Forged ${trophyName}!`, 'success');
          await this.refreshUserBalances();
          this.closeModal();
          await this.loadData();
        } else {
          // warning/error — keep modal open with the message from backend
          this.modalError = respMsg || 'Forge failed. Please try again.';
        }
      } catch (err) {
        console.error('[Forge] Claim network error', err);
        const msg = err.response?.data?.message || err.response?.data?.error || 'Network error. Please try again.';
        this.modalError = msg;
      } finally {
        this.forging = false;
      }
    },

    isOwnedBadge(badge) {
      return this.userBadgeIds.has(badge.id);
    },

    showToast(msg, type = 'info') {
      this.toastMsg = msg;
      this.toastType = type;
      if (this.toastTimer) clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => { this.toastMsg = ''; }, 2800);
    },

    progressLabel(trophy) {
      const s = trophy._status;
      if (s === 'ready') return 'Complete';
      if (s === 'locked') return 'Locked';
      if (s === 'forged') return 'Forged';
      return 'In progress';
    },
    progressFillClass(trophy) {
      const s = trophy._status;
      if (s === 'ready' || s === 'forged') return 'fc-progress-fill--complete';
      if (s === 'locked') return 'fc-progress-fill--empty';
      return 'fc-progress-fill--in-progress';
    },
    trophyImage(trophy) {
      if (!trophy?.image) return '';
      const img = trophy.image;
      if (img.startsWith('http') || img.startsWith('/')) return img;
      return `/storage/trophies/${img}`;
    },
    badgeImage(badge) {
      if (!badge?.image) return '';
      const img = badge.image;
      if (img.startsWith('http') || img.startsWith('/')) return img;
      const integration = typeof badge.integration === 'object'
        ? badge.integration?.name
        : (badge.integration || 'unknown');
      return `/storage/integrations/${integration}/${img}`;
    },
    badgeEmoji(badge) {
      return badge.emoji || '🏆';
    },
    trophyStrokeColor(trophy) {
      if (trophy._status === 'locked') return '#2a2c2e';
      if (trophy._status === 'forged' || trophy._status === 'ready') return '#c1f527';
      return '#ff6100';
    },
    trophyFillColor(trophy, layer) {
      if (trophy._status === 'ready' || trophy._status === 'forged') {
        return ['#c1f527', '#a6d820', '#8ab81a'][layer];
      }
      return ['#ff6100', '#d4500c', '#a33c06'][layer];
    }
  }
};
</script>

<style lang="scss" scoped>
.forge-page {
  min-width: 0;
  max-width: 100%;
}

/* ========== HERO ========== */
.forge-hero {
  position: relative;
  padding: 44px 48px 40px;
  overflow: hidden;
  border-bottom: 1px solid rgba(255, 97, 0, 0.08);
}
.forge-hero-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 900px 500px at 20% 50%, rgba(255, 97, 0, 0.18), transparent 65%),
    radial-gradient(ellipse 600px 400px at 85% 50%, rgba(193, 245, 39, 0.08), transparent 65%);
}
.forge-hero-content {
  position: relative;
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 40px;
  align-items: center;
}
.forge-hero-left { min-width: 0; }
.page-tag {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-bottom: 14px;
}
.page-title {
  font-family: var(--display);
  font-size: 56px;
  line-height: 1;
  letter-spacing: 0.03em;
  margin-bottom: 16px;
}
.page-title-accent {
  color: var(--primary);
  text-shadow: 0 0 24px rgba(255, 97, 0, 0.3);
  border-bottom: 3px solid var(--primary);
  padding-bottom: 2px;
}
.page-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.7;
  max-width: 560px;
}

.forge-hero-aside { display: flex; flex-direction: column; gap: 12px; }
.quick-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.qstat {
  padding: 16px 12px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  text-align: center;
}
.qstat--ready {
  border-color: rgba(193, 245, 39, 0.3);
  background: linear-gradient(180deg, rgba(193, 245, 39, 0.06), transparent);
}
.qstat--forged {
  border-color: rgba(255, 97, 0, 0.3);
  background: linear-gradient(180deg, rgba(255, 97, 0, 0.06), transparent);
}
.qstat-val { font-family: var(--display); font-size: 30px; line-height: 1; color: var(--text); }
.qstat--ready .qstat-val { color: var(--accent); text-shadow: 0 0 12px rgba(193, 245, 39, 0.35); }
.qstat--forged .qstat-val { color: var(--primary); text-shadow: 0 0 12px rgba(255, 97, 0, 0.35); }
.qstat-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  margin-top: 4px;
}

/* ========== FILTER ========== */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px 48px;
  border-bottom: 1px dashed rgba(255, 97, 0, 0.08);
  flex-wrap: wrap;
}
.filter-bar-label {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-right: 8px;
}
.filter-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 14px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 20px;
  color: var(--text-muted);
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.1em;
  cursor: pointer;
  transition: all 0.15s;
}
.filter-pill:hover { border-color: rgba(255, 97, 0, 0.4); color: var(--text); }
.filter-pill--active {
  background: var(--accent);
  color: var(--bg);
  border-color: var(--accent);
}
.filter-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 1px 7px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
  font-size: 10px;
  font-weight: bold;
}
.filter-pill:not(.filter-pill--active) .filter-count { background: var(--surface-2); }

/* ========== LOADING ========== */
.forge-loading { padding: 80px 48px; text-align: center; }
.forge-loading-pulse {
  width: 36px;
  height: 36px;
  margin: 0 auto 18px;
  border: 2px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
.forge-loading p { color: var(--text-muted); font-size: 12px; letter-spacing: 0.14em; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ========== CONTENT ========== */
.forge-content { padding: 32px 48px 40px; display: flex; flex-direction: column; gap: 40px; }
.forge-section { display: flex; flex-direction: column; gap: 16px; }
.section-label { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; }
.label-text { font-size: 11px; color: var(--primary); letter-spacing: 0.14em; text-transform: uppercase; }
.section-meta { font-size: 10px; color: var(--text-dim); letter-spacing: 0.14em; text-transform: uppercase; }

/* ========== FORGE CARD ========== */
.forge-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
.forge-card {
  position: relative;
  padding: 22px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: all 0.2s;
}
.forge-card:hover { border-color: rgba(255, 97, 0, 0.35); }
.forge-card--ready {
  border-color: rgba(193, 245, 39, 0.35);
  box-shadow: 0 0 0 1px rgba(193, 245, 39, 0.08) inset;
}
.forge-card--ready::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(193, 245, 39, 0.05), transparent 60%);
  pointer-events: none;
  border-radius: 6px;
}
.forge-card--locked { opacity: 0.5; }
.forge-card--forged { border-color: rgba(193, 245, 39, 0.25); opacity: 0.88; }
.forge-card--deep-link {
  box-shadow: 0 0 0 2px var(--accent);
  animation: pulse-highlight 1.8s ease-out;
}
@keyframes pulse-highlight {
  0% { box-shadow: 0 0 0 2px var(--accent), 0 0 32px rgba(193, 245, 39, 0.6); }
  100% { box-shadow: 0 0 0 2px var(--accent), 0 0 0 rgba(193, 245, 39, 0); }
}

.ready-flag, .forged-flag {
  position: absolute;
  top: -1px;
  right: -1px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  font-size: 9px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-weight: bold;
  border-radius: 0 6px 0 4px;
  z-index: 2;
}
.ready-flag { background: var(--accent); color: var(--bg); }
.ready-flag-dot {
  width: 5px;
  height: 5px;
  background: var(--bg);
  border-radius: 50%;
  animation: pulse-dot 1.4s ease-in-out infinite;
}
@keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
.forged-flag {
  background: var(--surface-2);
  color: var(--accent);
  border: 1px solid rgba(193, 245, 39, 0.35);
}

.fc-header { display: flex; gap: 14px; align-items: flex-start; }
.fc-asset {
  width: 68px;
  height: 68px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 12px rgba(193, 245, 39, 0.12));
}
.forge-card--ready .fc-asset,
.forge-card--forged .fc-asset {
  filter: drop-shadow(0 0 18px rgba(193, 245, 39, 0.22));
}
.forge-card--locked .fc-asset { filter: none; }
.fc-asset img, .fc-asset svg { width: 100%; height: 100%; object-fit: contain; }

.fc-info { min-width: 0; flex: 1; }
.fc-name {
  font-size: 16px;
  color: var(--text);
  letter-spacing: 0.02em;
  line-height: 1.2;
  margin-bottom: 4px;
}
.fc-sub { font-size: 9px; color: var(--text-dim); letter-spacing: 0.16em; text-transform: uppercase; }

.fc-desc {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 3em;
}

.fc-badges { display: flex; gap: 6px; flex-wrap: wrap; }
.fc-badge {
  width: 32px;
  height: 32px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}
.fc-badge img { width: 70%; height: 70%; object-fit: contain; }
.fc-badge-emoji { font-size: 16px; }
.fc-badge--owned {
  border-color: rgba(193, 245, 39, 0.45);
  box-shadow: 0 0 6px rgba(193, 245, 39, 0.2);
}
.fc-badge--missing { opacity: 0.35; filter: grayscale(0.8); }
.fc-badge--more { color: var(--text-muted); font-size: 10px; font-family: var(--mono); }

.fc-progress { margin-top: 4px; }
.fc-progress-bar {
  height: 4px;
  background: var(--surface-3);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 6px;
}
.fc-progress-fill { height: 100%; transition: width 0.4s ease; }
.fc-progress-fill--empty { background: var(--surface-3); }
.fc-progress-fill--in-progress {
  background: var(--accent);
  box-shadow: 0 0 6px rgba(193, 245, 39, 0.3);
}
.fc-progress-fill--complete {
  background: var(--primary);
  box-shadow: 0 0 8px rgba(255, 97, 0, 0.4);
}
.fc-progress-text {
  display: flex;
  justify-content: space-between;
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.fc-meta { display: flex; gap: 6px; flex-wrap: wrap; }
.fc-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  font-size: 10px;
  letter-spacing: 0.1em;
  border-radius: 3px;
  font-family: var(--mono);
}
.fc-pill--xp { background: rgba(193, 245, 39, 0.12); color: var(--accent); }
.fc-pill--cost { background: rgba(255, 97, 0, 0.1); color: var(--primary); }
.fc-pill--reward {
  background: rgba(193, 245, 39, 0.08);
  color: var(--accent);
  border: 1px dashed rgba(193, 245, 39, 0.35);
}
.pill-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

.fc-cta { margin-top: 4px; }
.fc-btn {
  width: 100%;
  padding: 11px 16px;
  font-size: 11px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-family: var(--mono);
  border: 1px solid transparent;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.15s;
}
.fc-btn--forge { background: var(--primary); color: var(--bg); font-weight: bold; }
.fc-btn--forge:hover {
  box-shadow: 0 0 18px rgba(255, 97, 0, 0.5);
  transform: translateY(-1px);
}
.fc-btn--missing, .fc-btn--locked, .fc-btn--forged {
  background: transparent;
  color: var(--text-dim);
  border-color: var(--border);
  cursor: default;
}
.fc-btn--forged { color: var(--accent); border-color: rgba(193, 245, 39, 0.2); }

/* ========== MODAL ========== */
.forge-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 3, 0.85);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 24px;
}
.forge-modal {
  position: relative;
  width: 100%;
  max-width: 440px;
  padding: 40px 32px 28px;
  background: var(--surface);
  border: 1px solid var(--accent);
  border-radius: 8px;
  box-shadow: 0 0 60px rgba(193, 245, 39, 0.15);
  text-align: center;
}
.forge-modal::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(193, 245, 39, 0.08), transparent 60%);
  pointer-events: none;
  border-radius: 8px;
}
.fm-close {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 28px;
  height: 28px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 3px;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.fm-close:hover { border-color: var(--primary); color: var(--primary); }
.fm-trophy-art {
  width: 160px;
  height: 160px;
  margin: 0 auto 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 32px rgba(193, 245, 39, 0.35));
  position: relative;
}
.fm-trophy-art img, .fm-trophy-art svg { width: 100%; height: 100%; object-fit: contain; }
.fm-title {
  font-family: var(--display);
  font-size: 32px;
  color: var(--text);
  margin-bottom: 6px;
  position: relative;
}
.fm-question { font-size: 12px; color: var(--text-muted); margin-bottom: 24px; position: relative; }

.fm-costs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 20px;
  position: relative;
}
.fm-cost-cell {
  padding: 16px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: 4px;
}
.fm-cost-val { font-family: var(--display); font-size: 28px; line-height: 1; margin-bottom: 4px; }
.fm-cost-val--spend { color: var(--primary); }
.fm-cost-val--earn { color: var(--accent); }
.fm-cost-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.fm-error {
  padding: 10px 14px;
  margin-bottom: 16px;
  background: rgba(226, 75, 74, 0.1);
  border: 1px solid rgba(226, 75, 74, 0.3);
  color: #e24b4a;
  font-size: 11px;
  text-align: left;
  border-radius: 3px;
  position: relative;
}

.fm-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; position: relative; }
.fm-btn {
  padding: 12px 18px;
  font-size: 11px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-family: var(--mono);
  border-radius: 3px;
  cursor: pointer;
  transition: all 0.15s;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
}
.fm-btn:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); }
.fm-btn--confirm {
  background: var(--accent);
  color: var(--bg);
  border-color: var(--accent);
  font-weight: bold;
}
.fm-btn--confirm:hover:not(:disabled) {
  box-shadow: 0 0 20px rgba(193, 245, 39, 0.5);
  color: var(--bg);
  border-color: var(--accent);
}
.fm-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

/* ========== TOAST ========== */
.forge-toast {
  position: fixed;
  bottom: 32px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 22px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  font-size: 12px;
  letter-spacing: 0.1em;
  z-index: 999;
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.5);
}
.forge-toast--success {
  border-color: var(--accent);
  color: var(--accent);
  box-shadow: 0 0 24px rgba(193, 245, 39, 0.2);
}
.forge-toast--error { border-color: #e24b4a; color: #e24b4a; }
.forge-toast--info { color: var(--text); }
.toast-enter-active, .toast-leave-active { transition: all 0.25s; }
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(10px);
}

/* ========== EMPTY ========== */
.forge-empty { padding: 60px 24px; text-align: center; color: var(--text-muted); }
.forge-empty p { margin-bottom: 12px; font-size: 13px; }
.forge-empty-cta {
  font-size: 11px;
  color: var(--primary);
  border: 1px solid var(--primary);
  background: transparent;
  padding: 8px 16px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-family: var(--mono);
  cursor: pointer;
  border-radius: 3px;
}
.forge-empty-cta:hover { background: var(--primary); color: var(--bg); }

/* ========== TERMINAL ========== */
.terminal-strip {
  padding: 18px 48px;
  border-top: 1px dashed rgba(255, 97, 0, 0.08);
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
  display: flex;
  justify-content: space-between;
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
  .forge-hero { padding: 28px 24px; }
  .forge-hero-content { grid-template-columns: 1fr; gap: 20px; }
  .page-title { font-size: 40px; }
  .filter-bar { padding: 14px 24px; }
  .forge-content { padding: 24px 24px 36px; }
  .terminal-strip { padding: 14px 24px; }
  .forge-grid { grid-template-columns: 1fr; }
  .forge-modal { padding: 32px 20px 24px; }
  .fm-trophy-art { width: 130px; height: 130px; }
  .fm-title { font-size: 26px; }
}
@media (max-width: 600px) {
  .page-title { font-size: 32px; }
  .quick-stats { grid-template-columns: repeat(3, 1fr); }
  .fm-costs, .fm-actions { grid-template-columns: 1fr; }
}
</style>

<template>
  <section class="brand-dashboard-v2">
    <!-- Status banner — shown when account_status is pending or rejected -->
    <div
      v-if="accountStatusBanner"
      :class="['bd-status-banner', `bd-status-banner--${accountStatusBanner.variant}`]"
    >
      <div class="bd-status-icon">▲</div>
      <div class="bd-status-content">
        <div class="bd-status-title">{{ accountStatusBanner.title }}</div>
        <div class="bd-status-text">{{ accountStatusBanner.body }}</div>
      </div>
    </div>

    <header class="dash-header">
      <div class="dash-header-text">
        <h1>Brand Dashboard</h1>
        <p class="dash-subtitle">Performance · Audience · Campaigns</p>
      </div>
      <a
        v-if="brandHallPath"
        :href="brandHallPath"
        :class="['dash-hall-btn', { 'dash-hall-btn--locked': isLocked }]"
        :title="isLocked ? lockedTitle : null"
        @click="isLocked ? $event.preventDefault() : null"
      >
        → View public hall
        <span v-if="isLocked" class="bd-lock-icon">▲</span>
      </a>
    </header>

    <div class="dash-stack">
      <!-- 1. Performance Overview -->
      <PerformanceOverview />

      <!-- 2. Secondary Metrics Strip -->
      <SecondaryMetricsStrip />

      <!-- 3. Audience Intelligence -->
      <AudienceIntelligence />

      <!-- 4. Dual Row: Campaigns + Activity -->
      <div class="dual-row">
        <CampaignsTable />
        <ActivityFeed />
      </div>

      <!-- 5. Locked Pro Features -->
      <LockedProFeatures />
    </div>
  </section>
</template>

<script>
import PerformanceOverview from './v2/PerformanceOverview.vue';
import SecondaryMetricsStrip from './v2/SecondaryMetricsStrip.vue';
import AudienceIntelligence from './v2/AudienceIntelligence.vue';
import CampaignsTable from './v2/CampaignsTable.vue';
import ActivityFeed from './v2/ActivityFeed.vue';
import LockedProFeatures from './v2/LockedProFeatures.vue';

export default {
  name: 'BrandDashboardV2',
  components: { PerformanceOverview, SecondaryMetricsStrip, AudienceIntelligence, CampaignsTable, ActivityFeed, LockedProFeatures },
  computed: {
    brandHallPath() {
      try {
        const stored = JSON.parse(localStorage.getItem('user') || '{}');
        return stored.username ? `/${stored.username}` : null;
      } catch (e) {
        return null;
      }
    },
    accountStatusBanner() {
      const status = this.$store.state.user?.account_status;
      if (status === 'pending') {
        return {
          variant: 'pending',
          title: 'Account pending approval',
          body: 'Your brand account is under review. The dashboard is yours to explore — trophy creation and your public hall unlock once approved.',
        };
      }
      if (status === 'rejected') {
        return {
          variant: 'rejected',
          title: 'Account not approved',
          body: "We weren't able to approve your account at this time. Reach out to hello@trophyroom.gg to discuss next steps.",
        };
      }
      return null;
    },
    isLocked() {
      const status = this.$store.state.user?.account_status;
      return status && status !== 'active';
    },
    lockedTitle() {
      return 'Locked until your account is approved';
    },
  },
};
</script>

<style scoped>
.brand-dashboard-v2 {
  padding: 24px 40px 80px;
  max-width: 1500px;
  color: var(--text, #feeddf);
  min-height: 100vh;
}

/* Header — matches mockup (.dash-header / .dash-title / .dash-subtitle) */
.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  padding-bottom: 20px;
  margin-bottom: 32px;
  gap: 24px;
  flex-wrap: wrap;
}

.dash-header h1 {
  font-family: 'VT323', monospace;
  font-size: 48px;
  color: var(--primary, #ff6100);
  line-height: 1;
  letter-spacing: 1px;
  margin: 0 0 8px 0;
  text-transform: uppercase;
}

.dash-subtitle {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  margin: 0;
}

/* Section stack — mockup uses ~16px between sections */
.dash-stack {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Dual row (Campaigns 1.6fr | Activity 1fr) — matches mockup */
.dual-row {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 16px;
  align-items: start;
}

@media (max-width: 1024px) {
  .dual-row {
    grid-template-columns: 1fr;
  }
}

/* Scaffolding placeholders — replaced in commits 2-7 */
.placeholder {
  background: var(--bg-elev, #0a0b0f);
  border: 1px dashed rgba(255, 97, 0, 0.3);
  padding: 32px;
  text-align: center;
  color: rgba(254, 237, 223, 0.4);
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  letter-spacing: 0.05em;
}

.dash-header-text {
  /* Container for title + subtitle. Layout handled by parent flex. */
}

.dash-hall-btn {
  display: inline-block;
  border: 1px solid var(--accent, #c1f527);
  color: var(--accent, #c1f527);
  background: transparent;
  padding: 10px 20px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  letter-spacing: 2px;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 150ms ease;
  white-space: nowrap;
  align-self: center;
}

.dash-hall-btn:hover {
  background: var(--accent, #c1f527);
  color: var(--bg, #000003);
}

/* Status banner — shown when account_status is pending or rejected */
.bd-status-banner {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 20px;
  margin-bottom: 24px;
  border: 1px solid;
  background: linear-gradient(135deg, rgba(20, 22, 26, 0.9) 0%, var(--banner-tint, transparent) 100%);
}
.bd-status-banner--pending {
  --banner-tint: rgba(255, 184, 0, 0.04);
  border-color: var(--warn, #ffb800);
}
.bd-status-banner--rejected {
  --banner-tint: rgba(255, 80, 80, 0.04);
  border-color: rgba(255, 80, 80, 0.6);
}
.bd-status-icon {
  font-family: 'VT323', monospace;
  font-size: 32px;
}
.bd-status-banner--pending .bd-status-icon { color: var(--warn, #ffb800); }
.bd-status-banner--rejected .bd-status-icon { color: rgba(255, 120, 120, 0.95); }
.bd-status-content { flex: 1; }
.bd-status-title {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 4px;
}
.bd-status-banner--pending .bd-status-title { color: var(--warn, #ffb800); }
.bd-status-banner--rejected .bd-status-title { color: rgba(255, 120, 120, 0.95); }
.bd-status-text {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  letter-spacing: 0.5px;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
}

/* Locked state for the View public hall button (brand pending/rejected) */
.dash-hall-btn--locked {
  opacity: 0.4;
  cursor: url('/images/cursor-trex.png') 22 11, not-allowed;
  pointer-events: auto;
}
.dash-hall-btn--locked:hover {
  opacity: 0.4;
  background: transparent;
  border-color: var(--accent, #c1f527);
  color: var(--accent, #c1f527);  /* mantener chartreuse en hover, consistente con at-rest */
}
.bd-lock-icon {
  margin-left: 6px;
  font-family: 'VT323', monospace;
  font-size: 14px;
  color: var(--warn, #ffb800);
  opacity: 0.8;
}
</style>

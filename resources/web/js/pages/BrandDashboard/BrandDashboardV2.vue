<template>
  <section class="brand-dashboard-v2">
    <header class="dash-header">
      <h1>Brand Dashboard</h1>
      <p class="dash-subtitle">Performance · Audience · Campaigns</p>
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

      <!-- 5. Locked Pro Features (Commit 7) -->
      <div class="placeholder">[LockedProFeatures goes here]</div>
    </div>
  </section>
</template>

<script>
import PerformanceOverview from './v2/PerformanceOverview.vue';
import SecondaryMetricsStrip from './v2/SecondaryMetricsStrip.vue';
import AudienceIntelligence from './v2/AudienceIntelligence.vue';
import CampaignsTable from './v2/CampaignsTable.vue';
import ActivityFeed from './v2/ActivityFeed.vue';

export default {
  name: 'BrandDashboardV2',
  components: { PerformanceOverview, SecondaryMetricsStrip, AudienceIntelligence, CampaignsTable, ActivityFeed },
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
  border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  padding-bottom: 20px;
  margin-bottom: 32px;
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
</style>

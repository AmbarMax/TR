<template>
  <section class="audience-intelligence">
    <div class="section-label">
      Audience intelligence
      <span class="section-meta">cross-gaming signal · unique to TrophyRoom</span>
    </div>

    <div v-if="loading" class="state-msg">Loading audience data…</div>

    <div v-else-if="error" class="state-msg state-error">
      <span>Failed to load audience intelligence.</span>
      <button class="retry-btn" @click="load">Retry</button>
    </div>

    <div v-else-if="data" class="audience-grid">
      <!-- Card 1: Platforms breakdown -->
      <div class="audience-card">
        <div class="audience-card-title">
          Platforms
          <span class="badge-meta">{{ totalPursuers }} pursuers</span>
        </div>
        <div v-if="!data.platforms_breakdown.length" class="empty-inline">No platforms data yet</div>
        <div v-else class="platform-list">
          <div v-for="p in data.platforms_breakdown" :key="p.platform" class="platform-row">
            <span class="platform-name">{{ p.platform }}</span>
            <div class="platform-bar-track">
              <div class="platform-bar-fill" :style="{ '--w': p.percent / 100 }"></div>
            </div>
            <span class="platform-pct">{{ p.percent }}%</span>
          </div>
        </div>
      </div>

      <!-- Card 2: Top achievements -->
      <div class="audience-card">
        <div class="audience-card-title">
          Top achievements
          <span class="badge-meta">your audience</span>
        </div>
        <div v-if="!data.top_achievements.length" class="empty-inline">No achievement data yet</div>
        <div v-else class="ach-list">
          <div v-for="(ach, i) in data.top_achievements" :key="ach.badge_id" class="ach-row">
            <span class="ach-rank">{{ formatRank(i + 1) }}</span>
            <span class="ach-name">
              <span class="ach-game">{{ capitalize(ach.platform) }}:</span>{{ ach.badge_name }}
            </span>
            <span class="ach-count">{{ ach.grants }}</span>
          </div>
        </div>
      </div>

      <!-- Card 3: Affinity keywords (always empty in v.2) -->
      <div class="audience-card">
        <div class="audience-card-title">
          Affinity keywords
          <span class="badge-meta">discord roles</span>
        </div>
        <div v-if="data.keywords_cross_discord && data.keywords_cross_discord.length > 0" class="kw-list">
          <span
            v-for="(kw, i) in data.keywords_cross_discord"
            :key="kw.keyword"
            :class="['kw-pill', { hot: i === 0 }]"
          >
            {{ kw.keyword }}
            <span class="kw-count">{{ kw.mentions }}</span>
          </span>
        </div>
        <div v-else class="kw-empty">
          <p class="kw-empty-main">Connect Discord to unlock affinity keywords.</p>
          <p class="kw-empty-sub">Audience intel beyond your community.</p>
        </div>
      </div>

      <!-- Card 4: Conversion funnel -->
      <div class="audience-card">
        <div class="audience-card-title">
          Conversion funnel
          <span class="badge-meta">all campaigns</span>
        </div>
        <div class="funnel-compact">
          <template v-for="(stage, i) in funnelStages" :key="stage.label">
            <div class="funnel-step" :class="{ final: stage.final }">
              <div class="funnel-step-bar">
                <div class="funnel-step-fill" :class="{ final: stage.final }" :style="{ '--w': stage.w }"></div>
                <div class="funnel-step-content">
                  <span>{{ stage.label }}</span>
                  <span class="funnel-step-num">{{ stage.value }}</span>
                </div>
              </div>
            </div>
            <div v-if="i < funnelStages.length - 1" class="funnel-ratio">{{ funnelRatio(i) }}%</div>
          </template>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { fetchAudience } from '../../../services/brandAnalytics.js';

export default {
  name: 'AudienceIntelligence',
  data() {
    return {
      data: null,
      loading: true,
      error: null,
    };
  },
  computed: {
    totalPursuers() {
      if (!this.data?.platforms_breakdown) return 0;
      return this.data.platforms_breakdown.reduce((sum, p) => sum + (p.user_count || 0), 0);
    },
    funnelStages() {
      const f = this.data?.funnel;
      if (!f) return [];
      const stages = [
        { label: 'Started pursuit', value: f.started_pursuit || 0, final: false },
        { label: 'Earned first badge', value: f.earned_first_badge || 0, final: false },
        { label: 'Forged trophy', value: f.forged_trophy || 0, final: true },
      ];
      const max = Math.max(...stages.map(s => s.value), 1);
      return stages.map(s => ({ ...s, w: s.value / max }));
    },
  },
  mounted() {
    this.load();
  },
  methods: {
    async load() {
      this.loading = true;
      this.error = null;
      try {
        const res = await fetchAudience();
        this.data = res.data;
      } catch (e) {
        console.error('[AudienceIntelligence] load failed', e);
        this.error = e;
      } finally {
        this.loading = false;
      }
    },
    formatRank(n) {
      return String(n).padStart(2, '0');
    },
    capitalize(s) {
      if (!s) return '';
      return s.charAt(0).toUpperCase() + s.slice(1);
    },
    funnelRatio(i) {
      const stages = this.funnelStages;
      const cur = stages[i]?.value || 0;
      const next = stages[i + 1]?.value || 0;
      if (cur === 0) return 0;
      return Math.round((next / cur) * 100);
    },
  },
};
</script>

<style scoped>
.audience-intelligence {
  display: block;
}

/* Section label */
.section-label {
  display: flex;
  align-items: center;
  gap: 12px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: var(--primary, #ff6100);
  letter-spacing: 3px;
  text-transform: uppercase;
  margin: 0 0 16px 0;
}
.section-label::before {
  content: '◆';
  color: var(--accent, #c1f527);
}
.section-meta {
  margin-left: auto;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 1px;
  font-size: 10px;
}

/* Audience grid */
.audience-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

@media (max-width: 1024px) {
  .audience-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .audience-grid {
    grid-template-columns: 1fr;
  }
}

/* Audience card base */
.audience-card {
  border: 1px solid var(--line-strong, rgba(254, 237, 223, 0.15));
  background: var(--bg-elev, #0a0b0f);
  padding: 18px;
  display: flex;
  flex-direction: column;
}

.audience-card-title {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: var(--primary, #ff6100);
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 16px;
  padding-bottom: 8px;
  border-bottom: 1px dashed var(--primary-line, rgba(255, 97, 0, 0.25));
}
.audience-card-title .badge-meta {
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  margin-left: 8px;
  font-size: 10px;
  text-transform: none;
  letter-spacing: 1px;
}

/* Empty inline (used by Card 1 and Card 2 when arrays are empty) */
.empty-inline {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 1px;
  padding: 20px 0;
  text-align: center;
}

/* === Card 1: Platforms === */
.platform-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.platform-row {
  display: grid;
  grid-template-columns: 70px 1fr 40px;
  align-items: center;
  gap: 10px;
  font-size: 11px;
}
.platform-name {
  font-family: 'Share Tech Mono', monospace;
  color: var(--text, #feeddf);
  letter-spacing: 1px;
  text-transform: uppercase;
  font-size: 10px;
}
.platform-bar-track {
  height: 5px;
  background: var(--line, rgba(254, 237, 223, 0.08));
  position: relative;
  overflow: hidden;
}
.platform-bar-fill {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, var(--primary, #ff6100) 0%, var(--accent, #c1f527) 100%);
  transform-origin: left;
  transform: scaleX(var(--w, 0));
  animation: growBar 1s cubic-bezier(0.2, 0.8, 0.3, 1) forwards;
}
@keyframes growBar {
  from { transform: scaleX(0); }
  to { transform: scaleX(var(--w, 0)); }
}
.platform-pct {
  font-family: 'VT323', monospace;
  color: var(--accent, #c1f527);
  font-size: 14px;
  text-align: right;
  letter-spacing: 1px;
}

/* === Card 2: Top achievements === */
.ach-list {
  display: flex;
  flex-direction: column;
}
.ach-row {
  display: grid;
  grid-template-columns: 22px 1fr auto;
  align-items: center;
  gap: 8px;
  padding: 6px 0;
  border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  font-size: 11px;
}
.ach-row:last-child {
  border-bottom: none;
}
.ach-rank {
  font-family: 'VT323', monospace;
  color: var(--primary, #ff6100);
  font-size: 16px;
  letter-spacing: 1px;
}
.ach-name {
  font-family: 'Share Tech Mono', monospace;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  font-size: 11px;
}
.ach-game {
  color: var(--text, #feeddf);
  margin-right: 4px;
}
.ach-count {
  font-family: 'VT323', monospace;
  color: var(--accent, #c1f527);
  font-size: 14px;
  letter-spacing: 1px;
}

/* === Card 3: Keywords === */
.kw-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.kw-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 8px;
  border: 1px solid var(--primary-line, rgba(255, 97, 0, 0.25));
  background: var(--primary-faint, rgba(255, 97, 0, 0.12));
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  letter-spacing: 0.5px;
}
.kw-pill.hot {
  border-color: var(--accent, #c1f527);
  background: var(--accent-faint, rgba(193, 245, 39, 0.1));
}
.kw-pill .kw-count {
  font-family: 'VT323', monospace;
  color: var(--accent, #c1f527);
  font-size: 12px;
  border-left: 1px solid var(--primary-line, rgba(255, 97, 0, 0.25));
  padding-left: 6px;
}

/* Empty state for keywords */
.kw-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  text-align: center;
  padding: 12px 0;
}
.kw-empty-main {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  letter-spacing: 0.5px;
  margin: 0 0 8px 0;
  line-height: 1.4;
}
.kw-empty-sub {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 0.5px;
  margin: 0;
  line-height: 1.4;
}

/* === Card 4: Funnel === */
.funnel-compact {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.funnel-step {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.funnel-step-bar {
  position: relative;
  height: 28px;
  background: var(--line, rgba(254, 237, 223, 0.08));
  overflow: hidden;
}
.funnel-step-fill {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, var(--primary-faint, rgba(255, 97, 0, 0.12)) 0%, var(--primary, #ff6100) 100%);
  transform-origin: left;
  transform: scaleX(var(--w, 0));
  animation: growBar 1.2s cubic-bezier(0.2, 0.8, 0.3, 1) forwards;
}
.funnel-step-fill.final {
  background: linear-gradient(90deg, var(--accent-faint, rgba(193, 245, 39, 0.1)) 0%, var(--accent, #c1f527) 100%);
}
.funnel-step-content {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 10px;
  height: 100%;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--text, #feeddf);
  letter-spacing: 1px;
  text-transform: uppercase;
}
.funnel-step-num {
  font-family: 'VT323', monospace;
  font-size: 14px;
  color: var(--text, #feeddf);
  letter-spacing: 1px;
}
.funnel-step.final .funnel-step-num {
  color: var(--bg, #000003);
  font-weight: bold;
}
.funnel-step.final .funnel-step-content {
  color: var(--bg, #000003);
}
.funnel-ratio {
  text-align: center;
  font-family: 'Share Tech Mono', monospace;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  font-size: 9px;
  letter-spacing: 1.5px;
  padding: 2px 0;
}

/* Loading / error */
.state-msg {
  padding: 40px;
  text-align: center;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  letter-spacing: 0.1em;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  background: var(--bg-elev, #0a0b0f);
  border: 1px solid var(--line, rgba(254, 237, 223, 0.08));
}
.state-error {
  color: var(--danger, #ff3860);
}
.retry-btn {
  margin-left: 12px;
  padding: 4px 12px;
  font-family: inherit;
  font-size: inherit;
  letter-spacing: inherit;
  background: transparent;
  border: 1px solid currentColor;
  color: inherit;
  cursor: pointer;
  text-transform: uppercase;
}
.retry-btn:hover {
  background: rgba(255, 56, 96, 0.1);
}
</style>

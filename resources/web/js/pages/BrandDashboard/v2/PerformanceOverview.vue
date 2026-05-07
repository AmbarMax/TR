<template>
  <section class="performance-overview">
    <div class="section-label">
      Performance overview
      <span class="section-meta">vs. previous period</span>
    </div>

    <div v-if="loading" class="state-msg">Loading metrics…</div>

    <div v-else-if="error" class="state-msg state-error">
      <span>Failed to load performance data.</span>
      <button class="retry-btn" @click="load">Retry</button>
    </div>

    <div v-else-if="data" class="hero-stats">
      <!-- Card 1 — Active Pursuers -->
      <div class="stat-card accent">
        <div class="stat-card-label">Active pursuers</div>
        <div class="stat-card-value">{{ data.active_pursuers.value }}</div>
        <div :class="['stat-card-trend', deltaClass(data.active_pursuers.delta_7d)]">
          {{ formatDelta(data.active_pursuers.delta_7d, '7d') }}
        </div>
      </div>

      <!-- Card 2 — Trophies Forged + sparkline -->
      <div class="stat-card">
        <div class="stat-card-label">Trophies forged · 30d</div>
        <div class="stat-card-value">{{ data.trophies_forged.value }}</div>
        <div class="sparkline-wrap">
          <svg viewBox="0 0 200 28" preserveAspectRatio="none">
            <defs>
              <linearGradient id="sparklineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#c1f527" stop-opacity="0.6" />
                <stop offset="100%" stop-color="#c1f527" stop-opacity="0" />
              </linearGradient>
            </defs>
            <polyline class="sparkline-area" :points="sparklineAreaPoints" />
            <polyline class="sparkline-path" :points="sparklineLinePoints" />
            <circle class="sparkline-dot" :cx="lastDot.x" :cy="lastDot.y" r="2.5" />
          </svg>
        </div>
        <div :class="['stat-card-trend', deltaClass(data.trophies_forged.delta_30d)]" style="margin-top: 4px;">
          {{ formatDelta(data.trophies_forged.delta_30d, '30d') }}
        </div>
      </div>

      <!-- Card 3 — Badges Granted -->
      <div class="stat-card primary">
        <div class="stat-card-label">Badges granted · 30d</div>
        <div class="stat-card-value">{{ data.badges_granted.value }}</div>
        <div :class="['stat-card-trend', deltaClass(data.badges_granted.delta_30d)]">
          {{ formatDelta(data.badges_granted.delta_30d, '30d') }}
        </div>
      </div>

      <!-- Card 4 — CPT (locked) -->
      <div class="stat-card locked">
        <div class="coming-soon-ribbon">LOCKED</div>
        <div class="stat-card-label">Avg cost per trophy</div>
        <div class="stat-card-locked-val">— $— est.</div>
        <div class="stat-card-locked-hint">
          <span class="lock-ico"></span>
          {{ data.cpt.tooltip || 'Activate billing for real CPT' }}
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { fetchPerformance } from '../../../services/brandAnalytics.js';

export default {
  name: 'PerformanceOverview',
  data() {
    return {
      data: null,
      loading: true,
      error: null,
    };
  },
  computed: {
    sparklineCoords() {
      const arr = this.data?.trophies_forged?.sparkline;
      if (!arr || !arr.length) return [];
      const max = Math.max(...arr, 1);
      const w = 200;
      const h = 28;
      return arr.map((v, i) => ({
        x: (i / (arr.length - 1)) * w,
        y: h - (v / max) * h,
      }));
    },
    sparklineLinePoints() {
      return this.sparklineCoords.map(p => `${p.x},${p.y}`).join(' ');
    },
    sparklineAreaPoints() {
      const line = this.sparklineLinePoints;
      return line ? `0,28 ${line} 200,28` : '';
    },
    lastDot() {
      const last = this.sparklineCoords[this.sparklineCoords.length - 1];
      return last || { x: 200, y: 28 };
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
        const res = await fetchPerformance();
        this.data = res.data;
      } catch (e) {
        console.error('[PerformanceOverview] load failed', e);
        this.error = e;
      } finally {
        this.loading = false;
      }
    },
    formatDelta(delta, period) {
      if (delta === null || delta === undefined) return 'New (no prior data)';
      const arrow = delta > 0 ? '↑' : delta < 0 ? '↓' : '→';
      const sign = delta > 0 ? '+' : '';
      return `${arrow} ${sign}${delta}% vs prev ${period}`;
    },
    deltaClass(delta) {
      if (delta === null || delta === undefined) return 'neutral';
      if (delta > 0) return 'up';
      if (delta < 0) return 'down';
      return 'neutral';
    },
  },
};
</script>

<style scoped>
.performance-overview {
  display: block;
}

/* Section label — matches mockup */
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

/* Hero stats grid */
.hero-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

@media (max-width: 1024px) {
  .hero-stats {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .hero-stats {
    grid-template-columns: 1fr;
  }
}

/* Stat card base */
.stat-card {
  border: 1px solid var(--line-strong, rgba(254, 237, 223, 0.15));
  padding: 20px;
  background: var(--bg-elev, #0a0b0f);
  position: relative;
  overflow: hidden;
}
.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: var(--primary, #ff6100);
  transition: width 600ms;
}
.stat-card:hover::before {
  width: 100%;
}

.stat-card-label {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 2.5px;
  text-transform: uppercase;
  margin-bottom: 12px;
}

.stat-card-value {
  font-family: 'VT323', monospace;
  font-size: 56px;
  color: var(--text, #feeddf);
  line-height: 1;
  letter-spacing: 2px;
}

.stat-card.accent .stat-card-value {
  color: var(--accent, #c1f527);
  text-shadow: 0 0 18px rgba(193, 245, 39, 0.4);
}
.stat-card.primary .stat-card-value {
  color: var(--primary, #ff6100);
  text-shadow: 0 0 16px rgba(255, 97, 0, 0.4);
}

.stat-card-trend {
  margin-top: 8px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: var(--accent, #c1f527);
  letter-spacing: 1px;
}
.stat-card-trend.up { color: var(--accent, #c1f527); }
.stat-card-trend.down { color: var(--danger, #ff3860); }
.stat-card-trend.neutral { color: var(--text-faint, rgba(254, 237, 223, 0.35)); }

/* Locked card */
.stat-card.locked {
  border-color: var(--line, rgba(254, 237, 223, 0.08));
  background: linear-gradient(135deg, var(--bg-elev, #0a0b0f) 0%, rgba(255, 184, 0, 0.03) 100%);
}
.stat-card-locked-val {
  font-family: 'VT323', monospace;
  font-size: 36px;
  color: var(--warn, #ffb800);
  letter-spacing: 2px;
  line-height: 1;
}
.stat-card-locked-hint {
  margin-top: 6px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--warn, #ffb800);
  letter-spacing: 1.5px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.lock-ico {
  display: inline-block;
  width: 9px;
  height: 9px;
  border: 1px solid var(--warn, #ffb800);
  position: relative;
}
.lock-ico::after {
  content: '';
  position: absolute;
  top: -3px;
  left: 1px;
  width: 5px;
  height: 4px;
  border: 1px solid var(--warn, #ffb800);
  border-bottom: none;
}
.coming-soon-ribbon {
  position: absolute;
  top: 8px;
  right: -28px;
  background: var(--warn, #ffb800);
  color: var(--bg, #000003);
  font-family: 'Share Tech Mono', monospace;
  font-size: 8px;
  font-weight: bold;
  padding: 2px 32px;
  transform: rotate(45deg);
  letter-spacing: 1.5px;
}

/* Sparkline */
.sparkline-wrap {
  margin-top: 12px;
  height: 28px;
  position: relative;
}
.sparkline-wrap svg {
  width: 100%;
  height: 100%;
  display: block;
}
.sparkline-path {
  fill: none;
  stroke: var(--accent, #c1f527);
  stroke-width: 1.5;
  stroke-linejoin: round;
  filter: drop-shadow(0 0 3px rgba(193, 245, 39, 0.5));
}
.sparkline-area {
  fill: url(#sparklineGradient);
  opacity: 0.4;
  stroke: none;
}
.sparkline-dot {
  fill: var(--accent, #c1f527);
  filter: drop-shadow(0 0 4px rgba(193, 245, 39, 0.8));
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

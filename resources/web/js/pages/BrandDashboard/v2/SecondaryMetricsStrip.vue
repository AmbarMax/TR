<template>
  <section class="secondary-metrics-strip">
    <div class="section-label">Secondary metrics</div>

    <div v-if="loading" class="state-msg">Loading metrics…</div>

    <div v-else-if="error" class="state-msg state-error">
      <span>Failed to load secondary metrics.</span>
      <button class="retry-btn" @click="load">Retry</button>
    </div>

    <div v-else-if="data" class="secondary-strip">
      <!-- Cell 1: Total badges granted -->
      <div class="secondary-cell">
        <div class="secondary-label">Total badges granted</div>
        <div class="secondary-value accent">{{ data.total_badges_granted.value }}</div>
        <div class="secondary-meta">{{ data.total_badges_granted.label }}</div>
      </div>

      <!-- Cell 2: Cross-hall overlap -->
      <div class="secondary-cell">
        <div class="secondary-label">Cross-hall overlap</div>
        <div class="secondary-value">
          <template v-if="topCrossHall">
            @{{ topCrossHall.brand_username }}
            <span class="unit">{{ topCrossHall.overlap_percent }}%</span>
          </template>
          <template v-else>—</template>
        </div>
        <div class="secondary-meta">
          <span v-for="(part, i) in crossHallMetaParts" :key="i" :class="{ strong: part.strong }">{{ part.text }}</span>
        </div>
      </div>

      <!-- Cell 3: Multi-platform users -->
      <div class="secondary-cell">
        <div class="secondary-label">Multi-platform users</div>
        <div class="secondary-value primary">
          {{ data.multi_platform_users_percent }}<span class="unit">%</span>
        </div>
        <div class="secondary-meta">connect <strong>2+ platforms</strong></div>
      </div>

      <!-- Cell 4: Achievement velocity -->
      <div class="secondary-cell">
        <div class="secondary-label">Achievement velocity</div>
        <div class="secondary-value accent">
          {{ formatVelocity(data.achievement_velocity.value) }}<span class="unit">/day</span>
        </div>
        <div class="secondary-meta">{{ data.achievement_velocity.label }}</div>
      </div>
    </div>
  </section>
</template>

<script>
import { fetchSecondaryMetrics } from '../../../services/brandAnalytics.js';

export default {
  name: 'SecondaryMetricsStrip',
  data() {
    return {
      data: null,
      loading: true,
      error: null,
    };
  },
  computed: {
    topCrossHall() {
      return this.data?.cross_hall_overlap?.[0] || null;
    },
    restCrossHall() {
      return this.data?.cross_hall_overlap?.slice(1, 3) || [];
    },
    crossHallMetaParts() {
      if (!this.topCrossHall) {
        return [{ text: 'No cross-brand overlap yet' }];
      }
      if (this.restCrossHall.length === 0) {
        return [{ text: '—' }];
      }
      const parts = [{ text: 'also: ' }];
      this.restCrossHall.forEach((item, i) => {
        parts.push({ text: `@${item.brand_username} ` });
        parts.push({ text: `${item.overlap_percent}%`, strong: true });
        if (i < this.restCrossHall.length - 1) {
          parts.push({ text: ' · ' });
        }
      });
      return parts;
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
        const res = await fetchSecondaryMetrics();
        this.data = res.data;
      } catch (e) {
        console.error('[SecondaryMetricsStrip] load failed', e);
        this.error = e;
      } finally {
        this.loading = false;
      }
    },
    formatVelocity(v) {
      if (v == null) return '0';
      return Number.isInteger(v) ? v.toString() : v.toString();
    },
  },
};
</script>

<style scoped>
.secondary-metrics-strip {
  display: block;
}

/* Section label — same pattern as PerformanceOverview */
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

/* Strip — single bordered box, 4 cells share borders */
.secondary-strip {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  border: 1px solid var(--line-strong, rgba(254, 237, 223, 0.15));
  background: var(--bg-elev, #0a0b0f);
  overflow: hidden;
}

@media (max-width: 1024px) {
  .secondary-strip {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .secondary-strip {
    grid-template-columns: 1fr;
  }
}

.secondary-cell {
  padding: 14px 18px;
  border-right: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  display: flex;
  flex-direction: column;
  gap: 4px;
  position: relative;
}
.secondary-cell:last-child {
  border-right: none;
}

@media (max-width: 1024px) {
  /* In 2-col grid, only odd-positioned cells get border-right; even are end-of-row. */
  .secondary-cell:nth-child(2n) {
    border-right: none;
  }
  /* Add bottom border for top row */
  .secondary-cell:nth-child(-n+2) {
    border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  }
}

@media (max-width: 600px) {
  .secondary-cell {
    border-right: none;
    border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  }
  .secondary-cell:last-child {
    border-bottom: none;
  }
}

/* Hover indicator: vertical primary line on left side */
.secondary-cell::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 2px;
  height: 100%;
  background: var(--primary, #ff6100);
  opacity: 0;
  transition: opacity 200ms;
}
.secondary-cell:hover::before {
  opacity: 1;
}

.secondary-label {
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 2px;
  text-transform: uppercase;
}

.secondary-value {
  font-family: 'VT323', monospace;
  font-size: 24px;
  color: var(--text, #feeddf);
  letter-spacing: 1px;
  line-height: 1.1;
}
.secondary-value.accent {
  color: var(--accent, #c1f527);
}
.secondary-value.primary {
  color: var(--primary, #ff6100);
}
.secondary-value .unit {
  font-size: 14px;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  margin-left: 4px;
}

.secondary-meta {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 1px;
}
.secondary-meta strong,
.secondary-meta .strong {
  color: var(--accent, #c1f527);
  font-weight: normal;
}

/* Loading / error — same pattern as PerformanceOverview */
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

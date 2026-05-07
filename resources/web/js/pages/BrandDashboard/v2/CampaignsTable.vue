<template>
  <section class="campaigns-section">
    <div class="section-label">
      Campaigns
      <router-link to="/brand-dashboard" class="btn-create">+ NEW</router-link>
    </div>

    <div v-if="loading" class="state-msg">Loading campaigns…</div>

    <div v-else-if="error" class="state-msg state-error">
      <span>Failed to load campaigns.</span>
      <button class="retry-btn" @click="load(false)">Retry</button>
    </div>

    <div v-else-if="data" class="campaigns-table" :class="{ refetching }">
      <table>
        <thead>
          <tr>
            <th>Campaign</th>
            <th>Status</th>
            <th
              :class="['sortable', { active: currentSort === 'pursuers' }]"
              @click="onSortClick('pursuers')"
            >
              Pursuing<span v-if="currentSort === 'pursuers'" class="sort-arrow">↓</span>
            </th>
            <th
              :class="['sortable', { active: currentSort === 'forges' }]"
              @click="onSortClick('forges')"
            >
              Forged<span v-if="currentSort === 'forges'" class="sort-arrow">↓</span>
            </th>
            <th
              :class="['sortable', { active: currentSort === 'conversion' }]"
              @click="onSortClick('conversion')"
            >
              Conv<span v-if="currentSort === 'conversion'" class="sort-arrow">↓</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!data.data.length">
            <td colspan="5" class="empty-row">No campaigns yet.</td>
          </tr>
          <tr v-for="row in data.data" :key="row.trophy_id">
            <td>
              <div class="camp-name-row">
                <div class="camp-icon">{{ thumbInitial(row.name) }}</div>
                <div>
                  <div class="camp-name">{{ row.name }}</div>
                  <div class="camp-meta">Created {{ formatRelativeTime(row.created_at) }}</div>
                </div>
              </div>
            </td>
            <td><span :class="['status-pill', row.status]">{{ row.status }}</span></td>
            <td><span class="metric-num" :class="{ accent: row.pursuers > 0 }">{{ row.pursuers }}</span></td>
            <td><span class="metric-num" :class="{ accent: row.forges > 0 }">{{ row.forges }}</span></td>
            <td>
              <span v-if="row.pursuers === 0" class="conv-rate dim">—</span>
              <span v-else :class="['conv-rate', convClass(row.conversion_percent)]">{{ formatConv(row.conversion_percent) }}%</span>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="data.meta && data.meta.total > data.data.length" class="table-footer">
        Showing {{ data.data.length }} of {{ data.meta.total }}
      </div>
    </div>
  </section>
</template>

<script>
import { fetchCampaigns } from '../../../services/brandAnalytics.js';

export default {
  name: 'CampaignsTable',
  data() {
    return {
      data: null,
      loading: true,
      refetching: false,
      error: null,
      currentSort: 'created_at',
    };
  },
  mounted() {
    this.load(false);
  },
  methods: {
    async load(isRefetch = false) {
      if (isRefetch) {
        this.refetching = true;
      } else {
        this.loading = true;
      }
      this.error = null;
      try {
        const res = await fetchCampaigns(this.currentSort);
        this.data = res.data;
      } catch (e) {
        console.error('[CampaignsTable] load failed', e);
        this.error = e;
      } finally {
        this.loading = false;
        this.refetching = false;
      }
    },
    onSortClick(column) {
      if (column === this.currentSort) return;
      this.currentSort = column;
      this.load(true);
    },
    thumbInitial(name) {
      if (!name) return '?';
      const firstAlpha = name.match(/[a-zA-Z]/);
      return firstAlpha ? firstAlpha[0].toUpperCase() : '?';
    },
    convClass(pct) {
      if (pct > 50) return 'good';
      if (pct >= 20) return 'mid';
      return 'low';
    },
    formatConv(pct) {
      return Number.isInteger(pct) ? pct.toString() : pct.toFixed(2).replace(/\.?0+$/, '');
    },
    formatRelativeTime(iso) {
      if (!iso) return '—';
      const ts = new Date(iso).getTime();
      if (Number.isNaN(ts)) return '—';
      const diffSec = (Date.now() - ts) / 1000;
      if (diffSec < 60) return 'just now';
      if (diffSec < 3600) return `${Math.floor(diffSec / 60)}m ago`;
      if (diffSec < 86400) return `${Math.floor(diffSec / 3600)}h ago`;
      if (diffSec < 86400 * 30) return `${Math.floor(diffSec / 86400)}d ago`;
      return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    },
  },
};
</script>

<style scoped>
.campaigns-section {
  display: flex;
  flex-direction: column;
}

/* Section label with + NEW button on the right */
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
.btn-create {
  margin-left: auto;
  background: var(--primary, #ff6100);
  color: var(--bg, #000003);
  border: none;
  padding: 6px 12px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  letter-spacing: 1.5px;
  text-decoration: none;
  cursor: pointer;
  transition: background 150ms;
}
.btn-create:hover {
  background: #ff7a25;
}

/* Table container */
.campaigns-table {
  border: 1px solid var(--line-strong, rgba(254, 237, 223, 0.15));
  background: var(--bg-elev, #0a0b0f);
  overflow: hidden;
  transition: opacity 200ms;
}
.campaigns-table.refetching {
  opacity: 0.5;
  pointer-events: none;
}

.campaigns-table table {
  width: 100%;
  border-collapse: collapse;
}
.campaigns-table th {
  text-align: left;
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 2px;
  text-transform: uppercase;
  padding: 10px 12px;
  border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  background: var(--bg, #000003);
  font-weight: normal;
  user-select: none;
}
.campaigns-table th.sortable {
  cursor: pointer;
}
.campaigns-table th.sortable:hover {
  color: var(--text, #feeddf);
}
.campaigns-table th.sortable.active {
  color: var(--accent, #c1f527);
}
.sort-arrow {
  margin-left: 4px;
  font-size: 10px;
}
.campaigns-table td {
  padding: 12px;
  border-bottom: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}
.campaigns-table tr:last-child td {
  border-bottom: none;
}
.campaigns-table tr:hover td {
  background: var(--primary-faint, rgba(255, 97, 0, 0.12));
  cursor: pointer;
}
.empty-row {
  text-align: center;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 1px;
  padding: 32px 12px;
}

/* Campaign name row */
.camp-name-row {
  display: flex;
  align-items: center;
  gap: 10px;
}
.camp-icon {
  width: 28px;
  height: 28px;
  border: 1px solid var(--primary-line, rgba(255, 97, 0, 0.25));
  background: var(--bg, #000003);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'VT323', monospace;
  font-size: 16px;
  color: var(--primary, #ff6100);
  flex-shrink: 0;
}
.camp-name {
  font-family: 'Share Tech Mono', monospace;
  color: var(--text, #feeddf);
  font-size: 12px;
}
.camp-meta {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  margin-top: 2px;
  letter-spacing: 0.5px;
}

/* Status pills */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 6px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  border: 1px solid;
}
.status-pill.active {
  color: var(--accent, #c1f527);
  border-color: var(--accent, #c1f527);
}
.status-pill.active::before {
  content: '●';
  color: var(--accent, #c1f527);
  font-size: 8px;
}
.status-pill.draft {
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  border-color: var(--line-strong, rgba(254, 237, 223, 0.15));
}
.status-pill.draft::before {
  content: '○';
  font-size: 8px;
}
.status-pill.paused {
  color: var(--warn, #ffb800);
  border-color: var(--warn, #ffb800);
}
.status-pill.paused::before {
  content: '⏸';
  font-size: 7px;
}

/* Metric numbers */
.metric-num {
  font-family: 'VT323', monospace;
  font-size: 16px;
  color: var(--text, #feeddf);
  letter-spacing: 1px;
}
.metric-num.accent {
  color: var(--accent, #c1f527);
}

/* Conversion rate */
.conv-rate {
  font-family: 'VT323', monospace;
  font-size: 16px;
  letter-spacing: 1px;
}
.conv-rate.good {
  color: var(--accent, #c1f527);
}
.conv-rate.mid {
  color: var(--warn, #ffb800);
}
.conv-rate.low {
  color: var(--danger, #ff3860);
}
.conv-rate.dim {
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
}

/* Footer */
.table-footer {
  padding: 10px 12px;
  border-top: 1px solid var(--line, rgba(254, 237, 223, 0.08));
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  letter-spacing: 1px;
  text-align: center;
  background: var(--bg, #000003);
}

/* Loading / error — same pattern as previous components */
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

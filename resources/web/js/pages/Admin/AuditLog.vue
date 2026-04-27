<template>
  <div class="audit-log">
    <div class="al-toolbar">
      <div class="al-toolbar-left">
        <h2 class="al-section-title">Audit log</h2>
        <p class="al-section-meta">{{ statusLabel }}</p>
      </div>
    </div>

    <div class="al-filter-pills">
      <button
        v-for="f in filters"
        :key="f.key"
        class="al-pill"
        :class="{ 'al-pill--active': activeFilter === f.key }"
        type="button"
        @click="setFilter(f.key)"
      >
        {{ f.label }}
      </button>
    </div>

    <div v-if="error" class="al-error">{{ error }}</div>

    <div v-if="loading && !entries.length" class="al-state">Loading audit log…</div>
    <div v-else-if="!entries.length" class="al-state al-state-hint">
      <p v-if="activeFilter !== 'all'">No <code>{{ activeFilter }}</code> events yet.</p>
      <p v-else>No activity recorded yet.</p>
    </div>

    <div v-else class="al-list">
      <AuditLogRow v-for="entry in entries" :key="entry.id" :entry="entry" />
    </div>

    <div v-if="entries.length" class="al-pagination">
      <button
        v-if="nextCursor"
        class="al-load-more"
        type="button"
        :disabled="loadingMore"
        @click="loadMore"
      >
        {{ loadingMore ? "Loading…" : "Load more" }}
      </button>
      <span v-else class="al-end-marker">— end of log —</span>
    </div>
  </div>
</template>

<script>
import api from "../../api/api.js";
import AuditLogRow from "./components/AuditLogRow.vue";

const FILTERS = [
  { key: "all",        label: "All" },
  { key: "role",       label: "Role" },
  { key: "user",       label: "User" },
  { key: "trophy",     label: "Trophy" },
  { key: "badge",      label: "Badge" },
  { key: "badge_rule", label: "Badge rule" },
  { key: "poll",       label: "Poll" },
  { key: "event",      label: "Event" },
  { key: "guild",      label: "Guild" },
  { key: "chest",      label: "Chest" },
  { key: "key",        label: "Key" },
];

export default {
  name: "AuditLog",
  components: { AuditLogRow },
  data() {
    return {
      entries: [],
      nextCursor: null,
      perPage: 25,

      loading: false,
      loadingMore: false,
      error: "",

      filters: FILTERS,
    };
  },
  computed: {
    activeFilter() {
      return this.$route.query.log_name || "all";
    },
    statusLabel() {
      if (this.loading && !this.entries.length) return "loading…";
      const count = this.entries.length;
      if (!count) return "no entries";
      const more = this.nextCursor ? "+ more" : "complete";
      return `${count} entries shown · ${more}`;
    },
  },
  watch: {
    "$route.query.log_name"() {
      this.reloadFromScratch();
    },
  },
  mounted() {
    this.reloadFromScratch();
  },
  methods: {
    async reloadFromScratch() {
      this.entries = [];
      this.nextCursor = null;
      await this.loadPage();
    },

    async loadPage(append = false) {
      if (append) {
        this.loadingMore = true;
      } else {
        this.loading = true;
      }
      this.error = "";

      const params = { per_page: this.perPage };
      if (this.activeFilter && this.activeFilter !== "all") {
        params.log_name = this.activeFilter;
      }
      if (append && this.nextCursor) {
        params.cursor = this.nextCursor;
      }

      try {
        const { data } = await api.get("/api/admin/audit-log", { params });
        const items = data?.data ?? [];
        this.entries = append ? [...this.entries, ...items] : items;
        this.nextCursor = data?.meta?.next_cursor ?? null;
      } catch (e) {
        this.error = e.response?.data?.message || "Failed to load audit log.";
        if (!append) this.entries = [];
      } finally {
        this.loading = false;
        this.loadingMore = false;
      }
    },

    loadMore() {
      if (this.loadingMore || !this.nextCursor) return;
      this.loadPage(true);
    },

    setFilter(key) {
      const query = { ...this.$route.query };
      if (key === "all") {
        delete query.log_name;
      } else {
        query.log_name = key;
      }
      this.$router.push({ name: "admin.audit", query });
    },
  },
};
</script>

<style>
/* All selectors prefixed with .audit-log so styles never leak. */
.audit-log {
  --al-bg: #000003;
  --al-surface: #0e0f11;
  --al-surface-2: #1a1c1f;
  --al-border: #2a2c2e;
  --al-text: #feeddf;
  --al-text-muted: #9a9590;
  --al-text-dim: #5a5550;
  --al-primary: #ff6100;
  --al-primary-glow: rgba(255, 97, 0, 0.45);
  --al-accent: #c1f527;
  --al-accent-glow: rgba(193, 245, 39, 0.35);
  --al-danger: #e11b2b;
  --al-mono: 'Share Tech Mono', monospace;
  --al-display: 'VT323', monospace;
  font-family: var(--al-mono);
  color: var(--al-text);
  font-size: 13px;
}

.audit-log .al-toolbar {
  display: flex; align-items: flex-end; justify-content: space-between;
  gap: 16px; flex-wrap: wrap;
  margin-bottom: 18px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--al-border);
}
.audit-log .al-toolbar-left { display: flex; flex-direction: column; gap: 4px; }
.audit-log .al-section-title {
  font-family: var(--al-display);
  font-size: 28px; line-height: 1; letter-spacing: 0.015em;
  color: var(--al-text); margin: 0;
}
.audit-log .al-section-meta {
  font-size: 10px; color: var(--al-text-dim);
  letter-spacing: 0.18em; text-transform: uppercase; margin: 0;
}

.audit-log .al-filter-pills {
  display: flex; gap: 8px; flex-wrap: wrap;
  margin-bottom: 24px;
}
.audit-log .al-pill {
  padding: 7px 14px;
  font-family: var(--al-mono); font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  background: transparent;
  color: var(--al-text-muted);
  border: 1px solid var(--al-border);
  cursor: pointer; transition: all 0.15s;
}
.audit-log .al-pill:hover { color: var(--al-text); border-color: var(--al-text-dim); }
.audit-log .al-pill--active {
  background: var(--al-primary); color: var(--al-bg); border-color: var(--al-primary);
  box-shadow: 0 0 12px var(--al-primary-glow);
}

.audit-log .al-error {
  padding: 12px 16px;
  background: rgba(225, 27, 43, 0.08);
  border: 1px solid rgba(225, 27, 43, 0.4);
  color: var(--al-danger);
  margin-bottom: 16px;
  font-size: 12px;
}

.audit-log .al-state {
  padding: 60px 20px; text-align: center;
  color: var(--al-text-muted); font-size: 13px;
  border: 1px dashed var(--al-border);
}
.audit-log .al-state-hint code {
  background: var(--al-surface-2); padding: 1px 6px;
  font-family: var(--al-mono); color: var(--al-accent);
}

.audit-log .al-list {
  display: flex; flex-direction: column;
  border: 1px solid var(--al-border);
  background: var(--al-surface);
}

/* Row */
.audit-log .al-row { border-bottom: 1px solid var(--al-border); }
.audit-log .al-row:last-child { border-bottom: none; }
.audit-log .al-row-head {
  display: grid;
  grid-template-columns: 100px 96px 1fr 32px;
  align-items: center; gap: 14px;
  padding: 12px 16px;
  cursor: pointer;
  transition: background 0.15s;
}
.audit-log .al-row-head:hover { background: rgba(255, 97, 0, 0.04); }
.audit-log .al-row--expanded .al-row-head { background: rgba(255, 97, 0, 0.06); }
.audit-log .al-row-time {
  display: flex; flex-direction: column; gap: 2px;
  font-family: var(--al-mono);
}
.audit-log .al-row-time-rel { font-size: 11px; color: var(--al-text); }
.audit-log .al-row-time-abs { font-size: 9px; color: var(--al-text-dim); letter-spacing: 0.06em; }
.audit-log .al-row-tag {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 4px 10px;
  font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase;
  border-radius: 3px;
  border: 1px solid var(--al-border);
  background: rgba(42, 44, 46, 0.5);
  color: var(--al-text-muted);
  white-space: nowrap;
}
.audit-log .al-row-tag--role       { color: var(--al-accent);  border-color: rgba(193, 245, 39, 0.35); background: rgba(193, 245, 39, 0.08); }
.audit-log .al-row-tag--user       { color: var(--al-text);    border-color: rgba(254, 237, 223, 0.3); background: rgba(254, 237, 223, 0.05); }
.audit-log .al-row-tag--trophy     { color: var(--al-primary); border-color: rgba(255, 97, 0, 0.35);  background: rgba(255, 97, 0, 0.08); }
.audit-log .al-row-tag--badge      { color: #66c0f4;            border-color: rgba(102, 192, 244, 0.35); background: rgba(102, 192, 244, 0.08); }
.audit-log .al-row-tag--badge_rule { color: #66c0f4;            border-color: rgba(102, 192, 244, 0.25); background: rgba(102, 192, 244, 0.05); }
.audit-log .al-row-tag--poll       { color: #d4500c;            border-color: rgba(212, 80, 12, 0.35);  background: rgba(212, 80, 12, 0.08); }
.audit-log .al-row-tag--event      { color: #f5c547;            border-color: rgba(245, 197, 71, 0.35); background: rgba(245, 197, 71, 0.08); }
.audit-log .al-row-tag--guild      { color: #a855f7;            border-color: rgba(168, 85, 247, 0.35); background: rgba(168, 85, 247, 0.08); }
.audit-log .al-row-tag--chest      { color: var(--al-primary); border-color: rgba(255, 97, 0, 0.25);  background: rgba(255, 97, 0, 0.05); }
.audit-log .al-row-tag--key        { color: var(--al-accent);  border-color: rgba(193, 245, 39, 0.25); background: rgba(193, 245, 39, 0.05); }
.audit-log .al-row-summary {
  display: flex; gap: 8px; align-items: baseline;
  font-size: 12px; min-width: 0;
  flex-wrap: wrap;
}
.audit-log .al-row-causer {
  color: var(--al-text); letter-spacing: 0.04em;
  font-weight: 400;
}
.audit-log .al-row-action {
  color: var(--al-text-muted); letter-spacing: 0.02em;
}
.audit-log .al-row-subject {
  color: var(--al-primary); letter-spacing: 0.04em;
  text-shadow: 0 0 6px var(--al-primary-glow);
}
.audit-log .al-row-expand {
  width: 24px; height: 24px;
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--al-text-muted);
  background: transparent; border: 1px solid var(--al-border);
  cursor: pointer; transition: all 0.15s;
  font-size: 14px;
}
.audit-log .al-row-expand:hover { color: var(--al-text); border-color: var(--al-text-dim); }

.audit-log .al-row-detail {
  padding: 16px 16px 20px;
  border-top: 1px solid rgba(42, 44, 46, 0.5);
  background: rgba(0, 0, 0, 0.25);
}
.audit-log .al-detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
  margin-bottom: 14px;
}
.audit-log .al-detail-cell { display: flex; flex-direction: column; gap: 4px; }
.audit-log .al-detail-label {
  font-size: 9px; letter-spacing: 0.22em; text-transform: uppercase;
  color: var(--al-text-dim);
}
.audit-log .al-detail-value {
  font-size: 12px; color: var(--al-text); letter-spacing: 0.04em;
  word-break: break-word;
}
.audit-log .al-detail-properties { display: flex; flex-direction: column; gap: 6px; }
.audit-log .al-detail-pre {
  background: var(--al-bg);
  border: 1px solid var(--al-border);
  padding: 12px 14px;
  font-family: var(--al-mono);
  font-size: 11px;
  color: var(--al-text-muted);
  overflow-x: auto;
  white-space: pre-wrap;
  word-break: break-word;
  margin: 0;
}

/* Pagination */
.audit-log .al-pagination {
  margin-top: 20px;
  display: flex; justify-content: center;
}
.audit-log .al-load-more {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 10px 24px;
  font-family: var(--al-mono); font-size: 11px;
  letter-spacing: 0.2em; text-transform: uppercase;
  background: transparent; color: var(--al-text-muted);
  border: 1px solid var(--al-border);
  cursor: pointer; transition: all 0.15s;
}
.audit-log .al-load-more:hover:not(:disabled) {
  color: var(--al-text); border-color: var(--al-text-dim);
}
.audit-log .al-load-more:disabled { opacity: 0.5; cursor: default; }
.audit-log .al-end-marker {
  font-size: 10px; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--al-text-dim);
}

@media (max-width: 720px) {
  .audit-log .al-row-head {
    grid-template-columns: 80px 80px 1fr 28px;
    gap: 10px;
    padding: 10px 12px;
  }
  .audit-log .al-row-time-abs { display: none; }
}
</style>

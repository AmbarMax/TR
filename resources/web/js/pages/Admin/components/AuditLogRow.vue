<template>
  <div class="al-row" :class="{ 'al-row--expanded': expanded }">
    <div class="al-row-head" @click="toggle">
      <div class="al-row-time">
        <div class="al-row-time-rel">{{ relativeTime }}</div>
        <div class="al-row-time-abs">{{ absoluteTime }}</div>
      </div>
      <span class="al-row-tag" :class="`al-row-tag--${entry.log_name}`">{{ entry.log_name }}</span>
      <div class="al-row-summary">
        <span class="al-row-causer">
          <template v-if="entry.causer && entry.causer.username">@{{ entry.causer.username }}</template>
          <template v-else>system</template>
        </span>
        <span class="al-row-action">{{ entry.description || entry.event || 'event' }}</span>
        <span v-if="entry.subject && entry.subject.label" class="al-row-subject">{{ entry.subject.label }}</span>
      </div>
      <button class="al-row-expand" type="button" :aria-expanded="expanded">
        {{ expanded ? '−' : '+' }}
      </button>
    </div>

    <div v-if="expanded" class="al-row-detail">
      <div class="al-detail-grid">
        <div class="al-detail-cell">
          <div class="al-detail-label">log_name</div>
          <div class="al-detail-value">{{ entry.log_name }}</div>
        </div>
        <div class="al-detail-cell">
          <div class="al-detail-label">event</div>
          <div class="al-detail-value">{{ entry.event || '—' }}</div>
        </div>
        <div class="al-detail-cell">
          <div class="al-detail-label">causer</div>
          <div class="al-detail-value">{{ causerText }}</div>
        </div>
        <div class="al-detail-cell">
          <div class="al-detail-label">subject</div>
          <div class="al-detail-value">{{ subjectText }}</div>
        </div>
      </div>
      <div v-if="hasProperties" class="al-detail-properties">
        <div class="al-detail-label">properties</div>
        <pre class="al-detail-pre">{{ formattedProperties }}</pre>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AuditLogRow",
  props: {
    entry: { type: Object, required: true },
  },
  data() {
    return { expanded: false };
  },
  computed: {
    relativeTime() {
      const ts = this.entry.created_at;
      if (!ts) return "—";
      const diff = Date.now() - new Date(ts).getTime();
      if (diff < 60000) return "just now";
      const min = Math.floor(diff / 60000);
      if (min < 60) return `${min}m ago`;
      const hrs = Math.floor(min / 60);
      if (hrs < 24) return `${hrs}h ago`;
      const days = Math.floor(hrs / 24);
      if (days < 7) return `${days}d ago`;
      return new Date(ts).toLocaleDateString();
    },
    absoluteTime() {
      const ts = this.entry.created_at;
      if (!ts) return "";
      try {
        return new Date(ts).toLocaleString("en-US", {
          month: "short", day: "numeric", hour: "2-digit", minute: "2-digit",
        });
      } catch (e) { return ""; }
    },
    causerText() {
      const c = this.entry.causer;
      if (!c) return "system";
      const handle = c.username ? `@${c.username}` : "?";
      const name = c.name ? ` (${c.name})` : "";
      return `${handle}${name}`;
    },
    subjectText() {
      const s = this.entry.subject;
      if (!s) return "—";
      return `${s.type || "?"}#${s.id || "?"} · ${s.label || "—"}`;
    },
    hasProperties() {
      return this.entry.properties && Object.keys(this.entry.properties).length > 0;
    },
    formattedProperties() {
      try {
        return JSON.stringify(this.entry.properties, null, 2);
      } catch (e) { return String(this.entry.properties); }
    },
  },
  methods: {
    toggle() { this.expanded = !this.expanded; },
  },
};
</script>

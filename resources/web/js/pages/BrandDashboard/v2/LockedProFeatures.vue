<template>
  <section class="locked-pro">
    <div class="section-label locked-label">
      Pro features
      <span class="section-meta">unlock with billing</span>
    </div>
    <div class="locked-section">
      <div class="lock-title">◈ OPTIMIZE FOR ROI</div>
      <div class="lock-desc">
        Activate paid campaigns to unlock real-time cost-per-trophy
        tracking, audience targeting by platform &amp; affinity keywords,
        AI-suggested badge configurations, cross-brand benchmarking
        against your sector, and scheduled campaign launches.
      </div>
      <a href="mailto:hello@trophyroom.gg" class="lock-cta">→ Contact us to activate</a>
      <div class="lock-features">
        <div class="lock-feat">Real CPT tracking</div>
        <div class="lock-feat">Audience targeting</div>
        <div class="lock-feat">AI campaign builder</div>
        <div class="lock-feat">Sector benchmarking</div>
        <div class="lock-feat">Scheduled launches</div>
        <div class="lock-feat">Custom reports</div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'LockedProFeatures',
};
</script>

<style scoped>
.locked-pro {
  display: block;
}

/* Section label override: warn color instead of primary.
   Keeps the ◆ ::before bullet accent green (consistent with the
   other section labels). */
.section-label {
  display: flex;
  align-items: center;
  gap: 12px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  letter-spacing: 3px;
  text-transform: uppercase;
  margin: 0 0 16px 0;
  color: var(--primary, #ff6100);
}
.section-label::before {
  content: '◆';
  color: var(--accent, #c1f527);
}
.section-label.locked-label {
  color: var(--warn, #ffb800);
}
.section-label.locked-label .section-meta {
  color: var(--warn, #ffb800);
  margin-left: auto;
  letter-spacing: 1px;
  font-size: 10px;
}

/* Locked section card */
.locked-section {
  border: 1px dashed var(--line-strong, rgba(254, 237, 223, 0.15));
  padding: 32px;
  text-align: center;
  margin-top: 8px;
  position: relative;
  background: linear-gradient(135deg, var(--bg-elev, #0a0b0f) 0%, rgba(255, 184, 0, 0.02) 100%);
}

.lock-title {
  font-family: 'VT323', monospace;
  font-size: 32px;
  color: var(--warn, #ffb800);
  margin-bottom: 8px;
  letter-spacing: 2px;
}

.lock-desc {
  font-family: 'Share Tech Mono', monospace;
  color: var(--text-dim, rgba(254, 237, 223, 0.6));
  font-size: 12px;
  letter-spacing: 1px;
  max-width: 540px;
  margin: 0 auto 16px;
  line-height: 1.7;
}

.lock-cta {
  display: inline-block;
  padding: 8px 20px;
  border: 1px solid var(--warn, #ffb800);
  color: var(--warn, #ffb800);
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  letter-spacing: 2px;
  text-transform: uppercase;
  text-decoration: none;
  cursor: pointer;
  transition: background 150ms, color 150ms;
}
.lock-cta:hover {
  background: var(--warn, #ffb800);
  color: var(--bg, #000003);
}

.lock-features {
  display: flex;
  justify-content: center;
  gap: 24px;
  margin-top: 20px;
  flex-wrap: wrap;
}
.lock-feat {
  font-family: 'Share Tech Mono', monospace;
  color: var(--text-faint, rgba(254, 237, 223, 0.35));
  font-size: 11px;
  letter-spacing: 1px;
}
.lock-feat::before {
  content: '◇';
  color: var(--warn, #ffb800);
  margin-right: 6px;
}
</style>

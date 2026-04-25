<template>
  <div class="about-grid">
    <div class="about-block">
      <div class="about-label">Issuer</div>
      <div class="about-value">
        {{ user.name || user.username }}
        <template v-if="isVerified"> · Verified</template>
      </div>
    </div>

    <div class="about-block">
      <div class="about-label">Account</div>
      <div class="about-value">Brand · @{{ user.username }}</div>
    </div>

    <div class="about-block">
      <div class="about-label">Active items</div>
      <div class="about-value">
        {{ stats.active_now ?? 0 }} live · {{ stats.issued_total ?? 0 }} issued total
      </div>
    </div>

    <div v-if="hasLinks" class="about-block links">
      <div class="about-label">Links</div>
      <div class="about-links">
        <a v-if="social.website" :href="social.website" target="_blank" rel="noopener" class="about-link">Website ↗</a>
        <a v-if="social.discord" :href="discordHref" target="_blank" rel="noopener" class="about-link">Discord ↗</a>
        <a v-if="social.twitter" :href="twitterHref" target="_blank" rel="noopener" class="about-link">Twitter ↗</a>
        <a v-if="social.twitch" :href="twitchHref" target="_blank" rel="noopener" class="about-link">Twitch ↗</a>
        <a v-if="social.youtube" :href="social.youtube" target="_blank" rel="noopener" class="about-link">YouTube ↗</a>
        <a v-if="social.instagram" :href="instagramHref" target="_blank" rel="noopener" class="about-link">Instagram ↗</a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "AboutHall",
  props: {
    user: { type: Object, required: true },
  },
  computed: {
    stats() {
      return this.user.stats || {};
    },
    social() {
      return this.user.social || {};
    },
    isVerified() {
      return !!this.user.verified_at;
    },
    hasLinks() {
      const s = this.social;
      return !!(s.website || s.discord || s.twitter || s.twitch || s.youtube || s.instagram);
    },
    discordHref() {
      const v = this.social.discord;
      if (!v) return "#";
      return v.startsWith("http") ? v : `https://discord.com/users/${v}`;
    },
    twitterHref() {
      const v = this.social.twitter;
      if (!v) return "#";
      return v.startsWith("http") ? v : `https://twitter.com/${v.replace(/^@/, "")}`;
    },
    twitchHref() {
      const v = this.social.twitch;
      if (!v) return "#";
      return v.startsWith("http") ? v : `https://twitch.tv/${v}`;
    },
    instagramHref() {
      const v = this.social.instagram;
      if (!v) return "#";
      return v.startsWith("http") ? v : `https://instagram.com/${v.replace(/^@/, "")}`;
    },
  },
};
</script>

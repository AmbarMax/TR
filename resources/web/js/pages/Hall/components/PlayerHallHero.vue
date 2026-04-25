<template>
  <section class="hero">
    <div class="hero-banner">
      <div class="hero-banner-img" :style="bannerStyle"></div>
      <div class="hero-banner-asset-slot"></div>
    </div>

    <div class="hero-content">
      <div class="hero-left">
        <div class="hero-tag">Public profile · trophyroom.gg/{{ user.username }}</div>

        <div class="identity">
          <div class="avatar-ring">
            <img v-if="hasCustomAvatar" :src="user.avatar" :alt="user.username" class="avatar-img avatar-photo" />
            <div v-else class="avatar-img">{{ initials }}</div>
          </div>

          <div class="identity-meta">
            <h1 class="hero-name">{{ user.username }}</h1>
            <div class="hero-handle">
              <span v-if="user.name">{{ user.name }}</span>
              <template v-if="user.name && connectedPlatforms.length">
                <span class="hero-handle-sep" aria-hidden="true">·</span>
              </template>
              <div v-if="connectedPlatforms.length" class="platforms">
                <span
                  v-for="p in connectedPlatforms"
                  :key="p.key"
                  class="plat-icon"
                  :title="`${p.name} connected`"
                  v-html="p.icon"
                ></span>
              </div>
            </div>
            <p v-if="user.tagline" class="hero-tagline">{{ user.tagline }}</p>
          </div>
        </div>

        <div class="hero-stats">
          <div class="stat-cell highlight">
            <div class="stat-cell-num">{{ stats.badges ?? 0 }}</div>
            <div class="stat-cell-lbl">Badges</div>
          </div>
          <div class="stat-cell">
            <div class="stat-cell-num">{{ stats.trophies ?? 0 }}</div>
            <div class="stat-cell-lbl">Trophies</div>
          </div>
          <div class="stat-cell">
            <div class="stat-cell-num">{{ stats.achievements ?? 0 }}</div>
            <div class="stat-cell-lbl">Achievements</div>
          </div>
          <div class="stat-cell">
            <div class="stat-cell-num">{{ stats.platforms_count ?? 0 }}</div>
            <div class="stat-cell-lbl">Platforms</div>
          </div>
        </div>
      </div>

      <slot name="showcase" />
    </div>

    <div class="scroll-cue">
      <span>Explore</span>
      <div class="scroll-cue-line"></div>
    </div>
  </section>
</template>

<script>
export default {
  name: "PlayerHallHero",
  props: {
    user: { type: Object, required: true },
    connectedPlatforms: { type: Array, default: () => [] },
  },
  computed: {
    stats() { return this.user.stats || {}; },
    initials() {
      const src = (this.user.username || "?").trim();
      return src.slice(0, 2).toUpperCase();
    },
    hasCustomAvatar() {
      return this.user.avatar && !this.user.avatar.includes("default-profile-img");
    },
    bannerStyle() {
      const bg = this.user.banner;
      if (bg && !bg.includes("default-background-img")) {
        return { backgroundImage: `url(${bg})`, backgroundSize: "cover", backgroundPosition: "center" };
      }
      return {};
    },
  },
};
</script>

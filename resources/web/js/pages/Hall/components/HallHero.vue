<template>
  <section class="hero">
    <div class="hero-banner">
      <div
        class="hero-banner-img"
        :style="bannerStyle"
      ></div>
      <div class="hero-banner-asset-slot"></div>
    </div>

    <div class="hero-content">
      <div class="hero-left">
        <div class="hero-tag">
          <template v-if="isVerified">Verified Brand</template>
          <template v-else>Brand</template>
          · trophyroom.gg/{{ user.username }}
        </div>

        <div class="identity">
          <div class="brand-logo-wrap">
            <div class="brand-logo">
              <img
                v-if="hasCustomAvatar"
                :src="user.avatar"
                :alt="user.name"
                class="brand-logo-img"
              />
              <span v-else class="brand-logo-fallback">{{ logoLetter }}</span>
            </div>
            <span v-if="isVerified" class="verified-tick" title="Verified Brand">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </span>
          </div>

          <div class="identity-meta">
            <h1 class="hero-name">{{ user.name || user.username }}</h1>
            <div class="hero-handle">
              <span>@{{ user.username }}</span>
              <span v-if="isVerified">· Verified Brand</span>
            </div>
            <p v-if="user.tagline" class="hero-tagline">{{ user.tagline }}</p>
          </div>

          <div class="brand-actions">
            <button class="btn-follow" :class="{ following: isFollowing }" type="button" @click="$emit('toggle-follow')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 12V8a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v8a4 4 0 0 0 4 4h4"/>
                <line x1="16" y1="16" x2="22" y2="16"/>
                <line v-if="!isFollowing" x1="19" y1="13" x2="19" y2="19"/>
              </svg>
              <span>{{ isFollowing ? 'Following' : 'Follow Hall' }}</span>
            </button>
            <button class="btn-share" type="button" title="Share Hall" @click="$emit('share')">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8M16 6l-4-4-4 4M12 2v13"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="hero-stats brand-stats">
          <div class="stat-cell highlight">
            <div class="stat-cell-num">{{ stats.active_now ?? 0 }}</div>
            <div class="stat-cell-lbl">Active Now</div>
          </div>
          <div class="stat-cell">
            <div class="stat-cell-num">{{ stats.issued_total ?? 0 }}</div>
            <div class="stat-cell-lbl">Issued Total</div>
          </div>
          <div class="stat-cell">
            <div class="stat-cell-num">{{ formatBigNumber(stats.conquerors ?? 0) }}</div>
            <div class="stat-cell-lbl">Conquerors</div>
          </div>
          <div class="stat-cell">
            <div class="stat-cell-num">{{ formatBigNumber(stats.followers ?? 0) }}</div>
            <div class="stat-cell-lbl">Followers</div>
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
  name: "HallHero",
  props: {
    user: { type: Object, required: true },
    isFollowing: { type: Boolean, default: false },
  },
  emits: ["toggle-follow", "share"],
  computed: {
    stats() {
      return this.user.stats || {};
    },
    isVerified() {
      return !!this.user.verified_at;
    },
    hasCustomAvatar() {
      return this.user.avatar && !this.user.avatar.includes("default-profile-img");
    },
    logoLetter() {
      return (this.user.name || this.user.username || "?").trim().charAt(0).toUpperCase();
    },
    bannerStyle() {
      if (this.user.banner && !this.user.banner.includes("default-background-img")) {
        return { backgroundImage: `url(${this.user.banner})`, backgroundSize: "cover", backgroundPosition: "center" };
      }
      return {};
    },
  },
  methods: {
    formatBigNumber(n) {
      const num = Number(n) || 0;
      if (num >= 1000) {
        const k = num / 1000;
        return (k >= 10 ? Math.round(k) : k.toFixed(1)) + "k";
      }
      return String(num);
    },
  },
};
</script>

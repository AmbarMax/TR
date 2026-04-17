<template>
  <ambar-notification v-if="notificationModalOpen"></ambar-notification>

  <!-- User found -->
  <div v-if="user !== null" class="vh">

    <!-- Banner + Avatar + Info -->
    <div class="vh-banner">
      <div class="vh-banner__bg">
        <img v-if="user.background" :src="user.background" alt="" class="vh-banner__img" />
      </div>
      <button v-if="isLoggedIn" class="vh-banner__back" @click="$router.push('/trophy-room')">
        ← Trophy Room
      </button>
      <div class="vh-banner__url" @click="copyLink" title="Copy link">
        trophyroom.gg/{{ user.username }}
      </div>
    </div>

    <div class="vh-profile">
      <div class="vh-avatar">
        <img v-if="user.avatar" :src="user.avatar" alt="" />
        <img v-else src="../../../images/web/img/user.svg" alt="user" />
      </div>
      <div class="vh-profile__info">
        <div class="vh-profile__name-row">
          <h1 class="vh-profile__name">{{ user.username }}</h1>
          <div class="vh-profile__platforms">
            <img v-if="discord_badges.length" src="../../../images/web/img/icons/discord.svg" alt="Discord" class="vh-profile__platform-icon" title="Discord connected" />
            <img v-if="steam_badges.length" src="../../../images/web/img/icons/steam.svg" alt="Steam" class="vh-profile__platform-icon" title="Steam connected" />
            <img v-if="github_badges.length" src="../../../images/web/img/icons/github.svg" alt="GitHub" class="vh-profile__platform-icon" title="GitHub connected" />
          </div>
        </div>
        <p class="vh-profile__bio" v-if="user.description">{{ user.description }}</p>
      </div>
      <button class="vh-btn vh-btn--secondary vh-profile__copy" @click="copyLink">
        Copy Link
      </button>
    </div>

    <!-- Stats Bar -->
    <div class="vh-stats">
      <div class="vh-stat">
        <span class="vh-stat__number">{{ totalBadges }}</span>
        <span class="vh-stat__label">Badges</span>
      </div>
      <div class="vh-stat">
        <span class="vh-stat__number">{{ trophies.length }}</span>
        <span class="vh-stat__label">Trophies</span>
      </div>
      <div class="vh-stat">
        <span class="vh-stat__number">{{ achievements.length }}</span>
        <span class="vh-stat__label">Achievements</span>
      </div>
      <div class="vh-stat">
        <span class="vh-stat__number">{{ totalPlatforms }}</span>
        <span class="vh-stat__label">Platforms</span>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="vh-actions" v-if="followStatus !== null">
      <button
        class="vh-btn"
        :class="followStatus === follow ? 'vh-btn--following' : 'vh-btn--follow'"
        @click="followAction(user.id, user.username)"
        :disabled="followBtnLoading"
      >
        {{ followStatus === follow ? 'Following' : 'Follow' }}
      </button>
    </div>

    <div class="vh-content">

      <!-- Featured Section -->
      <div class="vh-section" v-if="featuredItems.length">
        <div class="vh-section__header">
          <span class="vh-section__label">Featured</span>
          <span class="vh-section__meta">curated by player</span>
        </div>
        <div class="vh-featured-grid">
          <div class="vh-featured-card" v-for="item in featuredItems" :key="item.id">
            <div class="vh-featured-card__icon">
              <img v-if="item.image" :src="getFeaturedImageUrl(item)" alt="" />
            </div>
            <div class="vh-featured-card__name">{{ item.name }}</div>
            <div class="vh-featured-card__source" v-if="item.integration">{{ item.integration }}</div>
            <div class="vh-featured-card__source" v-else-if="item.description">{{ item.description }}</div>
          </div>
        </div>
      </div>

      <!-- Achievements Section -->
      <div class="vh-section" v-if="achievements.length">
        <div class="vh-section__header">
          <span class="vh-section__label">Achievements</span>
          <span class="vh-section__count">{{ achievements.length }}</span>
        </div>
        <div class="vh-ach-list">
          <div class="vh-ach-row" v-for="ach in achievements" :key="ach.id">
            <div class="vh-ach-row__icon">
              <img v-if="ach.image" :src="'/storage/achievements/' + ach.image" alt="" />
            </div>
            <div class="vh-ach-row__info">
              <div class="vh-ach-row__name">{{ ach.name }}</div>
              <div class="vh-ach-row__status">
                <span v-if="ach.status === 1" class="vh-ach-row__validated">✓ Validated</span>
                <span v-if="ach.validations_count" class="vh-ach-row__vouches"> · {{ ach.validations_count }} vouches</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Forged Trophies Section -->
      <div class="vh-section" v-if="trophies.length">
        <div class="vh-section__header">
          <span class="vh-section__label">Forged trophies</span>
          <span class="vh-section__count">{{ trophies.length }}</span>
        </div>
        <div class="vh-trophy-grid">
          <TrophyCard
            v-for="trophy in trophies"
            :key="trophy.id"
            :trophy="trophy"
            :show-forge-button="false"
            :show-showcase="false"
          />
        </div>
      </div>

      <!-- Platform Badges Section -->
      <div class="vh-section" v-if="totalBadges > 0">
        <div class="vh-section__header">
          <span class="vh-section__label">Platform badges</span>
        </div>

        <!-- Filter Pills -->
        <div class="vh-filter-pills">
          <button class="vh-pill" :class="{ 'vh-pill--active': badgeFilter === 'all' }" @click="setBadgeFilter('all')">All</button>
          <button class="vh-pill" :class="{ 'vh-pill--active': badgeFilter === 'discord' }" @click="setBadgeFilter('discord')" v-if="discord_badges.length">Discord</button>
          <button class="vh-pill" :class="{ 'vh-pill--active': badgeFilter === 'steam' }" @click="setBadgeFilter('steam')" v-if="steam_badges.length">Steam</button>
          <button class="vh-pill" :class="{ 'vh-pill--active': badgeFilter === 'github' }" @click="setBadgeFilter('github')" v-if="github_badges.length">GitHub</button>
        </div>

        <div class="vh-platform-groups">
          <!-- Discord Badges -->
          <div class="vh-platform-group" v-show="discord_badges.length && (badgeFilter === 'all' || badgeFilter === 'discord')">
            <div class="vh-platform-header">
              <img src="../../../images/web/img/icons/discord.svg" alt="Discord" class="vh-platform-icon" />
              <span class="vh-platform-name">Discord</span>
              <span class="vh-platform-count">{{ discord_badges.length }}</span>
            </div>
            <div class="vh-badge-grid">
              <BadgeTile
                v-for="badge in discord_badges"
                :key="badge.id"
                :badge="badge"
                service="discord"
              />
            </div>
          </div>

          <!-- Steam Badges -->
          <div class="vh-platform-group" v-show="steam_badges.length && (badgeFilter === 'all' || badgeFilter === 'steam')">
            <div class="vh-platform-header">
              <img src="../../../images/web/img/icons/steam.svg" alt="Steam" class="vh-platform-icon" />
              <span class="vh-platform-name">Steam</span>
              <span class="vh-platform-count">{{ steam_badges.length }}</span>
            </div>
            <div class="vh-badge-grid">
              <BadgeTile
                v-for="badge in steam_badges"
                :key="badge.id"
                :badge="badge"
                service="steam"
              />
            </div>
          </div>

          <!-- GitHub Badges -->
          <div class="vh-platform-group" v-show="github_badges.length && (badgeFilter === 'all' || badgeFilter === 'github')">
            <div class="vh-platform-header">
              <img src="../../../images/web/img/icons/github.svg" alt="GitHub" class="vh-platform-icon" />
              <span class="vh-platform-name">GitHub</span>
              <span class="vh-platform-count">{{ github_badges.length }}</span>
            </div>
            <div class="vh-badge-grid">
              <BadgeTile
                v-for="badge in github_badges"
                :key="badge.id"
                :badge="badge"
                service="github"
              />
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- User not found -->
  <div v-if="user === null" class="vh-not-found">
    <h2 class="vh-not-found__title">User not found</h2>
    <router-link to="/" class="vh-not-found__link">
      <button-white :text="'Go to homepage'"></button-white>
    </router-link>
  </div>
</template>

<script>

import {defineComponent} from "vue";
import achievementCard from "../../parts/achievement-card.vue";
import forgeCard from "../../parts/forge-card.vue";
import buttonWhite from "../../parts/button.vue";
import CustomSelect from "../../parts/custom-select.vue";
import api from "../../api/api.js";
import ambarNotification from "../../components/modals/ambar-notification.vue";
import store from "../../store/store.js";
import {Centrifuge} from "centrifuge";
import SubscriptionPricesBlock from "./components/SubscriptionPricesBlock.vue";
import validateCard from "../../parts/validate-card.vue";
import TrophyCard from "../../parts/trophy-card.vue";
import BadgeTile from "../../parts/badge-tile.vue";
import AchievementRow from "../../parts/achievement-row.vue";


export default defineComponent({
  components: {
    SubscriptionPricesBlock,
    CustomSelect,
    achievementCard,
    forgeCard,
    buttonWhite,
    ambarNotification,
    store,
    validateCard,
    TrophyCard,
    BadgeTile,
    AchievementRow,
  },
  data() {
    return {
      count_achievements: 0,
      activeTab: 1,
      import_text: "Import",
      achievements: [],
      NFTs: [],
      trophies: [],
      github_badges: [],
      steam_badges: [],
      discord_badges: [],
      button_text: "Validate",
      icon_type: "Round",
      user: {},
      followStatus: null,
      follow: 1,
      followBtnLoading: false,
      userBalances: {
        uru: 0,
        ambar: 0,
        rune: 0,
      },
      userNFTs: 0,
      badgeFilter: 'all',
      isLoggedIn: false
    }
  },
  methods: {
    changeTab(tabNumber) {
      this.activeTab = tabNumber;
    },
    getUserData(username) {
      api.get(`/api/virtual-hall/${username}`).then(resp => {
        if (resp.status === 200) {
          this.followStatus = resp.data.followStatus;

          if (resp.data.user.data) {

              this.subscribeCentrifugoBalances(resp.data.user.data.id);

              this.user = resp.data.user.data;
            this.user.balances.forEach( (balance)=>{
              this.userBalances[balance.currency.name] = +(balance.amount);
            })

            if (resp.data.user.data.avatar && resp.data.user.data.avatar != '/images/avatar/default-profile-img.png'){
              this.user.avatar = resp.data.user.data.avatar;
            }
            if (resp.data.user.data.background && resp.data.user.data.background != '/images/background/default-background-img.png'){
              this.user.background = resp.data.user.data.background;
            }
            if (resp.data.user.data.background && resp.data.user.data.background != '/images/background/default-background-img.png'){
              this.user.background = resp.data.user.data.background;
            }

            this.github_badges = resp.data.user.data.badges.data.filter(item => item.integration === 'github');
            this.steam_badges = resp.data.user.data.badges.data.filter(item => item.integration === 'steam');
            this.discord_badges = resp.data.user.data.badges.data.filter(item => item.integration === 'discord');

            const allTrophies = resp.data.user.data.trophies;
            this.trophies = allTrophies.filter(item => item.is_nft === 0);
            this.NFTs = allTrophies.filter(item => item.is_nft === 1);
            this.userNFTs = this.NFTs.length;
            this.achievements = resp.data.user.data.achievements;
          } else {
            this.user = null;
          }
        }
      })
    },
    copyLink() {
      navigator.clipboard.writeText(window.location.href);
      store.state.notification = {
        message: 'Link copied to clipboard.',
        type: 'info',
        show: true
      }
    },
    followAction(userId, userName) {

      this.followBtnLoading = true;
      api.post(`/api/follow/action`, {id: userId}).then(resp => {
        if (resp && resp.data){
          this.followStatus = resp.data.followStatus;
          let msg = this.followStatus === this.follow ? `You follow to ${ userName }` : `You unfollow to ${ userName }`
          store.state.notification = {
            message: msg,
            type: 'info',
            show: true
          }
        }

      }).catch(error => {
        console.log('Error in followAction:', error);
      }).finally(() => {
        setTimeout(() => {
          this.followBtnLoading = false;
        }, 1000);
      });
    },
    setBadgeFilter(filter) {
      this.badgeFilter = filter;
    },
    getFeaturedImageUrl(item) {
      if (item.integration) {
        return `/storage/integrations/${item.integration}/${item.image}`;
      } else if (item.type !== undefined) {
        return `/storage/trophies/${item.image}`;
      } else {
        return `/storage/achievements/${item.image}`;
      }
    },
    async subscribeCentrifugoBalances(userId){
      this.centrifuge = new Centrifuge(localStorage.getItem('websocket_url'));
      this.centrifuge.setToken(localStorage.getItem('centrifugo_token'));

      const self = this;
      this.centrifuge.newSubscription('ambar-balance-ambar-' + userId)
          .on('publication', function(ctx) {
            self.userBalances.ambar = Math.floor(ctx.data.amount);
          }).subscribe();

      this.centrifuge.newSubscription('ambar-balance-uru-' + userId)
          .on('publication', function(ctx) {
            self.userBalances.uru = Math.floor(ctx.data.amount);
          }).subscribe();

      this.centrifuge.newSubscription('ambar-balance-rune-' + userId)
          .on('publication', function(ctx) {
            self.userBalances.rune = Math.floor(ctx.data.amount);
          }).subscribe();

      // let authUser = JSON.parse(localStorage.getItem('user'));
      // while (!authUser) {
      //   await new Promise(resolve => setTimeout(resolve, 250));
      //   authUser = JSON.parse(localStorage.getItem('user'));
      // }

      this.centrifuge.newSubscription('network-' + userId)
          .on('publication', function(action) {
            if (action.data.userId === userId) {
              self.followStatus = action.data.type;
            }
          }).subscribe();

      this.centrifuge.connect();
    },
  },
  mounted() {
    this.isLoggedIn = !!localStorage.getItem('access_token');
    this.getUserData(this.$route.params.username);
  },
  computed: {
    notificationModalOpen: function () {
      return store.state.notification.show;
    },
    totalBadges() {
      return this.discord_badges.length + this.steam_badges.length + this.github_badges.length;
    },
    totalPlatforms() {
      let count = 0;
      if (this.discord_badges.length) count++;
      if (this.steam_badges.length) count++;
      if (this.github_badges.length) count++;
      return count;
    },
    featuredItems() {
      const badges = [...this.discord_badges, ...this.steam_badges, ...this.github_badges].filter(b => b.display);
      const trophyItems = this.trophies.filter(t => t.pivot && t.pivot.display);
      const achItems = this.achievements.filter(a => a.display);
      return [...trophyItems, ...achItems, ...badges].slice(0, 4);
    },
  }
})
</script>

<style scoped>
.vh {
  max-width: 860px;
  margin: 0 auto;
  padding: 0 20px 60px;
  font-family: 'Share Tech Mono', monospace;
}

/* Banner */
.vh-banner {
  position: relative;
  height: 180px;
  background: #0e0f11;
  border-radius: 8px 8px 0 0;
  overflow: hidden;
  margin-bottom: 0;
}

.vh-banner__bg {
  width: 100%;
  height: 100%;
}

.vh-banner__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.vh-banner__back {
  position: absolute;
  top: 16px;
  left: 16px;
  background: rgba(0, 0, 3, 0.7);
  border: 1px solid #2a2c2e;
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  padding: 6px 14px;
  border-radius: 4px;
  backdrop-filter: blur(4px);
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}

.vh-banner__back:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.vh-banner__url {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(0, 0, 3, 0.7);
  color: #9a9590;
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 4px;
  backdrop-filter: blur(4px);
  cursor: pointer;
  transition: color 0.15s, background 0.15s;
  font-family: 'Share Tech Mono', monospace;
}

.vh-banner__url:hover {
  color: #feeddf;
  background: rgba(0, 0, 3, 0.9);
}

/* Profile */
.vh-profile {
  display: flex;
  align-items: flex-end;
  gap: 16px;
  margin-top: -36px;
  padding: 0 16px;
  margin-bottom: 20px;
  position: relative;
  z-index: 1;
}

.vh-profile__copy {
  margin-left: auto;
  align-self: flex-end;
  flex-shrink: 0;
}

.vh-avatar {
  width: 80px;
  height: 80px;
  min-width: 80px;
  border-radius: 50%;
  border: 3px solid #000003;
  overflow: hidden;
  background: #0e0f11;
}

.vh-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.vh-profile__info {
  padding-bottom: 4px;
}

.vh-profile__name-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.vh-profile__name {
  color: #feeddf;
  font-size: 24px;
  font-weight: 400;
  margin: 0;
}

.vh-profile__platforms {
  display: flex;
  gap: 6px;
}

.vh-profile__platform-icon {
  width: 18px;
  height: 18px;
  opacity: 0.5;
}

.vh-profile__bio {
  color: #9a9590;
  font-size: 13px;
  margin: 4px 0 0;
}

/* Stats Bar */
.vh-stats {
  display: flex;
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px 0;
  margin-bottom: 20px;
}

.vh-stat {
  flex: 1;
  text-align: center;
  border-right: 1px solid #2a2c2e;
}

.vh-stat:last-child {
  border-right: none;
}

.vh-stat__number {
  display: block;
  color: #feeddf;
  font-size: 22px;
  font-weight: 400;
}

.vh-stat__label {
  display: block;
  color: #5a5550;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-top: 2px;
}

/* Action Buttons */
.vh-actions {
  display: flex;
  gap: 10px;
  margin-bottom: 32px;
}

.vh-btn {
  padding: 8px 20px;
  border-radius: 4px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.15s;
  border: 1px solid;
}

.vh-btn--follow {
  background: #c1f527;
  color: #000003;
  border-color: #c1f527;
}

.vh-btn--follow:hover {
  filter: brightness(1.1);
}

.vh-btn--following {
  background: transparent;
  color: #c1f527;
  border-color: #c1f527;
}

.vh-btn--secondary {
  background: transparent;
  color: #9a9590;
  border-color: #2a2c2e;
}

.vh-btn--secondary:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.vh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Content */
.vh-content {
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Sections */
.vh-section {
  margin-bottom: 36px;
}

.vh-section__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.vh-section__label {
  color: #ff6100;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.vh-section__meta {
  color: #5a5550;
  font-size: 11px;
  letter-spacing: 0.05em;
}

.vh-section__count {
  color: #5a5550;
  font-size: 12px;
}

/* Featured Grid */
.vh-featured-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
}

.vh-featured-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px;
  text-align: center;
  transition: border-color 0.15s;
}

.vh-featured-card:hover {
  border-color: rgba(255, 97, 0, 0.3);
}

.vh-featured-card__icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 10px;
  background: #1a1c1f;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.vh-featured-card__icon img {
  width: 44px;
  height: 44px;
  object-fit: contain;
}

.vh-featured-card__name {
  color: #feeddf;
  font-size: 13px;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.vh-featured-card__source {
  color: #5a5550;
  font-size: 11px;
  text-transform: capitalize;
}

/* Achievement Rows */
.vh-ach-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.vh-ach-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
}

.vh-ach-row__icon {
  width: 44px;
  height: 44px;
  min-width: 44px;
  background: #1a1c1f;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.vh-ach-row__icon img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.vh-ach-row__info {
  flex: 1;
}

.vh-ach-row__name {
  color: #feeddf;
  font-size: 14px;
}

.vh-ach-row__status {
  font-size: 12px;
  margin-top: 2px;
}

.vh-ach-row__validated {
  color: #c1f527;
}

.vh-ach-row__vouches {
  color: #9a9590;
}

/* Trophy Grid */
.vh-trophy-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

/* Filter Pills */
.vh-filter-pills {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.vh-pill {
  padding: 6px 16px;
  border-radius: 20px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.15s;
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
}

.vh-pill--active {
  background: #c1f527;
  color: #000003;
  border-color: #c1f527;
}

.vh-pill:hover:not(.vh-pill--active) {
  border-color: #9a9590;
  color: #feeddf;
}

/* Platform Groups */
.vh-platform-groups {
  min-height: 200px;
}

.vh-platform-group {
  margin-bottom: 24px;
}

.vh-platform-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.vh-platform-icon {
  width: 20px;
  height: 20px;
  opacity: 0.7;
}

.vh-platform-name {
  color: #9a9590;
  font-size: 14px;
}

.vh-platform-count {
  color: #5a5550;
  font-size: 12px;
}

.vh-badge-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* Not Found */
.vh-not-found {
  text-align: center;
  padding: 80px 20px;
}

.vh-not-found__title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 24px;
  font-weight: 400;
  margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 640px) {
  .vh-banner {
    height: 120px;
  }

  .vh-profile {
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-top: -40px;
  }

  .vh-profile__name-row {
    justify-content: center;
  }

  .vh-stats {
    flex-wrap: wrap;
  }

  .vh-stat {
    flex: 0 0 50%;
    padding: 8px 0;
    border-right: none;
  }

  .vh-stat:nth-child(1),
  .vh-stat:nth-child(2) {
    border-bottom: 1px solid #2a2c2e;
  }

  .vh-actions {
    justify-content: center;
  }

  .vh-featured-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .vh-trophy-grid {
    grid-template-columns: 1fr;
  }
}
</style>

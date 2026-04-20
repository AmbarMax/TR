<template>
  <div class="feed-page">

    <!-- PAGE HERO -->
    <div class="page-hero">
      <div class="page-hero-bg"></div>
      <div class="page-hero-inner">
        <div class="page-tag">Community</div>
        <h1 class="page-title"><span class="accent-word">Achievements</span></h1>
        <p class="page-subtitle">Share your feats, clips, and forged trophies with the community. Donate Ambar to celebrate others.</p>
      </div>
    </div>

    <!-- TABS BAR (sticky) -->
    <div class="tabs-bar">
      <button
        class="tab"
        :class="{ active: activeTab === 1 }"
        @click="changeTab(1)"
      >Following</button>
      <button
        class="tab"
        :class="{ active: activeTab === 2 }"
        @click="changeTab(2)"
      >My posts</button>
      <button
        class="tab"
        :class="{ active: activeTab === 3 }"
        @click="changeTab(3)"
      >My community</button>
    </div>

    <!-- TAB CONTENT -->
    <div v-if="activeTab === 1" class="tab-content">
      <div class="feed-layout">
        <div class="feed-main">
          <FeedComposer @post-created="refreshFeed" />
          <Followers ref="followersTab" />
        </div>
        <div class="feed-sidebar">
          <WallOfFame />
        </div>
      </div>
    </div>

    <div v-if="activeTab === 2" class="tab-content">
      <div class="feed-layout">
        <div class="feed-main">
          <FeedComposer @post-created="refreshFeed" />
          <MyFeed ref="myFeedTab" />
        </div>
        <div class="feed-sidebar">
          <WallOfFame />
        </div>
      </div>
    </div>

    <div v-if="activeTab === 3" class="tab-content">
      <div class="feed-layout">
        <div class="feed-main">
          <MyCommunity ref="communityTab" />
        </div>
        <div class="feed-sidebar">
          <WallOfFame />
        </div>
      </div>
    </div>

    <!-- TERMINAL STRIP -->
    <div class="terminal-strip">
      <div>achievements · feed · stream<span class="cursor-blink"></span></div>
      <div>status · nominal</div>
    </div>

  </div>
</template>

<script>
import Followers from "./components/Followers.vue";
import MyFeed from "./components/My-feed.vue";
import WallOfFame from "./components/WallOfFame.vue";
import FeedComposer from "./components/FeedComposer.vue";
import MyCommunity from "./components/MyCommunity.vue";
import {defineComponent} from "vue";
import store from "../../store/store.js";

export default defineComponent({
  components: {
    MyFeed,
    Followers,
    WallOfFame,
    FeedComposer,
    MyCommunity,
    store,
  },
  data() {
    return {
      activeTab: 1,
    }
  },
  methods: {
    changeTab(tabNumber) {
      this.activeTab = tabNumber;
    },
    importBudgesModalOpen(){
      store.state.importBudgesModalOpen = true;
    },
    refreshFeed() {
      if (this.activeTab === 1 && this.$refs.followersTab) {
        this.$refs.followersTab.items = [];
        this.$refs.followersTab.currentPage = 1;
        this.$refs.followersTab.endReached = false;
        this.$refs.followersTab.fetchData();
      }
      if (this.activeTab === 2 && this.$refs.myFeedTab) {
        this.$refs.myFeedTab.items = [];
        this.$refs.myFeedTab.currentPage = 1;
        this.$refs.myFeedTab.endReached = false;
        this.$refs.myFeedTab.fetchData();
      }
    },
  },
  mounted(){
    if (this.$route.query.tab === 'community') {
      this.activeTab = 3;
    }
  }
});
</script>

<style lang="scss" scoped>
.feed-page {
  min-width: 0;
  max-width: 100%;
}

/* Page Hero */
.page-hero {
  position: relative;
  padding: 40px 48px 0;
  overflow: hidden;
}
.page-hero-bg {
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 700px 400px at 90% 40%, rgba(255,97,0,0.1), transparent 60%),
    radial-gradient(ellipse 400px 300px at 90% 40%, rgba(193,245,39,0.03), transparent 65%);
}
.page-hero-inner {
  position: relative; z-index: 2;
}
.page-tag {
  display: inline-flex; align-items: center; gap: 10px;
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.32em; text-transform: uppercase;
  margin-bottom: 16px;
}
.page-tag::before {
  content: ''; width: 28px; height: 1px; background: var(--primary);
  box-shadow: 0 0 6px var(--primary);
}
.page-title {
  font-family: var(--display);
  font-size: 64px; line-height: 0.95;
  color: var(--text); letter-spacing: 0.015em;
  margin-bottom: 10px;
  text-shadow: 0 0 30px rgba(255,97,0,0.18);
}
.page-title .accent-word {
  color: var(--primary);
  text-shadow: 0 0 22px var(--primary-glow);
}
.page-subtitle {
  font-size: 13px; color: var(--text-muted);
  letter-spacing: 0.04em; line-height: 1.6;
  max-width: 580px;
}

/* Tabs Bar — sticky below header */
.tabs-bar {
  padding: 0 48px;
  border-bottom: 1px solid rgba(255,97,0,0.1);
  display: flex; gap: 32px;
  background: rgba(5,5,8,0.5);
  position: sticky; top: 64px; z-index: 30;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}
.tab {
  padding: 18px 0;
  font-family: var(--mono);
  font-size: 11px; letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--text-muted);
  border: none; background: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: all 0.15s;
  cursor: pointer;
}
.tab:hover { color: var(--text); }
.tab.active {
  color: var(--primary);
  border-bottom-color: var(--primary);
  text-shadow: 0 0 10px var(--primary-glow);
}

/* Feed Layout — grid main + sidebar */
.feed-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 0;
  max-width: 100%;
}
.feed-main {
  padding: 32px 40px 80px 48px;
  min-width: 0;
}
.feed-sidebar {
  position: sticky; top: 120px;
  align-self: start;
  padding: 32px 32px 32px 0;
  max-height: calc(100vh - 120px);
  overflow-y: auto;
}

/* Terminal Strip */
.terminal-strip {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  display: flex; justify-content: space-between;
  padding: 20px 48px;
  border-top: 1px solid rgba(42,44,46,0.5);
}
.cursor-blink {
  display: inline-block; width: 8px; height: 11px;
  background: var(--primary);
  margin-left: 4px; vertical-align: middle;
  animation: blink 1s steps(1) infinite;
}
@keyframes blink { 50% { opacity: 0; } }

/* Responsive */
@media (max-width: 1100px) {
  .feed-layout { grid-template-columns: 1fr; }
  .feed-sidebar {
    position: relative; top: auto;
    padding: 0 48px 40px;
    max-height: none;
  }
}
@media (max-width: 700px) {
  .page-hero { padding: 28px 20px 0; }
  .page-title { font-size: 44px; }
  .tabs-bar { padding: 0 20px; gap: 20px; top: 56px; }
  .feed-main { padding: 24px 20px 60px; }
  .feed-sidebar { padding: 0 20px 40px; }
  .terminal-strip { padding: 20px; }
}
</style>

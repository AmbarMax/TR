<template>
  <div class="feed-page">
    <div class="feed-header">
      <h1 class="feed-title">Achievements</h1>
      <p class="feed-subtitle">Share your feats, clips, and forged trophies with the community</p>
    </div>
    <div class="feed-tabs">
      <div :class="{ active: activeTab === 1 }" class="feed-tab" @click="changeTab(1)">Following</div>
      <div :class="{ active: activeTab === 2 }" class="feed-tab" @click="changeTab(2)">My posts</div>
      <div :class="{ active: activeTab === 3 }" class="feed-tab" @click="changeTab(3)">My community</div>
    </div>
    <div class="feed-layout">
      <div class="feed-content">
        <FeedComposer v-if="activeTab !== 3" @post-created="refreshFeed" />
        <div v-if="activeTab === 1"><Followers ref="followersTab" /></div>
        <div v-if="activeTab === 2"><MyFeed ref="myFeedTab" /></div>
        <div v-if="activeTab === 3"><MyCommunity ref="communityTab" /></div>
      </div>
      <div class="feed-sidebar">
        <WallOfFame />
      </div>
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

<style scoped>
.feed-page {
  padding-top: 20px;
  padding-left: 32px;
}
.feed-header {
  margin-bottom: 20px;
}
.feed-title {
  font-family: 'Share Tech Mono', monospace;
  font-size: 22px;
  font-weight: 400;
  color: #feeddf;
  margin: 0 0 4px 0;
}
.feed-subtitle {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  color: #9a9590;
  line-height: 1.5;
  margin: 0;
}
.feed-tabs {
  display: flex;
  border-bottom: 1px solid #2a2c2e;
  margin-bottom: 20px;
  gap: 0;
}
.feed-tab {
  padding: 10px 16px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  color: #9a9590;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: color 0.2s, border-color 0.2s;
}
.feed-tab:hover {
  color: #feeddf;
}
.feed-tab.active {
  color: #c1f527;
  border-bottom-color: #c1f527;
}
.feed-layout {
  display: flex;
  gap: 24px;
  align-items: flex-start;
}
.feed-content {
  flex: 1;
  min-width: 0;
  max-width: 680px;
}
.feed-sidebar {
  width: 300px;
  flex-shrink: 0;
  align-self: flex-start;
  position: sticky;
  top: 84px;
  max-height: calc(100vh - 100px);
  overflow-y: auto;
}
.feed-sidebar::-webkit-scrollbar { width: 4px; }
.feed-sidebar::-webkit-scrollbar-thumb { background: #ff6100; border-radius: 4px; }

@media (max-width: 968px) {
  .feed-layout {
    flex-direction: column;
  }
  .feed-sidebar {
    width: 100%;
    order: -1;
  }
}
</style>

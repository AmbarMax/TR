<template>
  <div class="main_block">
    <div class="page_info">
      <div class="title">
        <span>Feed</span>
        <div class="sub_title">
          <span>The place where you share your feats with friends and community. Unlike your trophy room, you can share anything here, not just your best achievements</span>
        </div>
      </div>
      <div class="tabs">
        <div :class="{ 'selected': activeTab === 1 }" class="tab" @click="changeTab(1)">
          Followers
        </div>
        <div :class="{ 'selected': activeTab === 2 }" class="tab" @click="changeTab(2)">
          My feed
        </div>
      </div>
    </div>
    <div v-if="activeTab === 1" class="my-vault-tab">
      <Followers />
    </div>
    <div v-if="activeTab === 2" class="my-vault-tab">
      <MyFeed />
    </div>
  </div>
</template>

<script>
import Followers from "./components/Followers.vue";
import MyFeed from "./components/My-feed.vue";
import {defineComponent} from "vue";
import store from "../../store/store.js";

export default defineComponent({
  components: {
    MyFeed,
    Followers,
    store,
  },
  data() {
    return {
      activeTab: 1,
      followersCount: 0,
      followingCount: 0,
    }
  },
  methods: {
    changeTab(tabNumber) {
      this.activeTab = tabNumber;
    },
    importBudgesModalOpen(){
      store.state.importBudgesModalOpen = true;
    },
  },
  mounted(){}
});
</script>

<style scoped>

</style>

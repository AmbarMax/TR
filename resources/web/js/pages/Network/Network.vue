<template>
  <div class="main_block">
    <div class="page_info">
      <div class="title">
        <span>Network</span>
        <div class="sub_title">
          <span>Check who is part of your legion of achievers and remove those you don't want in your adventure</span>
        </div>
      </div>
      <div class="tabs">
        <div :class="{ 'selected': activeTab === 1 }" class="tab" @click="changeTab(1)">
          Followers ({{$store.state.totalFollowers}})
        </div>
        <div :class="{ 'selected': activeTab === 2 }" class="tab" @click="changeTab(2)">
          Following ({{$store.state.totalFollowing}})
        </div>
      </div>
    </div>
    <div v-if="activeTab === 1" class="my-vault-tab">
      <Followers />
    </div>
    <div v-if="activeTab === 2" class="my-vault-tab">
      <Followings />
    </div>
  </div>
</template>

<script>
import {defineComponent} from "vue";
import store from "../../store/store.js";
import PagePagination from "../../components/PagePagination.vue";
import Followers from "./components/Followers.vue";
import Followings from "./components/Followings.vue";

export default defineComponent({
  components: {
    Followings,
    Followers,
    PagePagination,
    store,
  },
  data() {
    return {
      activeTab: 1,
      sectionFollowers: 'followers',
      sectionFollowings: 'followings',
      followersData: [],
      followingsData: [],
      followers: [],
      followings: [],
    }
  },
  methods: {
    changeTab(tabNumber) {
      this.activeTab = tabNumber;
    },
  },
});
</script>

<style scoped>
@media (max-width: 520px) {
    .tab {
        padding: 12px 17px!important;

    }
    .tabs {
        justify-content: space-around;
    }
}

</style>

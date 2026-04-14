<template>
  <div class="feed-list" @scroll="handleScroll">
    <Loader v-if="loading" />
    <div class="item-wrapper" v-for="item in $store.state.myPosts" v-if="!loading" :key="item.id">
      <feed-card :myFeed="true" :item="item" :index="item.id"></feed-card>
    </div>
  </div>
</template>

<script>
import {defineComponent} from "vue";
import Pagination from "../../../parts/Pagination.vue";
import CustomSelect from "../../../parts/custom-select.vue";
import achievementCard from "../../../parts/achievement-card.vue";
import buttonWhite from "../../../parts/button.vue";
import store from "../../../store/store.js";
import forgeCard from "../../../parts/forge-card.vue";
import api from "../../../api/api.js";
import ListItem from "./List.vue";
import List from "./List.vue";
import FeedCard from "./feed-card.vue";
import Loader from "../../../components/Loader.vue";

export default defineComponent({
  components: {
    Loader,
      FeedCard,
    List,
    ListItem,
    Pagination,
    CustomSelect,
    achievementCard,
    buttonWhite,
    store,
    forgeCard
  },
  data() {
    return {
      currentPage: 1,
      showTestData: true,
      posts: [],
      items: [],
      total: 0,
      endReached: false,
      loading: false,
    }
  },
  mounted(){
    window.addEventListener('scroll', this.handleScroll);
    this.fetchData();
  },
  methods: {
    async fetchData() {
      if (this.endReached) return;
      await api.get('/api/feed/my-posts/?page=' + this.currentPage).then(response => {
        if (response && response.data) {

          const newItems = response.data.data;
          store.state.myPostsTotal = response.data.meta.total;
          this.total = response.data.meta.total;

          if (newItems.length === 0) {
            this.endReached = true;
          } else {
            this.items = [...this.items, ...newItems];
            store.state.myPosts = this.items;
          }
        }
      }).catch(error => {
        console.error('Feed fetching data error:', error);
      });
    },

      handleScroll() {
          if ((window.innerHeight + window.scrollY >= (document.getElementById("web-app").offsetHeight - 2000)) && !this.endReached){
              this.currentPage++;
              this.fetchData();
          }
      },
  },
})

</script>

<style scoped>
.feed-list{
  overflow-y: auto;
}

.feed-list .item-wrapper {
    @media (max-width: 642px) {
        width: calc(100% - 20px);
    }

}
</style>

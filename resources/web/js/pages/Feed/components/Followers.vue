<template>
    <div class="feed-list" @scroll="handleScroll">
      <Loader v-if="loading" />
      <div class="item-wrapper" v-for="item in $store.state.posts" v-if="!loading" :key="item.id">
        <feed-card :myFeed="false" :item="item"></feed-card>
      </div>
    </div>
</template>

<script>

import FeedCard from "./feed-card.vue";
import api from "../../../api/api.js";
import store from "../../../store/store.js";
import Loader from "../../../components/Loader.vue";

export default {
    components: {
      Loader,
        FeedCard
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
        await api.get('/api/feed/posts/?page=' + this.currentPage).then(response => {
          if (response && response.data) {
            this.posts = response.data.data

            const newItems = response.data.data;
            store.state.postsTotal = response.data.meta.total;
            this.total = response.data.meta.total;

            if (newItems.length === 0) {
              this.endReached = true;
            } else {
              this.items = [...this.items, ...newItems];
              store.state.posts = this.items;
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
}
</script>

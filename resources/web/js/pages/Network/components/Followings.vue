<template>
  <div class="list-wrapper mt-30 mb-30" v-if="followings">
    <div class="item" v-for="item in followings" :key="item.id">
      <div class="left-part">
        <img v-if="!item.avatar" src="../../../../images/web/img/user.svg" alt="user" @click="navigateToVirtualHall(item.username)">
        <img v-else :src="item.avatar" alt="user" @click="navigateToVirtualHall(item.username)">
        <img v-else :src="'storage/users/' + item.id + '/avatar/'+item.avatar+'.jpg'" alt="user" @click="navigateToVirtualHall(item.username)">
        <span>
           <span @click="navigateToVirtualHall(item.username)" class="email-title">{{ item.email }}</span>
           <span @click="navigateToVirtualHall(item.username)" class="name-title">{{ item.name }}</span>
        </span>
      </div>
      <div class="icon-link-wrapper">
        <div class="green-bordered-block" @click="openNetworkRemoveActionModal(item.id, item.name)">
          Unfollow
        </div>
      </div>
    </div>
  </div>

  <PagePagination
      :items="this.followings"
      :current-page="this.currentPage"
      :total="this.total"
      :items-per-page="10"
      :method="getFollowings"
  />
</template>

<script>
import {defineComponent} from "vue";
import store from "../../../store/store.js"
import api from "../../../api/api.js";
import {Centrifuge} from "centrifuge";
import PagePagination from "../../../components/PagePagination.vue";

export default defineComponent({
  components: {
    PagePagination,
    store,
  },
  data() {
    return {
      followings: [],
      total: 0,
      currentPage: 1,
    }
  },
  methods: {
    async getFollowings(page) {
      await api.get('/api/follow/following?page='+page).then(response => {
        if (response && response.data) {
          store.state.followings = response.data[0].data;
          store.state.followings = response.data[0].total;
          store.state.totalFollowers = response.data.totalFollowers;
          store.state.totalFollowing = response.data[0].total;

          this.followings = response.data[0].data;
          this.total = response.data[0].total;
        }
      }).catch(error => {
        console.error('Followers fetching data error:', error);
      });
    },
    async subscribeCentrifugoNetworkUsers(){
      this.centrifuge = new Centrifuge(localStorage.getItem('websocket_url'));
      this.centrifuge.setToken(localStorage.getItem('centrifugo_token'));

      let user = JSON.parse(localStorage.getItem('user'));
      while (!user) {
        await new Promise(resolve => setTimeout(resolve, 250));
        user = JSON.parse(localStorage.getItem('user'));
      }
      let vm = this;

      this.centrifuge.newSubscription('network-' + user.id)
          .on('publication', function() {
            vm.getFollowings(1);
          }).subscribe();

      this.centrifuge.connect();
    },
    navigateToVirtualHall(username) {
      window.open(`/virtual-hall/${username}`, '_blank');
    },
    openNetworkRemoveActionModal(userId, userName) {
      store.state.networkRemoveUnfollowModal.title = 'Unfollowing from '+userName+'?';
      store.state.networkRemoveUnfollowModal.btn_text = 'Unfollow';
      store.state.networkRemoveUnfollowModal.action = 'api/follow/action';
      store.state.networkRemoveUnfollowModal.show = true;
      store.state.networkRemoveUnfollowModal.user_id = userId;
    }
  },
  mounted(){
    this.getFollowings(this.currentPage);
    this.subscribeCentrifugoNetworkUsers();
  }
});

</script>

<style scoped>

.list-wrapper {
  background: rgba(186, 186, 186, 0.15);
  width: 100%;
}

.item {
  display: flex;
  justify-content: space-between;
  padding: 18px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  gap: 12px;
}

.left-part {
  display: flex;
  align-items: center;
}

.left-part img {
  margin-right: 10px;
  cursor: pointer;
}

.left-part span {
  display: block;
}

img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
}

.email-title,
.name-title,
.green-bordered-block {
  font-family: 'Share Tech Mono', monospace;
}

.email-title,
.name-title {
  color: white;
  cursor: pointer;
}

.green-bordered-block {
  margin-top: 8px;
  border-radius: 2px;
  border: 1px #CAFB01 solid;
  width: 295px;
  white-space: nowrap;
  text-align: center;
  color: #CAFB01;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
  padding: 8px;
}

.name-title {
  color: #BABABA;
  padding-top: 10px;
  font-size: 16px;
}

.trash-icon-wrapper {
  margin-left: 10px;
  margin-top: 8px;
  padding-top: 3px;
  cursor: pointer;
}

.icon-link-wrapper {
  display: flex;
  align-items: center;
}

@media (max-width: 1065px) {
  .item, .icon-link-wrapper {
    flex-direction: column;
  }

  .green-bordered-block {
    margin-top: 8px;
    padding: 8px;
  }
}

@media (max-width: 371px) {
  .green-bordered-block {
    width: 100%;
  }
  .email-title,
  .name-title,
  .green-bordered-block {
    word-break: break-word;
  }
}

</style>

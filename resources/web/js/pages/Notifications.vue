<template>
  <div class="page_info">
    <div class="title">
      <span>Notifications</span>
      <div class="sub_title">
        <span>Here you can accept or reject validation requests from your network, check who is following you and stay updated on all news from Ambar's team</span>
      </div>
    </div>
  </div>
    <div class="notifications">
        <div class="notifications__container mb-30" v-if="notifications.length">
            <div class="notifications__list">
                <div v-for="item of notifications" class="notifications-message">
                    <div class="notifications-message__icon">
                        <img :src="icons[item.type]" alt="notification-type">
                    </div>
                    <div class="notifications-message__content">
                        <span>
                            {{ item.created_at }}
                        </span>
                        <p>
                            {{ item.title }}
                        </p>

                        <div class="validate_btn_content">
                            <div class="validate_btn approve" v-if="item.type === 5 && item.entity_id" @click="openModal(item.entity_id)">
                                Validate achievement
                            </div>

                            <button class="validate_btn reject" v-if="item.type === 5 && item.entity_id" @click="reject(item.id)"
                                    :disabled="disableReject">
                                Reject
                            </button>
                        </div>
                      <div class="spacer" />
                    </div>
                </div>
            </div>
        </div>
      <PagePagination
          :items="notifications"
          :current-page="1"
          :total="total"
          :items-per-page="10"
          :method="fetchData"
      />
    </div>
</template>

<script>

import {defineComponent} from "vue";
import api from "../api/api.js";
import PagePagination from "../components/PagePagination.vue";
import store from "../store/store.js";
import {Centrifuge} from "centrifuge";

export default defineComponent({
  components: {PagePagination},
  data() {
        return {
            icons: {
                1: '/web/img/icons/Notifications_page/user.svg',
                2: '/web/img/icons/Notifications_page/bell.svg',
                3: '/web/img/icons/Notifications_page/bell.svg',
                4: '/web/img/icons/Notifications_page/bell.svg',
                5: '/web/img/icons/Notifications_page/bell.svg',
            },
            notifications: [],
            totalNotifications: [],
            currentPage: 1,
            total: 0,
            uri: '/api/notification/index?page=',
            disableReject: false
        }
    },
    methods: {
      async fetchData(page) {
        if (this.endReached) return;

          this.loading = true;

          await api.get('/api/notification/index?page=' + page).then(resp => {
            if (resp && resp.data) {
              this.notifications = resp.data[0].data;
              this.total = resp.data[0].total;
            }
          }).catch (error => {
            console.error('Error in notification index:', error);
          });
      },
      openModal(achId) {
        store.state.validateAchievementModal.show = true;
        store.state.validateAchievementModal.title = 'Do you agree to validate the achievement?';
        store.state.validateAchievementModal.entity_id = achId;
      },
      async subscribeCentrifugoNotifications(){
        this.centrifuge = new Centrifuge(localStorage.getItem('websocket_url'));
        this.centrifuge.setToken(localStorage.getItem('centrifugo_token'));

        let user = JSON.parse(localStorage.getItem('user'));
        while (!user) {
          await new Promise(resolve => setTimeout(resolve, 250));
          user = JSON.parse(localStorage.getItem('user'));
        }
        let vm = this;

        this.centrifuge.newSubscription('notification-user-' + user.id)
            .on('publication', function() {
              vm.fetchData(1);
            }).subscribe();

        this.centrifuge.newSubscription('proof-' + user.id)
            .on('publication', function() {
              vm.fetchData(1);
            }).subscribe();

        this.centrifuge.connect();
        },
      async reject(id) {
        this.disableReject = true;
        await api.post('api/achievement/social-approve/reject', {
          'id': id,
        }).then(response => {
          if (response && response.data) {
            store.state.validateAchievementModal.entity_id = '';
            this.disable_btn = true;
            store.state.notification.type = 'success';
            store.state.notification.message = 'You reject social approve request';
            store.state.notification.show = true;
          }
        }).catch(error => {
          console.error('Network fetching data error:', error);
        });
      }
    },
  mounted() {
    this.fetchData(this.currentPage);
    this.subscribeCentrifugoNotifications();
  },
})
</script>
<style scoped>
.validate_btn_content{
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
}
.notifications__list {
    width: 100%;
}
.approve{
  color: #CAFB01;
  border: 1px #CAFB01 solid;
}
.reject{
  color: #fb014c;
  border: 1px #fb013b solid;
}
.validate_btn {
  padding: 6px 10px;
  overflow: hidden;
  background: #1F1C20;
  font-family: 'Share Tech Mono', monospace;
  border-radius: 2px;
  font-weight: 400;
  line-height: 12px;
  width: 150px;
  text-align: center;
  cursor: pointer;
}

@media (max-width: 430px) {
  .validate_btn_content{
    flex-direction: column;
  }
  .reject{
    margin-top: 10px;
  }
}

.page_info {
    margin: 40px;
    width: unset;
}

@media (max-width: 968px) {
    .page_info {
        margin: 130px 40px 0 40px;
    }

}
@media (max-width: 1308px) {
    .notifications {
        margin-top: 0px;
    }
}
</style>

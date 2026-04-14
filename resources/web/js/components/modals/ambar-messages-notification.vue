<template>
    <div class="message-notification" @mouseleave="closeWindow">
        <div class="message-notification__header">
            <h4>
                Notifications
            </h4>
            <img @mousedown="closeWindow" src="../../../images/web/img/icons/close.svg" alt="close-button">
        </div>
        <router-link to="/notifications" class="message-notification__view-all-button">
            <span>
                View All
            </span>
            <img src="../../../images/web/img/icons/arrow-green.svg" alt="arrow-right">
        </router-link>
        <div class="message-notification__list" ref="scrollable" @scroll="handleScroll">
            <div v-if="items.length === 0" class="message-notification__empty">
                You don't have new notifications
            </div>
            <div v-for="item of items" class="notification-message" :key="item.id">
                <img class="notification-message__icon" :src="icons[item.type]" alt="notification-icon">
                <div class="notification-message__text-block">
                    <span>
                        {{item.created_at}}
                    </span>
                    <p>
                        {{item.title}}
                    </p>
<!--                    <router-link :to="{ path: '/notifications' }" class="notification-message__more">-->
<!--                        <span>-->
<!--                            View More-->
<!--                        </span>-->
<!--                        <img src="/web/img/icons/arrow-green.svg" alt="arrow-right">-->
<!--                    </router-link>-->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import store from "../../store/store.js";
import api from "../../api/api.js";

export default {
    components: {
        store
    },
    data() {
        return {
            icons: {
                1: '/web/img/icons/message-notifications/user.svg',
                2: '/web/img/icons/message-notifications/bell.svg',
                3: '/web/img/icons/message-notifications/bell.svg',
                4: '/web/img/icons/message-notifications/bell.svg',
                5: '/web/img/icons/message-notifications/bell.svg',
            },
            items: [],
            loading: false,
            currentPage: 1,
            endReached: false,
            total: 0
        }
    },
    mounted() {
      this.fetchData();
    },
    methods: {
      async fetchData() {
        if (this.endReached) return;

        this.loading = true;

        await api.get('/api/notification/index?page=' + this.currentPage).then(resp => {
          if (resp && resp.data) {
            const newItems = resp.data[0].data;
            store.state.messageNotification.data = resp.data[0].data;
            this.total = resp.data[0].total;
            store.state.unread_notifications_count = resp.data.unreadCount;
            if (newItems.length === 0) {
              this.endReached = true;
            } else {
              this.items = [...this.items, ...newItems];
              store.state.messageNotification.data = this.items;
              this.page++;
            }
          }
        }).catch(error => {
          console.error('Error in get notifications in header:', error);
        }).finally(() => {
          this.loading = false;
        })
      },
        closeWindow() {
            store.state.messageNotification.show = false;
        },
      handleScroll() {
        const scrollContainer = this.$refs.scrollable;
        if (scrollContainer.scrollTop + scrollContainer.clientHeight === scrollContainer.scrollHeight
            && this.items
            && this.items.length < this.total
        ) {
          this.currentPage++;
          this.fetchData();
        }
      },
    }
}
</script>

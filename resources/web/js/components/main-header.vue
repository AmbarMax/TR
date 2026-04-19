<template>
  <header class="main-header">
    <div class="header-left">
      <div class="breadcrumb">
        <span>TrophyRoom</span>
        <span class="breadcrumb-dot"></span>
        <span class="breadcrumb-current">{{ currentPageLabel }}</span>
      </div>
    </div>

    <div class="header-right">
      <div class="wallet-rail">
        <div class="coin" title="Ambar">
          <span class="coin-dot coin-dot--ambar"></span>
          <div class="coin-meta">
            <div class="coin-val">{{ formatNumber(ambar) }}</div>
            <div class="coin-lbl">Ambar</div>
          </div>
        </div>
        <div class="coin" title="Uru">
          <span class="coin-dot coin-dot--uru"></span>
          <div class="coin-meta">
            <div class="coin-val">{{ formatNumber(uru) }}</div>
            <div class="coin-lbl">Uru</div>
          </div>
        </div>
      </div>

      <button class="bell-btn" @click="onBellClick" aria-label="Notifications">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span v-if="hasNotifications" class="bell-dot"></span>
      </button>

      <router-link :to="{ path: '/profile' }" class="avatar-pill">
        <span class="avatar-img">{{ avatarInitial }}</span>
        <span class="avatar-name">{{ username }}</span>
      </router-link>
    </div>
  </header>
</template>

<script>

import store from "../store/store.js";
import api from "../api/api.js";
import { Centrifuge } from 'centrifuge';

export default {
  name: 'MainHeader',
  data() {
    return {
      centrifuge: null,
    }
  },
  computed: {
    // Vuex bindings preserved from legacy main-header
    ambar() {
      return store.state.user?.balances?.ambar ?? 0;
    },
    uru() {
      return store.state.user?.balances?.uru ?? 0;
    },
    username() {
      return store.state.userUsername || 'User';
    },
    avatarInitial() {
      return (store.state.userUsername || 'U').charAt(0).toUpperCase();
    },
    hasNotifications() {
      return !!store.state.unread_notifications_count;
    },
    currentPageLabel() {
      const name = this.$route?.name || '';
      const map = {
        dashboard: 'Dashboard',
        'trophy-room': 'Trophy Room',
        forge: 'Forge',
        feed: 'Achievements',
        rewards: 'Rewards',
        exchange: 'Exchange',
        network: 'Network',
        settings: 'Settings',
        profile: 'Settings',
        'brand-dashboard': 'Admin Panel'
      };
      return map[name] || name.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
  },
  methods: {
    formatNumber(n) {
      const num = Number(n) || 0;
      return num.toLocaleString('en-US');
    },
    onBellClick() {
      this.$router.push('/dashboard');
      this.$emit('bell-click');
    },
    getUserBalances() {
      api.get('/api/profile').then(resp => {
        if (resp && resp.status === 200) {
          store.state.userUsername = resp.data.user.data.username;
          store.state.google2fa_status = resp.data.user.data.google2fa_status;
          if (resp.data.user.data.avatar === '/images/avatar/default-profile-img.png') {
            store.state.userAvatar = '';
          } else {
            store.state.userAvatar = resp.data.user.data.avatar;
          }
          for (let balance of resp.data.user.data.balances) {
            store.state.user.balances[balance.currency.name] = Math.floor(balance.amount);
          }
          store.state.unread_notifications_count = resp.data.user.data.unread_notifications_count;
          store.state.user.roles = resp.data.user.data.roles;
        }
      }).catch(error => {
        console.log('api profile error: ', error)
      })
    },
    async subscribeCentrifugoBalances() {
      this.centrifuge = new Centrifuge(localStorage.getItem('websocket_url'));
      this.centrifuge.setToken(localStorage.getItem('centrifugo_token'));

      let user = JSON.parse(localStorage.getItem('user'));
      while (!user) {
        await new Promise(resolve => setTimeout(resolve, 250));
        user = JSON.parse(localStorage.getItem('user'));
      }

      this.centrifuge.newSubscription('ambar-balance-ambar-' + user.id)
        .on('publication', function(ctx) {
          store.state.user.balances.ambar = Math.floor(ctx.data.amount);
        }).subscribe();

      this.centrifuge.newSubscription('ambar-balance-uru-' + user.id)
        .on('publication', function(ctx) {
          store.state.user.balances.uru = Math.floor(ctx.data.amount);
        }).subscribe();

      this.centrifuge.newSubscription('ambar-balance-rune-' + user.id)
        .on('publication', function(ctx) {
          store.state.user.balances.rune = Math.floor(ctx.data.amount);
        }).subscribe();

      this.centrifuge.newSubscription('notification-user-' + user.id)
        .on('publication', function(notification) {
          store.state.unread_notifications_count = notification.data.unreadCount;
        }).subscribe();

      this.centrifuge.newSubscription('sync-platform')
        .on('publication', function(data) {
          if (data.data.result === true) {
            store.state.notification = {
              message: 'Congratulations! You have successfully obtained your badge from ' + data.data.platform + '!',
              type: 'success',
              show: true
            }
          } else {
            store.state.notification = {
              message: 'You are not available to get your badge from ' + data.data.platform,
              type: 'info',
              show: true
            }
          }
        }).subscribe();

      this.centrifuge.connect();
    }
  },
  mounted() {
    let user = JSON.parse(localStorage.getItem('user'));
    if (user) {
      this.getUserBalances();
      this.subscribeCentrifugoBalances();
    }
  }
};
</script>

<style lang="scss" scoped>
.main-header {
  position: sticky;
  top: 0;
  z-index: 40;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 14px 48px;
  background: rgba(0, 0, 3, 0.7);
  backdrop-filter: blur(14px) saturate(1.3);
  -webkit-backdrop-filter: blur(14px) saturate(1.3);
  border-bottom: 1px solid rgba(255, 97, 0, 0.12);
}

.header-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
.header-right { display: flex; align-items: center; gap: 12px; }

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  white-space: nowrap;
}
.breadcrumb-dot {
  width: 4px;
  height: 4px;
  background: var(--primary);
  border-radius: 50%;
  box-shadow: 0 0 8px var(--primary);
}
.breadcrumb-current { color: var(--primary); }

.wallet-rail {
  display: flex;
  gap: 10px;
  align-items: center;
}
.coin {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 8px 14px;
  border: 1px solid rgba(255, 97, 0, 0.15);
  background: rgba(14, 15, 17, 0.65);
}
.coin-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.coin-dot--ambar {
  background: var(--primary);
  box-shadow: 0 0 8px var(--primary);
}
.coin-dot--uru {
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
}
.coin-meta { line-height: 1; }
.coin-val {
  font-family: var(--display);
  font-size: 22px;
  color: var(--text);
  line-height: 1;
  letter-spacing: 0.04em;
}
.coin-lbl {
  font-size: 9px;
  color: var(--text-muted);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  margin-top: 2px;
}

.bell-btn {
  width: 36px;
  height: 36px;
  border: 1px solid rgba(255, 97, 0, 0.15);
  background: rgba(14, 15, 17, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  position: relative;
  transition: all 0.15s;
}
.bell-btn:hover {
  color: var(--primary);
  border-color: var(--primary);
}
.bell-dot {
  position: absolute;
  top: 7px;
  right: 7px;
  width: 7px;
  height: 7px;
  background: var(--primary);
  border-radius: 50%;
  box-shadow: 0 0 8px var(--primary);
  border: 1.5px solid var(--bg);
}

.avatar-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 13px 4px 4px;
  border: 1px solid rgba(255, 97, 0, 0.15);
  background: rgba(14, 15, 17, 0.65);
  transition: border-color 0.15s;
}
.avatar-pill:hover { border-color: var(--primary); }
.avatar-img {
  width: 28px;
  height: 28px;
  background: linear-gradient(135deg, #f5c547, #d98c3a);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  color: var(--bg);
  font-weight: bold;
  flex-shrink: 0;
}
.avatar-name {
  font-size: 12px;
  color: var(--text);
  letter-spacing: 0.05em;
}

@media (max-width: 768px) {
  .main-header { padding: 12px 20px; gap: 10px; flex-wrap: wrap; }
  .wallet-rail { gap: 6px; }
  .coin { padding: 6px 10px; }
  .coin-val { font-size: 18px; }
  .avatar-name { display: none; }
}
</style>

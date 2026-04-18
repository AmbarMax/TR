# CLAUDE_DASHBOARD_OPS.md — User Dashboard + Header Simplification

> **Operational brief for Claude Code.**
> This is the largest phase so far. It creates a new Dashboard page, simplifies the header,
> updates the router, and adds a sidebar item. Execute step by step.

---

## Scope

1. **Create `Dashboard.vue`** — new home page for authenticated users
2. **Simplify `main-header.vue`** — remove currencies, clean up mobile
3. **Update `Login.vue` and `Signup.vue`** — redirect to `/dashboard` instead of `/trophy-room`
4. **Update `routes.js`** — add dashboard route, change default redirect
5. **Update `sidebar.vue`** — add Dashboard as first nav item
6. **Update bell notification** — click redirects to dashboard instead of opening dropdown

---

## Step 1: Create Dashboard.vue

**File:** `resources/web/js/pages/Dashboard.vue`

This page fetches data from existing API endpoints and displays it in a dopamine-optimized layout.

**API calls (all existing):**
- `GET /api/badges` — user's badges (to cross-reference with trophy requirements)
- `GET /api/forge` — all available trophies with badge requirements
- `GET /api/forge/available-trophies` — user's forged trophies
- `GET /api/notifications` — recent notifications
- User balances come from `store.state.user.balances` (already in Vuex)

```vue
<template>
  <div class="main_block">
    <!-- Header -->
    <div class="dash-header">
      <div>
        <h1 class="dash-title">Welcome back, {{ $store.state.userUsername }}</h1>
        <p class="dash-tagline">Keep forging. Keep earning.</p>
      </div>
      <div class="dash-currencies">
        <div class="dash-currency">
          <span class="dash-currency__value dash-currency__value--ambar">{{ Math.floor($store.state.user.balances.ambar || 0) }}</span>
          <span class="dash-currency__label">Ambar</span>
        </div>
        <div class="dash-currency__sep"></div>
        <div class="dash-currency">
          <span class="dash-currency__value dash-currency__value--uru">{{ Math.floor($store.state.user.balances.uru || 0) }}</span>
          <span class="dash-currency__label">Uru</span>
        </div>
      </div>
    </div>

    <!-- Daily Missions -->
    <div class="dash-section">
      <div class="dash-section__header">
        <span class="dash-section__label">Daily missions</span>
        <span class="dash-section__timer" id="dash-timer"></span>
      </div>
      <div class="dash-missions">
        <div
          v-for="mission in missions"
          :key="mission.id"
          class="dash-mission"
          :class="{ 'dash-mission--done': mission.done }"
          @click="mission.action"
        >
          <div class="dash-mission__info">
            <div class="dash-mission__name">
              <span v-if="mission.done" class="dash-mission__check">&#10003;</span>
              {{ mission.name }}
            </div>
            <div class="dash-mission__desc">{{ mission.description }}</div>
          </div>
          <div class="dash-mission__reward" :class="mission.done ? 'dash-mission__reward--done' : ''">
            {{ mission.done ? 'Done' : mission.reward }}
          </div>
        </div>
      </div>
    </div>

    <!-- Trophies in Progress -->
    <div class="dash-section" v-if="trophiesInProgress.length">
      <div class="dash-section__header">
        <span class="dash-section__label">Trophies in progress</span>
        <span class="dash-section__meta">{{ trophiesInProgress.length }} in progress<span v-if="readyToForge"> · {{ readyToForge }} ready</span></span>
      </div>
      <div class="dash-trophy-grid">
        <div
          v-for="trophy in trophiesInProgress.slice(0, 6)"
          :key="trophy.id"
          class="dash-trophy"
          :class="{ 'dash-trophy--ready': isTrophyReady(trophy) }"
          @click="goToForge"
        >
          <div class="dash-trophy__top">
            <div class="dash-trophy__icon">
              <img v-if="trophy.image" :src="'/storage/trophies/' + trophy.image" alt="" />
              <span v-else>{{ trophy.name.charAt(0) }}</span>
            </div>
            <div class="dash-trophy__xp">{{ Math.floor(trophy.price || 0) }} XP</div>
          </div>
          <div class="dash-trophy__name">{{ trophy.name }}</div>
          <div class="dash-trophy__progress-text">{{ getTrophyBadgesOwned(trophy) }}/{{ getTrophyBadgesRequired(trophy) }} badges</div>
          <div class="dash-trophy__bar">
            <div class="dash-trophy__fill" :style="{ width: getTrophyPercent(trophy) + '%' }"></div>
          </div>
          <div class="dash-trophy__action" v-if="isTrophyReady(trophy)">Forge now</div>
          <div class="dash-trophy__missing" v-else>Missing {{ getTrophyBadgesRequired(trophy) - getTrophyBadgesOwned(trophy) }} badge{{ (getTrophyBadgesRequired(trophy) - getTrophyBadgesOwned(trophy)) !== 1 ? 's' : '' }}</div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="dash-section" v-if="notifications.length">
      <div class="dash-section__header">
        <span class="dash-section__label">Recent activity</span>
      </div>
      <div class="dash-activity">
        <div class="dash-activity-row" v-for="notif in notifications.slice(0, 5)" :key="notif.id">
          <div class="dash-activity-row__dot"></div>
          <div class="dash-activity-row__text">{{ notif.message }}</div>
          <div class="dash-activity-row__time">{{ formatTime(notif.created_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Connected Platforms -->
    <div class="dash-section">
      <div class="dash-section__header">
        <span class="dash-section__label">Connected platforms</span>
      </div>
      <div class="dash-platforms">
        <div
          v-for="platform in platforms"
          :key="platform.name"
          class="dash-platform"
          :class="platform.connected ? 'dash-platform--connected' : ''"
          @click="!platform.connected && connectPlatform(platform.type)"
        >
          <div class="dash-platform__dot" :class="platform.connected ? 'dash-platform__dot--on' : ''"></div>
          <span class="dash-platform__name">{{ platform.name }}</span>
          <span v-if="platform.connected" class="dash-platform__status">synced</span>
          <span v-else class="dash-platform__connect">connect</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import store from "../store/store.js";
import api from "../api/api.js";

export default defineComponent({
  data() {
    return {
      allTrophies: [],
      userTrophyIds: [],
      userBadgeIds: [],
      notifications: [],
      timerInterval: null
    }
  },
  computed: {
    missions() {
      const hasDiscord = this.userBadgeIds.length > 0 || this.platforms.find(p => p.name === 'Discord')?.connected;
      const hasSteam = this.platforms.find(p => p.name === 'Steam')?.connected;
      const hasGithub = this.platforms.find(p => p.name === 'GitHub')?.connected;

      return [
        {
          id: 1,
          name: hasDiscord ? 'Import more Discord badges' : 'Connect Discord',
          description: hasDiscord ? 'Sync your latest achievements' : 'Link your Discord account',
          reward: '+250',
          done: false,
          action: () => this.connectPlatform('discord')
        },
        {
          id: 2,
          name: 'Forge a trophy',
          description: this.readyToForge ? `You have ${this.readyToForge} ready to forge` : 'Complete badge requirements first',
          reward: '+Uru',
          done: false,
          action: () => this.$router.push('/forge')
        },
        {
          id: 3,
          name: 'Share an achievement',
          description: 'Post to the feed and earn Ambar',
          reward: '+50',
          done: false,
          action: () => this.$router.push('/feed')
        },
        {
          id: 4,
          name: 'Connect Discord',
          description: 'Completed',
          reward: 'Done',
          done: !!hasDiscord,
          action: () => {}
        }
      ];
    },
    platforms() {
      return [
        { name: 'Discord', type: 'discord', connected: this.hasPlatformBadges('discord') },
        { name: 'Steam', type: 'steam', connected: this.hasPlatformBadges('steam') },
        { name: 'GitHub', type: 'github', connected: this.hasPlatformBadges('github') },
        { name: 'Riot Games', type: 'riot', connected: false },
        { name: 'Strava', type: 'strava', connected: false },
      ];
    },
    trophiesInProgress() {
      return this.allTrophies.filter(t => {
        if (this.userTrophyIds.includes(t.id)) return false;
        return true;
      });
    },
    readyToForge() {
      return this.trophiesInProgress.filter(t => this.isTrophyReady(t)).length;
    }
  },
  methods: {
    hasPlatformBadges(platform) {
      return this.userBadgeIds.some(id => {
        return true;
      });
    },
    isTrophyReady(trophy) {
      if (!trophy.badges || !trophy.badges.length) return false;
      const owned = trophy.badges.filter(b => this.userBadgeIds.includes(b.id)).length;
      return owned >= trophy.badges.length;
    },
    getTrophyBadgesOwned(trophy) {
      if (!trophy.badges) return 0;
      return trophy.badges.filter(b => this.userBadgeIds.includes(b.id)).length;
    },
    getTrophyBadgesRequired(trophy) {
      return trophy.badges ? trophy.badges.length : 0;
    },
    getTrophyPercent(trophy) {
      const req = this.getTrophyBadgesRequired(trophy);
      if (!req) return 0;
      return Math.round((this.getTrophyBadgesOwned(trophy) / req) * 100);
    },
    connectPlatform(type) {
      const token = localStorage.getItem('access_token');
      window.location.href = '/login/' + type + '?token=' + encodeURIComponent(token);
    },
    goToForge() {
      this.$router.push('/forge');
    },
    formatTime(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      const now = new Date();
      const diff = Math.floor((now - date) / 1000);
      if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
      if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
      return Math.floor(diff / 86400) + 'd ago';
    },
    startTimer() {
      const updateTimer = () => {
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setUTCHours(24, 0, 0, 0);
        const diff = Math.floor((tomorrow - now) / 1000);
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const el = document.getElementById('dash-timer');
        if (el) el.textContent = 'Resets in ' + h + 'h ' + m + 'm';
      };
      updateTimer();
      this.timerInterval = setInterval(updateTimer, 60000);
    },
    async fetchData() {
      try {
        const [badgesResp, forgeResp, trophiesResp] = await Promise.all([
          api.get('/api/badges'),
          api.get('/api/forge'),
          api.get('/api/forge/available-trophies')
        ]);

        if (badgesResp.status === 200) {
          this.userBadgeIds = badgesResp.data.data.map(b => b.id);
        }
        if (forgeResp.status === 200) {
          this.allTrophies = forgeResp.data.trophies || [];
        }
        if (trophiesResp.status === 200) {
          const userTrophies = trophiesResp.data.trophies || [];
          this.userTrophyIds = userTrophies.map(t => t.id);
        }
      } catch (e) {
        console.log('Dashboard fetch error:', e);
      }

      try {
        const notifResp = await api.get('/api/notifications');
        if (notifResp.status === 200) {
          this.notifications = notifResp.data.data || notifResp.data || [];
        }
      } catch (e) {
        console.log('Notifications fetch error:', e);
      }
    }
  },
  mounted() {
    this.fetchData();
    this.startTimer();
  },
  beforeUnmount() {
    if (this.timerInterval) clearInterval(this.timerInterval);
  }
});
</script>

<style scoped>
.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
  gap: 20px;
}

.dash-title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 24px;
  font-weight: 400;
  margin: 0;
}

.dash-tagline {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  margin: 4px 0 0;
}

.dash-currencies {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-shrink: 0;
}

.dash-currency {
  text-align: right;
}

.dash-currency__value {
  display: block;
  font-family: 'Share Tech Mono', monospace;
  font-size: 20px;
}

.dash-currency__value--ambar { color: #ff6100; }
.dash-currency__value--uru { color: #c1f527; }

.dash-currency__label {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.dash-currency__sep {
  width: 1px;
  height: 28px;
  background: #2a2c2e;
}

/* Sections */
.dash-section {
  margin-bottom: 32px;
}

.dash-section__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.dash-section__label {
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.dash-section__timer {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

.dash-section__meta {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

/* Missions */
.dash-missions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.dash-mission {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 14px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: border-color 0.15s;
}

.dash-mission:hover {
  border-color: #3a3d40;
}

.dash-mission--done {
  border-color: rgba(193, 245, 39, 0.2);
}

.dash-mission__info {
  flex: 1;
  min-width: 0;
}

.dash-mission__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}

.dash-mission__check {
  color: #c1f527;
  margin-right: 4px;
}

.dash-mission__desc {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  margin-top: 2px;
}

.dash-mission__reward {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 4px;
  white-space: nowrap;
  margin-left: 12px;
  flex-shrink: 0;
}

.dash-mission__reward--done {
  background: rgba(193, 245, 39, 0.1);
  color: #c1f527;
}

/* Trophy Grid */
.dash-trophy-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.dash-trophy {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px;
  cursor: pointer;
  transition: border-color 0.15s;
}

.dash-trophy:hover {
  border-color: #3a3d40;
}

.dash-trophy--ready {
  border-color: #ff6100;
}

.dash-trophy__top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
}

.dash-trophy__icon {
  width: 40px;
  height: 40px;
  background: #1a1c1f;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.dash-trophy__icon img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.dash-trophy__icon span {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 16px;
}

.dash-trophy__xp {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 3px;
}

.dash-trophy__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dash-trophy__progress-text {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  margin-top: 4px;
}

.dash-trophy__bar {
  width: 100%;
  height: 3px;
  background: #252729;
  border-radius: 2px;
  margin-top: 6px;
  overflow: hidden;
}

.dash-trophy__fill {
  height: 100%;
  border-radius: 2px;
  background: #c1f527;
  transition: width 0.3s;
}

.dash-trophy--ready .dash-trophy__fill {
  background: #ff6100;
}

.dash-trophy__action {
  margin-top: 8px;
  background: #ff6100;
  color: #000003;
  text-align: center;
  padding: 6px;
  border-radius: 4px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

.dash-trophy__missing {
  margin-top: 8px;
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-align: center;
}

/* Activity */
.dash-activity {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.dash-activity-row {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.dash-activity-row__dot {
  width: 8px;
  height: 8px;
  min-width: 8px;
  background: #c1f527;
  border-radius: 50%;
}

.dash-activity-row__text {
  flex: 1;
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dash-activity-row__time {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  flex-shrink: 0;
}

/* Platforms */
.dash-platforms {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.dash-platform {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: border-color 0.15s;
}

.dash-platform--connected {
  border-color: rgba(193, 245, 39, 0.2);
  cursor: default;
}

.dash-platform:not(.dash-platform--connected):hover {
  border-color: #ff6100;
}

.dash-platform__dot {
  width: 8px;
  height: 8px;
  background: #5a5550;
  border-radius: 50%;
}

.dash-platform__dot--on {
  background: #c1f527;
}

.dash-platform__name {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

.dash-platform__status {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

.dash-platform__connect {
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

/* Responsive */
@media (max-width: 768px) {
  .dash-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .dash-currencies {
    width: 100%;
    justify-content: flex-start;
  }

  .dash-missions {
    grid-template-columns: 1fr;
  }

  .dash-trophy-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 480px) {
  .dash-trophy-grid {
    grid-template-columns: 1fr;
  }
}
</style>
```

**Note on `hasPlatformBadges`:** This method currently returns true for all badge IDs because we don't have the integration info in the badge ID list. A better approach: fetch the full badges data and check the `integration` field. Update the `fetchData` method to store the full badges array:

Replace `this.userBadgeIds = badgesResp.data.data.map(b => b.id);` with:

```js
this.userBadges = badgesResp.data.data;
this.userBadgeIds = this.userBadges.map(b => b.id);
```

Add `userBadges: []` to `data()`.

Then update `hasPlatformBadges`:

```js
hasPlatformBadges(platform) {
  return this.userBadges.some(b => b.integration === platform);
},
```

**Verify:** `npm run dev` — no errors on the new file.

---

## Step 2: Add Dashboard Route

**File:** `resources/web/js/router/routes.js`

Add import at top:
```js
import Dashboard from "../pages/Dashboard.vue";
```

Add as first child of the Main route (before `/profile`):
```js
{
    path: '/dashboard',
    component: Dashboard,
    name: 'dashboard'
},
```

Change the catch-all redirect at the bottom from:
```js
{
    path: '',
    redirect: '/profile',
},
```
to:
```js
{
    path: '',
    redirect: '/dashboard',
},
```

**Verify:** `npm run dev` → navigate to `/#/dashboard`. Page should load with the dashboard layout.

---

## Step 3: Update Login/Signup Redirects

**File:** `resources/web/js/pages/Login.vue`

In the `<script>`, find both occurrences of:
```js
router.push('/trophy-room');
```
Replace with:
```js
router.push('/dashboard');
```

There are two: one in `signIn()` and one in `signInContinue()`.

**File:** `resources/web/js/pages/Signup.vue`

In the `<script>`, find:
```js
router.push('/trophy-room');
```
Replace with:
```js
router.push('/dashboard');
```

**Verify:** `npm run dev` → login → should land on `/#/dashboard`.

---

## Step 4: Add Dashboard to Sidebar

**File:** `resources/web/js/components/sidebar.vue`

Add a new nav item as the FIRST item in the sidebar menu (before Trophy Room). Use a home/dashboard icon. The SVG can be a simple grid icon:

```html
<router-link to="/dashboard" :class="{ active_item: $route.name === 'dashboard' }">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="3" y="3" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
    <rect x="13" y="3" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
    <rect x="3" y="13" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
    <rect x="13" y="13" width="8" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
  </svg>
  <span>Dashboard</span>
</router-link>
```

**Verify:** `npm run dev` → sidebar shows Dashboard as first item, active state works.

---

## Step 5: Simplify Header

**File:** `resources/web/js/components/main-header.vue`

### 5a. Remove currencies from desktop
Find and REMOVE these three blocks (lines ~17-35):
```html
<div v-if="isDesktop" class="header_achievement">
    <img src="..." alt="circle" title="Uru">
    <span>...</span>
</div>
```
Remove all three (Uru, Ambar, Rune desktop blocks).

### 5b. Remove currencies from mobile
Find and REMOVE the mobile currency blocks (around lines 65-82):
```html
<div class="header_achievement">
    <img ...>
    <span>{{$store.state.user.balances.uru}}</span>
</div>
```
Remove all three mobile currency blocks.

### 5c. Remove the separator
Find and REMOVE:
```html
<div v-if="!isMobile" class="separator_vertical"></div>
```

### 5d. Change bell notification behavior
Find the bell click handler `@click="openCloseMessageNotification"` and change it to navigate to dashboard:

Replace:
```html
<div class="header_notification_indicator" @click="openCloseMessageNotification" ref="headerDropdownNotification">
```
With:
```html
<div class="header_notification_indicator" @click="$router.push('/dashboard')" ref="headerDropdownNotification">
```

Remove the notification dropdown that opens below the bell:
```html
<div class="header_message_notification_wrapper">
    <ambar-messages-notification v-if="store.state.messageNotification.show"></ambar-messages-notification>
</div>
```
Remove this entire block. The bell still shows the unread count badge but clicking goes to dashboard.

### 5e. Clean up the header layout
The header should now contain only:
- **Mobile:** Logo (left) + Bell with count (right) + Avatar (right) + Hamburger (right)
- **Desktop:** Bell with count + Avatar + Username + Dropdown arrow

Remove the `virtual-hall-logo` block at the top since we handle that in Main.vue now:
```html
<router-link to="/trophy-room" class="virtual-hall-logo" v-if="$route.name === 'virtual-hall'">
```
Remove this entire block.

### 5f. Update header styles in style.scss

Find `.header_achievement` styles and related currency styles in `style.scss`. Add a comment marking them as deprecated but don't delete yet (other pages might reference them).

Remove the mobile currency row styles if they exist as a separate block.

**Verify:** `npm run dev` → header shows only bell + avatar + username. No currencies. Bell click goes to dashboard. Mobile: logo + bell + avatar + hamburger.

---

## Step 6: Final Verification

```bash
npm run dev
```

Check:
- [ ] `/#/dashboard` loads with all sections (currencies, missions, trophies, activity, platforms)
- [ ] Currencies (Ambar/Uru) show correct values from Vuex store
- [ ] Daily missions timer shows countdown to midnight UTC
- [ ] Mission cards are clickable and navigate to correct pages
- [ ] Trophies in progress show with correct badge counts and progress bars
- [ ] "Forge now" on ready trophies navigates to `/#/forge`
- [ ] Recent activity shows notifications (may be empty if no notifications)
- [ ] Connected platforms show correct status
- [ ] "connect" on unconnected platforms triggers OAuth redirect
- [ ] Header: no currencies visible on any page
- [ ] Header: bell click goes to `/#/dashboard`
- [ ] Sidebar: Dashboard is first item, active state works
- [ ] Login redirects to `/#/dashboard`
- [ ] Signup redirects to `/#/dashboard`
- [ ] Default route (`/`) redirects to `/#/dashboard`
- [ ] Mobile (375px): everything stacks, header is clean
- [ ] All other pages (Trophy Room, Forge, Feed, etc.) still work
- [ ] No console errors

Then:
```bash
npm run build
```

Commit:
```
feat: user dashboard with XP display, daily missions, trophies in progress, simplified header
```

Deploy:
```bash
git push origin main
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build"
```

---

## Development Rules
1. One change at a time. Verify after each step.
2. Don't break existing pages.
3. Vue 3 Options API ONLY.
4. Mobile-first. Test at 375px.
5. Share Tech Mono only.

# CLAUDE_PHASE_5A_OPS.md — Unify Network into Achievements (My Community tab)

> Read TROPHYROOM_WORKING_GUIDE.md and CLAUDE.md before starting.
> Execute ONE step at a time. Run `npm run dev` after each step to verify no errors.
> Vue 3 Options API ONLY. Share Tech Mono only. No Composition API, no `<script setup>`.

---

## Goal

Absorb the Network page (followers/following) into the Achievements page as a third tab called "My Community". Remove Network from the sidebar. Redirect `/network` to `/feed`. Restyle the community list to match the design system.

---

## Step 1 — Create MyCommunity.vue component

**Create file:** `resources/web/js/pages/Feed/components/MyCommunity.vue`

This component replaces the entire Network page. It contains:
- Sub-filter pills (Followers / Following) 
- Search bar to filter by name/email
- User rows with avatar, username, stats, and action button
- Uses existing API endpoints and store modal for remove/unfollow

```vue
<template>
  <div class="my-community">
    <!-- Sub-filter pills -->
    <div class="community-filters">
      <div 
        :class="['filter-pill', { active: subTab === 'followers' }]" 
        @click="switchSubTab('followers')"
      >
        Followers · {{ totalFollowers }}
      </div>
      <div 
        :class="['filter-pill', { active: subTab === 'following' }]" 
        @click="switchSubTab('following')"
      >
        Following · {{ totalFollowing }}
      </div>
    </div>

    <!-- Search -->
    <div class="community-search">
      <input 
        type="text" 
        v-model="searchQuery" 
        placeholder="Search users..." 
        class="community-search-input"
      />
    </div>

    <!-- Loading -->
    <Loader v-if="loading" />

    <!-- User list -->
    <div v-if="!loading" class="community-list">
      <div 
        v-for="user in filteredUsers" 
        :key="user.id" 
        class="community-row"
      >
        <div class="community-user-left" @click="navigateToVirtualHall(user.username)">
          <img 
            v-if="user.avatar" 
            :src="user.avatar" 
            alt="avatar" 
            class="community-avatar"
          />
          <div v-else class="community-avatar-placeholder">
            {{ getInitials(user.name || user.email) }}
          </div>
          <div class="community-user-info">
            <div class="community-username">{{ user.name || user.email }}</div>
            <div class="community-user-meta">{{ user.email }}</div>
          </div>
        </div>
        <div class="community-user-action">
          <button 
            v-if="subTab === 'followers'" 
            class="community-btn community-btn-secondary"
            @click="openRemoveModal(user.id, user.name || user.email)"
          >
            Remove
          </button>
          <button 
            v-if="subTab === 'following'" 
            class="community-btn community-btn-secondary"
            @click="openUnfollowModal(user.id, user.name || user.email)"
          >
            Unfollow
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="filteredUsers.length === 0 && !loading" class="community-empty">
        <span v-if="searchQuery">No users match "{{ searchQuery }}"</span>
        <span v-else-if="subTab === 'followers'">No followers yet</span>
        <span v-else>You're not following anyone yet</span>
      </div>
    </div>

    <!-- Pagination -->
    <PagePagination
      :items="users"
      :current-page="currentPage"
      :total="subTab === 'followers' ? totalFollowers : totalFollowing"
      :items-per-page="10"
      :method="fetchData"
    />
  </div>
</template>

<script>
import { defineComponent } from "vue";
import api from "../../../api/api.js";
import store from "../../../store/store.js";
import Loader from "../../../components/Loader.vue";
import PagePagination from "../../../components/PagePagination.vue";

export default defineComponent({
  components: {
    Loader,
    PagePagination,
  },
  data() {
    return {
      subTab: "followers",
      searchQuery: "",
      users: [],
      totalFollowers: 0,
      totalFollowing: 0,
      currentPage: 1,
      loading: false,
    };
  },
  computed: {
    filteredUsers() {
      if (!this.searchQuery.trim()) return this.users;
      const q = this.searchQuery.toLowerCase();
      return this.users.filter(
        (u) =>
          (u.name && u.name.toLowerCase().includes(q)) ||
          (u.email && u.email.toLowerCase().includes(q)) ||
          (u.username && u.username.toLowerCase().includes(q))
      );
    },
  },
  methods: {
    switchSubTab(tab) {
      this.subTab = tab;
      this.currentPage = 1;
      this.users = [];
      this.searchQuery = "";
      this.fetchData(1);
    },
    async fetchData(page) {
      this.loading = true;
      this.currentPage = page;
      const endpoint =
        this.subTab === "followers"
          ? "/api/follow/followers?page=" + page
          : "/api/follow/following?page=" + page;

      try {
        const response = await api.get(endpoint);
        if (response && response.data) {
          this.users = response.data[0].data;
          const total = response.data[0].total;

          if (this.subTab === "followers") {
            this.totalFollowers = total;
            store.state.totalFollowers = total;
            if (response.data.totalFollowing !== undefined) {
              this.totalFollowing = response.data.totalFollowing;
              store.state.totalFollowing = response.data.totalFollowing;
            }
          } else {
            this.totalFollowing = total;
            store.state.totalFollowing = total;
            if (response.data.totalFollowers !== undefined) {
              this.totalFollowers = response.data.totalFollowers;
              store.state.totalFollowers = response.data.totalFollowers;
            }
          }
        }
      } catch (error) {
        console.error("Community fetching data error:", error);
      } finally {
        this.loading = false;
      }
    },
    getInitials(name) {
      if (!name) return "?";
      return name
        .split(" ")
        .map((w) => w[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
    },
    navigateToVirtualHall(username) {
      if (username) {
        window.open("/virtual-hall/" + username, "_blank");
      }
    },
    openRemoveModal(userId, userName) {
      store.state.networkRemoveUnfollowModal.title =
        "Remove follower " + userName + "?";
      store.state.networkRemoveUnfollowModal.btn_text = "Remove";
      store.state.networkRemoveUnfollowModal.action = "api/follow/destroy";
      store.state.networkRemoveUnfollowModal.show = true;
      store.state.networkRemoveUnfollowModal.user_id = userId;
    },
    openUnfollowModal(userId, userName) {
      store.state.networkRemoveUnfollowModal.title =
        "Unfollowing from " + userName + "?";
      store.state.networkRemoveUnfollowModal.btn_text = "Unfollow";
      store.state.networkRemoveUnfollowModal.action = "api/follow/action";
      store.state.networkRemoveUnfollowModal.show = true;
      store.state.networkRemoveUnfollowModal.user_id = userId;
    },
  },
  mounted() {
    this.fetchData(1);
  },
});
</script>

<style scoped>
.my-community {
  padding-top: 4px;
}

.community-filters {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.filter-pill {
  padding: 6px 16px;
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #9a9590;
  border: 1px solid #2a2c2e;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-pill:hover {
  color: #feeddf;
  border-color: #5a5550;
}

.filter-pill.active {
  background: #c1f527;
  color: #000003;
  border-color: #c1f527;
}

.community-search {
  margin-bottom: 16px;
}

.community-search-input {
  width: 100%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 10px 14px;
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s;
}

.community-search-input::placeholder {
  color: #5a5550;
}

.community-search-input:focus {
  border-color: #ff6100;
}

.community-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.community-row {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  transition: border-color 0.2s;
}

.community-row:hover {
  border-color: rgba(255, 97, 0, 0.3);
}

.community-user-left {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  flex: 1;
  min-width: 0;
}

.community-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.community-avatar-placeholder {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #9a9590;
  flex-shrink: 0;
}

.community-user-info {
  min-width: 0;
}

.community-username {
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.community-user-meta {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.community-btn {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  padding: 5px 14px;
  border-radius: 4px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
  border: none;
}

.community-btn-secondary {
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
}

.community-btn-secondary:hover {
  border-color: #e24b4a;
  color: #e24b4a;
}

.community-empty {
  padding: 40px 0;
  text-align: center;
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #5a5550;
}

@media (max-width: 520px) {
  .community-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  .community-user-action {
    width: 100%;
  }
  .community-btn {
    width: 100%;
    text-align: center;
  }
}
</style>
```

**After creating the file, run:** `npm run dev`

---

## Step 2 — Update Feed.vue to add "My Community" tab

**Edit file:** `resources/web/js/pages/Feed/Feed.vue`

Replace the ENTIRE file content with:

```vue
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
    // Check if redirected from /network with tab param
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
```

**After editing, run:** `npm run dev`

---

## Step 3 — Update routes.js: redirect /network → /feed?tab=community

**Edit file:** `resources/web/js/router/routes.js`

Find and replace the network route:

```js
// FIND THIS:
            {
                path: '/network',
                component: Network,
                name: 'network'
            },

// REPLACE WITH:
            {
                path: '/network',
                redirect: { name: 'feed', query: { tab: 'community' } }
            },
```

Also remove the Network import at the top of the file. Find and delete this line:

```js
import Network from "../pages/Network/Network.vue";
```

**After editing, run:** `npm run dev`

---

## Step 4 — Update sidebar: remove Network, rename Brand Dashboard

**Edit file:** `resources/web/js/components/sidebar.vue`

**4a.** Find the ENTIRE Network `<li>` block and DELETE it. It starts with:
```html
            <li>
                <router-link to="/network" :class="{ active_item: $route.path === '/network' }">
```
And ends with:
```html
              </router-link>
            </li>
```
Delete everything from that `<li>` to its closing `</li>`, including both SVG variants.

**4b.** Find the Brand Dashboard `<li>` block and change the label text from `Brand Dashboard` to `Admin Panel`:

Find:
```html
                    <span>Brand Dashboard</span>
```
Replace with:
```html
                    <span>Admin Panel</span>
```

**After editing, run:** `npm run dev`

---

## Step 5 — Verify and commit

Run these checks:

1. `npm run dev` — should compile with zero errors
2. Open browser, navigate to `/feed` — should show 3 tabs: Following, My posts, My community
3. Click "My community" tab — should show filter pills (Followers/Following), search bar, user list
4. Navigate to `/network` — should redirect to `/feed?tab=community`
5. Sidebar should NOT have "Network" item
6. Brand Dashboard should now say "Admin Panel"

**If all checks pass, commit:**

```bash
git add -A && git commit -m "feat: unify Network into Achievements as My Community tab, rename Brand Dashboard to Admin Panel"
```

**Do NOT push yet. Wait for Max to review.**

---

## Files changed summary

| File | Action |
|------|--------|
| `resources/web/js/pages/Feed/components/MyCommunity.vue` | **NEW** — community tab component |
| `resources/web/js/pages/Feed/Feed.vue` | **MODIFIED** — add 3rd tab, import MyCommunity |
| `resources/web/js/router/routes.js` | **MODIFIED** — redirect /network, remove import |
| `resources/web/js/components/sidebar.vue` | **MODIFIED** — remove Network, rename Brand Dashboard |

## Files NOT touched (preserved as-is)

| File | Reason |
|------|--------|
| `resources/web/js/pages/Network/` | Keep directory — dead code but safe to leave for now |
| Backend (`routes/api.php`, controllers) | No backend changes needed — same API endpoints |
| `store/store.js` | No changes — reuses existing `networkRemoveUnfollowModal` state |

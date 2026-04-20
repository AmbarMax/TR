<template>
  <div class="my-community">

    <!-- Sub-filter pills -->
    <div class="community-filters">
      <button
        class="cpill"
        :class="{ active: subTab === 'followers' }"
        @click="switchSubTab('followers')"
      >
        Followers · {{ totalFollowers }}
      </button>
      <button
        class="cpill"
        :class="{ active: subTab === 'following' }"
        @click="switchSubTab('following')"
      >
        Following · {{ totalFollowing }}
      </button>
    </div>

    <!-- Search -->
    <div class="community-search-wrap">
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Search users..."
        class="community-search"
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
        <div class="community-user" @click="navigateToVirtualHall(user.username)">
          <img
            v-if="user.avatar"
            :src="user.avatar"
            alt="avatar"
            class="community-avatar"
          />
          <div v-else class="community-avatar community-avatar--initials">
            {{ getInitials(user.name || user.email) }}
          </div>
          <div class="community-user-info">
            <div class="community-username">{{ user.name || user.email }}</div>
            <div class="community-user-meta">{{ user.email }}</div>
          </div>
        </div>
        <div class="community-actions">
          <button
            v-if="subTab === 'followers'"
            class="community-action remove"
            @click="openRemoveModal(user.id, user.name || user.email)"
          >Remove</button>
          <button
            v-if="subTab === 'following'"
            class="community-action"
            @click="openUnfollowModal(user.id, user.name || user.email)"
          >Unfollow</button>
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

<style lang="scss" scoped>
.my-community {
  padding-top: 8px;
}

/* Sub-filter pills */
.community-filters {
  display: flex;
  gap: 8px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}
.cpill {
  padding: 8px 16px;
  font-size: 11px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  font-family: var(--mono);
  cursor: pointer;
  transition: all 0.15s;
}
.cpill:hover { color: var(--text); border-color: var(--text-dim); }
.cpill.active {
  color: var(--bg);
  background: var(--accent);
  border-color: var(--accent);
  box-shadow: 0 0 14px var(--accent-glow);
}

/* Search */
.community-search-wrap { margin-bottom: 18px; }
.community-search {
  width: 100%;
  padding: 12px 16px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  color: var(--text);
  font-family: var(--mono);
  font-size: 13px;
  letter-spacing: 0.03em;
  transition: border-color 0.15s;
}
.community-search:focus {
  border-color: var(--primary);
  outline: none;
}
.community-search::placeholder { color: var(--text-dim); }

/* List */
.community-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.community-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: rgba(14, 15, 17, 0.7);
  border: 1px solid rgba(42, 44, 46, 0.7);
  transition: border-color 0.15s;
}
.community-row:hover { border-color: rgba(255, 97, 0, 0.2); }

.community-user {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 0;
  cursor: pointer;
}

.community-avatar {
  width: 40px; height: 40px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: 1px solid var(--border);
}
.community-avatar--initials {
  background: linear-gradient(135deg, var(--primary), var(--accent));
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--bg);
  font-family: var(--mono);
  font-size: 12px;
  font-weight: bold;
  letter-spacing: 0.04em;
}

.community-user-info {
  flex: 1;
  min-width: 0;
}
.community-username {
  font-size: 13px;
  color: var(--text);
  letter-spacing: 0.04em;
  margin-bottom: 3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.community-user:hover .community-username { color: var(--primary); }
.community-user-meta {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.08em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Action buttons */
.community-actions {
  flex-shrink: 0;
}
.community-action {
  padding: 8px 14px;
  font-size: 10px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  font-family: var(--mono);
  cursor: pointer;
  transition: all 0.15s;
}
.community-action:hover {
  color: var(--text);
  border-color: var(--text-dim);
}
.community-action.remove:hover {
  color: #e24b4a;
  border-color: rgba(226, 75, 74, 0.35);
  background: rgba(226, 75, 74, 0.04);
}

/* Empty */
.community-empty {
  padding: 40px 20px;
  text-align: center;
  font-size: 12px;
  color: var(--text-dim);
  letter-spacing: 0.08em;
  border: 1px dashed rgba(42, 44, 46, 0.7);
}

@media (max-width: 700px) {
  .community-row { padding: 12px 14px; gap: 12px; flex-wrap: wrap; }
  .community-user-meta { display: none; }
}
</style>

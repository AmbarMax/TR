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

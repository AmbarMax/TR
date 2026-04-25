<template>
  <div class="hall-page">
    <div v-if="loading" class="hall-loading">
      <p>Loading profile…</p>
    </div>
    <PlayerHallView v-else-if="user && resolvedAccountType === 'player'" :user="user" />
    <BrandHallView v-else-if="user && resolvedAccountType === 'brand'" :user="user" />
    <div v-else-if="user" class="hall-loading">
      <p>Loading profile…</p>
    </div>
  </div>
</template>

<script>
import api from "../../api/api.js";
import PlayerHallView from "./PlayerHallView.vue";
import BrandHallView from "./BrandHallView.vue";

export default {
  name: "Hall",
  components: { PlayerHallView, BrandHallView },
  data() {
    return {
      user: null,
      loading: true,
    };
  },
  computed: {
    routeUsername() {
      return this.$route.params.username;
    },
    resolvedAccountType() {
      const t = this.user?.account_type;
      return t === "brand" ? "brand" : "player";
    },
  },
  watch: {
    routeUsername: {
      immediate: false,
      handler(newVal, oldVal) {
        if (newVal && newVal !== oldVal) {
          this.fetchUser();
        }
      },
    },
  },
  mounted() {
    this.fetchUser();
  },
  methods: {
    async fetchUser() {
      this.loading = true;
      this.user = null;
      try {
        const { data } = await api.get(`/api/users/${encodeURIComponent(this.routeUsername)}`);
        this.user = data?.data ?? data;
      } catch (e) {
        if (e.response?.status === 404) {
          this.$router.replace("/login");
          return;
        }
        console.error("Hall fetch failed", e);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.hall-page {
  min-height: 100vh;
  background: #000003;
  color: #fff;
}
.hall-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  color: #888;
  font-family: system-ui, sans-serif;
}
</style>

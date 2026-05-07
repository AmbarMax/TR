<template>
  <BrandDashboardV2 v-if="isBrand" />
  <Dashboard v-else />
</template>

<script>
import Dashboard from './Dashboard.vue';
import BrandDashboardV2 from './BrandDashboard/BrandDashboardV2.vue';

export default {
  name: 'DashboardResolver',
  components: { Dashboard, BrandDashboardV2 },
  computed: {
    isBrand() {
      return this.$store.getters.isBrand;
    },
  },
};
</script>

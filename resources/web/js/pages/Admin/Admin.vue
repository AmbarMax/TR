<template>
  <div class="admin-panel">
    <!-- HERO -->
    <div class="page-hero">
      <div class="page-hero-bg"></div>
      <div class="page-hero-inner">
        <div class="page-tag">TR Admin</div>
        <h1 class="page-title">Admin <span class="accent-word">Panel</span></h1>
      </div>
    </div>

    <!-- TABS -->
    <div class="tabs-bar">
      <router-link
        v-for="tab in tabs"
        :key="tab.id"
        :to="tab.to"
        class="tab"
        :class="{ 'tab--active': isActiveTab(tab) }"
      >
        {{ tab.label }}
      </router-link>
    </div>

    <!-- CONTENT -->
    <div class="ap-content">
      <router-view />

      <!-- TERMINAL -->
      <div class="terminal-strip">
        <div>admin · tr console · {{ currentTabLabel }}<span class="cursor-blink"></span></div>
        <div>{{ statusLabel }}</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Admin",
  data() {
    return {
      tabs: [
        { id: "brands", label: "Brands",    to: "/admin/brands", name: "admin.brands" },
        { id: "roles",  label: "Roles",     to: "/admin/roles",  name: "admin.roles"  },
        { id: "audit",  label: "Audit Log", to: "/admin/audit",  name: "admin.audit"  },
      ],
    };
  },
  computed: {
    currentTab() {
      return this.tabs.find(t => t.name === this.$route.name) || this.tabs[0];
    },
    currentTabLabel() {
      return this.currentTab.label.toLowerCase();
    },
    statusLabel() {
      return this.$store?.getters?.isSuperAdmin ? "tr_superadmin" : "tr_admin";
    },
  },
  methods: {
    isActiveTab(tab) {
      return this.$route.name === tab.name;
    },
  },
};
</script>

<style>
/* Selectors prefixed with .admin-panel so they don't leak. The Admin
   page mounts inside Main.vue's <router-view> so the global sidebar +
   main-header are inherited from there — only the page-hero + tabs +
   content chrome lives here. */
.admin-panel {
  display: flex;
  flex-direction: column;
  min-height: 100%;
  background: var(--bg);
  color: var(--text);
  font-family: var(--mono);
}

/* HERO */
.admin-panel .page-hero {
  position: relative;
  padding: 40px 48px 0;
  overflow: hidden;
}
.admin-panel .page-hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background: radial-gradient(ellipse 700px 400px at 90% 40%, rgba(255, 97, 0, 0.1), transparent 60%);
  pointer-events: none;
}
.admin-panel .page-hero-inner {
  position: relative;
  z-index: 2;
  padding-bottom: 0;
}
.admin-panel .page-tag {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 10px;
  color: var(--primary);
  letter-spacing: 0.32em;
  text-transform: uppercase;
  margin-bottom: 12px;
}
.admin-panel .page-tag::before {
  content: '';
  width: 28px;
  height: 1px;
  background: var(--primary);
  box-shadow: 0 0 6px var(--primary);
}
.admin-panel .page-title {
  font-family: var(--display);
  font-size: 56px;
  line-height: 0.95;
  color: var(--text);
  letter-spacing: 0.015em;
  margin: 0 0 6px;
  text-shadow: 0 0 30px rgba(255, 97, 0, 0.18);
  font-weight: 400;
}
.admin-panel .page-title .accent-word {
  color: var(--primary);
  text-shadow: 0 0 22px var(--primary-glow);
}

/* TABS */
.admin-panel .tabs-bar {
  padding: 0 48px;
  border-bottom: 1px solid rgba(255, 97, 0, 0.1);
  display: flex;
  gap: 28px;
  background: rgba(5, 5, 8, 0.5);
  position: sticky;
  top: 64px;
  z-index: 30;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  margin-top: 32px;
}
.admin-panel .tab {
  padding: 16px 0;
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--text-muted);
  border: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: all 0.15s;
  cursor: pointer;
  background: transparent;
  text-decoration: none;
}
.admin-panel .tab:hover {
  color: var(--text);
}
.admin-panel .tab--active {
  color: var(--primary);
  border-bottom-color: var(--primary);
  text-shadow: 0 0 10px var(--primary-glow);
}

/* CONTENT */
.admin-panel .ap-content {
  padding: 40px 48px 80px;
  max-width: 1600px;
  flex: 1;
}

/* TERMINAL */
.admin-panel .terminal-strip {
  margin-top: 32px;
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  display: flex;
  justify-content: space-between;
  padding-top: 20px;
  border-top: 1px solid rgba(42, 44, 46, 0.5);
}
.admin-panel .cursor-blink {
  display: inline-block;
  width: 8px;
  height: 11px;
  background: var(--primary);
  margin-left: 4px;
  vertical-align: middle;
  animation: admin-panel-blink 1s steps(1) infinite;
}
@keyframes admin-panel-blink {
  50% { opacity: 0; }
}

/* RESPONSIVE */
@media (max-width: 1100px) {
  .admin-panel .page-hero { padding: 32px 32px 0; }
  .admin-panel .tabs-bar { padding: 0 32px; }
  .admin-panel .ap-content { padding: 32px 32px 60px; }
}
@media (max-width: 700px) {
  .admin-panel .page-hero { padding: 28px 20px 0; }
  .admin-panel .page-title { font-size: 40px; }
  .admin-panel .tabs-bar {
    padding: 0 20px;
    gap: 16px;
    top: 56px;
    overflow-x: auto;
  }
  .admin-panel .ap-content { padding: 24px 20px 60px; }
}
</style>

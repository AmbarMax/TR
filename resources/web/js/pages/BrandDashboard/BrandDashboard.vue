<template>
    <div class="brand-dashboard">
        <div class="bd-header">
            <div class="bd-header__title">
                <span class="bd-section-label">Brand Admin</span>
                <h1 class="bd-title">Dashboard</h1>
            </div>
            <nav class="bd-tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    class="bd-tab"
                    :class="{ 'bd-tab--active': activeTab === tab.id }"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </nav>
        </div>

        <div class="bd-content">
            <dashboard-overview v-if="activeTab === 'overview'" />
            <trophy-manager    v-if="activeTab === 'trophies'" />
            <badge-manager     v-if="activeTab === 'badges'" />
            <poll-manager      v-if="activeTab === 'polls'" />
            <event-manager     v-if="activeTab === 'events'" />
        </div>
    </div>
</template>

<script>
import DashboardOverview from './DashboardOverview.vue';
import TrophyManager     from './TrophyManager.vue';
import BadgeManager      from './BadgeManager.vue';
import PollManager       from './PollManager.vue';
import EventManager      from './EventManager.vue';

export default {
    components: { DashboardOverview, TrophyManager, BadgeManager, PollManager, EventManager },
    data() {
        return {
            activeTab: 'overview',
            tabs: [
                { id: 'overview',  label: 'Overview'  },
                { id: 'trophies',  label: 'Trophies'  },
                { id: 'badges',    label: 'Badges'    },
                { id: 'polls',     label: 'Polls'     },
                { id: 'events',    label: 'Events'    },
            ],
        };
    },
}
</script>

<style scoped>
.brand-dashboard {
    display: flex;
    flex-direction: column;
    min-height: 100%;
    background-color: #000003;
}

.bd-header {
    padding: 32px 40px 0;
    border-bottom: 1px solid #2a2c2e;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.bd-section-label {
    display: block;
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #ff6100;
    text-transform: uppercase;
    letter-spacing: 0.12em;
}

.bd-title {
    font-family: 'Share Tech Mono', monospace;
    font-size: 24px;
    color: #feeddf;
    font-weight: 400;
    margin: 4px 0 0;
}

.bd-tabs {
    display: flex;
    flex-direction: row;
    gap: 0;
}

.bd-tab {
    padding: 10px 24px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 14px;
    color: #9a9590;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: color 0.15s, border-color 0.15s;
}

.bd-tab:hover {
    color: #feeddf;
}

.bd-tab--active {
    color: #c1f527;
    border-bottom-color: #c1f527;
}

.bd-content {
    padding: 32px 40px;
    flex: 1;
}

@media (max-width: 768px) {
    .bd-header {
        padding: 24px 20px 0;
    }
    .bd-content {
        padding: 24px 20px;
    }
    .bd-tab {
        padding: 10px 16px;
        font-size: 13px;
    }
}
</style>

<template>
    <div class="admin-panel">
        <!-- HERO -->
        <div class="page-hero">
            <div class="page-hero-bg"></div>
            <div class="page-hero-inner">
                <div class="page-tag">Brand Admin</div>
                <h1 class="page-title">Admin <span class="accent-word">Panel</span></h1>
            </div>
        </div>

        <!-- TABS -->
        <div class="tabs-bar">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                class="tab"
                :class="{ 'tab--active': activeTab === tab.id }"
                @click="activeTab = tab.id"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- CONTENT -->
        <div class="ap-content">
            <dashboard-overview v-if="activeTab === 'overview'" />
            <trophy-manager    v-if="activeTab === 'trophies'" />
            <badge-manager     v-if="activeTab === 'badges'" />
            <poll-manager      v-if="activeTab === 'polls'" />
            <event-manager     v-if="activeTab === 'events'" />

            <!-- TERMINAL -->
            <div class="terminal-strip">
                <div>admin · brand dashboard · {{ activeTab }}<span class="cursor-blink"></span></div>
                <div>nominal</div>
            </div>
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
.admin-panel {
    display: flex;
    flex-direction: column;
    min-height: 100%;
    background: var(--bg);
    color: var(--text);
    font-family: var(--mono);
}

/* HERO */
.page-hero {
    position: relative;
    padding: 40px 48px 0;
    overflow: hidden;
}
.page-hero-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
    background: radial-gradient(ellipse 700px 400px at 90% 40%, rgba(255, 97, 0, 0.1), transparent 60%);
    pointer-events: none;
}
.page-hero-inner {
    position: relative;
    z-index: 2;
    padding-bottom: 0;
}
.page-tag {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 10px;
    color: var(--primary);
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin-bottom: 12px;
}
.page-tag::before {
    content: '';
    width: 28px;
    height: 1px;
    background: var(--primary);
    box-shadow: 0 0 6px var(--primary);
}
.page-title {
    font-family: var(--display);
    font-size: 56px;
    line-height: 0.95;
    color: var(--text);
    letter-spacing: 0.015em;
    margin: 0 0 6px;
    text-shadow: 0 0 30px rgba(255, 97, 0, 0.18);
    font-weight: 400;
}
.page-title .accent-word {
    color: var(--primary);
    text-shadow: 0 0 22px var(--primary-glow);
}

/* TABS */
.tabs-bar {
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
.tab {
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
}
.tab:hover {
    color: var(--text);
}
.tab--active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    text-shadow: 0 0 10px var(--primary-glow);
}

/* CONTENT */
.ap-content {
    padding: 40px 48px 80px;
    max-width: 1400px;
    flex: 1;
}

/* TERMINAL */
.terminal-strip {
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
.cursor-blink {
    display: inline-block;
    width: 8px;
    height: 11px;
    background: var(--primary);
    margin-left: 4px;
    vertical-align: middle;
    animation: ap-blink 1s steps(1) infinite;
}
@keyframes ap-blink {
    50% { opacity: 0; }
}

/* RESPONSIVE */
@media (max-width: 1100px) {
    .page-hero { padding: 32px 32px 0; }
    .tabs-bar { padding: 0 32px; }
    .ap-content { padding: 32px 32px 60px; }
}
@media (max-width: 700px) {
    .page-hero { padding: 28px 20px 0; }
    .page-title { font-size: 40px; }
    .tabs-bar {
        padding: 0 20px;
        gap: 16px;
        top: 56px;
        overflow-x: auto;
    }
    .ap-content { padding: 24px 20px 60px; }
}
</style>

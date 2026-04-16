<template>
    <div class="bd-overview">
        <div class="bd-stats-grid">
            <div class="bd-stat-card">
                <span class="bd-stat-label">Linked Users</span>
                <span class="bd-stat-value">{{ stats.linkedUsers }}</span>
            </div>
            <div class="bd-stat-card">
                <span class="bd-stat-label">Active Rules</span>
                <span class="bd-stat-value">{{ stats.activeRules }}</span>
            </div>
            <div class="bd-stat-card">
                <span class="bd-stat-label">Synced Channels</span>
                <span class="bd-stat-value">{{ stats.channels }}</span>
            </div>
            <div class="bd-stat-card">
                <span class="bd-stat-label">Badges Granted</span>
                <span class="bd-stat-value">{{ stats.badgesGranted }}</span>
            </div>
        </div>

        <div class="bd-guild-status">
            <span class="bd-section-label">Guild Connection</span>
            <div v-if="guild" class="bd-guild-card">
                <div class="bd-guild-info">
                    <span class="bd-guild-name">{{ guild.name }}</span>
                    <span class="bd-guild-badge bd-guild-badge--connected">Connected</span>
                </div>
            </div>
            <div v-else-if="loading" class="bd-empty">Loading...</div>
            <div v-else class="bd-empty">No guild connected.</div>
        </div>

        <div class="bd-activity">
            <span class="bd-section-label">Recent Activity</span>
            <div v-if="loading" class="bd-empty">Loading...</div>
            <div v-else-if="activity.length === 0" class="bd-empty">No recent activity.</div>
            <ul v-else class="bd-activity-list">
                <li v-for="(item, i) in activity" :key="i" class="bd-activity-item">
                    <span class="bd-activity-user">{{ item.user }}</span>
                    <span class="bd-activity-badge">{{ item.badge }}</span>
                    <span class="bd-activity-time">{{ item.time }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import api from './botApi.js';

export default {
    data() {
        return {
            loading: true,
            guild: null,
            activity: [],
            stats: { linkedUsers: '—', activeRules: '—', channels: '—', badgesGranted: '—' },
        };
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            try {
                const res = await api.get('/api/brand/stats');
                const d = res.data;
                this.stats.linkedUsers   = d.linked_users   ?? '—';
                this.stats.activeRules   = d.active_rules   ?? '—';
                this.stats.channels      = d.synced_channels ?? '—';
                this.stats.badgesGranted = d.badges_granted  ?? '—';
                this.activity            = d.recent_activity ?? [];
            } catch (e) {
                // silently fail
            } finally {
                this.loading = false;
            }
        },
    },
}
</script>

<style scoped>
.bd-overview { display: flex; flex-direction: column; gap: 32px; }

.bd-section-label {
    display: block;
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #ff6100;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    margin-bottom: 12px;
}

.bd-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.bd-stat-card {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    transition: border-color 0.15s;
}

.bd-stat-card:hover { border-color: rgba(255, 97, 0, 0.3); }

.bd-stat-label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #9a9590;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.bd-stat-value {
    font-family: 'Share Tech Mono', monospace;
    font-size: 28px;
    color: #feeddf;
}

.bd-guild-card {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 16px 20px;
}

.bd-guild-info { display: flex; align-items: center; gap: 12px; }

.bd-guild-name {
    font-family: 'Share Tech Mono', monospace;
    font-size: 16px;
    color: #feeddf;
}

.bd-guild-badge {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
}

.bd-guild-badge--connected { background: rgba(193, 245, 39, 0.1); color: #c1f527; }

.bd-activity-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.bd-activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 12px 16px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
}

.bd-activity-user  { color: #feeddf; flex: 1; }
.bd-activity-badge { color: #c1f527; }
.bd-activity-time  { color: #5a5550; font-size: 11px; }

.bd-empty {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    color: #5a5550;
    padding: 16px 0;
}

@media (max-width: 768px) {
    .bd-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .bd-stats-grid { grid-template-columns: 1fr; }
}
</style>

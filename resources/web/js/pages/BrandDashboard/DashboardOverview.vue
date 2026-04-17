<template>
    <div class="bd-overview">

        <!-- Guild selector modal (shown after OAuth callback) -->
        <div v-if="guildModal.open" class="bd-modal-overlay" @click.self="guildModal.open = false">
            <div class="bd-modal">
                <span class="bd-section-label">Select Your Discord Server</span>
                <p class="bd-modal-hint">You have admin access in these servers. Select one to connect.</p>
                <div v-if="guildModal.guilds.length === 0" class="bd-empty">No servers found where you have Administrator permission.</div>
                <ul v-else class="bd-guild-list">
                    <li
                        v-for="g in guildModal.guilds"
                        :key="g.id"
                        class="bd-guild-option"
                        :class="{ 'bd-guild-option--selected': guildModal.selected === g.id }"
                        @click="guildModal.selected = g.id; guildModal.selectedName = g.name"
                    >
                        <span class="bd-guild-option__name">{{ g.name }}</span>
                        <span class="bd-guild-option__id">{{ g.id }}</span>
                    </li>
                </ul>
                <div class="bd-modal-actions">
                    <button
                        class="bd-btn bd-btn--primary"
                        :disabled="!guildModal.selected || guildModal.saving"
                        @click="confirmGuildSelect"
                    >
                        {{ guildModal.saving ? 'Connecting…' : 'Connect' }}
                    </button>
                    <button class="bd-btn bd-btn--ghost" @click="guildModal.open = false">Cancel</button>
                </div>
                <p v-if="guildModal.error" class="bd-modal-error">{{ guildModal.error }}</p>
            </div>
        </div>

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

            <div v-if="loadingGuild" class="bd-empty">Loading…</div>

            <div v-else-if="guild" class="bd-guild-card">
                <div class="bd-guild-info">
                    <span class="bd-guild-name">{{ guild.guild_name }}</span>
                    <span class="bd-guild-badge bd-guild-badge--connected">Connected</span>
                </div>
                <button class="bd-btn bd-btn--ghost bd-btn--sm" :disabled="disconnecting" @click="disconnect">
                    {{ disconnecting ? 'Disconnecting…' : 'Disconnect' }}
                </button>
            </div>

            <div v-else class="bd-guild-connect">
                <p class="bd-guild-connect__hint">Connect your Discord server to enable rules, polls, events, and badge granting.</p>
                <button class="bd-btn bd-btn--primary" @click="connectDiscord">
                    Connect Discord Server
                </button>
                <p v-if="guildError" class="bd-connect-error">Connection failed. Please try again.</p>
            </div>
        </div>

        <div class="bd-activity">
            <span class="bd-section-label">Recent Activity</span>
            <div v-if="loading" class="bd-empty">Loading…</div>
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
            loadingGuild: true,
            disconnecting: false,
            guildError: false,
            guild: null,
            activity: [],
            stats: { linkedUsers: '—', activeRules: '—', channels: '—', badgesGranted: '—' },
            guildModal: {
                open: false,
                guilds: [],
                selected: null,
                selectedName: '',
                saving: false,
                error: '',
            },
        };
    },
    mounted() {
        this.checkGuildsCallback();
        this.load();
        this.loadGuild();
    },
    methods: {
        checkGuildsCallback() {
            const params = new URLSearchParams(window.location.search);
            const raw = params.get('guilds');
            const error = params.get('guild_error');

            if (error) {
                this.guildError = true;
                window.history.replaceState({}, '', '/brand-dashboard');
                return;
            }

            if (!raw) return;

            try {
                const decoded = JSON.parse(atob(decodeURIComponent(raw)));
                if (decoded.guilds) {
                    this.guildModal.guilds = decoded.guilds;
                    this.guildModal.open = true;
                }
            } catch (e) {
                console.error('[DashboardOverview] failed to parse guilds callback', e);
            }

            window.history.replaceState({}, '', '/brand-dashboard');
        },

        async loadGuild() {
            this.loadingGuild = true;
            try {
                const res = await api.get('/api/brand/guild');
                this.guild = res.data?.guild ?? null;
            } catch (e) {
                console.error('[DashboardOverview] guild load failed', e);
            } finally {
                this.loadingGuild = false;
            }
        },

        async load() {
            this.loading = true;
            try {
                const res = await api.get('/api/brand/stats');
                const d = res.data;
                this.stats.linkedUsers   = d.linked_users    ?? '—';
                this.stats.activeRules   = d.active_rules    ?? '—';
                this.stats.channels      = d.synced_channels ?? '—';
                this.stats.badgesGranted = d.badges_granted  ?? '—';
                this.activity            = d.recent_activity ?? [];
            } catch (e) {
                console.error('[DashboardOverview] stats load failed', e);
            } finally {
                this.loading = false;
            }
        },

        connectDiscord() {
            const token = localStorage.getItem('access_token');
            window.location.href = `/api/brand/guild/connect?token=${encodeURIComponent(token)}`;
        },

        async confirmGuildSelect() {
            if (!this.guildModal.selected) return;
            this.guildModal.saving = true;
            this.guildModal.error = '';
            try {
                const res = await api.post('/api/brand/guild/select', {
                    guild_id:   this.guildModal.selected,
                    guild_name: this.guildModal.selectedName,
                });
                this.guild = res.data?.guild ?? null;
                this.guildModal.open = false;
                this.guildModal.selected = null;
            } catch (e) {
                console.error('[DashboardOverview] guild select failed', e);
                this.guildModal.error = 'Failed to connect. Please try again.';
            } finally {
                this.guildModal.saving = false;
            }
        },

        async disconnect() {
            if (!confirm('Disconnect this Discord server?')) return;
            this.disconnecting = true;
            try {
                await api.delete('/api/brand/guild');
                this.guild = null;
            } catch (e) {
                console.error('[DashboardOverview] disconnect failed', e);
            } finally {
                this.disconnecting = false;
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

/* Stats */
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

/* Guild */
.bd-guild-card {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.bd-guild-info { display: flex; align-items: center; gap: 12px; flex: 1; }

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

.bd-guild-connect {
    background: #0e0f11;
    border: 1px dashed #2a2c2e;
    border-radius: 6px;
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.bd-guild-connect__hint {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    color: #5a5550;
    margin: 0;
}

.bd-connect-error {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #ff5050;
    margin: 0;
}

/* Buttons */
.bd-btn {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    padding: 8px 18px;
    transition: opacity 0.15s;
    align-self: flex-start;
}

.bd-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.bd-btn--primary { background: #c1f527; color: #000003; }
.bd-btn--ghost   { background: transparent; border: 1px solid #2a2c2e; color: #9a9590; }
.bd-btn--sm      { padding: 5px 12px; font-size: 11px; align-self: center; }

/* Modal */
.bd-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 3, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.bd-modal {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 8px;
    padding: 28px 32px;
    width: 460px;
    max-width: 92vw;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.bd-modal-hint {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #5a5550;
    margin: 0;
}

.bd-guild-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 280px;
    overflow-y: auto;
}

.bd-guild-option {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 12px 16px;
    background: #1a1c1f;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    cursor: pointer;
    transition: border-color 0.15s;
}

.bd-guild-option:hover { border-color: rgba(255, 97, 0, 0.4); }

.bd-guild-option--selected { border-color: #ff6100; background: rgba(255, 97, 0, 0.06); }

.bd-guild-option__name {
    font-family: 'Share Tech Mono', monospace;
    font-size: 14px;
    color: #feeddf;
}

.bd-guild-option__id {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #5a5550;
}

.bd-modal-actions { display: flex; gap: 12px; }

.bd-modal-error {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #ff5050;
    margin: 0;
}

/* Activity */
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

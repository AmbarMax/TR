<template>
    <div class="ap-overview">

        <!-- Guild selector modal (shown after OAuth callback) -->
        <div v-if="guildModal.open" class="ap-modal-overlay" @click.self="guildModal.open = false">
            <div class="ap-modal">
                <div class="sec-label"><span class="label-text">Select your Discord server</span></div>
                <p class="ap-modal-hint">You have admin access in these servers. Select one to connect.</p>
                <div v-if="guildModal.guilds.length === 0" class="ap-empty">No servers found where you have Administrator permission.</div>
                <ul v-else class="ap-guild-list">
                    <li
                        v-for="g in guildModal.guilds"
                        :key="g.id"
                        class="ap-guild-option"
                        :class="{ 'ap-guild-option--selected': guildModal.selected === g.id }"
                        @click="guildModal.selected = g.id; guildModal.selectedName = g.name"
                    >
                        <span class="ap-guild-option__name">{{ g.name }}</span>
                        <span class="ap-guild-option__id">{{ g.id }}</span>
                    </li>
                </ul>
                <div class="ap-modal-actions">
                    <button
                        class="btn-create"
                        :disabled="!guildModal.selected || guildModal.saving"
                        @click="confirmGuildSelect"
                    >
                        {{ guildModal.saving ? 'Connecting…' : '+ Connect' }}
                    </button>
                    <button class="btn-ghost" @click="guildModal.open = false">Cancel</button>
                </div>
                <p v-if="guildModal.error" class="ap-modal-error">{{ guildModal.error }}</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stat-boxes">
            <div class="stat-box">
                <div class="stat-box-label">Linked users</div>
                <div class="stat-box-val">{{ stats.linkedUsers }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">Active rules</div>
                <div class="stat-box-val">{{ stats.activeRules }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">Synced channels</div>
                <div class="stat-box-val">{{ stats.channels }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">Badges granted</div>
                <div class="stat-box-val">{{ stats.badgesGranted }}</div>
            </div>
        </div>

        <!-- Guild connection -->
        <div class="sec-label"><span class="label-text">Guild connection</span></div>

        <div v-if="loadingGuild" class="ap-empty">Loading…</div>

        <div v-else-if="guild" class="guild-row">
            <span class="guild-name">{{ guild.guild_name }}</span>
            <span class="status-badge connected">Connected</span>
            <div class="guild-actions">
                <button class="btn-ghost" :disabled="disconnecting" @click="disconnect">
                    {{ disconnecting ? 'Disconnecting…' : 'Disconnect' }}
                </button>
            </div>
        </div>

        <div v-else class="ap-guild-connect">
            <p class="ap-guild-connect__hint">Connect your Discord server to enable rules, polls, events, and badge granting.</p>
            <button class="btn-create" @click="connectDiscord">
                + Connect Discord Server
            </button>
            <p v-if="guildError" class="ap-connect-error">Connection failed. Please try again.</p>
        </div>

        <!-- Recent activity -->
        <div class="sec-label ap-sec-spaced"><span class="label-text">Recent activity</span></div>
        <div v-if="loading" class="ap-empty-box">Loading…</div>
        <div v-else-if="activity.length === 0" class="ap-empty-box">No recent activity.</div>
        <ul v-else class="ap-activity-list">
            <li v-for="(item, i) in activity" :key="i" class="list-row">
                <div class="list-row-info">
                    <div class="list-row-name">{{ item.user }}</div>
                    <div class="list-row-sub">{{ item.badge }}</div>
                </div>
                <span class="ap-activity-time">{{ item.time }}</span>
            </li>
        </ul>
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
.ap-overview {
    display: flex;
    flex-direction: column;
    font-family: var(--mono);
    color: var(--text);
}

/* Section labels */
.sec-label {
    font-size: 11px;
    color: var(--primary);
    letter-spacing: 0.25em;
    text-transform: uppercase;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.sec-label::before {
    content: '';
    width: 20px;
    height: 1px;
    background: var(--primary);
    box-shadow: 0 0 6px var(--primary);
}
.sec-label .label-text {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}
.sec-label .label-text::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(255, 97, 0, 0.3), transparent);
    margin-left: 12px;
    min-width: 40px;
}
.ap-sec-spaced { margin-top: 40px; }

/* Stat boxes */
.stat-boxes {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 40px;
}
.stat-box {
    padding: 18px 20px;
    background: rgba(14, 15, 17, 0.7);
    border: 1px solid rgba(42, 44, 46, 0.7);
    transition: border-color 0.15s;
}
.stat-box:hover { border-color: rgba(255, 97, 0, 0.2); }
.stat-box-label {
    font-size: 10px;
    color: var(--text-dim);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.stat-box-val {
    font-family: var(--display);
    font-size: 36px;
    color: var(--text);
    line-height: 1;
    letter-spacing: 0.02em;
}

/* Guild row */
.guild-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: rgba(14, 15, 17, 0.7);
    border: 1px solid rgba(42, 44, 46, 0.7);
    margin-bottom: 40px;
}
.guild-name {
    font-size: 14px;
    color: var(--text);
    letter-spacing: 0.04em;
    flex: 1;
}
.guild-actions { flex-shrink: 0; }

/* Guild connect (no guild) */
.ap-guild-connect {
    padding: 24px 20px;
    background: rgba(14, 15, 17, 0.7);
    border: 1px dashed rgba(255, 97, 0, 0.25);
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 40px;
    align-items: flex-start;
}
.ap-guild-connect__hint {
    font-size: 12px;
    color: var(--text-muted);
    letter-spacing: 0.06em;
    margin: 0;
}
.ap-connect-error {
    font-size: 12px;
    color: #e24b4a;
    margin: 0;
    letter-spacing: 0.06em;
}

/* Status badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    font-size: 9px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}
.status-badge.connected {
    background: rgba(193, 245, 39, 0.12);
    color: var(--accent);
    border: 1px solid rgba(193, 245, 39, 0.3);
}
.status-badge.connected::before {
    content: '';
    width: 5px;
    height: 5px;
    background: var(--accent);
    border-radius: 50%;
    box-shadow: 0 0 6px var(--accent);
    animation: ap-pulse-dot 1.4s ease-in-out infinite;
}
@keyframes ap-pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%      { opacity: 0.6; transform: scale(1.3); }
}

/* Buttons */
.btn-create {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    font-family: var(--mono);
    font-size: 10px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    background: var(--accent);
    color: var(--bg);
    border: 1px solid var(--accent);
    box-shadow: 0 0 12px var(--accent-glow);
    transition: all 0.15s;
    cursor: pointer;
}
.btn-create:hover:not(:disabled) {
    background: #d4ff4a;
    box-shadow: 0 0 22px var(--accent-glow);
}
.btn-create:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-ghost {
    padding: 8px 14px;
    font-family: var(--mono);
    font-size: 10px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--text-muted);
    border: 1px solid var(--border);
    background: transparent;
    transition: all 0.15s;
    cursor: pointer;
}
.btn-ghost:hover:not(:disabled) {
    color: var(--text);
    border-color: var(--text-dim);
}
.btn-ghost:disabled { opacity: 0.5; cursor: not-allowed; }

/* List rows (activity) */
.ap-activity-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
}
.list-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    background: rgba(14, 15, 17, 0.6);
    border: 1px solid rgba(42, 44, 46, 0.6);
    margin-bottom: 8px;
    transition: border-color 0.15s;
}
.list-row:hover { border-color: rgba(255, 97, 0, 0.2); }
.list-row-info { flex: 1; min-width: 0; }
.list-row-name {
    font-size: 13px;
    color: var(--text);
    letter-spacing: 0.04em;
}
.list-row-sub {
    font-size: 10px;
    color: var(--text-dim);
    letter-spacing: 0.08em;
    margin-top: 3px;
}
.ap-activity-time {
    font-size: 10px;
    color: var(--text-dim);
    letter-spacing: 0.08em;
    flex-shrink: 0;
}

/* Empty states */
.ap-empty {
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.06em;
    padding: 16px 0;
    margin-bottom: 40px;
}
.ap-empty-box {
    padding: 20px 24px;
    background: rgba(14, 15, 17, 0.5);
    border: 1px solid rgba(42, 44, 46, 0.5);
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.06em;
}

/* Modal */
.ap-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 3, 0.85);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.ap-modal {
    background: rgba(14, 15, 17, 0.95);
    border: 1px solid rgba(255, 97, 0, 0.2);
    padding: 28px 32px;
    width: 460px;
    max-width: 92vw;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: 0 0 40px rgba(0, 0, 0, 0.8);
}
.ap-modal-hint {
    font-size: 12px;
    color: var(--text-muted);
    letter-spacing: 0.06em;
    margin: 0;
}
.ap-guild-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 280px;
    overflow-y: auto;
}
.ap-guild-option {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 12px 16px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.15s;
}
.ap-guild-option:hover { border-color: rgba(255, 97, 0, 0.4); }
.ap-guild-option--selected {
    border-color: var(--primary);
    background: rgba(255, 97, 0, 0.06);
    box-shadow: 0 0 10px rgba(255, 97, 0, 0.15);
}
.ap-guild-option__name {
    font-size: 13px;
    color: var(--text);
    letter-spacing: 0.04em;
}
.ap-guild-option__id {
    font-size: 10px;
    color: var(--text-dim);
    letter-spacing: 0.08em;
}
.ap-modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 6px;
}
.ap-modal-error {
    font-size: 12px;
    color: #e24b4a;
    margin: 0;
    letter-spacing: 0.06em;
}

/* Responsive */
@media (max-width: 1100px) {
    .stat-boxes { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 700px) {
    .stat-boxes { grid-template-columns: 1fr 1fr; }
    .guild-row { flex-wrap: wrap; }
}
</style>

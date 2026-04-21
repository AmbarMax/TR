<template>
    <div class="pm">
        <div class="dual-layout">
            <!-- Create poll -->
            <section class="pm-section">
                <div class="sec-label"><span class="label-text">Create poll</span></div>
                <form class="form-panel" @submit.prevent="createPoll">
                    <div class="field">
                        <label class="field-label">Title</label>
                        <input type="text" class="field-input" v-model="form.title" placeholder="Poll question" required />
                    </div>
                    <div class="field">
                        <label class="field-label">Options</label>
                        <div v-for="(opt, i) in form.options" :key="i" class="pm-option-row">
                            <input type="text" class="field-input" v-model="form.options[i]" :placeholder="`Option ${i + 1}`" required />
                            <button type="button" class="btn-ghost btn-ghost--sq" @click="removeOption(i)" v-if="form.options.length > 2">✕</button>
                        </div>
                        <button type="button" class="btn-ghost pm-add-option" @click="addOption">+ Add option</button>
                    </div>
                    <div class="field-row field-row-2">
                        <div class="field">
                            <label class="field-label">Channel</label>
                            <select class="field-select" v-model="form.channel_id" required>
                                <option value="">Select channel</option>
                                <option v-for="ch in channels" :key="ch.id" :value="ch.channel_id">{{ ch.name }}</option>
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Badge (optional)</label>
                            <select class="field-select" v-model="form.badge_id">
                                <option value="">None</option>
                                <option v-for="b in badges" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label class="field-label">Duration (hours)</label>
                        <input type="number" class="field-input" v-model.number="form.duration_hours" min="1" placeholder="24" />
                    </div>
                    <button type="submit" class="btn-create" :disabled="submitting">
                        {{ submitting ? 'Creating…' : '+ Create Poll' }}
                    </button>
                </form>
            </section>

            <!-- Polls list -->
            <section class="pm-section">
                <div class="sec-label"><span class="label-text">Polls</span></div>
                <div v-if="loading" class="pm-empty">Loading…</div>
                <div v-else-if="polls.length === 0" class="pm-empty">No polls yet.</div>
                <ul v-else class="pm-list">
                    <li v-for="poll in polls" :key="poll.id" class="pm-card">
                        <div class="pm-card__header">
                            <span class="pm-card__title">{{ poll.title }}</span>
                            <span class="status-badge" :class="statusClass(poll.status)">{{ poll.status }}</span>
                        </div>

                        <!-- Results panel -->
                        <div v-if="expanded[poll.id]" class="pm-results">
                            <div v-if="loadingResults[poll.id]" class="pm-results__loading">Loading…</div>
                            <template v-else-if="results[poll.id]">
                                <div v-if="results[poll.id].total === 0" class="pm-results__empty">No votes yet.</div>
                                <div v-else class="pm-results__rows">
                                    <div
                                        v-for="row in results[poll.id].results"
                                        :key="row.value"
                                        class="pm-result-row"
                                    >
                                        <span class="pm-result-row__label">{{ row.label }}</span>
                                        <div class="pm-result-row__bar-wrap">
                                            <div
                                                class="pm-result-row__bar"
                                                :style="{ width: barWidth(row.count, results[poll.id].total) }"
                                            ></div>
                                        </div>
                                        <span class="pm-result-row__count">{{ row.count }}</span>
                                    </div>
                                    <span class="pm-results__total">{{ results[poll.id].total }} total votes</span>
                                </div>
                            </template>
                        </div>

                        <div class="pm-card__actions">
                            <button class="btn-ghost" @click="toggleResults(poll)">
                                {{ expanded[poll.id] ? 'Hide Results' : 'Results' }}
                            </button>
                            <button
                                v-if="results[poll.id] && results[poll.id].total > 0"
                                class="btn-ghost"
                                @click="downloadCsv(poll)"
                            >
                                CSV
                            </button>
                            <button
                                v-if="poll.status === 'active'"
                                class="btn-ghost"
                                @click="closePoll(poll.id)"
                            >Close</button>
                            <button class="btn-danger" @click="deletePoll(poll.id)">Delete</button>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>

<script>
import api from './botApi.js';

export default {
    data() {
        return {
            polls: [],
            channels: [],
            badges: [],
            loading: true,
            submitting: false,
            expanded: {},
            results: {},
            loadingResults: {},
            form: {
                title: '',
                options: ['', ''],
                channel_id: '',
                badge_id: '',
                duration_hours: 24,
            },
        };
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            this.loading = true;
            try {
                const [pollsRes, chRes, badgesRes] = await Promise.all([
                    api.get('/api/brand/polls'),
                    api.get('/api/brand/channels'),
                    api.get('/api/brand/badges'),
                ]);
                this.polls    = pollsRes.data?.polls    ?? [];
                this.channels = chRes.data?.channels   ?? [];
                this.badges   = badgesRes.data?.badges  ?? [];
            } catch (e) {
                console.error('[PollManager] load failed', e);
            } finally {
                this.loading = false;
            }
        },

        addOption() { this.form.options.push(''); },
        removeOption(i) { this.form.options.splice(i, 1); },

        async createPoll() {
            this.submitting = true;
            try {
                await api.post('/api/brand/polls', this.form);
                this.form = { title: '', options: ['', ''], channel_id: '', badge_id: '', duration_hours: 24 };
                await this.load();
            } catch (e) {
                console.error('createPoll error', e);
            } finally {
                this.submitting = false;
            }
        },

        async closePoll(id) {
            try {
                await api.post(`/api/brand/polls/${id}/close`);
                const poll = this.polls.find(p => p.id === id);
                if (poll) poll.status = 'closed';
            } catch (e) {
                console.error('closePoll error', e);
            }
        },

        async deletePoll(id) {
            if (!confirm('Delete this poll and all its votes?')) return;
            try {
                await api.delete(`/api/brand/polls/${id}`);
                this.polls = this.polls.filter(p => p.id !== id);
            } catch (e) {
                console.error('deletePoll error', e);
            }
        },

        async toggleResults(poll) {
            const id = poll.id;
            this.expanded = { ...this.expanded, [id]: !this.expanded[id] };
            if (this.expanded[id] && !this.results[id]) {
                await this.fetchResults(id);
            }
        },

        async fetchResults(id) {
            this.loadingResults = { ...this.loadingResults, [id]: true };
            try {
                const res = await api.get(`/api/brand/polls/${id}/results`);
                this.results = { ...this.results, [id]: res.data };
            } catch (e) {
                console.error('fetchResults error', e);
            } finally {
                this.loadingResults = { ...this.loadingResults, [id]: false };
            }
        },

        barWidth(count, total) {
            if (!total) return '0%';
            return Math.round((count / total) * 100) + '%';
        },

        statusClass(status) {
            return {
                draft:  'inactive',
                active: 'active',
                closed: 'closed',
            }[status] ?? 'inactive';
        },

        downloadCsv(poll) {
            const data = this.results[poll.id];
            if (!data) return;
            const rows = [['Poll', 'Option', 'Votes']];
            for (const row of data.results) {
                rows.push([poll.title, row.label, row.count]);
            }
            rows.push(['', 'TOTAL', data.total]);
            const csv = rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `poll-results-${poll.id.slice(0, 8)}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        },
    },
}
</script>

<style scoped>
.pm {
    display: flex;
    flex-direction: column;
    font-family: var(--mono);
    color: var(--text);
}

.dual-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    align-items: start;
}

/* Section label */
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

.pm-section { display: flex; flex-direction: column; }

/* Form panel */
.form-panel {
    padding: 24px 28px;
    background: rgba(14, 15, 17, 0.7);
    border: 1px solid rgba(42, 44, 46, 0.7);
}

/* Fields */
.field { margin-bottom: 16px; }
.field:last-child { margin-bottom: 0; }
.field-label {
    display: block;
    font-size: 10px;
    color: var(--text-dim);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.field-input,
.field-select {
    width: 100%;
    padding: 10px 14px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    color: var(--text);
    font-family: var(--mono);
    font-size: 13px;
    letter-spacing: 0.03em;
    transition: border-color 0.15s;
    outline: none;
}
.field-input:focus,
.field-select:focus { border-color: var(--primary); }
.field-input::placeholder { color: var(--text-dim); }
.field-select {
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239a9590' stroke-width='2'><path d='M6 9l6 6 6-6'/></svg>");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
    cursor: pointer;
}
.field-select option { background: var(--surface-2); color: var(--text); }

.field-row {
    display: grid;
    gap: 12px;
    margin-bottom: 16px;
}
.field-row-2 { grid-template-columns: 1fr 1fr; }
.field-row .field { margin-bottom: 0; }

.pm-option-row {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 6px;
}
.pm-option-row .field-input { flex: 1; }
.pm-add-option {
    width: 100%;
    text-align: center;
    justify-content: center;
    margin-top: 2px;
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
    white-space: nowrap;
    margin-top: 8px;
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
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-ghost:hover:not(:disabled) {
    color: var(--text);
    border-color: var(--text-dim);
}
.btn-ghost:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-ghost--sq { padding: 8px 10px; }

.btn-danger {
    padding: 8px 14px;
    font-family: var(--mono);
    font-size: 10px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #e24b4a;
    border: 1px solid rgba(226, 75, 74, 0.3);
    background: transparent;
    transition: all 0.15s;
    cursor: pointer;
    white-space: nowrap;
}
.btn-danger:hover {
    background: rgba(226, 75, 74, 0.1);
    border-color: #e24b4a;
}

/* Poll list / cards */
.pm-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
}
.pm-card {
    background: rgba(14, 15, 17, 0.6);
    border: 1px solid rgba(42, 44, 46, 0.6);
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 8px;
    transition: border-color 0.15s;
}
.pm-card:hover { border-color: rgba(255, 97, 0, 0.2); }
.pm-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.pm-card__title {
    font-size: 13px;
    color: var(--text);
    letter-spacing: 0.04em;
    flex: 1;
}
.pm-card__actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
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
    white-space: nowrap;
}
.status-badge.active {
    background: rgba(193, 245, 39, 0.12);
    color: var(--accent);
    border: 1px solid rgba(193, 245, 39, 0.3);
}
.status-badge.inactive {
    background: rgba(42, 44, 46, 0.5);
    color: var(--text-dim);
    border: 1px solid var(--border);
}
.status-badge.closed {
    background: rgba(255, 97, 0, 0.1);
    color: var(--primary);
    border: 1px solid rgba(255, 97, 0, 0.3);
}

/* Results */
.pm-results {
    border-top: 1px solid var(--border);
    padding-top: 12px;
}
.pm-results__loading,
.pm-results__empty {
    font-size: 11px;
    color: var(--text-dim);
    letter-spacing: 0.06em;
}
.pm-results__rows {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.pm-result-row {
    display: grid;
    grid-template-columns: 120px 1fr 36px;
    align-items: center;
    gap: 10px;
}
.pm-result-row__label {
    font-size: 11px;
    color: var(--text-muted);
    letter-spacing: 0.04em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.pm-result-row__bar-wrap {
    background: var(--surface-2);
    border: 1px solid var(--border);
    height: 8px;
    overflow: hidden;
}
.pm-result-row__bar {
    height: 100%;
    background: var(--accent);
    box-shadow: 0 0 6px var(--accent-glow);
    transition: width 0.4s ease;
}
.pm-result-row__count {
    font-size: 11px;
    color: var(--text);
    text-align: right;
}
.pm-results__total {
    font-size: 10px;
    color: var(--text-dim);
    letter-spacing: 0.08em;
    margin-top: 4px;
    text-transform: uppercase;
}

/* Empty */
.pm-empty {
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.06em;
    padding: 16px 0;
}

@media (max-width: 1100px) {
    .dual-layout { grid-template-columns: 1fr; }
}
@media (max-width: 700px) {
    .field-row-2 { grid-template-columns: 1fr; }
}
</style>

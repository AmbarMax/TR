<template>
    <div class="pm">
        <div class="pm-cols">
            <!-- Create poll -->
            <section class="pm-section">
                <span class="bd-section-label">Create Poll</span>
                <form class="pm-form" @submit.prevent="createPoll">
                    <div class="pm-field">
                        <label>Title</label>
                        <input type="text" v-model="form.title" placeholder="Poll question" required />
                    </div>
                    <div class="pm-field">
                        <label>Options</label>
                        <div v-for="(opt, i) in form.options" :key="i" class="pm-option-row">
                            <input type="text" v-model="form.options[i]" :placeholder="`Option ${i + 1}`" required />
                            <button type="button" class="pm-btn pm-btn--ghost pm-btn--sm" @click="removeOption(i)" v-if="form.options.length > 2">✕</button>
                        </div>
                        <button type="button" class="pm-btn pm-btn--ghost pm-btn--sm" @click="addOption">+ Add option</button>
                    </div>
                    <div class="pm-form-row">
                        <div class="pm-field">
                            <label>Channel</label>
                            <select v-model="form.channel_id" required>
                                <option value="">Select channel</option>
                                <option v-for="ch in channels" :key="ch.id" :value="ch.channel_id">{{ ch.name }}</option>
                            </select>
                        </div>
                        <div class="pm-field">
                            <label>Badge (optional)</label>
                            <select v-model="form.badge_id">
                                <option value="">None</option>
                                <option v-for="b in badges" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="pm-field">
                        <label>Duration (hours)</label>
                        <input type="number" v-model.number="form.duration_hours" min="1" placeholder="24" />
                    </div>
                    <button type="submit" class="pm-btn pm-btn--primary" :disabled="submitting">
                        {{ submitting ? 'Creating…' : '+ Create Poll' }}
                    </button>
                </form>
            </section>

            <!-- Polls list -->
            <section class="pm-section">
                <span class="bd-section-label">Polls</span>
                <div v-if="loading" class="pm-empty">Loading…</div>
                <div v-else-if="polls.length === 0" class="pm-empty">No polls yet.</div>
                <ul v-else class="pm-list">
                    <li v-for="poll in polls" :key="poll.id" class="pm-card">
                        <div class="pm-card__header">
                            <span class="pm-card__title">{{ poll.title }}</span>
                            <span class="pm-status" :class="statusClass(poll.status)">{{ poll.status }}</span>
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
                            <button class="pm-btn pm-btn--ghost pm-btn--sm" @click="toggleResults(poll)">
                                {{ expanded[poll.id] ? 'Hide Results' : 'Results' }}
                            </button>
                            <button
                                v-if="results[poll.id] && results[poll.id].total > 0"
                                class="pm-btn pm-btn--ghost pm-btn--sm"
                                @click="downloadCsv(poll)"
                            >
                                CSV
                            </button>
                            <button
                                v-if="poll.status === 'active'"
                                class="pm-btn pm-btn--ghost pm-btn--sm"
                                @click="closePoll(poll.id)"
                            >Close</button>
                            <button class="pm-btn pm-btn--danger pm-btn--sm" @click="deletePoll(poll.id)">Delete</button>
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
                draft:  'pm-status--draft',
                active: 'pm-status--active',
                closed: 'pm-status--closed',
            }[status] ?? 'pm-status--draft';
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
.pm { display: flex; flex-direction: column; gap: 32px; }

.pm-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    align-items: start;
}

.bd-section-label {
    display: block;
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #ff6100;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    margin-bottom: 16px;
}

.pm-section { display: flex; flex-direction: column; gap: 16px; }

.pm-form {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.pm-field { display: flex; flex-direction: column; gap: 6px; }

.pm-field label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #9a9590;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.pm-field input,
.pm-field select {
    background: #1a1c1f;
    border: 1px solid #2a2c2e;
    border-radius: 4px;
    padding: 8px 12px;
    color: #feeddf;
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    outline: none;
}

.pm-field input:focus,
.pm-field select:focus { border-color: #ff6100; }

.pm-option-row { display: flex; gap: 8px; align-items: center; }
.pm-option-row input { flex: 1; }

.pm-btn {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    padding: 8px 16px;
    transition: opacity 0.15s;
}

.pm-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.pm-btn--primary { background: #c1f527; color: #000003; align-self: flex-start; }
.pm-btn--ghost   { background: transparent; border: 1px solid #2a2c2e; color: #9a9590; }
.pm-btn--danger  { background: transparent; border: 1px solid rgba(255,80,80,0.3); color: #ff5050; }
.pm-btn--sm      { padding: 4px 10px; font-size: 11px; }

.pm-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }

.pm-card {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pm-card__header { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.pm-card__title  { font-family: 'Share Tech Mono', monospace; font-size: 14px; color: #feeddf; flex: 1; }
.pm-card__actions { display: flex; gap: 8px; flex-wrap: wrap; }

.pm-status {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
    white-space: nowrap;
}

.pm-status--draft  { background: rgba(90, 85, 80, 0.2);   color: #5a5550; }
.pm-status--active { background: rgba(193, 245, 39, 0.1); color: #c1f527; }
.pm-status--closed { background: rgba(255, 97, 0, 0.1);   color: #ff6100; }

/* Results */
.pm-results {
    border-top: 1px solid #2a2c2e;
    padding-top: 12px;
}

.pm-results__loading,
.pm-results__empty {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #5a5550;
}

.pm-results__rows { display: flex; flex-direction: column; gap: 8px; }

.pm-result-row {
    display: grid;
    grid-template-columns: 120px 1fr 36px;
    align-items: center;
    gap: 10px;
}

.pm-result-row__label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #9a9590;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pm-result-row__bar-wrap {
    background: #1a1c1f;
    border-radius: 2px;
    height: 8px;
    overflow: hidden;
}

.pm-result-row__bar {
    height: 100%;
    background: #c1f527;
    border-radius: 2px;
    transition: width 0.4s ease;
}

.pm-result-row__count {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #feeddf;
    text-align: right;
}

.pm-results__total {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #5a5550;
    margin-top: 4px;
}

.pm-empty { font-family: 'Share Tech Mono', monospace; font-size: 13px; color: #5a5550; padding: 16px 0; }

@media (max-width: 900px) {
    .pm-cols { grid-template-columns: 1fr; }
    .pm-form-row { grid-template-columns: 1fr; }
}
</style>

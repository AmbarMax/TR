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
                                <option v-for="ch in channels" :key="ch.id" :value="ch.id">{{ ch.name }}</option>
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

            <!-- Pending polls -->
            <section class="pm-section">
                <span class="bd-section-label">Pending Polls</span>
                <div v-if="loading" class="pm-empty">Loading…</div>
                <div v-else-if="polls.length === 0" class="pm-empty">No pending polls.</div>
                <ul v-else class="pm-list">
                    <li v-for="poll in polls" :key="poll.id" class="pm-card">
                        <div class="pm-card__header">
                            <span class="pm-card__title">{{ poll.title }}</span>
                            <span class="pm-status pm-status--pending">Pending</span>
                        </div>
                        <div class="pm-card__actions">
                            <button class="pm-btn pm-btn--ghost pm-btn--sm" @click="closePoll(poll.id)">Close</button>
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
                    api.get('/api/bot/polls/pending'),
                    api.get('/api/bot/channels'),
                    api.get('/api/badges'),
                ]);
                this.polls    = pollsRes.data?.data   ?? [];
                this.channels = chRes.data?.data      ?? [];
                this.badges   = badgesRes.data?.data  ?? [];
            } catch (e) {
                // silently fail
            } finally {
                this.loading = false;
            }
        },
        addOption() { this.form.options.push(''); },
        removeOption(i) { this.form.options.splice(i, 1); },
        async createPoll() {
            this.submitting = true;
            try {
                await api.post('/api/bot/polls', this.form);
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
                await api.post(`/api/bot/polls/${id}/close`);
                this.polls = this.polls.filter(p => p.id !== id);
            } catch (e) {
                console.error('closePoll error', e);
            }
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

.pm-card__title { font-family: 'Share Tech Mono', monospace; font-size: 14px; color: #feeddf; flex: 1; }

.pm-card__actions { display: flex; gap: 8px; }

.pm-status {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
}

.pm-status--pending { background: rgba(255, 97, 0, 0.1); color: #ff6100; }

.pm-empty { font-family: 'Share Tech Mono', monospace; font-size: 13px; color: #5a5550; padding: 16px 0; }

@media (max-width: 900px) {
    .pm-cols { grid-template-columns: 1fr; }
    .pm-form-row { grid-template-columns: 1fr; }
}
</style>

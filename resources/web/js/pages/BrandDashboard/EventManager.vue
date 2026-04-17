<template>
    <div class="em">
        <div class="em-cols">
            <!-- Create event -->
            <section class="em-section">
                <span class="bd-section-label">Create Event</span>
                <form class="em-form" @submit.prevent="createEvent">
                    <div class="em-field">
                        <label>Title</label>
                        <input type="text" v-model="form.title" placeholder="Event name" required />
                    </div>
                    <div class="em-field">
                        <label>Description</label>
                        <textarea v-model="form.description" placeholder="What's this event about?" rows="3"></textarea>
                    </div>
                    <div class="em-field">
                        <label>Badge (optional)</label>
                        <select v-model="form.badge_id">
                            <option value="">None</option>
                            <option v-for="b in badges" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                    <div class="em-form-row">
                        <div class="em-field">
                            <label>Start</label>
                            <input type="datetime-local" v-model="form.starts_at" required />
                        </div>
                        <div class="em-field">
                            <label>End</label>
                            <input type="datetime-local" v-model="form.ends_at" required />
                        </div>
                    </div>
                    <button type="submit" class="em-btn em-btn--primary" :disabled="submitting">
                        {{ submitting ? 'Creating…' : '+ Create Event' }}
                    </button>
                </form>
            </section>

            <!-- Pending events -->
            <section class="em-section">
                <span class="bd-section-label">Pending Events</span>
                <div v-if="loading" class="em-empty">Loading…</div>
                <div v-else-if="events.length === 0" class="em-empty">No pending events.</div>
                <ul v-else class="em-list">
                    <li v-for="event in events" :key="event.id" class="em-card">
                        <div class="em-card__header">
                            <span class="em-card__title">{{ event.title }}</span>
                            <span class="em-status" :class="statusClass(event.status)">{{ event.status }}</span>
                        </div>
                        <p v-if="event.description" class="em-card__desc">{{ event.description }}</p>
                        <div class="em-card__actions">
                            <button class="em-btn em-btn--ghost em-btn--sm" @click="completeEvent(event.id)">Mark Complete</button>
                            <button class="em-btn em-btn--danger em-btn--sm" @click="deleteEvent(event.id)">Delete</button>
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
            events: [],
            badges: [],
            loading: true,
            submitting: false,
            form: {
                title: '',
                description: '',
                badge_id: '',
                starts_at: '',
                ends_at: '',
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
                const [eventsRes, badgesRes] = await Promise.all([
                    api.get('/api/brand/events'),
                    api.get('/api/brand/badges'),
                ]);
                this.events = eventsRes.data?.events ?? [];
                this.badges = badgesRes.data?.badges ?? [];
            } catch (e) {
                console.error('[EventManager] load failed', e);
            } finally {
                this.loading = false;
            }
        },
        async createEvent() {
            this.submitting = true;
            try {
                await api.post('/api/brand/events', this.form);
                this.form = { title: '', description: '', badge_id: '', starts_at: '', ends_at: '' };
                await this.load();
            } catch (e) {
                console.error('createEvent error', e);
            } finally {
                this.submitting = false;
            }
        },
        async completeEvent(id) {
            try {
                await api.post(`/api/brand/events/${id}/complete`);
                this.events = this.events.filter(e => e.id !== id);
            } catch (e) {
                console.error('completeEvent error', e);
            }
        },
        async deleteEvent(id) {
            if (!confirm('Delete this event?')) return;
            try {
                await api.delete(`/api/brand/events/${id}`);
                this.events = this.events.filter(e => e.id !== id);
            } catch (e) {
                console.error('deleteEvent error', e);
            }
        },
        statusClass(status) {
            return {
                draft:     'em-status--draft',
                scheduled: 'em-status--scheduled',
                active:    'em-status--active',
            }[status] ?? 'em-status--draft';
        },
    },
}
</script>

<style scoped>
.em { display: flex; flex-direction: column; gap: 32px; }

.em-cols {
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

.em-section { display: flex; flex-direction: column; gap: 16px; }

.em-form {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.em-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.em-field { display: flex; flex-direction: column; gap: 6px; }

.em-field label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #9a9590;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.em-field input,
.em-field select,
.em-field textarea {
    background: #1a1c1f;
    border: 1px solid #2a2c2e;
    border-radius: 4px;
    padding: 8px 12px;
    color: #feeddf;
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    outline: none;
    resize: vertical;
}

.em-field input:focus,
.em-field select:focus,
.em-field textarea:focus { border-color: #ff6100; }

.em-btn {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    padding: 8px 16px;
    transition: opacity 0.15s;
}

.em-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.em-btn--primary { background: #c1f527; color: #000003; align-self: flex-start; }
.em-btn--ghost   { background: transparent; border: 1px solid #2a2c2e; color: #9a9590; }
.em-btn--danger  { background: transparent; border: 1px solid rgba(255,80,80,0.3); color: #ff5050; }
.em-btn--sm      { padding: 4px 10px; font-size: 11px; }

.em-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }

.em-card {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.em-card__header { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.em-card__title  { font-family: 'Share Tech Mono', monospace; font-size: 14px; color: #feeddf; flex: 1; }
.em-card__desc   { font-family: 'Share Tech Mono', monospace; font-size: 12px; color: #9a9590; margin: 0; }
.em-card__actions { display: flex; gap: 8px; }

.em-status {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
}

.em-status--draft     { background: rgba(90, 85, 80, 0.2);   color: #5a5550; }
.em-status--scheduled { background: rgba(255, 97, 0, 0.1);  color: #ff6100; }
.em-status--active    { background: rgba(193, 245, 39, 0.1); color: #c1f527; }

.em-empty { font-family: 'Share Tech Mono', monospace; font-size: 13px; color: #5a5550; padding: 16px 0; }

@media (max-width: 900px) {
    .em-cols { grid-template-columns: 1fr; }
    .em-form-row { grid-template-columns: 1fr; }
}
</style>

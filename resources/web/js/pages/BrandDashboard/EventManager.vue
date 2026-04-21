<template>
    <div class="em">
        <div class="dual-layout">
            <!-- Create event -->
            <section class="em-section">
                <div class="sec-label"><span class="label-text">Create event</span></div>
                <form class="form-panel" @submit.prevent="createEvent">
                    <div class="field">
                        <label class="field-label">Title</label>
                        <input type="text" class="field-input" v-model="form.title" placeholder="Event name" required />
                    </div>
                    <div class="field">
                        <label class="field-label">Description</label>
                        <textarea class="field-textarea" v-model="form.description" placeholder="What's this event about?" rows="3"></textarea>
                    </div>
                    <div class="field">
                        <label class="field-label">Badge (optional)</label>
                        <select class="field-select" v-model="form.badge_id">
                            <option value="">None</option>
                            <option v-for="b in badges" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                    </div>
                    <div class="field-row field-row-2">
                        <div class="field">
                            <label class="field-label">Start</label>
                            <input ref="startsAtInput" type="text" readonly placeholder="Pick date & time" class="field-input em-datepicker" required />
                        </div>
                        <div class="field">
                            <label class="field-label">End</label>
                            <input ref="endsAtInput" type="text" readonly placeholder="Pick date & time" class="field-input em-datepicker" required />
                        </div>
                    </div>
                    <button type="submit" class="btn-create" :disabled="submitting">
                        {{ submitting ? 'Creating…' : '+ Create Event' }}
                    </button>
                </form>
            </section>

            <!-- Pending events -->
            <section class="em-section">
                <div class="sec-label"><span class="label-text">Pending events</span></div>
                <div v-if="loading" class="em-empty">Loading…</div>
                <div v-else-if="events.length === 0" class="em-empty-box">No pending events.</div>
                <ul v-else class="em-list">
                    <li v-for="event in events" :key="event.id" class="em-card">
                        <div class="em-card__header">
                            <span class="em-card__title">{{ event.title }}</span>
                            <span class="status-badge" :class="statusClass(event.status)">{{ event.status }}</span>
                        </div>
                        <p v-if="event.description" class="em-card__desc">{{ event.description }}</p>
                        <div class="em-card__actions">
                            <button class="btn-ghost" @click="completeEvent(event.id)">Mark Complete</button>
                            <button class="btn-danger" @click="deleteEvent(event.id)">Delete</button>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>

<script>
import api from './botApi.js';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

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
        this._initPickers();
    },
    beforeUnmount() {
        this._fpStart?.destroy();
        this._fpEnd?.destroy();
    },
    methods: {
        _initPickers() {
            const opts = {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                time_24hr: true,
                minDate: 'today',
                theme: 'dark',
            };
            this._fpStart = flatpickr(this.$refs.startsAtInput, {
                ...opts,
                onChange: ([date]) => {
                    this.form.starts_at = date
                        ? flatpickr.formatDate(date, 'Y-m-d H:i:00')
                        : '';
                    if (this._fpEnd) this._fpEnd.set('minDate', date || 'today');
                },
            });
            this._fpEnd = flatpickr(this.$refs.endsAtInput, {
                ...opts,
                onChange: ([date]) => {
                    this.form.ends_at = date
                        ? flatpickr.formatDate(date, 'Y-m-d H:i:00')
                        : '';
                },
            });
        },
        _resetPickers() {
            this._fpStart?.clear();
            this._fpEnd?.clear();
        },
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
                this._resetPickers();
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
                draft:     'inactive',
                scheduled: 'scheduled',
                active:    'active',
            }[status] ?? 'inactive';
        },
    },
}
</script>

<style scoped>
.em {
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

.em-section { display: flex; flex-direction: column; }

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
.field-select,
.field-textarea {
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
.field-select:focus,
.field-textarea:focus { border-color: var(--primary); }
.field-input::placeholder,
.field-textarea::placeholder { color: var(--text-dim); }
.field-textarea {
    min-height: 80px;
    resize: vertical;
}
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
.em-datepicker { cursor: pointer; }

.field-row {
    display: grid;
    gap: 12px;
    margin-bottom: 16px;
}
.field-row-2 { grid-template-columns: 1fr 1fr; }
.field-row .field { margin-bottom: 0; }

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
}
.btn-ghost:hover:not(:disabled) {
    color: var(--text);
    border-color: var(--text-dim);
}

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

/* Event cards */
.em-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
}
.em-card {
    background: rgba(14, 15, 17, 0.6);
    border: 1px solid rgba(42, 44, 46, 0.6);
    padding: 14px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 8px;
    transition: border-color 0.15s;
}
.em-card:hover { border-color: rgba(255, 97, 0, 0.2); }
.em-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.em-card__title {
    font-size: 13px;
    color: var(--text);
    letter-spacing: 0.04em;
    flex: 1;
}
.em-card__desc {
    font-size: 11px;
    color: var(--text-muted);
    letter-spacing: 0.04em;
    margin: 0;
}
.em-card__actions {
    display: flex;
    gap: 6px;
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
.status-badge.scheduled {
    background: rgba(255, 97, 0, 0.12);
    color: var(--primary);
    border: 1px solid rgba(255, 97, 0, 0.3);
}

/* Empty */
.em-empty {
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.06em;
    padding: 16px 0;
}
.em-empty-box {
    padding: 20px 24px;
    background: rgba(14, 15, 17, 0.5);
    border: 1px solid rgba(42, 44, 46, 0.5);
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.06em;
}

@media (max-width: 1100px) {
    .dual-layout { grid-template-columns: 1fr; }
}
@media (max-width: 700px) {
    .field-row-2 { grid-template-columns: 1fr; }
}
</style>

<style>
/* flatpickr overrides — scoped doesn't reach the portal */
.flatpickr-calendar {
    background: #0e0f11 !important;
    border: 1px solid rgba(255, 97, 0, 0.2) !important;
    border-radius: 0 !important;
    font-family: 'Share Tech Mono', monospace !important;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.7) !important;
}
.flatpickr-day { color: #9a9590 !important; border-radius: 0 !important; }
.flatpickr-day:hover,
.flatpickr-day.prevMonthDay:hover,
.flatpickr-day.nextMonthDay:hover { background: rgba(255, 97, 0, 0.15) !important; border-color: transparent !important; }
.flatpickr-day.selected,
.flatpickr-day.selected:hover { background: #ff6100 !important; border-color: #ff6100 !important; color: #000003 !important; }
.flatpickr-day.today { border-color: #ff6100 !important; }
.flatpickr-months .flatpickr-month,
.flatpickr-weekdays,
.flatpickr-time { background: #0e0f11 !important; }
.flatpickr-current-month,
.flatpickr-monthDropdown-months,
.numInput,
span.flatpickr-weekday { color: #feeddf !important; }
.flatpickr-time input { color: #feeddf !important; background: #1a1c1f !important; }
.flatpickr-time .flatpickr-time-separator { color: #5a5550 !important; }
.flatpickr-prev-month svg,
.flatpickr-next-month svg { fill: #9a9590 !important; }
</style>

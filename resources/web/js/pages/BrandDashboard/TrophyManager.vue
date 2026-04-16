<template>
    <div class="tm">

        <!-- Header -->
        <div class="tm-header">
            <span class="tm-label">Trophies</span>
            <button class="tm-btn tm-btn--primary" @click="openCreate">+ Create Trophy</button>
        </div>

        <!-- Form Panel (create / edit) -->
        <div v-if="showForm" class="tm-panel">
            <div class="tm-panel__head">
                <span class="tm-panel__title">{{ editingTrophy ? 'Edit Trophy' : 'New Trophy' }}</span>
                <button class="tm-panel__close" type="button" @click="closeForm">✕</button>
            </div>

            <form class="tm-form" @submit.prevent="submitForm">
                <!-- Row 1: name + type -->
                <div class="tm-form-row">
                    <div class="tm-field tm-field--grow">
                        <label>Title</label>
                        <input type="text" v-model="form.name" placeholder="Trophy name" required />
                    </div>
                    <div class="tm-field tm-field--fixed">
                        <label>Type</label>
                        <select v-model="form.type">
                            <option value="trophy">Trophy</option>
                            <option value="key">Key</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="tm-field">
                    <label>Description</label>
                    <textarea v-model="form.description" placeholder="Describe what this trophy represents" rows="2"></textarea>
                </div>

                <!-- Row 2: price + receive + weight + availability -->
                <div class="tm-form-row tm-form-row--4">
                    <div class="tm-field">
                        <label>Price (Ambar)</label>
                        <input type="number" v-model.number="form.price" min="0" step="1" placeholder="0" required />
                    </div>
                    <div class="tm-field">
                        <label>Reward (Uru)</label>
                        <input type="number" v-model.number="form.receive" min="0" step="1" placeholder="0" required />
                    </div>
                    <div class="tm-field">
                        <label>Weight (XP)</label>
                        <input type="number" v-model.number="form.weight" min="0" step="1" placeholder="0" />
                    </div>
                    <div class="tm-field">
                        <label>Availability</label>
                        <input type="number" v-model.number="form.availability" min="1" step="1" placeholder="Unlimited" />
                    </div>
                </div>

                <!-- Image upload -->
                <div class="tm-field">
                    <label>
                        Image
                        <span v-if="editingTrophy" class="tm-field-hint">— leave blank to keep current</span>
                    </label>
                    <div class="tm-upload" @click="$refs.trophyImg.click()">
                        <img v-if="imagePreview" :src="imagePreview" class="tm-upload__preview" alt="preview" />
                        <img v-else-if="editingTrophy && editingTrophy.image_url" :src="editingTrophy.image_url" class="tm-upload__preview" alt="current" />
                        <span v-else class="tm-upload__placeholder">Click to select image</span>
                    </div>
                    <input ref="trophyImg" type="file" accept="image/*" style="display:none" @change="onImageChange" />
                </div>

                <!-- Badge multi-select -->
                <div class="tm-field">
                    <label>Required Badges</label>
                    <div v-if="availableBadges.length" class="tm-badge-selector">
                        <div
                            v-for="badge in availableBadges"
                            :key="badge.id"
                            class="tm-badge-chip"
                            :class="{ 'tm-badge-chip--on': form.badge_ids.includes(badge.id) }"
                            @click="toggleBadge(badge.id)"
                        >
                            <img v-if="badge.image" :src="'/storage/' + badge.image" class="tm-badge-chip__img" alt="" @error="$event.target.style.display='none'" />
                            <span>{{ badge.name }}</span>
                        </div>
                    </div>
                    <p v-else class="tm-field-hint">No badges available — create badges in the Badges tab first.</p>

                    <!-- Selected chips summary -->
                    <div v-if="form.badge_ids.length" class="tm-selected-badges">
                        <span
                            v-for="id in form.badge_ids"
                            :key="id"
                            class="tm-selected-chip"
                        >
                            {{ badgeName(id) }}
                            <button type="button" class="tm-selected-chip__rm" @click="toggleBadge(id)">✕</button>
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tm-form-actions">
                    <button
                        type="submit"
                        class="tm-btn tm-btn--primary"
                        :disabled="submitting || (!editingTrophy && !form.image)"
                    >
                        {{ submitting ? 'Saving…' : (editingTrophy ? 'Save Changes' : '+ Create Trophy') }}
                    </button>
                    <button type="button" class="tm-btn tm-btn--ghost" @click="closeForm">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Trophy Grid -->
        <div v-if="loading" class="tm-empty">Loading trophies…</div>
        <div v-else-if="trophies.length === 0 && !showForm" class="tm-empty">No trophies yet. Use "+ Create Trophy" to add your first one.</div>
        <div v-else-if="trophies.length" class="tm-grid">
            <div v-for="t in trophies" :key="t.id" class="tm-card">
                <div class="tm-card__img-wrap">
                    <img v-if="t.image_url" :src="t.image_url" class="tm-card__img" alt="" />
                    <div v-else class="tm-card__img-empty">?</div>
                </div>

                <div class="tm-card__body">
                    <div class="tm-card__top">
                        <span class="tm-card__name">{{ t.name }}</span>
                        <span class="tm-type-pill" :class="'tm-type-pill--' + t.type">{{ t.type }}</span>
                    </div>

                    <p v-if="t.description" class="tm-card__desc">{{ t.description }}</p>

                    <div class="tm-card__economy">
                        <span class="tm-economy__cost">{{ t.price }} Ambar</span>
                        <span class="tm-economy__sep">→</span>
                        <span class="tm-economy__reward">{{ t.receive }} Uru</span>
                        <span v-if="t.weight" class="tm-economy__xp">· {{ t.weight }} XP</span>
                    </div>

                    <div class="tm-card__footer">
                        <div class="tm-card__req">
                            <template v-if="t.badges && t.badges.length">
                                <img
                                    v-for="b in t.badges.slice(0, 5)"
                                    :key="b.id"
                                    :src="'/storage/' + b.image"
                                    class="tm-req-thumb"
                                    :title="b.name"
                                    alt=""
                                    @error="$event.target.style.display='none'"
                                />
                                <span v-if="t.badges.length > 5" class="tm-req-more">+{{ t.badges.length - 5 }}</span>
                            </template>
                            <span v-else class="tm-req-none">No requirements</span>
                        </div>
                        <span class="tm-card__avail">
                            {{ t.forged_count }}/{{ t.availability ?? '∞' }} forged
                        </span>
                    </div>
                </div>

                <div class="tm-card__actions">
                    <button class="tm-btn tm-btn--ghost tm-btn--sm" @click="openEdit(t)">Edit</button>
                    <button class="tm-btn tm-btn--danger tm-btn--sm" @click="deleteTrophy(t.id)">Delete</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import api from './botApi.js';

export default {
    data() {
        return {
            trophies:       [],
            availableBadges: [],
            loading:        true,
            showForm:       false,
            editingTrophy:  null,
            submitting:     false,
            imagePreview:   null,
            form: {
                name:         '',
                description:  '',
                type:         'trophy',
                price:        0,
                receive:      0,
                weight:       0,
                availability: null,
                badge_ids:    [],
                image:        null,
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
                const [tRes, bRes] = await Promise.all([
                    api.get('/api/brand/trophies'),
                    api.get('/api/brand/badges'),
                ]);
                this.trophies        = tRes.data?.trophies ?? [];
                this.availableBadges = bRes.data?.badges   ?? [];
            } catch (e) {
                // silently fail
            } finally {
                this.loading = false;
            }
        },

        // ── Form open/close ──────────────────────────────────────────────────

        openCreate() {
            this.editingTrophy = null;
            this.form = {
                name: '', description: '', type: 'trophy',
                price: 0, receive: 0, weight: 0, availability: null,
                badge_ids: [], image: null,
            };
            this.imagePreview = null;
            if (this.$refs.trophyImg) this.$refs.trophyImg.value = '';
            this.showForm = true;
        },

        openEdit(trophy) {
            this.editingTrophy = trophy;
            this.form = {
                name:         trophy.name,
                description:  trophy.description || '',
                type:         trophy.type || 'trophy',
                price:        trophy.price,
                receive:      trophy.receive,
                weight:       trophy.weight || 0,
                availability: trophy.availability || null,
                badge_ids:    (trophy.badges || []).map(b => b.id),
                image:        null,
            };
            this.imagePreview = null;
            if (this.$refs.trophyImg) this.$refs.trophyImg.value = '';
            this.showForm = true;
        },

        closeForm() {
            this.showForm      = false;
            this.editingTrophy = null;
            this.imagePreview  = null;
            if (this.$refs.trophyImg) this.$refs.trophyImg.value = '';
        },

        // ── Badge selector ───────────────────────────────────────────────────

        toggleBadge(id) {
            const idx = this.form.badge_ids.indexOf(id);
            if (idx === -1) this.form.badge_ids.push(id);
            else this.form.badge_ids.splice(idx, 1);
        },

        badgeName(id) {
            return this.availableBadges.find(b => b.id === id)?.name ?? id;
        },

        // ── Image upload ─────────────────────────────────────────────────────

        onImageChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.form.image  = file;
            this.imagePreview = URL.createObjectURL(file);
        },

        // ── Submit (create or update) ────────────────────────────────────────

        async submitForm() {
            this.submitting = true;
            try {
                const fd = new FormData();
                fd.append('name',        this.form.name);
                fd.append('description', this.form.description || '');
                fd.append('type',        this.form.type);
                fd.append('price',       this.form.price);
                fd.append('receive',     this.form.receive);
                fd.append('weight',      this.form.weight || 0);
                if (this.form.availability) fd.append('availability', this.form.availability);
                this.form.badge_ids.forEach(id => fd.append('badge_ids[]', id));
                if (this.form.image) fd.append('image', this.form.image);

                const headers = { 'Content-Type': 'multipart/form-data' };
                let trophy;

                if (this.editingTrophy) {
                    fd.append('_method', 'PUT');
                    const res = await api.post(`/api/brand/trophies/${this.editingTrophy.id}`, fd, { headers });
                    trophy = res.data.trophy;
                    const idx = this.trophies.findIndex(t => t.id === trophy.id);
                    if (idx !== -1) this.trophies.splice(idx, 1, trophy);
                } else {
                    const res = await api.post('/api/brand/trophies', fd, { headers });
                    trophy = res.data.trophy;
                    this.trophies.unshift(trophy);
                }

                this.closeForm();
            } catch (e) {
                console.error('submitForm error', e);
            } finally {
                this.submitting = false;
            }
        },

        // ── Delete ───────────────────────────────────────────────────────────

        async deleteTrophy(id) {
            if (!confirm('Delete this trophy? This action cannot be undone.')) return;
            try {
                await api.delete(`/api/brand/trophies/${id}`);
                this.trophies = this.trophies.filter(t => t.id !== id);
            } catch (e) {
                console.error('deleteTrophy error', e);
            }
        },
    },
};
</script>

<style scoped>
/* ── Layout ─────────────────────────────────────────────────────────────── */
.tm { display: flex; flex-direction: column; gap: 24px; }

.tm-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.tm-label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #ff6100;
    text-transform: uppercase;
    letter-spacing: 0.12em;
}

.tm-empty {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    color: #5a5550;
    padding: 24px 0;
}

/* ── Form Panel ─────────────────────────────────────────────────────────── */
.tm-panel {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    overflow: hidden;
}

.tm-panel__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid #2a2c2e;
}

.tm-panel__title {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    color: #feeddf;
}

.tm-panel__close {
    background: none;
    border: none;
    color: #5a5550;
    font-size: 16px;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
    transition: color 0.15s;
}
.tm-panel__close:hover { color: #feeddf; }

/* ── Form ───────────────────────────────────────────────────────────────── */
.tm-form {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.tm-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.tm-form-row--4 { grid-template-columns: repeat(4, 1fr); }

.tm-field { display: flex; flex-direction: column; gap: 6px; }
.tm-field--grow { flex: 1; }
.tm-field--fixed { width: 140px; flex-shrink: 0; }

.tm-field label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #9a9590;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.tm-field-hint {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #5a5550;
    font-weight: normal;
    text-transform: none;
    letter-spacing: 0;
    margin: 0;
}

.tm-field input,
.tm-field select,
.tm-field textarea {
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

.tm-field input:focus,
.tm-field select:focus,
.tm-field textarea:focus { border-color: #ff6100; }

.tm-field select option { background: #1a1c1f; }

/* ── Image upload ───────────────────────────────────────────────────────── */
.tm-upload {
    background: #1a1c1f;
    border: 1px dashed #2a2c2e;
    border-radius: 4px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
    transition: border-color 0.15s;
}
.tm-upload:hover { border-color: #ff6100; }

.tm-upload__preview { width: 100%; height: 100%; object-fit: contain; }
.tm-upload__placeholder {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #5a5550;
}

/* ── Badge selector ─────────────────────────────────────────────────────── */
.tm-badge-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px;
    background: #1a1c1f;
    border: 1px solid #2a2c2e;
    border-radius: 4px;
    max-height: 180px;
    overflow-y: auto;
}

.tm-badge-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border-radius: 4px;
    border: 1px solid #2a2c2e;
    background: #0e0f11;
    cursor: pointer;
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #9a9590;
    transition: border-color 0.12s, color 0.12s, background 0.12s;
    user-select: none;
}
.tm-badge-chip:hover { border-color: #ff6100; color: #feeddf; }
.tm-badge-chip--on {
    border-color: #c1f527;
    color: #c1f527;
    background: rgba(193, 245, 39, 0.07);
}

.tm-badge-chip__img {
    width: 20px;
    height: 20px;
    border-radius: 3px;
    object-fit: cover;
}

.tm-selected-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 6px;
}

.tm-selected-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    background: rgba(193, 245, 39, 0.1);
    border: 1px solid rgba(193, 245, 39, 0.3);
    border-radius: 4px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #c1f527;
}

.tm-selected-chip__rm {
    background: none;
    border: none;
    color: rgba(193, 245, 39, 0.6);
    cursor: pointer;
    font-size: 11px;
    padding: 0;
    line-height: 1;
    transition: color 0.12s;
}
.tm-selected-chip__rm:hover { color: #c1f527; }

/* ── Form actions ───────────────────────────────────────────────────────── */
.tm-form-actions {
    display: flex;
    gap: 10px;
    padding-top: 4px;
}

/* ── Buttons ────────────────────────────────────────────────────────────── */
.tm-btn {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    padding: 8px 16px;
    transition: opacity 0.15s;
    white-space: nowrap;
}
.tm-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.tm-btn--primary { background: #c1f527; color: #000003; }
.tm-btn--ghost   { background: transparent; border: 1px solid #2a2c2e; color: #9a9590; }
.tm-btn--danger  { background: transparent; border: 1px solid rgba(255, 80, 80, 0.3); color: #ff5050; }
.tm-btn--sm      { padding: 4px 10px; font-size: 11px; }
.tm-btn--danger:hover { border-color: #ff5050; }

/* ── Trophy Grid ────────────────────────────────────────────────────────── */
.tm-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

/* ── Trophy Card ────────────────────────────────────────────────────────── */
.tm-card {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-left: 3px solid #ff6100;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: border-color 0.15s;
}
.tm-card:hover { border-color: rgba(255, 97, 0, 0.5); border-left-color: #ff6100; }

.tm-card__img-wrap {
    height: 120px;
    background: #1a1c1f;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.tm-card__img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.tm-card__img-empty {
    font-family: 'Share Tech Mono', monospace;
    font-size: 24px;
    color: #2a2c2e;
}

.tm-card__body {
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
}

.tm-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}

.tm-card__name {
    font-family: 'Share Tech Mono', monospace;
    font-size: 14px;
    color: #feeddf;
    line-height: 1.3;
}

.tm-card__desc {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #5a5550;
    margin: 0;
    line-height: 1.4;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* ── Type pill ──────────────────────────────────────────────────────────── */
.tm-type-pill {
    font-family: 'Share Tech Mono', monospace;
    font-size: 10px;
    padding: 2px 7px;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    white-space: nowrap;
    flex-shrink: 0;
}
.tm-type-pill--trophy {
    background: rgba(255, 97, 0, 0.12);
    color: #ff6100;
    border: 1px solid rgba(255, 97, 0, 0.25);
}
.tm-type-pill--key {
    background: rgba(193, 245, 39, 0.08);
    color: #c1f527;
    border: 1px solid rgba(193, 245, 39, 0.2);
}

/* ── Economy row ────────────────────────────────────────────────────────── */
.tm-card__economy {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
}
.tm-economy__cost   { color: #ff6100; }
.tm-economy__sep    { color: #5a5550; }
.tm-economy__reward { color: #9a9590; }
.tm-economy__xp     { color: #5a5550; }

/* ── Card footer (badges + availability) ───────────────────────────────── */
.tm-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: auto;
    padding-top: 4px;
}

.tm-card__req {
    display: flex;
    align-items: center;
    gap: 3px;
    flex-wrap: wrap;
}

.tm-req-thumb {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #2a2c2e;
}

.tm-req-more {
    font-family: 'Share Tech Mono', monospace;
    font-size: 10px;
    color: #5a5550;
    padding: 0 2px;
}

.tm-req-none {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #5a5550;
}

.tm-card__avail {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #5a5550;
    white-space: nowrap;
}

/* ── Card actions ───────────────────────────────────────────────────────── */
.tm-card__actions {
    display: flex;
    gap: 8px;
    padding: 10px 16px;
    border-top: 1px solid #2a2c2e;
}

/* ── Responsive ─────────────────────────────────────────────────────────── */
@media (max-width: 1100px) {
    .tm-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .tm-form-row--4 { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 700px) {
    .tm-grid { grid-template-columns: 1fr; }
    .tm-form-row { grid-template-columns: 1fr; }
    .tm-field--fixed { width: auto; }
}
</style>

<template>
    <div class="tm">

        <!-- Header -->
        <div class="trophies-header">
            <div class="sec-label tm-header-label"><span class="label-text">Trophies</span></div>
            <button class="btn-create" @click="openCreate">+ Create Trophy</button>
        </div>

        <!-- Form Panel (create / edit) -->
        <div v-if="showForm" class="trophy-form visible">
            <div class="trophy-form-header">
                <span class="trophy-form-title">{{ editingTrophy ? 'Edit Trophy' : 'New Trophy' }}</span>
                <button class="trophy-form-close" type="button" @click="closeForm" aria-label="Close">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <form @submit.prevent="submitForm">
                <!-- Row 1: name + type -->
                <div class="field-row field-row-2">
                    <div class="field">
                        <label class="field-label">Title</label>
                        <input type="text" class="field-input" v-model="form.name" placeholder="Trophy name" required />
                    </div>
                    <div class="field">
                        <label class="field-label">Type</label>
                        <select class="field-select" v-model="form.type">
                            <option value="trophy">Trophy</option>
                            <option value="key">Key</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="field">
                    <label class="field-label">Description</label>
                    <textarea class="field-textarea" v-model="form.description" placeholder="Describe what this trophy represents" rows="2"></textarea>
                </div>

                <!-- Row 2: price + receive + weight + availability -->
                <div class="field-row field-row-4">
                    <div class="field">
                        <label class="field-label">Price (Ambar)</label>
                        <input type="number" class="field-input" v-model.number="form.price" min="0" step="1" placeholder="0" required />
                    </div>
                    <div class="field">
                        <label class="field-label">Reward (Uru)</label>
                        <input type="number" class="field-input" v-model.number="form.receive" min="0" step="1" placeholder="0" required />
                    </div>
                    <div class="field">
                        <label class="field-label">Weight (XP)</label>
                        <input type="number" class="field-input" v-model.number="form.weight" min="0" step="1" placeholder="0" />
                    </div>
                    <div class="field">
                        <label class="field-label">Availability</label>
                        <input type="number" class="field-input" v-model.number="form.availability" min="1" step="1" placeholder="Unlimited" />
                    </div>
                </div>

                <!-- Image upload -->
                <div class="field">
                    <label class="field-label">
                        Image
                        <span v-if="editingTrophy" class="field-hint">— leave blank to keep current</span>
                    </label>
                    <div class="image-upload" @click="$refs.trophyImg.click()">
                        <img v-if="imagePreview" :src="imagePreview" class="image-upload__preview" alt="preview" />
                        <img v-else-if="editingTrophy && editingTrophy.image_url" :src="editingTrophy.image_url" class="image-upload__preview" alt="current" />
                        <span v-else class="image-upload-text">Click to select image</span>
                    </div>
                    <input ref="trophyImg" type="file" accept="image/*" style="display:none" @change="onImageChange" />
                </div>

                <!-- Badge multi-select -->
                <div class="field">
                    <label class="field-label">Required Badges</label>
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
                    <p v-else class="field-hint tm-field-hint-block">No badges available — create badges in the Badges tab first.</p>

                    <!-- Selected chips summary -->
                    <div v-if="form.badge_ids.length" class="tm-selected-badges">
                        <span
                            v-for="id in form.badge_ids"
                            :key="id"
                            class="badge-tag badge-tag--on"
                        >
                            {{ badgeName(id) }}
                            <button type="button" class="badge-tag__rm" @click="toggleBadge(id)">✕</button>
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="tm-form-actions">
                    <button
                        type="submit"
                        class="btn-create"
                        :disabled="submitting || (!editingTrophy && !form.image)"
                    >
                        {{ submitting ? 'Saving…' : (editingTrophy ? 'Save Changes' : '+ Create Trophy') }}
                    </button>
                    <button type="button" class="btn-ghost" @click="closeForm">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Trophy List -->
        <div v-if="loading" class="tm-empty">Loading trophies…</div>
        <div v-else-if="trophies.length === 0 && !showForm" class="tm-empty">No trophies yet. Use "+ Create Trophy" to add your first one.</div>
        <div v-else-if="trophies.length" class="tm-list">
            <div v-for="t in trophies" :key="t.id" class="trophy-list-item">
                <div class="trophy-item-art">
                    <img v-if="t.image_url" :src="t.image_url" class="trophy-item-art__img" alt="" />
                    <span v-else class="trophy-item-art__empty">?</span>
                </div>

                <div class="trophy-item-info">
                    <div class="trophy-item-name">{{ t.name }}</div>
                    <div v-if="t.description" class="trophy-item-meta trophy-item-meta--desc">{{ t.description }}</div>
                    <div class="trophy-item-meta">
                        <span class="trophy-item-econ">{{ t.price }} Ambar → {{ t.receive }} Uru</span>
                        <template v-if="t.badges && t.badges.length">
                            <span class="trophy-req-sep">·</span>
                            <img
                                v-for="b in t.badges.slice(0, 5)"
                                :key="b.id"
                                :src="'/storage/' + b.image"
                                class="trophy-req-thumb"
                                :title="b.name"
                                alt=""
                                @error="$event.target.style.display='none'"
                            />
                            <span v-if="t.badges.length > 5" class="trophy-req-more">+{{ t.badges.length - 5 }}</span>
                        </template>
                    </div>
                </div>

                <div class="trophy-item-pills">
                    <span class="trophy-item-pill" :class="'trophy-item-pill--type-' + t.type">{{ t.type }}</span>
                    <span v-if="t.weight" class="trophy-item-pill xp">{{ t.weight }} XP</span>
                    <span class="trophy-item-pill price">{{ t.forged_count }}/{{ t.availability ?? '∞' }}</span>
                </div>

                <div class="trophy-item-actions">
                    <button class="btn-ghost" @click="openEdit(t)">Edit</button>
                    <button class="btn-danger" @click="deleteTrophy(t.id)">Delete</button>
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
                console.error('[TrophyManager] load failed', e);
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
.tm {
    display: flex;
    flex-direction: column;
    font-family: var(--mono);
    color: var(--text);
}

/* Header */
.trophies-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}
.tm-header-label { margin-bottom: 0; }

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

/* Trophy form panel */
.trophy-form {
    padding: 28px;
    background: rgba(14, 15, 17, 0.7);
    border: 1px solid rgba(255, 97, 0, 0.15);
    margin-bottom: 24px;
    display: none;
}
.trophy-form.visible { display: block; }
.trophy-form-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.trophy-form-title {
    font-size: 14px;
    color: var(--text);
    letter-spacing: 0.06em;
}
.trophy-form-close {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dim);
    border: 1px solid var(--border);
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
}
.trophy-form-close:hover {
    color: var(--text);
    border-color: var(--text-dim);
}

/* Form fields */
.field { margin-bottom: 16px; }
.field-label {
    display: block;
    font-size: 10px;
    color: var(--text-dim);
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin-bottom: 6px;
}
.field-hint {
    font-size: 10px;
    color: var(--text-dim);
    text-transform: none;
    letter-spacing: 0.04em;
    margin-left: 6px;
}
.tm-field-hint-block {
    font-size: 11px;
    color: var(--text-dim);
    margin: 4px 0 0;
    letter-spacing: 0.04em;
}
.field-input,
.field-textarea,
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
.field-textarea:focus,
.field-select:focus { border-color: var(--primary); }
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

.field-row {
    display: grid;
    gap: 12px;
    margin-bottom: 16px;
}
.field-row-2 { grid-template-columns: 1fr 1fr; }
.field-row-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
.field-row .field { margin-bottom: 0; }

/* Image upload */
.image-upload {
    border: 1px dashed rgba(255, 97, 0, 0.25);
    padding: 28px;
    text-align: center;
    cursor: pointer;
    transition: all 0.15s;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.image-upload:hover {
    border-color: var(--primary);
    background: rgba(255, 97, 0, 0.03);
}
.image-upload-text {
    font-size: 11px;
    color: var(--text-dim);
    letter-spacing: 0.08em;
}
.image-upload__preview {
    max-width: 100%;
    max-height: 140px;
    object-fit: contain;
}

/* Badge selector */
.tm-badge-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    max-height: 180px;
    overflow-y: auto;
}
.tm-badge-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border: 1px solid var(--border);
    background: var(--surface);
    cursor: pointer;
    font-family: var(--mono);
    font-size: 11px;
    color: var(--text-muted);
    letter-spacing: 0.06em;
    transition: all 0.12s;
    user-select: none;
}
.tm-badge-chip:hover {
    border-color: var(--primary);
    color: var(--text);
}
.tm-badge-chip--on {
    border-color: var(--accent);
    color: var(--accent);
    background: rgba(193, 245, 39, 0.07);
}
.tm-badge-chip__img {
    width: 18px;
    height: 18px;
    object-fit: cover;
}

/* Selected badge tags */
.tm-selected-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}
.badge-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    font-size: 10px;
    letter-spacing: 0.1em;
    background: rgba(42, 44, 46, 0.6);
    color: var(--text-muted);
    border: 1px solid var(--border);
}
.badge-tag--on {
    background: rgba(193, 245, 39, 0.1);
    color: var(--accent);
    border-color: rgba(193, 245, 39, 0.3);
}
.badge-tag__rm {
    background: none;
    border: none;
    color: rgba(193, 245, 39, 0.6);
    cursor: pointer;
    font-size: 10px;
    padding: 0;
    line-height: 1;
    transition: color 0.12s;
}
.badge-tag__rm:hover { color: var(--accent); }

/* Form actions */
.tm-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 8px;
}

/* Buttons (shared admin panel patterns) */
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
.btn-ghost:disabled { opacity: 0.5; cursor: not-allowed; }

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

/* Trophy list */
.tm-list {
    display: flex;
    flex-direction: column;
}
.trophy-list-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: rgba(14, 15, 17, 0.6);
    border: 1px solid rgba(42, 44, 46, 0.6);
    margin-bottom: 8px;
    transition: border-color 0.15s;
}
.trophy-list-item:hover { border-color: rgba(255, 97, 0, 0.2); }
.trophy-item-art {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--surface-2);
    border: 1px solid var(--border);
    filter: drop-shadow(0 0 10px rgba(255, 97, 0, 0.15));
    overflow: hidden;
}
.trophy-item-art__img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.trophy-item-art__empty {
    font-family: var(--display);
    font-size: 20px;
    color: var(--text-dim);
}
.trophy-item-info {
    flex: 1;
    min-width: 0;
}
.trophy-item-name {
    font-family: var(--display);
    font-size: 20px;
    color: var(--text);
    letter-spacing: 0.02em;
    line-height: 1;
}
.trophy-item-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    color: var(--text-dim);
    letter-spacing: 0.08em;
    margin-top: 4px;
    flex-wrap: wrap;
}
.trophy-item-meta--desc {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 360px;
}
.trophy-item-econ { color: var(--primary); }
.trophy-req-sep { color: var(--text-dim); }
.trophy-req-thumb {
    width: 18px;
    height: 18px;
    object-fit: cover;
    border: 1px solid var(--border);
}
.trophy-req-more {
    font-size: 10px;
    color: var(--text-dim);
    padding: 0 2px;
}
.trophy-item-pills {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
    flex-wrap: wrap;
    align-items: center;
}
.trophy-item-pill {
    padding: 3px 8px;
    font-size: 9px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    white-space: nowrap;
}
.trophy-item-pill--type-trophy {
    background: rgba(255, 97, 0, 0.12);
    color: var(--primary);
    border: 1px solid rgba(255, 97, 0, 0.25);
}
.trophy-item-pill--type-key {
    background: rgba(193, 245, 39, 0.08);
    color: var(--accent);
    border: 1px solid rgba(193, 245, 39, 0.2);
}
.trophy-item-pill.xp {
    background: rgba(255, 97, 0, 0.12);
    color: var(--primary);
}
.trophy-item-pill.price {
    background: rgba(42, 44, 46, 0.5);
    color: var(--text-muted);
    border: 1px solid var(--border);
}
.trophy-item-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

/* Empty */
.tm-empty {
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: 0.06em;
    padding: 16px 0;
}

/* Responsive */
@media (max-width: 1100px) {
    .field-row-4 { grid-template-columns: 1fr 1fr; }
    .trophy-list-item { flex-wrap: wrap; }
    .trophy-item-info { min-width: 180px; }
}
@media (max-width: 700px) {
    .trophies-header { flex-wrap: wrap; gap: 12px; }
    .field-row-2, .field-row-4 { grid-template-columns: 1fr; }
    .trophy-item-meta--desc { max-width: 100%; }
    .trophy-item-pills, .trophy-item-actions { width: 100%; }
}
</style>

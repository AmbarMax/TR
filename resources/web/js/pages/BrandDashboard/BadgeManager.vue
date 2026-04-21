<template>
    <div class="bm">
        <div class="dual-layout">
            <!-- Badge Rules -->
            <section class="bm-section">
                <div class="sec-label"><span class="label-text">Badge rules</span></div>

                <form class="form-panel" @submit.prevent="createRule">
                    <div class="field-row field-row-2">
                        <div class="field">
                            <label class="field-label">Trigger</label>
                            <select class="field-select" v-model="ruleForm.trigger_type" required>
                                <option value="">Select trigger</option>
                                <option value="voice_minutes">Voice minutes</option>
                                <option value="message_count">Message count</option>
                                <option value="reaction">Reaction</option>
                                <option value="event_join">Event join</option>
                                <option value="poll_answer">Poll answer</option>
                                <option value="role_obtain">Role obtain</option>
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Channel</label>
                            <select class="field-select" v-model="ruleForm.channel_id">
                                <option value="">Any channel</option>
                                <option v-for="ch in channels" :key="ch.id" :value="ch.channel_id">{{ ch.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="field-row field-row-2">
                        <div class="field">
                            <label class="field-label">Threshold</label>
                            <input type="number" class="field-input" v-model.number="ruleForm.threshold" min="1" placeholder="e.g. 60" required />
                        </div>
                        <div class="field">
                            <label class="field-label">Badge</label>
                            <select class="field-select" v-model="ruleForm.badge_id" required>
                                <option value="">Select badge</option>
                                <option v-for="b in badges" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-create" :disabled="submittingRule">
                        {{ submittingRule ? 'Creating…' : '+ Create Rule' }}
                    </button>
                </form>

                <div v-if="loadingRules" class="bm-empty">Loading rules…</div>
                <div v-else-if="rules.length === 0" class="bm-empty">No rules yet.</div>
                <ul v-else class="bm-rules-list">
                    <li v-for="rule in rules" :key="rule.id" class="list-row">
                        <div class="list-row-info">
                            <div class="list-row-name">{{ rule.trigger_type }}</div>
                            <div class="list-row-sub">threshold: {{ rule.threshold }}</div>
                        </div>
                        <span class="status-badge" :class="rule.active ? 'active' : 'inactive'">
                            {{ rule.active ? 'Active' : 'Inactive' }}
                        </span>
                        <div class="list-row-actions">
                            <button class="btn-ghost" @click="toggleRule(rule)">
                                {{ rule.active ? 'Disable' : 'Enable' }}
                            </button>
                        </div>
                    </li>
                </ul>
            </section>

            <!-- Badges -->
            <section class="bm-section">
                <div class="sec-label"><span class="label-text">Badges</span></div>

                <form class="form-panel" @submit.prevent="createBadge">
                    <div class="field-row field-row-2">
                        <div class="field">
                            <label class="field-label">Name</label>
                            <input type="text" class="field-input" v-model="badgeForm.name" placeholder="Badge name" required />
                        </div>
                        <div class="field">
                            <label class="field-label">Type</label>
                            <select class="field-select" v-model.number="badgeForm.type">
                                <option :value="3">Custom (Bot)</option>
                                <option :value="0">Common</option>
                                <option :value="2">Discord Badge</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label class="field-label">Description</label>
                        <input type="text" class="field-input" v-model="badgeForm.description" placeholder="Short description (optional)" />
                    </div>
                    <div class="field">
                        <label class="field-label">Image</label>
                        <div class="image-upload" @click="$refs.imageInput.click()">
                            <img v-if="imagePreview" :src="imagePreview" class="image-upload__preview" alt="preview" />
                            <span v-else class="image-upload-text">Click to select image</span>
                        </div>
                        <input
                            ref="imageInput"
                            type="file"
                            accept="image/*"
                            style="display:none"
                            @change="onImageChange"
                        />
                    </div>
                    <button type="submit" class="btn-create" :disabled="submittingBadge || !badgeForm.image">
                        {{ submittingBadge ? 'Creating…' : '+ Create Badge' }}
                    </button>
                    <span v-if="badgeSuccess" class="bm-success">{{ badgeSuccess }}</span>
                </form>

                <div v-if="editingBadge" class="form-panel bm-edit-form">
                    <div class="sec-label bm-edit-label"><span class="label-text">Edit badge</span></div>
                    <div class="field">
                        <label class="field-label">Name</label>
                        <input type="text" class="field-input" v-model="editForm.name" required />
                    </div>
                    <div class="field">
                        <label class="field-label">Description</label>
                        <input type="text" class="field-input" v-model="editForm.description" />
                    </div>
                    <div class="field">
                        <label class="field-label">Type</label>
                        <select class="field-select" v-model.number="editForm.type">
                            <option :value="3">Custom (Bot)</option>
                            <option :value="0">Common</option>
                            <option :value="2">Discord Badge</option>
                        </select>
                    </div>
                    <div class="bm-edit-actions">
                        <button type="button" class="btn-create" :disabled="savingEdit" @click="saveEdit">
                            {{ savingEdit ? 'Saving…' : 'Save' }}
                        </button>
                        <button type="button" class="btn-ghost" @click="cancelEdit">Cancel</button>
                    </div>
                </div>

                <div v-if="loadingBadges" class="bm-empty">Loading badges…</div>
                <div v-else-if="badges.length === 0" class="bm-empty">No badges found.</div>
                <ul v-else class="bm-badges-list">
                    <li v-for="badge in badges" :key="badge.id" class="list-row">
                        <div class="list-row-thumb">
                            <img v-if="badge.image" :src="'/storage/' + badge.image.replace(/^public\//, '')" class="list-row-thumb__img" alt="">
                        </div>
                        <div class="list-row-info">
                            <div class="list-row-name">{{ badge.name }}</div>
                            <div v-if="badge.description" class="list-row-sub">{{ badge.description }}</div>
                        </div>
                        <div class="list-row-actions">
                            <button class="btn-ghost" @click="startEdit(badge)">Edit</button>
                            <button class="btn-danger" @click="deleteBadge(badge)">Delete</button>
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
            rules: [],
            badges: [],
            channels: [],
            loadingRules: true,
            loadingBadges: true,
            submittingRule: false,
            submittingBadge: false,
            badgeSuccess: '',
            ruleForm: { trigger_type: '', channel_id: '', threshold: null, badge_id: '' },
            badgeForm: { name: '', description: '', type: 3, image: null },
            imagePreview: null,
            editingBadge: null,
            editForm: { name: '', description: '', type: 3 },
            savingEdit: false,
        };
    },
    mounted() {
        this.load();
    },
    methods: {
        async load() {
            try {
                const [rulesRes, badgesRes, chRes] = await Promise.all([
                    api.get('/api/brand/rules'),
                    api.get('/api/brand/badges'),
                    api.get('/api/brand/channels'),
                ]);
                this.rules    = rulesRes.data?.rules    ?? [];
                this.badges   = badgesRes.data?.badges  ?? [];
                this.channels = chRes.data?.channels    ?? [];
            } catch (e) {
                console.error('[BadgeManager] load failed', e);
            } finally {
                this.loadingRules  = false;
                this.loadingBadges = false;
            }
        },
        async createRule() {
            this.submittingRule = true;
            try {
                await api.post('/api/brand/rules', this.ruleForm);
                this.ruleForm = { trigger_type: '', channel_id: '', threshold: null, badge_id: '' };
                await this.load();
            } catch (e) {
                console.error('createRule error', e);
            } finally {
                this.submittingRule = false;
            }
        },
        async toggleRule(rule) {
            try {
                await api.put(`/api/brand/rules/${rule.id}`, { active: !rule.active });
                rule.active = !rule.active;
            } catch (e) {
                console.error('toggleRule error', e);
            }
        },
        onImageChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.badgeForm.image = file;
            this.imagePreview = URL.createObjectURL(file);
        },
        startEdit(badge) {
            this.editingBadge = badge;
            this.editForm = { name: badge.name, description: badge.description ?? '', type: badge.type };
        },
        cancelEdit() {
            this.editingBadge = null;
        },
        async saveEdit() {
            this.savingEdit = true;
            try {
                await api.put(`/api/brand/badges/${this.editingBadge.id}`, this.editForm);
                this.editingBadge = null;
                const badgesRes = await api.get('/api/brand/badges');
                this.badges = badgesRes.data?.badges ?? [];
            } catch (e) {
                console.error('[BadgeManager] saveEdit error', e?.response?.data ?? e);
            } finally {
                this.savingEdit = false;
            }
        },
        async deleteBadge(badge) {
            if (!confirm(`Delete badge "${badge.name}"?`)) return;
            try {
                await api.delete(`/api/brand/badges/${badge.id}`);
                this.badges = this.badges.filter(b => b.id !== badge.id);
            } catch (e) {
                console.error('[BadgeManager] deleteBadge error', e?.response?.data ?? e);
            }
        },
        async createBadge() {
            this.submittingBadge = true;
            this.badgeSuccess = '';
            try {
                const fd = new FormData();
                fd.append('name', this.badgeForm.name);
                fd.append('description', this.badgeForm.description ?? '');
                fd.append('type', this.badgeForm.type);
                fd.append('image', this.badgeForm.image);
                // Do NOT set Content-Type manually — axios must auto-set it with the multipart boundary
                await api.post('/api/brand/badges', fd);
                this.badgeForm = { name: '', description: '', type: 3, image: null };
                this.imagePreview = null;
                this.$refs.imageInput.value = '';
                this.badgeSuccess = 'Badge created successfully.';
                const badgesRes = await api.get('/api/brand/badges');
                this.badges = badgesRes.data?.badges ?? [];
            } catch (e) {
                console.error('[BadgeManager] createBadge error', e?.response?.data ?? e);
            } finally {
                this.submittingBadge = false;
            }
        },
    },
}
</script>

<style scoped>
.bm {
    display: flex;
    flex-direction: column;
    font-family: var(--mono);
    color: var(--text);
}

/* Dual layout */
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
.bm-edit-label { margin-bottom: 12px; }

.bm-section {
    display: flex;
    flex-direction: column;
}

/* Form panel */
.form-panel {
    padding: 24px 28px;
    background: rgba(14, 15, 17, 0.7);
    border: 1px solid rgba(42, 44, 46, 0.7);
    margin-bottom: 24px;
}
.bm-edit-form { border-color: rgba(255, 97, 0, 0.25); }

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

/* Image upload */
.image-upload {
    border: 1px dashed rgba(255, 97, 0, 0.25);
    padding: 22px;
    text-align: center;
    cursor: pointer;
    transition: all 0.15s;
    min-height: 80px;
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
    max-height: 100px;
    object-fit: contain;
}

/* Buttons (shared) */
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

.bm-edit-actions {
    display: flex;
    gap: 10px;
    margin-top: 8px;
}

/* List rows */
.bm-rules-list,
.bm-badges-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
}
.bm-badges-list { max-height: 420px; overflow-y: auto; }

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
.list-row-thumb {
    width: 36px;
    height: 36px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}
.list-row-thumb__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.list-row-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
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

/* Success / empty */
.bm-success {
    font-size: 11px;
    color: var(--accent);
    margin-top: 8px;
    letter-spacing: 0.08em;
}
.bm-empty {
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

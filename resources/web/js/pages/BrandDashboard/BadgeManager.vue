<template>
    <div class="bm">
        <div class="bm-cols">
            <!-- Badge Rules -->
            <section class="bm-section">
                <span class="bd-section-label">Badge Rules</span>

                <form class="bm-form" @submit.prevent="createRule">
                    <div class="bm-form-row">
                        <div class="bm-field">
                            <label>Trigger</label>
                            <select v-model="ruleForm.trigger_type" required>
                                <option value="">Select trigger</option>
                                <option value="voice_minutes">Voice minutes</option>
                                <option value="message_count">Message count</option>
                                <option value="reaction">Reaction</option>
                                <option value="event_join">Event join</option>
                                <option value="poll_answer">Poll answer</option>
                                <option value="role_obtain">Role obtain</option>
                            </select>
                        </div>
                        <div class="bm-field">
                            <label>Channel</label>
                            <select v-model="ruleForm.channel_id">
                                <option value="">Any channel</option>
                                <option v-for="ch in channels" :key="ch.id" :value="ch.channel_id">{{ ch.name }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="bm-form-row">
                        <div class="bm-field">
                            <label>Threshold</label>
                            <input type="number" v-model.number="ruleForm.threshold" min="1" placeholder="e.g. 60" required />
                        </div>
                        <div class="bm-field">
                            <label>Badge</label>
                            <select v-model="ruleForm.badge_id" required>
                                <option value="">Select badge</option>
                                <option v-for="b in badges" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="bm-btn bm-btn--primary" :disabled="submittingRule">
                        {{ submittingRule ? 'Creating…' : '+ Create Rule' }}
                    </button>
                </form>

                <div v-if="loadingRules" class="bm-empty">Loading rules…</div>
                <div v-else-if="rules.length === 0" class="bm-empty">No rules yet.</div>
                <ul v-else class="bm-list">
                    <li v-for="rule in rules" :key="rule.id" class="bm-list-item">
                        <div class="bm-list-item__info">
                            <span class="bm-list-item__name">{{ rule.trigger_type }}</span>
                            <span class="bm-list-item__meta">threshold: {{ rule.threshold }}</span>
                        </div>
                        <span class="bm-status" :class="rule.active ? 'bm-status--on' : 'bm-status--off'">
                            {{ rule.active ? 'Active' : 'Inactive' }}
                        </span>
                        <button class="bm-btn bm-btn--ghost bm-btn--sm" @click="toggleRule(rule)">
                            {{ rule.active ? 'Disable' : 'Enable' }}
                        </button>
                    </li>
                </ul>
            </section>

            <!-- Badges -->
            <section class="bm-section">
                <span class="bd-section-label">Badges</span>

                <form class="bm-form" @submit.prevent="createBadge">
                    <div class="bm-form-row">
                        <div class="bm-field">
                            <label>Name</label>
                            <input type="text" v-model="badgeForm.name" placeholder="Badge name" required />
                        </div>
                        <div class="bm-field">
                            <label>Type</label>
                            <select v-model.number="badgeForm.type">
                                <option :value="3">Custom (Bot)</option>
                                <option :value="0">Common</option>
                                <option :value="2">Discord Badge</option>
                            </select>
                        </div>
                    </div>
                    <div class="bm-field">
                        <label>Description</label>
                        <input type="text" v-model="badgeForm.description" placeholder="Short description (optional)" />
                    </div>
                    <div class="bm-field">
                        <label>Image</label>
                        <div class="bm-upload" @click="$refs.imageInput.click()">
                            <img v-if="imagePreview" :src="imagePreview" class="bm-upload-preview" alt="preview" />
                            <span v-else class="bm-upload-placeholder">Click to select image</span>
                        </div>
                        <input
                            ref="imageInput"
                            type="file"
                            accept="image/*"
                            style="display:none"
                            @change="onImageChange"
                        />
                    </div>
                    <button type="submit" class="bm-btn bm-btn--primary" :disabled="submittingBadge || !badgeForm.image">
                        {{ submittingBadge ? 'Creating…' : '+ Create Badge' }}
                    </button>
                    <span v-if="badgeSuccess" class="bm-success">{{ badgeSuccess }}</span>
                </form>

                <div v-if="editingBadge" class="bm-form bm-edit-form">
                    <span class="bd-section-label" style="margin-bottom:8px">Edit Badge</span>
                    <div class="bm-field">
                        <label>Name</label>
                        <input type="text" v-model="editForm.name" required />
                    </div>
                    <div class="bm-field">
                        <label>Description</label>
                        <input type="text" v-model="editForm.description" />
                    </div>
                    <div class="bm-field">
                        <label>Type</label>
                        <select v-model.number="editForm.type">
                            <option :value="3">Custom (Bot)</option>
                            <option :value="0">Common</option>
                            <option :value="2">Discord Badge</option>
                        </select>
                    </div>
                    <div class="bm-form-row" style="margin-top:4px">
                        <button type="button" class="bm-btn bm-btn--primary" :disabled="savingEdit" @click="saveEdit">
                            {{ savingEdit ? 'Saving…' : 'Save' }}
                        </button>
                        <button type="button" class="bm-btn bm-btn--ghost" @click="cancelEdit">Cancel</button>
                    </div>
                </div>

                <div v-if="loadingBadges" class="bm-empty">Loading badges…</div>
                <div v-else-if="badges.length === 0" class="bm-empty">No badges found.</div>
                <ul v-else class="bm-list bm-list--scroll">
                    <li v-for="badge in badges" :key="badge.id" class="bm-list-item">
                        <img v-if="badge.image" :src="'/storage/' + badge.image.replace(/^public\//, '')" class="bm-badge-img" alt="">
                        <div class="bm-list-item__info">
                            <span class="bm-list-item__name">{{ badge.name }}</span>
                            <span v-if="badge.description" class="bm-list-item__meta">{{ badge.description }}</span>
                        </div>
                        <button class="bm-btn bm-btn--ghost bm-btn--sm" @click="startEdit(badge)">Edit</button>
                        <button class="bm-btn bm-btn--danger bm-btn--sm" @click="deleteBadge(badge)">Delete</button>
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
.bm-success {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #c1f527;
    margin-top: 4px;
}

.bm { display: flex; flex-direction: column; gap: 32px; }

.bm-cols {
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

.bm-section { display: flex; flex-direction: column; gap: 16px; }

.bm-form {
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.bm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.bm-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.bm-field label {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    color: #9a9590;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.bm-field input,
.bm-field select {
    background: #1a1c1f;
    border: 1px solid #2a2c2e;
    border-radius: 4px;
    padding: 8px 12px;
    color: #feeddf;
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    outline: none;
}

.bm-field input:focus,
.bm-field select:focus { border-color: #ff6100; }

.bm-field select option { background: #1a1c1f; }

.bm-btn {
    font-family: 'Share Tech Mono', monospace;
    font-size: 13px;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    padding: 8px 16px;
    transition: opacity 0.15s;
}

.bm-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.bm-btn--primary { background: #c1f527; color: #000003; align-self: flex-start; }
.bm-btn--ghost   { background: transparent; border: 1px solid #2a2c2e; color: #9a9590; }
.bm-btn--danger  { background: transparent; border: 1px solid rgba(255,80,80,0.3); color: #ff5050; }
.bm-btn--sm      { padding: 4px 10px; font-size: 11px; }

.bm-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.bm-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #0e0f11;
    border: 1px solid #2a2c2e;
    border-radius: 6px;
    padding: 12px 16px;
}

.bm-list-item__info { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.bm-list-item__name { font-family: 'Share Tech Mono', monospace; font-size: 13px; color: #feeddf; }
.bm-list-item__meta { font-family: 'Share Tech Mono', monospace; font-size: 11px; color: #5a5550; }

.bm-status {
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
}

.bm-status--on  { background: rgba(193, 245, 39, 0.1); color: #c1f527; }
.bm-status--off { background: rgba(90, 85, 80, 0.2);   color: #5a5550; }

.bm-badge-img { width: 32px; height: 32px; border-radius: 4px; object-fit: cover; flex-shrink: 0; }

.bm-upload {
    background: #1a1c1f;
    border: 1px dashed #2a2c2e;
    border-radius: 4px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    overflow: hidden;
    transition: border-color 0.15s;
}

.bm-upload:hover { border-color: #ff6100; }

.bm-upload-preview {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.bm-upload-placeholder {
    font-family: 'Share Tech Mono', monospace;
    font-size: 12px;
    color: #5a5550;
}

.bm-empty { font-family: 'Share Tech Mono', monospace; font-size: 13px; color: #5a5550; padding: 16px 0; }

.bm-list--scroll { max-height: 320px; overflow-y: auto; }

.bm-edit-form { margin-bottom: 0; }

@media (max-width: 900px) {
    .bm-cols { grid-template-columns: 1fr; }
    .bm-form-row { grid-template-columns: 1fr; }
}
</style>

<template>
  <transition name="mb-fade">
    <div v-if="show" class="mb-modal" @click.self="$emit('close')">
      <div class="mb-card mb-card--wide" role="dialog" aria-modal="true">
        <button class="mb-close" type="button" aria-label="Close" @click="$emit('close')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>

        <h2 class="mb-title">Edit @{{ brand?.username }}</h2>

        <form class="mb-form" @submit.prevent="submit">
          <div class="mb-field-row">
            <div class="mb-field">
              <label class="mb-label">Accent color</label>
              <div class="mb-accent-input">
                <input
                  type="color"
                  class="mb-color-picker"
                  :value="form.accent_color || '#ff6100'"
                  @input="form.accent_color = $event.target.value"
                />
                <input
                  v-model="form.accent_color"
                  type="text"
                  class="mb-input"
                  placeholder="#ff6100"
                  pattern="^#[0-9a-fA-F]{6}$"
                />
              </div>
            </div>

            <div class="mb-field">
              <label class="mb-label">Featured</label>
              <label class="mb-toggle">
                <input v-model="form.is_featured" type="checkbox" />
                <span class="mb-toggle-track"></span>
                <span class="mb-toggle-text">{{ form.is_featured ? "Featured" : "Not featured" }}</span>
              </label>
            </div>
          </div>

          <div class="mb-field-row">
            <div class="mb-field">
              <label class="mb-label">Verified</label>
              <label class="mb-toggle">
                <input
                  type="checkbox"
                  :checked="!!form.verified_at"
                  @change="onVerifyToggle($event.target.checked)"
                />
                <span class="mb-toggle-track"></span>
                <span class="mb-toggle-text">
                  <template v-if="form.verified_at">Verified · {{ formatDate(form.verified_at) }}</template>
                  <template v-else>Not verified</template>
                </span>
              </label>
            </div>
          </div>

          <div class="mb-field">
            <label class="mb-label">Tagline <span class="mb-hint">{{ (form.tagline || "").length }}/80</span></label>
            <input
              v-model="form.tagline"
              type="text"
              class="mb-input"
              maxlength="80"
              placeholder="A short brand tagline shown on the Hall hero"
            />
          </div>

          <div class="mb-field">
            <label class="mb-label">Banner URL <span class="mb-hint">(optional)</span></label>
            <input
              v-model="form.banner"
              type="text"
              class="mb-input"
              placeholder="https://… or /storage/…"
            />
          </div>

          <div v-if="error" class="mb-error">{{ error }}</div>
          <div v-if="fieldErrors" class="mb-field-errors">
            <ul>
              <li v-for="(msgs, field) in fieldErrors" :key="field">
                <strong>{{ field }}:</strong> {{ Array.isArray(msgs) ? msgs[0] : msgs }}
              </li>
            </ul>
          </div>

          <div class="mb-actions">
            <button class="mb-btn mb-btn-ghost" type="button" :disabled="submitting" @click="$emit('close')">Cancel</button>
            <button class="mb-btn mb-btn-primary" type="submit" :disabled="submitting">
              {{ submitting ? "Saving…" : "Save changes" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script>
import api from "../../../api/api.js";

export default {
  name: "EditBrandModal",
  props: {
    show: { type: Boolean, default: false },
    brand: { type: Object, default: null },
  },
  emits: ["close", "saved"],
  data() {
    return {
      form: {
        accent_color: "",
        tagline: "",
        verified_at: null,
        is_featured: false,
        banner: "",
      },
      submitting: false,
      error: "",
      fieldErrors: null,
    };
  },
  watch: {
    show(open) {
      if (open && this.brand) this.hydrate();
    },
    brand(b) {
      if (b && this.show) this.hydrate();
    },
  },
  methods: {
    hydrate() {
      this.form.accent_color = this.brand?.accent_color || "";
      this.form.tagline = this.brand?.tagline || "";
      this.form.verified_at = this.brand?.verified_at || null;
      this.form.is_featured = !!this.brand?.is_featured;
      this.form.banner = this.brand?.banner || "";
      this.error = "";
      this.fieldErrors = null;
    },
    onVerifyToggle(checked) {
      this.form.verified_at = checked ? new Date().toISOString() : null;
    },
    formatDate(iso) {
      try {
        return new Date(iso).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
      } catch (e) { return ""; }
    },
    payload() {
      const p = {};
      // Send only the keys that changed/are non-trivial.
      if (this.form.accent_color !== (this.brand?.accent_color || "")) {
        p.accent_color = this.form.accent_color || null;
      }
      if (this.form.tagline !== (this.brand?.tagline || "")) {
        p.tagline = this.form.tagline || null;
      }
      const incomingVerified = this.form.verified_at || null;
      const currentVerified = this.brand?.verified_at || null;
      if (incomingVerified !== currentVerified) {
        p.verified_at = incomingVerified;
      }
      if (this.form.is_featured !== !!this.brand?.is_featured) {
        p.is_featured = !!this.form.is_featured;
      }
      if (this.form.banner !== (this.brand?.banner || "")) {
        p.banner = this.form.banner || null;
      }
      return p;
    },
    async submit() {
      if (this.submitting || !this.brand) return;
      const payload = this.payload();
      if (!Object.keys(payload).length) {
        this.$emit("close");
        return;
      }
      this.submitting = true;
      this.error = "";
      this.fieldErrors = null;
      try {
        const { data } = await api.patch(
          `/api/admin/brands/${encodeURIComponent(this.brand.username)}`,
          payload
        );
        this.$emit("saved", data?.data);
      } catch (e) {
        const status = e.response?.status;
        if (status === 422) {
          this.fieldErrors = e.response?.data?.errors || null;
          this.error = "Validation failed. Check the highlighted fields.";
        } else {
          this.error = e.response?.data?.message || "Save failed.";
        }
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

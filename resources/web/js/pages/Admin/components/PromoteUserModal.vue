<template>
  <transition name="mb-fade">
    <div v-if="show" class="mb-modal" @click.self="$emit('close')">
      <div class="mb-card" role="dialog" aria-modal="true">
        <button class="mb-close" type="button" aria-label="Close" @click="$emit('close')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>

        <h2 class="mb-title">Promote user to brand</h2>
        <p class="mb-body">
          Search for a player account. Promoting flips <code>account_type</code> to <code>brand</code> and assigns the <code>brand_admin</code> role.
        </p>

        <input
          v-model="query"
          type="text"
          class="mb-search"
          placeholder="Search by username, name or email…"
          autofocus
          @input="onSearchInput"
        />

        <div class="mb-results">
          <div v-if="searching" class="mb-state">Searching…</div>
          <div v-else-if="!results.length && query" class="mb-state">No matches.</div>
          <div v-else-if="!results.length" class="mb-state mb-state-hint">Type to search.</div>
          <button
            v-for="user in results"
            :key="user.id"
            class="mb-result-row"
            :class="{ selected: selected?.id === user.id }"
            type="button"
            @click="selected = user"
          >
            <div class="mb-result-avatar">{{ initials(user) }}</div>
            <div class="mb-result-info">
              <div class="mb-result-name">@{{ user.username }}</div>
              <div class="mb-result-meta">{{ user.name || user.email }}</div>
            </div>
            <span v-if="selected?.id === user.id" class="mb-result-check">✓</span>
          </button>
        </div>

        <div v-if="error" class="mb-error">{{ error }}</div>

        <div class="mb-actions">
          <button class="mb-btn mb-btn-ghost" type="button" :disabled="submitting" @click="$emit('close')">Cancel</button>
          <button
            class="mb-btn mb-btn-primary"
            type="button"
            :disabled="!selected || submitting"
            @click="submit"
          >
            {{ submitting ? "Promoting…" : selected ? `Promote @${selected.username}` : "Select a user" }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import api from "../../../api/api.js";

export default {
  name: "PromoteUserModal",
  props: {
    show: { type: Boolean, default: false },
  },
  emits: ["close", "promoted"],
  data() {
    return {
      query: "",
      results: [],
      selected: null,
      searching: false,
      submitting: false,
      error: "",
      debounceId: null,
    };
  },
  watch: {
    show(open) {
      if (open) {
        this.reset();
      } else {
        clearTimeout(this.debounceId);
      }
    },
  },
  beforeUnmount() {
    clearTimeout(this.debounceId);
  },
  methods: {
    reset() {
      this.query = "";
      this.results = [];
      this.selected = null;
      this.error = "";
      this.submitting = false;
    },
    onSearchInput() {
      this.selected = null;
      clearTimeout(this.debounceId);
      this.debounceId = setTimeout(this.runSearch, 250);
    },
    async runSearch() {
      if (!this.query || this.query.length < 1) {
        this.results = [];
        return;
      }
      this.searching = true;
      try {
        const { data } = await api.get("/api/admin/users/searchable", {
          params: { q: this.query },
        });
        this.results = data?.data ?? [];
      } catch (e) {
        this.results = [];
        this.error = "Search failed.";
      } finally {
        this.searching = false;
      }
    },
    initials(user) {
      const src = (user.username || user.name || "?").trim();
      return src.slice(0, 2).toUpperCase();
    },
    async submit() {
      if (!this.selected || this.submitting) return;
      this.submitting = true;
      this.error = "";
      try {
        const { data } = await api.post("/api/admin/brands/promote", { user_id: this.selected.id });
        this.$emit("promoted", data?.data);
      } catch (e) {
        this.error = e.response?.data?.message || "Promotion failed.";
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

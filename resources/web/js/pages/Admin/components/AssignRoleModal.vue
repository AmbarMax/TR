<template>
  <transition name="mr-fade">
    <div v-if="show" class="mr-modal" @click.self="$emit('close')">
      <div class="mr-card" role="dialog" aria-modal="true">
        <button class="mr-close" type="button" aria-label="Close" @click="$emit('close')">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>

        <h2 class="mr-title">Assign staff role</h2>
        <p class="mr-body">
          Search any user, then pick one of <code>tr_moderator</code>, <code>tr_admin</code>, or <code>tr_superadmin</code>.
        </p>

        <div class="mr-field">
          <label class="mr-label">User</label>
          <input
            v-if="!lockedUser"
            v-model="query"
            type="text"
            class="mr-input"
            placeholder="Search by username, name or email…"
            autofocus
            @input="onSearchInput"
          />
          <div v-else class="mr-locked-user">
            <div class="mr-locked-avatar">{{ initials(lockedUser) }}</div>
            <div class="mr-locked-info">
              <div class="mr-locked-name">@{{ lockedUser.username }}</div>
              <div class="mr-locked-meta">{{ lockedUser.name || lockedUser.email }}</div>
            </div>
          </div>
        </div>

        <div v-if="!lockedUser" class="mr-results">
          <div v-if="searching" class="mr-state">Searching…</div>
          <div v-else-if="!results.length && query" class="mr-state">No matches.</div>
          <div v-else-if="!results.length" class="mr-state mr-state-hint">Type to search.</div>
          <button
            v-for="user in results"
            :key="user.id"
            class="mr-result-row"
            :class="{ selected: selected?.id === user.id }"
            type="button"
            @click="selected = user"
          >
            <div class="mr-result-avatar">{{ initials(user) }}</div>
            <div class="mr-result-info">
              <div class="mr-result-name">@{{ user.username }}</div>
              <div class="mr-result-meta">
                {{ user.name || user.email }}
                <span v-if="user.roles && user.roles.length" class="mr-result-roles">
                  · {{ user.roles.join(", ") }}
                </span>
              </div>
            </div>
            <span v-if="selected?.id === user.id" class="mr-result-check">✓</span>
          </button>
        </div>

        <div class="mr-field">
          <label class="mr-label">Role</label>
          <div class="mr-role-options">
            <button
              v-for="opt in availableRoles"
              :key="opt.value"
              type="button"
              class="mr-role-option"
              :class="{ selected: chosenRole === opt.value }"
              @click="chosenRole = opt.value"
            >
              <span class="mr-role-option-name">{{ opt.label }}</span>
              <span class="mr-role-option-id">{{ opt.value }}</span>
              <span v-if="opt.disabledReason" class="mr-role-option-locked">{{ opt.disabledReason }}</span>
            </button>
          </div>
          <p v-if="!callerIsSuper" class="mr-hint">
            tr_superadmin is hidden because only an existing tr_superadmin can grant it.
          </p>
        </div>

        <div v-if="error" class="mr-error">{{ error }}</div>
        <div v-if="fieldErrors" class="mr-field-errors">
          <ul>
            <li v-for="(msgs, field) in fieldErrors" :key="field">
              <strong>{{ field }}:</strong> {{ Array.isArray(msgs) ? msgs[0] : msgs }}
            </li>
          </ul>
        </div>

        <div class="mr-actions">
          <button class="mr-btn mr-btn-ghost" type="button" :disabled="submitting" @click="$emit('close')">Cancel</button>
          <button
            class="mr-btn mr-btn-primary"
            type="button"
            :disabled="!canSubmit || submitting"
            @click="submit"
          >
            {{ submitting ? "Assigning…" : ctaLabel }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import api from "../../../api/api.js";

const ROLE_OPTIONS = [
  { value: "tr_moderator",  label: "Moderator" },
  { value: "tr_admin",      label: "Admin" },
  { value: "tr_superadmin", label: "Super Admin" },
];

export default {
  name: "AssignRoleModal",
  props: {
    show: { type: Boolean, default: false },
    lockedUser: { type: Object, default: null },
    callerIsSuper: { type: Boolean, default: false },
  },
  emits: ["close", "assigned"],
  data() {
    return {
      query: "",
      results: [],
      selected: null,
      chosenRole: null,
      searching: false,
      submitting: false,
      error: "",
      fieldErrors: null,
      debounceId: null,
    };
  },
  computed: {
    availableRoles() {
      return ROLE_OPTIONS.filter(opt => {
        if (opt.value === "tr_superadmin" && !this.callerIsSuper) return false;
        return true;
      });
    },
    targetUser() {
      return this.lockedUser || this.selected;
    },
    canSubmit() {
      return !!(this.targetUser && this.chosenRole);
    },
    ctaLabel() {
      if (this.targetUser && this.chosenRole) {
        const roleLabel = ROLE_OPTIONS.find(o => o.value === this.chosenRole)?.label || this.chosenRole;
        return `Assign ${roleLabel} to @${this.targetUser.username}`;
      }
      return this.targetUser ? "Pick a role" : "Pick a user and role";
    },
  },
  watch: {
    show(open) {
      if (open) this.reset();
      else clearTimeout(this.debounceId);
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
      this.chosenRole = null;
      this.error = "";
      this.fieldErrors = null;
      this.submitting = false;
    },
    onSearchInput() {
      this.selected = null;
      clearTimeout(this.debounceId);
      this.debounceId = setTimeout(this.runSearch, 250);
    },
    async runSearch() {
      if (!this.query) {
        this.results = [];
        return;
      }
      this.searching = true;
      try {
        const { data } = await api.get("/api/admin/users/search-for-role", {
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
      const src = (user?.username || user?.name || "?").trim();
      return src.slice(0, 2).toUpperCase();
    },
    async submit() {
      if (!this.canSubmit || this.submitting) return;
      const user = this.targetUser;
      this.submitting = true;
      this.error = "";
      this.fieldErrors = null;
      try {
        const { data } = await api.post(
          `/api/admin/users/${encodeURIComponent(user.username)}/roles`,
          { role: this.chosenRole }
        );
        this.$emit("assigned", { user: data?.data, role: this.chosenRole });
      } catch (e) {
        const status = e.response?.status;
        if (status === 403) {
          this.error = e.response?.data?.message || "Forbidden.";
        } else if (status === 422) {
          this.fieldErrors = e.response?.data?.errors || null;
          this.error = e.response?.data?.message || "Validation failed.";
        } else {
          this.error = e.response?.data?.message || "Assignment failed.";
        }
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

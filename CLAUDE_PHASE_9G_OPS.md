# CLAUDE_PHASE_9G_OPS.md — Phase 9G: Forge redesign

> **Audience:** Claude Code (CLI). Run from `~/Documents/trophyroom`.
> **Read first:** `CLAUDE.md` + this brief.
> **Scope:** Rediseño completo de `Forge.vue` según `forge.html`. Catálogo de trophies agrupados por estado (Ready / In progress / Locked / Forged) con filter pills, cards grandes con progress + badges visuales + meta pills, y modal de confirmación para el forge action. Deep-linking desde Dashboard/Trophy Room.

---

## Goal

Reemplazar `pages/Forge.vue` por el nuevo diseño:

1. **Page hero** — title "Forge your trophies" con "trophies" en naranja + underline + subtitle + 3 quick-stats (Available / Ready / Forged)
2. **Filter bar** — pills con counts por estado (All / Ready to forge / Almost ready / In progress / Forged)
3. **Secciones agrupadas por estado** (aparecen solo las con contenido):
   - Ready to forge → cards con flag verde, progress 100%, botón "Forge now" (naranja sólido)
   - In progress → progress parcial, botón "Missing N badge" (disabled state)
   - Locked → cards grises con ?, 0 badges owned
   - Forged → cards con checkmark chartreuse, "Already forged"
4. **Forge cards** con asset trophy 120px + name + subtitle + description + badges grid (owned vs missing) + progress bar + pills (XP / Ambar cost / Uru reward) + CTA contextual
5. **Modal de confirmación** — se dispara con click Forge now. Trophy art 140×140, costs breakdown (Ambar spent / Uru earned), cancel/confirm. Confirm llama API, cierra modal, refresca data.
6. **Deep-linking:** `?trophy={id}` auto-abre el modal si el trophy está Ready. Toast informativo si está Locked/In progress/Forged.
7. **Terminal footer** con counts.

---

## Non-goals

- NO tocar Dashboard, Trophy Room, Virtual Hall, sidebar, main-header, App.vue, Main.vue.
- NO tocar legacy `forge-card.vue` component (se reemplaza por markup inline o nuevo parts/forge-trophy-card.vue si preferís reutilizable).
- NO modificar backend endpoints — solo consumir los existentes (`/api/forge`, `/api/forge/trophies`, `/api/forge/claim/{id}` o similar).
- NO implementar sort UI (mantenemos orden del backend).
- NO implementar filtros adicionales (solo los 4-5 del mockup).
- NO modificar la lógica de claim del backend — solo disparar la action.

---

## Pre-work (OBLIGATORIO antes del Step 1)

### 1. Leer Forge.vue actual

```bash
cat resources/web/js/pages/Forge.vue
```

Identificar:
- Qué endpoints consume y con qué shape
- Cómo dispara el claim/forge action (método, endpoint, payload)
- Si tiene modal existente o usa un componente global de modal
- Componentes importados (probablemente `forge-card.vue`)

### 2. Endpoint de claim/forge

```bash
grep -rn "forge.*claim\|claim.*trophy\|/forge/" resources/web/js/ | grep -v node_modules | head -20
grep -rn "forgeClaimed\|confirmForge\|claim" app/Http/Controllers 2>/dev/null | head -20
grep -n "forge" routes/api.php | head -20
```

**Reportar:**
- Endpoint real del forge action: método + URL + payload esperado + shape del response
- ¿El response devuelve el user actualizado (con nuevos balances) o hay que recargar?
- ¿Qué errores puede tirar (insufficient Ambar, missing badges, already forged)? Status codes.

### 3. Shape real de `/api/forge` vs `/api/forge/trophies`

Ya vimos en 9D/9E que:
- `/api/forge` devuelve el **catálogo** (todas las trophies con su array `badges`)
- `/api/forge/trophies` devuelve las **forjadas por el user** (con pivot)

Confirmar qué campos tiene cada trophy en `/api/forge`:
- `badges` array (con qué shape por badge)
- `price` (Ambar cost), `receive` (Uru reward), `weight` (XP?)
- `image` (filename)
- `series`, `is_nft` flags
- Algún campo `required_badges_count` o solo `badges.length`
- Si backend ya marca estados (`status: 'ready'/'locked'/...`) o solo devuelve los raw fields

### 4. Shape real de `/api/badges`

¿Tiene `badge.id`, `badge.integration`, `badge.image`? Ya confirmamos en phases anteriores — solo revalidar que nada cambió.

### 5. User balance real

```bash
grep -n "balances\|ambar.*balance\|user\.ambar" resources/web/js/store/store.js | head -10
```

Confirmar que `store.state.user.balances.ambar` existe (se usa para validar en el modal si el user puede pagar).

**Reportar los hallazgos** antes de avanzar a Step 1.

---

## Steps

### Step 1 — Rediseñar Forge.vue

**Archivo:** `resources/web/js/pages/Forge.vue`

Reemplazar completo. Ajustar según Pre-work.

#### Template

```vue
<template>
  <div class="forge-page">

    <!-- PAGE HERO -->
    <section class="forge-hero">
      <div class="forge-hero-bg"></div>
      <div class="forge-hero-content">
        <div class="forge-hero-left">
          <div class="page-tag">The Forge</div>
          <h1 class="page-title">
            Forge your <span class="page-title-accent">trophies</span>
          </h1>
          <p class="page-subtitle">
            Collect badges from your connected platforms, then spend Ambar to forge them into trophies.
            Each trophy earns Uru — the currency that unlocks real rewards.
          </p>
        </div>
        <aside class="forge-hero-aside">
          <div class="quick-stats">
            <div class="qstat">
              <div class="qstat-val">{{ availableCount }}</div>
              <div class="qstat-lbl">Available</div>
            </div>
            <div class="qstat qstat--ready">
              <div class="qstat-val">{{ readyCount }}</div>
              <div class="qstat-lbl">Ready</div>
            </div>
            <div class="qstat qstat--forged">
              <div class="qstat-val">{{ forgedCount }}</div>
              <div class="qstat-lbl">Forged</div>
            </div>
          </div>
        </aside>
      </div>
    </section>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <span class="filter-bar-label">Filter</span>
      <button
        v-for="filter in filterOptions"
        :key="filter.key"
        class="filter-pill"
        :class="{ 'filter-pill--active': activeFilter === filter.key }"
        @click="activeFilter = filter.key"
      >
        {{ filter.label }}
        <span class="filter-count">{{ filter.count }}</span>
      </button>
    </div>

    <!-- LOADING -->
    <div v-if="loading" class="forge-loading">
      <div class="forge-loading-pulse"></div>
      <p>Loading the forge...</p>
    </div>

    <!-- CONTENT -->
    <div v-else class="forge-content">

      <!-- Dynamic sections by status -->
      <section
        v-for="section in visibleSections"
        :key="section.key"
        class="forge-section"
      >
        <div class="section-label">
          <span class="label-text">{{ section.title }}</span>
          <span class="section-meta">
            {{ section.trophies.length }} {{ section.trophies.length === 1 ? 'trophy' : 'trophies' }}{{ section.metaSuffix || '' }}
          </span>
        </div>

        <div class="forge-grid">
          <article
            v-for="trophy in section.trophies"
            :key="trophy.id"
            class="forge-card"
            :class="[
              `forge-card--${trophy._status}`,
              { 'forge-card--deep-link': trophy._status === 'ready' && trophy.id === deepLinkedTrophyId }
            ]"
          >
            <div v-if="trophy._status === 'ready'" class="ready-flag">
              <span class="ready-flag-dot"></span>
              <span>Ready</span>
            </div>
            <div v-if="trophy._status === 'forged'" class="forged-flag">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              <span>Forged</span>
            </div>

            <div class="fc-header">
              <div class="fc-asset">
                <img v-if="trophy.image" :src="trophyImage(trophy)" :alt="trophy.name">
                <svg v-else viewBox="0 0 100 100">
                  <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" :fill="trophy._status === 'locked' ? '#0e0f11' : '#0e0f11'" :stroke="trophyStrokeColor(trophy)" stroke-width="2" :stroke-dasharray="trophy._status === 'locked' ? '4 3' : ''"/>
                  <text v-if="trophy._status === 'locked'" x="50" y="58" text-anchor="middle" font-family="VT323" font-size="36" fill="#5a5550">?</text>
                  <g v-else>
                    <polygon points="50,22 75,35 50,48 25,35" :fill="trophyFillColor(trophy, 0)"/>
                    <polygon points="50,48 75,35 75,65 50,78" :fill="trophyFillColor(trophy, 1)"/>
                    <polygon points="50,48 25,35 25,65 50,78" :fill="trophyFillColor(trophy, 2)"/>
                  </g>
                </svg>
              </div>
              <div class="fc-info">
                <div class="fc-name">{{ trophy.name }}</div>
                <div class="fc-sub">
                  <span v-if="trophy.series">Series {{ trophy.series }}</span>
                  <span v-else-if="trophy.is_nft">NFT · Exclusive</span>
                  <span v-else>Standard</span>
                </div>
              </div>
            </div>

            <p class="fc-desc">{{ trophy.description || 'No description available.' }}</p>

            <!-- Badge grid: owned vs missing -->
            <div v-if="trophy.badges && trophy.badges.length" class="fc-badges">
              <div
                v-for="(badge, i) in trophy.badges.slice(0, 6)"
                :key="`${trophy.id}-b${i}`"
                class="fc-badge"
                :class="{
                  'fc-badge--owned': isOwnedBadge(badge),
                  'fc-badge--missing': !isOwnedBadge(badge)
                }"
                :title="badge.name || ''"
              >
                <img v-if="badge.image" :src="badgeImage(badge)" :alt="badge.name">
                <span v-else class="fc-badge-emoji">{{ badgeEmoji(badge) }}</span>
              </div>
              <div v-if="trophy.badges.length > 6" class="fc-badge fc-badge--more">
                +{{ trophy.badges.length - 6 }}
              </div>
            </div>

            <!-- Progress -->
            <div class="fc-progress">
              <div class="fc-progress-bar">
                <div
                  class="fc-progress-fill"
                  :class="progressFillClass(trophy)"
                  :style="{ width: trophy._progressPercent + '%' }"
                ></div>
              </div>
              <div class="fc-progress-text">
                <span>{{ trophy._badgesOwned }}/{{ trophy._badgesTotal }} badges</span>
                <span>{{ progressLabel(trophy) }}</span>
              </div>
            </div>

            <!-- Meta pills -->
            <div class="fc-meta">
              <span class="fc-pill fc-pill--xp">+{{ trophy.weight || trophy.price || 0 }} XP</span>
              <span class="fc-pill fc-pill--cost">
                <span class="pill-dot"></span>{{ trophy.price || 0 }} Ambar
              </span>
              <span class="fc-pill fc-pill--reward">
                <span class="pill-dot"></span>+{{ trophy.receive || 0 }} Uru
              </span>
            </div>

            <!-- CTA -->
            <div class="fc-cta">
              <button
                v-if="trophy._status === 'ready'"
                class="fc-btn fc-btn--forge"
                @click="openForgeModal(trophy)"
              >
                Forge now →
              </button>
              <button
                v-else-if="trophy._status === 'in_progress' || trophy._status === 'almost'"
                class="fc-btn fc-btn--missing"
                disabled
              >
                Missing {{ trophy._badgesMissing }} {{ trophy._badgesMissing === 1 ? 'badge' : 'badges' }}
              </button>
              <button
                v-else-if="trophy._status === 'locked'"
                class="fc-btn fc-btn--locked"
                disabled
              >
                Locked · 0 badges
              </button>
              <button
                v-else-if="trophy._status === 'forged'"
                class="fc-btn fc-btn--forged"
                disabled
              >
                ✓ Already forged
              </button>
            </div>
          </article>
        </div>
      </section>

      <!-- EMPTY STATE (if filter returns nothing) -->
      <div v-if="!visibleSections.length" class="forge-empty">
        <p>No trophies match this filter.</p>
        <button class="forge-empty-cta" @click="activeFilter = 'all'">Show all</button>
      </div>

    </div>

    <!-- TERMINAL FOOTER -->
    <div class="terminal-strip">
      <div>forge.catalog · {{ allTrophies.length }} trophies<span class="cursor-blink"></span></div>
      <div>balance · {{ userAmbar }} ambar · {{ userUru }} uru</div>
    </div>

    <!-- FORGE CONFIRMATION MODAL -->
    <transition name="modal">
      <div
        v-if="modalTrophy"
        class="forge-modal-overlay"
        @click.self="closeModal"
      >
        <div class="forge-modal" role="dialog" aria-modal="true">
          <button class="fm-close" @click="closeModal" aria-label="Close">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>

          <div class="fm-trophy-art">
            <img v-if="modalTrophy.image" :src="trophyImage(modalTrophy)" :alt="modalTrophy.name">
            <svg v-else viewBox="0 0 100 100">
              <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
              <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
              <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
              <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
            </svg>
          </div>

          <h2 class="fm-title">{{ modalTrophy.name }}</h2>
          <p class="fm-question">Forge this trophy? This action is permanent.</p>

          <div class="fm-costs">
            <div class="fm-cost-cell">
              <div class="fm-cost-val fm-cost-val--spend">-{{ modalTrophy.price || 0 }}</div>
              <div class="fm-cost-lbl">Ambar spent</div>
            </div>
            <div class="fm-cost-cell">
              <div class="fm-cost-val fm-cost-val--earn">+{{ modalTrophy.receive || 0 }}</div>
              <div class="fm-cost-lbl">Uru earned</div>
            </div>
          </div>

          <div v-if="modalError" class="fm-error">{{ modalError }}</div>
          <div v-if="insufficientFunds" class="fm-error">
            You don't have enough Ambar. Need {{ modalTrophy.price }}, have {{ userAmbar }}.
          </div>

          <div class="fm-actions">
            <button
              class="fm-btn fm-btn--cancel"
              @click="closeModal"
              :disabled="forging"
            >Cancel</button>
            <button
              class="fm-btn fm-btn--confirm"
              @click="confirmForge"
              :disabled="forging || insufficientFunds"
            >
              {{ forging ? 'Forging...' : 'Confirm forge' }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- TOAST -->
    <transition name="toast">
      <div v-if="toastMsg" class="forge-toast" :class="`forge-toast--${toastType}`">
        {{ toastMsg }}
      </div>
    </transition>

  </div>
</template>
```

#### Script

**IMPORTANTE:** el Pre-work determina el endpoint real del claim. Ajustar abajo donde dice `TODO: confirm from pre-work`.

```vue
<script>
import api from '../api/api.js';

const FILTER_KEYS = {
  ALL: 'all',
  READY: 'ready',
  ALMOST: 'almost',
  IN_PROGRESS: 'in_progress',
  FORGED: 'forged'
};

export default {
  name: 'Forge',
  data() {
    return {
      allTrophies: [],
      userBadgeIds: new Set(),
      userForgedIds: new Set(),

      loading: true,
      activeFilter: FILTER_KEYS.ALL,

      modalTrophy: null,
      modalError: '',
      forging: false,

      toastMsg: '',
      toastType: 'info',
      toastTimer: null,

      deepLinkedTrophyId: null
    };
  },
  computed: {
    userAmbar() {
      const v = this.$store?.state?.user?.balances?.ambar || 0;
      return Math.floor(v);
    },
    userUru() {
      const v = this.$store?.state?.user?.balances?.uru || 0;
      return Math.floor(v);
    },
    insufficientFunds() {
      if (!this.modalTrophy) return false;
      return this.userAmbar < (this.modalTrophy.price || 0);
    },

    // Enrich each trophy with derived status + progress
    enrichedTrophies() {
      return this.allTrophies.map(t => {
        const trophyBadges = t.badges || [];
        const total = trophyBadges.length;
        const owned = trophyBadges.filter(b => this.userBadgeIds.has(b.id)).length;
        const missing = Math.max(0, total - owned);
        const percent = total === 0 ? 0 : Math.round((owned / total) * 100);
        const isForged = this.userForgedIds.has(t.id);

        let status = 'locked';
        if (isForged) status = 'forged';
        else if (total === 0 || owned === 0) status = 'locked';
        else if (owned >= total) status = 'ready';
        else if (owned === total - 1) status = 'almost';
        else status = 'in_progress';

        return {
          ...t,
          _badgesTotal: total,
          _badgesOwned: owned,
          _badgesMissing: missing,
          _progressPercent: percent,
          _status: status
        };
      });
    },

    // Counts for the filter bar
    availableCount() {
      return this.enrichedTrophies.filter(t => t._status !== 'forged').length;
    },
    readyCount() {
      return this.enrichedTrophies.filter(t => t._status === 'ready').length;
    },
    almostCount() {
      return this.enrichedTrophies.filter(t => t._status === 'almost').length;
    },
    inProgressCount() {
      return this.enrichedTrophies.filter(t => t._status === 'in_progress').length;
    },
    forgedCount() {
      return this.enrichedTrophies.filter(t => t._status === 'forged').length;
    },

    filterOptions() {
      return [
        { key: FILTER_KEYS.ALL, label: 'All', count: this.allTrophies.length },
        { key: FILTER_KEYS.READY, label: 'Ready to forge', count: this.readyCount },
        { key: FILTER_KEYS.ALMOST, label: 'Almost ready', count: this.almostCount },
        { key: FILTER_KEYS.IN_PROGRESS, label: 'In progress', count: this.inProgressCount },
        { key: FILTER_KEYS.FORGED, label: 'Forged', count: this.forgedCount }
      ].filter(f => f.count > 0 || f.key === FILTER_KEYS.ALL);
    },

    // Sections by status, filtered by activeFilter
    visibleSections() {
      const sections = [];

      const include = (key) => {
        if (this.activeFilter === FILTER_KEYS.ALL) return true;
        return this.activeFilter === key;
      };

      const ready = this.enrichedTrophies.filter(t => t._status === 'ready');
      const almost = this.enrichedTrophies.filter(t => t._status === 'almost');
      const inProgress = this.enrichedTrophies.filter(t => t._status === 'in_progress');
      const locked = this.enrichedTrophies.filter(t => t._status === 'locked');
      const forged = this.enrichedTrophies.filter(t => t._status === 'forged');

      if (include(FILTER_KEYS.READY) && ready.length) {
        sections.push({ key: 'ready', title: 'Ready to forge', trophies: ready, metaSuffix: ' awaiting' });
      }
      if (include(FILTER_KEYS.ALMOST) && almost.length) {
        sections.push({ key: 'almost', title: 'Almost ready', trophies: almost });
      }
      if (include(FILTER_KEYS.IN_PROGRESS) && inProgress.length) {
        sections.push({ key: 'in_progress', title: 'In progress', trophies: inProgress });
      }
      if (this.activeFilter === FILTER_KEYS.ALL && locked.length) {
        sections.push({ key: 'locked', title: 'Locked', trophies: locked });
      }
      if (include(FILTER_KEYS.FORGED) && forged.length) {
        sections.push({ key: 'forged', title: 'Forged', trophies: forged, metaSuffix: ' completed' });
      }

      return sections;
    }
  },
  mounted() {
    this.loadData().then(() => {
      this.handleDeepLink();
    });
  },
  watch: {
    '$route.query.trophy'() {
      this.handleDeepLink();
    }
  },
  beforeUnmount() {
    if (this.toastTimer) clearTimeout(this.toastTimer);
  },
  methods: {
    async loadData() {
      this.loading = true;
      try {
        const [forgeRes, badgesRes, trophiesRes] = await Promise.all([
          api.get('/api/forge').catch(() => ({ data: { trophies: [] } })),
          api.get('/api/badges').catch(() => ({ data: { data: [] } })),
          api.get('/api/forge/trophies').catch(() => ({ data: { trophies: [] } }))
        ]);

        this.allTrophies = forgeRes.data?.trophies || forgeRes.data || [];
        const badges = badgesRes.data?.data || badgesRes.data || [];
        this.userBadgeIds = new Set(badges.map(b => b.id));

        const forgedTrophies = trophiesRes.data?.trophies || trophiesRes.data || [];
        this.userForgedIds = new Set(forgedTrophies.map(t => t.id));
      } catch (err) {
        console.error('[Forge] Load failed', err);
        this.showToast('Failed to load forge catalog', 'error');
      } finally {
        this.loading = false;
      }
    },

    handleDeepLink() {
      const trophyId = this.$route.query.trophy;
      if (!trophyId) {
        this.deepLinkedTrophyId = null;
        return;
      }
      const id = parseInt(trophyId);
      this.deepLinkedTrophyId = id;
      const trophy = this.enrichedTrophies.find(t => t.id === id);
      if (!trophy) {
        this.showToast('Trophy not found', 'error');
        return;
      }

      if (trophy._status === 'ready') {
        this.openForgeModal(trophy);
      } else if (trophy._status === 'forged') {
        this.showToast('You already forged this trophy', 'info');
      } else if (trophy._status === 'locked') {
        this.showToast('This trophy is locked — collect badges first', 'info');
      } else {
        this.showToast(`Not ready yet — missing ${trophy._badgesMissing} ${trophy._badgesMissing === 1 ? 'badge' : 'badges'}`, 'info');
      }
    },

    openForgeModal(trophy) {
      this.modalError = '';
      this.modalTrophy = trophy;
    },
    closeModal() {
      if (this.forging) return;
      this.modalTrophy = null;
      this.modalError = '';
      // clean the query param so it doesn't retrigger
      if (this.$route.query.trophy) {
        this.$router.replace({ query: { ...this.$route.query, trophy: undefined } });
      }
    },

    async confirmForge() {
      if (!this.modalTrophy || this.forging || this.insufficientFunds) return;
      this.forging = true;
      this.modalError = '';

      try {
        // TODO: confirm endpoint from pre-work.
        // Likely candidates:
        //   POST /api/forge/claim/{id}
        //   POST /api/forge/{id}/forge
        //   PUT  /api/forge/{id}
        // Use whatever the project actually has.
        const trophyId = this.modalTrophy.id;
        const response = await api.post(`/api/forge/claim/${trophyId}`);

        // Success
        this.showToast(`Forged ${this.modalTrophy.name}!`, 'success');

        // Update local user forged IDs immediately
        this.userForgedIds = new Set([...this.userForgedIds, trophyId]);

        // Update user balances if response contains them
        const updatedUser = response.data?.user;
        if (updatedUser?.balances && this.$store) {
          // Dispatch to store if there's an action, else mutate state directly
          if (this.$store.state.user) {
            this.$store.state.user.balances = {
              ...this.$store.state.user.balances,
              ...updatedUser.balances
            };
          }
        } else {
          // Fallback: refetch badges/trophies to get fresh state. Balances may need store refresh.
          // If store has getUserBalances action, dispatch it.
          if (this.$store?._actions?.getUserBalances) {
            await this.$store.dispatch('getUserBalances');
          }
        }

        this.closeModal();

        // Refresh forged list to confirm
        await this.loadData();
      } catch (err) {
        console.error('[Forge] Claim failed', err);
        const msg = err.response?.data?.message || err.response?.data?.error || 'Forge failed. Please try again.';
        this.modalError = msg;
      } finally {
        this.forging = false;
      }
    },

    isOwnedBadge(badge) {
      return this.userBadgeIds.has(badge.id);
    },

    showToast(msg, type = 'info') {
      this.toastMsg = msg;
      this.toastType = type;
      if (this.toastTimer) clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => { this.toastMsg = ''; }, 2800);
    },

    progressLabel(trophy) {
      const s = trophy._status;
      if (s === 'ready') return 'Complete';
      if (s === 'locked') return 'Locked';
      if (s === 'forged') return 'Forged';
      return 'In progress';
    },
    progressFillClass(trophy) {
      const s = trophy._status;
      if (s === 'ready' || s === 'forged') return 'fc-progress-fill--complete';
      if (s === 'locked') return 'fc-progress-fill--empty';
      return 'fc-progress-fill--in-progress';
    },
    trophyImage(trophy) {
      if (!trophy?.image) return '';
      const img = trophy.image;
      if (img.startsWith('http') || img.startsWith('/')) return img;
      return `/storage/trophies/${img}`;
    },
    badgeImage(badge) {
      if (!badge?.image) return '';
      const img = badge.image;
      if (img.startsWith('http') || img.startsWith('/')) return img;
      const integration = typeof badge.integration === 'object'
        ? badge.integration?.name
        : badge.integration || 'unknown';
      return `/storage/integrations/${integration}/${img}`;
    },
    badgeEmoji(badge) {
      return badge.emoji || '🏆';
    },
    trophyStrokeColor(trophy) {
      if (trophy._status === 'locked') return '#2a2c2e';
      if (trophy._status === 'forged' || trophy._status === 'ready') return '#c1f527';
      return '#ff6100';
    },
    trophyFillColor(trophy, layer) {
      if (trophy._status === 'ready' || trophy._status === 'forged') {
        return ['#c1f527', '#a6d820', '#8ab81a'][layer];
      }
      return ['#ff6100', '#d4500c', '#a33c06'][layer];
    }
  }
};
</script>
```

#### Style (SCSS scoped)

```vue
<style lang="scss" scoped>
.forge-page {
  min-width: 0;
  max-width: 100%;
}

/* ========== HERO ========== */
.forge-hero {
  position: relative;
  padding: 44px 48px 40px;
  overflow: hidden;
  border-bottom: 1px solid rgba(255, 97, 0, 0.08);
}
.forge-hero-bg {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 900px 500px at 20% 50%, rgba(255, 97, 0, 0.18), transparent 65%),
    radial-gradient(ellipse 600px 400px at 85% 50%, rgba(193, 245, 39, 0.08), transparent 65%);
}
.forge-hero-content {
  position: relative;
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 40px;
  align-items: center;
}
.forge-hero-left { min-width: 0; }
.page-tag {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-bottom: 14px;
}
.page-title {
  font-family: var(--display);
  font-size: 56px;
  line-height: 1;
  letter-spacing: 0.03em;
  margin-bottom: 16px;
}
.page-title-accent {
  color: var(--primary);
  text-shadow: 0 0 24px rgba(255, 97, 0, 0.3);
  border-bottom: 3px solid var(--primary);
  padding-bottom: 2px;
}
.page-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.7;
  max-width: 560px;
}

.forge-hero-aside {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.quick-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.qstat {
  padding: 16px 12px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  text-align: center;
}
.qstat--ready {
  border-color: rgba(193, 245, 39, 0.3);
  background: linear-gradient(180deg, rgba(193, 245, 39, 0.06), transparent);
}
.qstat--forged {
  border-color: rgba(255, 97, 0, 0.3);
  background: linear-gradient(180deg, rgba(255, 97, 0, 0.06), transparent);
}
.qstat-val {
  font-family: var(--display);
  font-size: 30px;
  line-height: 1;
  color: var(--text);
}
.qstat--ready .qstat-val {
  color: var(--accent);
  text-shadow: 0 0 12px rgba(193, 245, 39, 0.35);
}
.qstat--forged .qstat-val {
  color: var(--primary);
  text-shadow: 0 0 12px rgba(255, 97, 0, 0.35);
}
.qstat-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  margin-top: 4px;
}

/* ========== FILTER BAR ========== */
.filter-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px 48px;
  border-bottom: 1px dashed rgba(255, 97, 0, 0.08);
  flex-wrap: wrap;
}
.filter-bar-label {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-right: 8px;
}
.filter-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 14px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 20px;
  color: var(--text-muted);
  font-family: var(--mono);
  font-size: 11px;
  letter-spacing: 0.1em;
  cursor: pointer;
  transition: all 0.15s;
}
.filter-pill:hover {
  border-color: rgba(255, 97, 0, 0.4);
  color: var(--text);
}
.filter-pill--active {
  background: var(--accent);
  color: var(--bg);
  border-color: var(--accent);
}
.filter-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 1px 7px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
  font-size: 10px;
  font-weight: bold;
}
.filter-pill:not(.filter-pill--active) .filter-count {
  background: var(--surface-2);
}

/* ========== LOADING ========== */
.forge-loading {
  padding: 80px 48px;
  text-align: center;
}
.forge-loading-pulse {
  width: 36px;
  height: 36px;
  margin: 0 auto 18px;
  border: 2px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
.forge-loading p {
  color: var(--text-muted);
  font-size: 12px;
  letter-spacing: 0.14em;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ========== CONTENT ========== */
.forge-content {
  padding: 32px 48px 40px;
  display: flex;
  flex-direction: column;
  gap: 40px;
}
.forge-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.section-label {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 16px;
}
.label-text {
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
.section-meta {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

/* ========== FORGE CARD ========== */
.forge-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 14px;
}
.forge-card {
  position: relative;
  padding: 22px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: all 0.2s;
}
.forge-card:hover {
  border-color: rgba(255, 97, 0, 0.35);
}
.forge-card--ready {
  border-color: rgba(193, 245, 39, 0.35);
  box-shadow: 0 0 0 1px rgba(193, 245, 39, 0.08) inset;
}
.forge-card--ready::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(193, 245, 39, 0.05), transparent 60%);
  pointer-events: none;
  border-radius: 6px;
}
.forge-card--locked {
  opacity: 0.5;
}
.forge-card--forged {
  border-color: rgba(193, 245, 39, 0.25);
  opacity: 0.88;
}
.forge-card--deep-link {
  box-shadow: 0 0 0 2px var(--accent);
  animation: pulse-highlight 1.8s ease-out;
}
@keyframes pulse-highlight {
  0% { box-shadow: 0 0 0 2px var(--accent), 0 0 32px rgba(193, 245, 39, 0.6); }
  100% { box-shadow: 0 0 0 2px var(--accent), 0 0 0 rgba(193, 245, 39, 0); }
}

.ready-flag, .forged-flag {
  position: absolute;
  top: -1px;
  right: -1px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  font-size: 9px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-weight: bold;
  border-radius: 0 6px 0 4px;
  z-index: 2;
}
.ready-flag {
  background: var(--accent);
  color: var(--bg);
}
.ready-flag-dot {
  width: 5px;
  height: 5px;
  background: var(--bg);
  border-radius: 50%;
  animation: pulse-dot 1.4s ease-in-out infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
.forged-flag {
  background: var(--surface-2);
  color: var(--accent);
  border: 1px solid rgba(193, 245, 39, 0.35);
}

.fc-header {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}
.fc-asset {
  width: 68px;
  height: 68px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 12px rgba(193, 245, 39, 0.12));
}
.forge-card--ready .fc-asset,
.forge-card--forged .fc-asset {
  filter: drop-shadow(0 0 18px rgba(193, 245, 39, 0.22));
}
.forge-card--locked .fc-asset { filter: none; }
.fc-asset img, .fc-asset svg {
  width: 100%; height: 100%; object-fit: contain;
}

.fc-info { min-width: 0; flex: 1; }
.fc-name {
  font-size: 16px;
  color: var(--text);
  letter-spacing: 0.02em;
  line-height: 1.2;
  margin-bottom: 4px;
}
.fc-sub {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.fc-desc {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 3em;
}

.fc-badges {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.fc-badge {
  width: 32px;
  height: 32px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}
.fc-badge img { width: 70%; height: 70%; object-fit: contain; }
.fc-badge-emoji { font-size: 16px; }
.fc-badge--owned {
  border-color: rgba(193, 245, 39, 0.45);
  box-shadow: 0 0 6px rgba(193, 245, 39, 0.2);
}
.fc-badge--missing {
  opacity: 0.35;
  filter: grayscale(0.8);
}
.fc-badge--more {
  color: var(--text-muted);
  font-size: 10px;
  font-family: var(--mono);
}

.fc-progress {
  margin-top: 4px;
}
.fc-progress-bar {
  height: 4px;
  background: var(--surface-3);
  border-radius: 2px;
  overflow: hidden;
  margin-bottom: 6px;
}
.fc-progress-fill {
  height: 100%;
  transition: width 0.4s ease;
}
.fc-progress-fill--empty { background: var(--surface-3); }
.fc-progress-fill--in-progress {
  background: var(--accent);
  box-shadow: 0 0 6px rgba(193, 245, 39, 0.3);
}
.fc-progress-fill--complete {
  background: var(--primary);
  box-shadow: 0 0 8px rgba(255, 97, 0, 0.4);
}
.fc-progress-text {
  display: flex;
  justify-content: space-between;
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.fc-meta {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.fc-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  font-size: 10px;
  letter-spacing: 0.1em;
  border-radius: 3px;
  font-family: var(--mono);
}
.fc-pill--xp {
  background: rgba(193, 245, 39, 0.12);
  color: var(--accent);
}
.fc-pill--cost {
  background: rgba(255, 97, 0, 0.1);
  color: var(--primary);
}
.fc-pill--reward {
  background: rgba(193, 245, 39, 0.08);
  color: var(--accent);
  border: 1px dashed rgba(193, 245, 39, 0.35);
}
.pill-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

.fc-cta { margin-top: 4px; }
.fc-btn {
  width: 100%;
  padding: 11px 16px;
  font-size: 11px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-family: var(--mono);
  border: 1px solid transparent;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.15s;
}
.fc-btn--forge {
  background: var(--primary);
  color: var(--bg);
  font-weight: bold;
}
.fc-btn--forge:hover {
  box-shadow: 0 0 18px rgba(255, 97, 0, 0.5);
  transform: translateY(-1px);
}
.fc-btn--missing,
.fc-btn--locked,
.fc-btn--forged {
  background: transparent;
  color: var(--text-dim);
  border-color: var(--border);
  cursor: default;
}
.fc-btn--forged { color: var(--accent); border-color: rgba(193, 245, 39, 0.2); }

/* ========== MODAL ========== */
.forge-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 3, 0.85);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 24px;
}
.forge-modal {
  position: relative;
  width: 100%;
  max-width: 440px;
  padding: 40px 32px 28px;
  background: var(--surface);
  border: 1px solid var(--accent);
  border-radius: 8px;
  box-shadow: 0 0 60px rgba(193, 245, 39, 0.15);
  text-align: center;
}
.forge-modal::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(ellipse at 50% 0%, rgba(193, 245, 39, 0.08), transparent 60%);
  pointer-events: none;
  border-radius: 8px;
}
.fm-close {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 28px;
  height: 28px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 3px;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.fm-close:hover {
  border-color: var(--primary);
  color: var(--primary);
}
.fm-trophy-art {
  width: 160px;
  height: 160px;
  margin: 0 auto 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 32px rgba(193, 245, 39, 0.35));
  position: relative;
}
.fm-trophy-art img, .fm-trophy-art svg {
  width: 100%; height: 100%; object-fit: contain;
}
.fm-title {
  font-family: var(--display);
  font-size: 32px;
  color: var(--text);
  margin-bottom: 6px;
  position: relative;
}
.fm-question {
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 24px;
  position: relative;
}

.fm-costs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 20px;
  position: relative;
}
.fm-cost-cell {
  padding: 16px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: 4px;
}
.fm-cost-val {
  font-family: var(--display);
  font-size: 28px;
  line-height: 1;
  margin-bottom: 4px;
}
.fm-cost-val--spend { color: var(--primary); }
.fm-cost-val--earn { color: var(--accent); }
.fm-cost-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.fm-error {
  padding: 10px 14px;
  margin-bottom: 16px;
  background: rgba(226, 75, 74, 0.1);
  border: 1px solid rgba(226, 75, 74, 0.3);
  color: #e24b4a;
  font-size: 11px;
  text-align: left;
  border-radius: 3px;
  position: relative;
}

.fm-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  position: relative;
}
.fm-btn {
  padding: 12px 18px;
  font-size: 11px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-family: var(--mono);
  border-radius: 3px;
  cursor: pointer;
  transition: all 0.15s;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
}
.fm-btn:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.fm-btn--confirm {
  background: var(--accent);
  color: var(--bg);
  border-color: var(--accent);
  font-weight: bold;
}
.fm-btn--confirm:hover:not(:disabled) {
  box-shadow: 0 0 20px rgba(193, 245, 39, 0.5);
  color: var(--bg);
  border-color: var(--accent);
}
.fm-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.modal-enter-active, .modal-leave-active { transition: opacity 0.2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .forge-modal, .modal-leave-active .forge-modal { transition: transform 0.2s; }
.modal-enter-from .forge-modal, .modal-leave-to .forge-modal { transform: translateY(10px); }

/* ========== TOAST ========== */
.forge-toast {
  position: fixed;
  bottom: 32px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 22px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  font-size: 12px;
  letter-spacing: 0.1em;
  z-index: 999;
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.5);
}
.forge-toast--success {
  border-color: var(--accent);
  color: var(--accent);
  box-shadow: 0 0 24px rgba(193, 245, 39, 0.2);
}
.forge-toast--error {
  border-color: #e24b4a;
  color: #e24b4a;
}
.forge-toast--info {
  color: var(--text);
}
.toast-enter-active, .toast-leave-active { transition: all 0.25s; }
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(10px);
}

/* ========== EMPTY STATE ========== */
.forge-empty {
  padding: 60px 24px;
  text-align: center;
  color: var(--text-muted);
}
.forge-empty p { margin-bottom: 12px; font-size: 13px; }
.forge-empty-cta {
  font-size: 11px;
  color: var(--primary);
  border: 1px solid var(--primary);
  background: transparent;
  padding: 8px 16px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-family: var(--mono);
  cursor: pointer;
  border-radius: 3px;
}
.forge-empty-cta:hover {
  background: var(--primary);
  color: var(--bg);
}

/* ========== TERMINAL ========== */
.terminal-strip {
  padding: 18px 48px;
  border-top: 1px dashed rgba(255, 97, 0, 0.08);
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.cursor-blink {
  display: inline-block;
  width: 7px;
  height: 10px;
  background: var(--primary);
  margin-left: 4px;
  animation: cursor-blink 1s step-end infinite;
  vertical-align: middle;
}
@keyframes cursor-blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

/* ========== RESPONSIVE ========== */
@media (max-width: 900px) {
  .forge-hero { padding: 28px 24px; }
  .forge-hero-content { grid-template-columns: 1fr; gap: 20px; }
  .page-title { font-size: 40px; }

  .filter-bar { padding: 14px 24px; }
  .forge-content { padding: 24px 24px 36px; }
  .terminal-strip { padding: 14px 24px; }

  .forge-grid { grid-template-columns: 1fr; }
  .forge-modal { padding: 32px 20px 24px; }
  .fm-trophy-art { width: 130px; height: 130px; }
  .fm-title { font-size: 26px; }
}

@media (max-width: 600px) {
  .page-title { font-size: 32px; }
  .quick-stats { grid-template-columns: repeat(3, 1fr); }
  .fm-costs, .fm-actions { grid-template-columns: 1fr; }
}
</style>
```

**Verificar:** `npm run dev`, abrir `/forge` logueado. Todas las secciones + filter + modal.

**Commit:** `feat: complete Forge redesign with status grouping, filter pills, confirm modal`

---

### Step 2 — Build + deploy

```bash
npm run build
```

```bash
cd ~/Documents/trophyroom && git add -A && git commit -m "feat: Phase 9G — Forge redesign complete" && git push origin main && ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build && chown -R www-data:www-data storage bootstrap/cache public/build && chmod -R 775 storage bootstrap/cache && rm -rf storage/framework/views/*"
```

Verificar hashes.

---

## Verification checklist

### Hero
- [ ] Title "Forge your" + "trophies" en naranja con underline
- [ ] Subtitle visible
- [ ] Quick stats con Available (total), Ready (chartreuse), Forged (naranja) reales

### Filter bar
- [ ] 5 pills con counts correctos (All / Ready / Almost / In progress / Forged)
- [ ] Pills con count=0 se ocultan (excepto All)
- [ ] Click en pill filtra las secciones

### Secciones
- [ ] "Ready to forge" → cards con flag verde "READY" pulsando, border chartreuse, botón naranja "Forge now"
- [ ] "Almost ready" → sin flag, progress alto, botón "Missing 1 badge" disabled
- [ ] "In progress" → sin flag, progress parcial, botón "Missing N badges" disabled
- [ ] "Locked" → opacity 50%, trophy ? gris, "Locked · 0 badges"
- [ ] "Forged" → flag chartreuse "Forged" con check, border chartreuse opacity 88%, botón "Already forged"

### Forge cards
- [ ] Asset trophy 68px
- [ ] Nombre + subtitle (series)
- [ ] Description con line-clamp 3
- [ ] Badges owned con border chartreuse glow, missing con opacity 35% + grayscale
- [ ] Si hay > 6 badges: muestra "+N" al final
- [ ] Progress bar con color correcto (empty/chartreuse/naranja)
- [ ] 3 pills: XP / Ambar cost / Uru reward

### Modal forge
- [ ] Click "Forge now" abre modal con backdrop blur
- [ ] Trophy art 160px grande con glow chartreuse
- [ ] Title VT323 + question
- [ ] 2 cost cells (spend naranja, earn chartreuse)
- [ ] Si insufficient Ambar → error rojo + confirm disabled
- [ ] Click Cancel o backdrop cierra modal
- [ ] Click Confirm → API call, loading state, success toast, trophy pasa a "Forged"
- [ ] Balances del user se actualizan (wallet-rail del header)

### Deep-linking
- [ ] `/forge?trophy={ready_id}` → abre modal de esa trophy automáticamente + highlight con pulse
- [ ] `/forge?trophy={locked_id}` → toast "This trophy is locked"
- [ ] `/forge?trophy={forged_id}` → toast "You already forged this trophy"
- [ ] `/forge?trophy={in_progress_id}` → toast "Not ready yet — missing N badges"
- [ ] Query param se limpia al cerrar modal

### Toast
- [ ] Aparece abajo centro
- [ ] Success (chartreuse), error (rojo), info (normal)
- [ ] Se oculta solo después de 2.8s

### Terminal footer
- [ ] "forge.catalog · N trophies" + cursor
- [ ] "balance · X ambar · Y uru"

### Mobile 375px
- [ ] Hero: stats abajo en 3 columnas
- [ ] Cards: 1 columna
- [ ] Modal: costs y actions en columna única

### Funcional
- [ ] Network: 3 API calls iniciales (200)
- [ ] Forge action: POST al endpoint real (200 o 400/422 si insufficient)
- [ ] Console limpia

---

## Qué reportar

1. **Pre-work hallazgos**:
   - Endpoint real del forge claim (método + URL + payload + response shape)
   - Errores esperados del claim (status codes y mensajes típicos)
   - Si backend ya categoriza o solo devuelve raw
   - Campos reales de trophy (series, is_nft, receive, weight, etc.)
2. Commits + hashes coincidentes
3. Ajustes hechos al código (especialmente endpoint de claim y update de balances)
4. Capturas: desktop con los 5 estados visibles, modal abierto, mobile 375px
5. Test del deep-linking con 4 URLs (ready/locked/forged/in_progress)
6. Errores/warnings

---

## Backlog post-9G

- Sort UI (si después lo querés)
- Lazy loading del catálogo si crece mucho
- Filter por plataforma (trophies que requieren badges de Discord / Steam / etc.)
- Search por nombre de trophy
- Modal con lista detallada de badges missing + link para ver dónde conseguirlos
- Analytics: track qué trophies abren el modal más vs cuáles se forjan
- Animación celebratory cuando se forja (confetti, sonido, etc.) — ritual matter
- "Forge history" mini-section con últimos forjes del user

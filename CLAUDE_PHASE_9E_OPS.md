# CLAUDE_PHASE_9E_OPS.md — Phase 9E: Trophy Room redesign

> **Audience:** Claude Code (CLI). Run from `~/Documents/trophyroom`.
> **Read first:** `CLAUDE.md` + this brief.
> **Scope:** Rediseño completo de `TrophyRoom.vue` siguiendo el mockup aprobado (`trophy-room.html`). 3 secciones (Forged trophies, Custom achievements, Platform badges) + hero + terminal footer. Conectado a APIs reales con toggle real de "showcase/in hall".

---

## Goal

Reemplazar `pages/TrophyRoom.vue` por el nuevo diseño del mockup aprobado:

1. **Hero** con title "Trophy Room", subtitle, quick-stats (Badges / Trophies / Custom), link "Virtual Hall preview".
2. **Forged trophies** — grid de cards grandes con badge, stats, acciones (view, add/remove hall). Promo hint si hay trophy ready.
3. **Custom achievements** — rows horizontales con thumb, info, estado "In hall"/"Add to hall".
4. **Platform badges** — agrupados por plataforma con filter pills dinámicos (All + una por cada plataforma con badges), grid de tiles.
5. **Terminal strip** footer.

Toggle de "In hall" funciona contra backend real. Sin tabs (My vault / Showcase) — cada item muestra visualmente su estado.

---

## Non-goals

- NO tocar `sidebar.vue`, `main-header.vue`, `App.vue`, `Main.vue`, `Dashboard.vue`.
- NO tocar legacy components (`achievement-card.vue`, `forge-card.vue`, `validate-card.vue`).
- NO rediseñar Forge, Virtual Hall, Feed u otras páginas.
- NO modificar backend endpoints (solo consumirlos).
- NO cambiar rutas.
- NO tocar la lógica de forge/claim (eso es responsabilidad de Forge.vue).

---

## Pre-work (OBLIGATORIO antes del Step 1)

Claude Code debe reportar los hallazgos de esta investigación **antes** de pegar código. Basado en lo que encuentre, ajustará el brief.

### 1. Leer TrophyRoom.vue actual completo

```bash
cat resources/web/js/pages/TrophyRoom.vue
```

Identificar:
- Qué APIs consume actualmente en `mounted()`/`created()`
- Qué campos del response usa (badges, trophies, achievements)
- Qué métodos maneja (probablemente ya hay algo de "showcase")
- Qué componentes importa (probablemente `trophy-card.vue`, `badge-tile.vue`, `achievement-row.vue`)

### 2. Verificar endpoints de showcase/virtual-hall

```bash
grep -rn "showcase\|virtual-hall\|in_hall\|featured" resources/web/js/ | grep -v node_modules | head -30
```

Y en backend:

```bash
grep -rn "showcase\|featured\|virtualHall" routes/api.php app/Http/Controllers 2>/dev/null | head -20
```

Identificar el endpoint real para togglear "showcase" en:
- Trophies
- Custom achievements
- (Badges probablemente no se showcasean individualmente)

Si existe, anotar método (POST/PUT) + URL + payload esperado.

### 3. Verificar shape real de `/api/badges`, `/api/forge`, `/api/achievement`

```bash
grep -rn "achievement\." resources/web/js/ | grep -v node_modules | head -15
grep -n "api/achievement" resources/web/js/ -r | head -10
```

Identificar:
- Shape de `/api/achievement` (array plano o wrapped? campo `image`? `validated`?)
- Shape de custom achievements (son iguales a trophies o distintos?)
- Campo que indica "está en hall" — ¿`showcase`, `in_hall`, `featured`, `is_showcased`?

### 4. Verificar existencia de componentes reutilizables

```bash
ls resources/web/js/parts/ | grep -E "badge|trophy|achievement"
cat resources/web/js/parts/badge-tile.vue 2>/dev/null | head -30
cat resources/web/js/parts/trophy-card.vue 2>/dev/null | head -30
cat resources/web/js/parts/achievement-row.vue 2>/dev/null | head -30
```

Decidir: ¿los reusamos con el estilo actual, los restyleamos, o escribimos markup nuevo inline?

**Regla:** si el componente existe y su markup es compatible con lo que necesita el nuevo diseño, lo **restyleamos** (agregar styles scoped nuevos en TrophyRoom.vue usando `:deep()` para pisar los del componente). Si el markup del componente es incompatible (ej. estructura diferente, faltan slots), escribimos markup nuevo inline en TrophyRoom.vue y **NO tocamos el componente** (lo puede seguir usando Virtual Hall u otras páginas).

**Reportar los hallazgos** antes de avanzar a Step 1.

---

## Steps

### Step 1 — Rediseñar TrophyRoom.vue

**Archivo:** `resources/web/js/pages/TrophyRoom.vue`

Reemplazar completo. Basado en el Pre-work, ajustar field names y endpoints.

#### Template

```vue
<template>
  <div class="trophy-room-page">
    <!-- HERO -->
    <section class="tr-hero">
      <div class="tr-hero-bg"></div>

      <div class="tr-hero-content">
        <div class="tr-hero-main">
          <div class="page-tag">Your private vault</div>
          <h1 class="page-title">Trophy <span class="page-title-accent">Room</span></h1>
          <p class="page-subtitle">
            Every badge, trophy, and achievement you've earned — forged, imported, or created.
            Manage what appears on your Virtual Hall and curate what the world sees.
          </p>
        </div>

        <div class="tr-hero-aside">
          <router-link :to="virtualHallPath" class="vhall-preview">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
            <span>Virtual Hall preview</span>
          </router-link>

          <div class="quick-stats">
            <div class="qstat qstat--highlight">
              <div class="qstat-val">{{ badges.length }}</div>
              <div class="qstat-lbl">Badges</div>
            </div>
            <div class="qstat">
              <div class="qstat-val">{{ forgedTrophies.length }}</div>
              <div class="qstat-lbl">Trophies</div>
            </div>
            <div class="qstat">
              <div class="qstat-val">{{ customAchievements.length }}</div>
              <div class="qstat-lbl">Custom</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <div class="tr-content">

      <!-- FORGED TROPHIES -->
      <section class="tr-section">
        <div class="section-header">
          <div class="section-label">Forged trophies</div>
          <div class="section-toolbar">
            <span class="section-count">{{ forgedTrophies.length }} total</span>
          </div>
        </div>

        <div v-if="forgedTrophies.length" class="trophies-vault">
          <div
            v-for="trophy in forgedTrophies"
            :key="trophy.id"
            class="vault-trophy"
            :class="{ 'vault-trophy--showcased': trophy.in_hall }"
          >
            <div v-if="trophy.in_hall" class="hall-flag">
              <span class="hall-flag-dot"></span>
              <span>In Virtual Hall</span>
            </div>

            <div class="vt-header">
              <div class="vt-asset">
                <img v-if="trophy.image" :src="trophy.image" :alt="trophy.name">
                <svg v-else viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                  <polygon points="50,8 90,30 90,70 50,92 10,70 10,30" fill="#0e0f11" stroke="#c1f527" stroke-width="2"/>
                  <polygon points="50,22 75,35 50,48 25,35" fill="#c1f527"/>
                  <polygon points="50,48 75,35 75,65 50,78" fill="#a6d820"/>
                  <polygon points="50,48 25,35 25,65 50,78" fill="#8ab81a"/>
                </svg>
              </div>
              <div class="vt-info">
                <div class="vt-name">{{ trophy.name }}</div>
                <div class="vt-sub">Forged · {{ formatDate(trophy.forged_at || trophy.created_at) }}</div>
              </div>
            </div>

            <div class="vt-desc">{{ trophy.description || '' }}</div>

            <div class="vt-meta">
              <div class="vt-meta-stats">
                <span class="vt-xp">+{{ trophy.xp || trophy.weight || trophy.price || 0 }} XP</span>
                <span v-if="trophy.series" class="vt-series">{{ trophy.series }}</span>
              </div>
              <div class="vt-actions">
                <button
                  class="vt-action vt-action--toggle"
                  :class="{ 'vt-action--remove': trophy.in_hall }"
                  :title="trophy.in_hall ? 'Remove from Virtual Hall' : 'Add to Virtual Hall'"
                  :disabled="togglingId === trophy.id"
                  @click="toggleShowcase('trophy', trophy)"
                >
                  <svg v-if="trophy.in_hall" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L6 18M6 6l12 12"/>
                  </svg>
                  <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14M5 12h14"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="empty-section">
          <p>No forged trophies yet. Head to the Forge when you have enough badges.</p>
          <router-link to="/forge" class="empty-cta">→ Go to Forge</router-link>
        </div>

        <div v-if="readyToForge" class="promo-hint">
          <div class="promo-text">
            <strong>You have {{ readyToForge.name }} ready to forge.</strong>
            {{ readyToForge.badges?.length || readyToForge.badges_count }}/{{ readyToForge.badges?.length || readyToForge.badges_count }} badges collected.
          </div>
          <router-link :to="{ path: '/forge', query: { trophy: readyToForge.id } }" class="promo-cta">
            <span>Go to Forge</span>
            <span>→</span>
          </router-link>
        </div>
      </section>

      <!-- CUSTOM ACHIEVEMENTS -->
      <section class="tr-section">
        <div class="section-header">
          <div class="section-label">Custom achievements</div>
          <div class="section-toolbar">
            <span class="section-count">
              {{ customAchievements.length }} total
              <span v-if="validatedAchievements.length">
                · {{ validatedAchievements.length }} validated
              </span>
            </span>
          </div>
        </div>

        <div v-if="customAchievements.length" class="achievements-list">
          <div
            v-for="ach in customAchievements"
            :key="ach.id"
            class="ach-row"
          >
            <div class="ach-thumb">
              <img v-if="ach.image" :src="achievementImage(ach)" :alt="ach.name">
              <div v-else class="ach-thumb-inner" :style="achThumbStyle(ach)">
                {{ achThumbLabel(ach) }}
              </div>
            </div>
            <div class="ach-info">
              <div class="ach-name">{{ ach.name }}</div>
              <div class="ach-desc">{{ ach.description || '' }}</div>
            </div>
            <div
              v-if="ach.validated"
              class="ach-status-badge"
            >Validated</div>
            <button
              class="ach-cta"
              :class="{ 'ach-cta--active': ach.in_hall }"
              :disabled="togglingId === ach.id"
              @click="toggleShowcase('achievement', ach)"
            >
              {{ ach.in_hall ? 'In hall' : 'Add to hall' }}
            </button>
          </div>
        </div>

        <div v-else class="empty-section">
          <p>No custom achievements yet. Create one from the Feed composer.</p>
          <router-link to="/feed" class="empty-cta">→ Go to Feed</router-link>
        </div>
      </section>

      <!-- PLATFORM BADGES -->
      <section class="tr-section">
        <div class="section-header">
          <div class="section-label">Platform badges</div>
          <div class="section-toolbar">
            <span class="section-count">
              {{ badges.length }} synced · {{ platformGroups.length }} {{ platformGroups.length === 1 ? 'platform' : 'platforms' }}
            </span>
            <div class="filter-pills">
              <button
                class="filter-pill"
                :class="{ 'filter-pill--active': activePlatformFilter === 'all' }"
                @click="activePlatformFilter = 'all'"
              >
                All
              </button>
              <button
                v-for="group in platformGroups"
                :key="group.key"
                class="filter-pill"
                :class="{ 'filter-pill--active': activePlatformFilter === group.key }"
                @click="activePlatformFilter = group.key"
              >
                {{ group.name }}
              </button>
            </div>
          </div>
        </div>

        <div v-if="filteredPlatformGroups.length" class="platform-sections">
          <div
            v-for="group in filteredPlatformGroups"
            :key="group.key"
            class="platform-section"
          >
            <div class="platform-header-row">
              <div class="ph-title">
                <div class="ph-icon" v-html="group.icon"></div>
                <div>
                  <div class="ph-name">{{ group.name }}</div>
                  <div class="ph-sub">Synced · {{ group.lastSyncLabel }}</div>
                </div>
              </div>
              <div class="ph-count">{{ group.badges.length }}</div>
            </div>

            <div class="badges-grid">
              <div
                v-for="badge in group.badges"
                :key="badge.id"
                class="badge-tile"
                :class="badgeRarity(badge)"
                :title="badge.name || ''"
              >
                <img v-if="badge.image" :src="badgeImage(badge)" :alt="badge.name">
                <span v-else class="badge-emoji">{{ badge.emoji || '🏆' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="empty-section">
          <p>No platform badges yet. Connect your gaming accounts to start importing.</p>
          <router-link to="/profile" class="empty-cta">→ Connect platforms</router-link>
        </div>

        <div v-if="availablePlatforms.length" class="promo-hint">
          <div class="promo-text">
            <strong>Connect more platforms to grow your vault.</strong>
            {{ availablePlatforms.join(', ') }} {{ availablePlatforms.length === 1 ? 'is' : 'are' }} waiting — each connection earns Ambar per imported badge.
          </div>
          <router-link to="/profile" class="promo-cta">
            <span>Connect platforms</span>
            <span>→</span>
          </router-link>
        </div>
      </section>

    </div>

    <!-- TERMINAL FOOTER -->
    <div class="terminal-strip">
      <div>
        trophy_room · vault · {{ totalItems }} items indexed<span class="cursor-blink"></span>
      </div>
      <div>last indexed · now · nominal</div>
    </div>
  </div>
</template>
```

#### Script

**CRÍTICO:** antes de pegar el script, Claude Code debe confirmar (Pre-work 2 y 3):
- El endpoint real de toggle showcase
- El campo real para "está en virtual hall" (puede ser `showcase`, `in_hall`, `featured`, `is_showcased`, etc.)
- Si customAchievements vienen de `/api/achievement` o de otro endpoint
- El prefix real para images (`/storage/trophies/`, `/storage/achievements/`, etc.)

Ajustar el código abajo donde corresponda.

```vue
<script>
import api from '../api/api.js';

export default {
  name: 'TrophyRoom',
  data() {
    return {
      badges: [],
      trophies: [],
      userTrophyIds: [], // IDs de trophies ya forjados
      achievements: [],

      activePlatformFilter: 'all',
      togglingId: null, // ID del item que está toggling (para disabled state)

      platformConfig: {
        discord: {
          name: 'Discord',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4C2.9 4 2 4.9 2 6v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM9.5 16c-1.4 0-2.5-1.1-2.5-2.5S8.1 11 9.5 11s2.5 1.1 2.5 2.5S10.9 16 9.5 16zm5 0c-1.4 0-2.5-1.1-2.5-2.5S13.1 11 14.5 11s2.5 1.1 2.5 2.5S15.9 16 14.5 16z"/></svg>'
        },
        steam: {
          name: 'Steam',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>'
        },
        github: {
          name: 'GitHub',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.9 2.9 1.3 3.6 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.2-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.7.2 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3"/></svg>'
        },
        riot: {
          name: 'Riot Games',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm0 22C6.48 22 2 17.52 2 12S6.48 2 12 2s10 4.48 10 10-4.48 10-10 10z"/></svg>'
        },
        strava: {
          name: 'Strava',
          icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M15.4 17.9l-2.1-4.1-3.1 6.1c-.3.6-.9 1.1-1.6 1.1H4.5c-.35 0-.7-.1-1-.3-.6-.3-.9-.9-.9-1.5 0-.25.05-.5.2-.75L11.6.7C11.85.05 12.5-.4 13.2-.4c.7 0 1.3.45 1.6 1.1l6.8 13.75c.1.25.2.5.2.75 0 .6-.35 1.2-.9 1.5-.3.2-.65.3-1 .3l-4.5-.1z"/></svg>'
        }
      }
    };
  },
  computed: {
    username() {
      return this.$store?.state?.userUsername || 'user';
    },
    virtualHallPath() {
      return `/virtual-hall/${this.username}`;
    },

    // Forged = user ya forjó esa trophy (basado en userTrophyIds)
    forgedTrophies() {
      return this.trophies
        .filter(t => this.userTrophyIds.includes(t.id))
        .map(t => ({
          ...t,
          // TODO confirmar en pre-work el field real — probablemente `showcase` o `in_hall`
          in_hall: t.showcase === true || t.showcase === 1 || t.in_hall === true
        }));
    },

    // Ready to forge: trophy con todos los badges pero no forjada
    readyToForge() {
      const userBadgeIds = new Set(this.badges.map(b => b.id));
      return this.trophies.find(t => {
        if (this.userTrophyIds.includes(t.id)) return false;
        const trophyBadges = t.badges || [];
        if (!trophyBadges.length) return false;
        return trophyBadges.every(b => userBadgeIds.has(b.id));
      });
    },

    customAchievements() {
      return this.achievements.map(a => ({
        ...a,
        in_hall: a.showcase === true || a.showcase === 1 || a.in_hall === true
      }));
    },
    validatedAchievements() {
      return this.customAchievements.filter(a => a.validated);
    },

    // Agrupa badges por plataforma (solo las que tienen badges)
    platformGroups() {
      const groups = {};
      this.badges.forEach(b => {
        const key = (b.integration || '').toLowerCase();
        if (!key) return;
        if (!groups[key]) groups[key] = [];
        groups[key].push(b);
      });

      return Object.entries(groups)
        .map(([key, badges]) => {
          const config = this.platformConfig[key] || {
            name: key.charAt(0).toUpperCase() + key.slice(1),
            icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>'
          };
          return {
            key,
            name: config.name,
            icon: config.icon,
            badges,
            lastSyncLabel: 'recently' // TODO: use real last_sync timestamp if backend provides it
          };
        })
        .sort((a, b) => b.badges.length - a.badges.length);
    },

    filteredPlatformGroups() {
      if (this.activePlatformFilter === 'all') return this.platformGroups;
      return this.platformGroups.filter(g => g.key === this.activePlatformFilter);
    },

    availablePlatforms() {
      const syncedKeys = new Set(this.platformGroups.map(g => g.key));
      return Object.entries(this.platformConfig)
        .filter(([key]) => !syncedKeys.has(key))
        .map(([, config]) => config.name);
    },

    totalItems() {
      return this.badges.length + this.forgedTrophies.length + this.customAchievements.length;
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    async loadData() {
      try {
        const [badgesRes, forgeRes, availableRes, achRes] = await Promise.all([
          api.get('/api/badges').catch(() => ({ data: { data: [] } })),
          api.get('/api/forge').catch(() => ({ data: { trophies: [] } })),
          api.get('/api/forge/available-trophies').catch(() => ({ data: { trophies: [] } })),
          api.get('/api/achievement').catch(() => ({ data: [] }))
        ]);

        this.badges = badgesRes.data?.data || badgesRes.data || [];
        this.trophies = forgeRes.data?.trophies || forgeRes.data || [];

        // available-trophies devuelve las que user ya forjó — necesitamos sus IDs
        const userTrophies = availableRes.data?.trophies || availableRes.data || [];
        this.userTrophyIds = userTrophies.map(t => t.id);

        // achievements: adaptar shape según pre-work
        this.achievements = achRes.data?.data || achRes.data || [];
      } catch (err) {
        console.error('[TrophyRoom] Failed to load data', err);
      }
    },

    async toggleShowcase(type, item) {
      if (this.togglingId === item.id) return;
      this.togglingId = item.id;

      const wasInHall = item.in_hall;
      // Optimistic update
      this.applyShowcaseChange(type, item.id, !wasInHall);

      try {
        // TODO: ajustar endpoint según pre-work
        // Posibles patrones del backend:
        //   POST /api/trophy/{id}/showcase
        //   PUT  /api/forge/{id} { showcase: bool }
        //   POST /api/virtual-hall/toggle { type, id }
        //
        // Usar el que el proyecto realmente tiene. Si no existe endpoint, comentar esta llamada
        // y dejar solo el optimistic update (con un console.warn señalando la ausencia).
        const endpoint = type === 'trophy'
          ? `/api/forge/${item.id}/showcase`
          : `/api/achievement/${item.id}/showcase`;

        await api.post(endpoint, { showcase: !wasInHall });
      } catch (err) {
        // Revertir si falla
        this.applyShowcaseChange(type, item.id, wasInHall);
        console.error('[TrophyRoom] Toggle showcase failed', err);
      } finally {
        this.togglingId = null;
      }
    },

    applyShowcaseChange(type, id, value) {
      const list = type === 'trophy' ? this.trophies : this.achievements;
      const idx = list.findIndex(x => x.id === id);
      if (idx >= 0) {
        // Set the field the backend uses (adjusted in pre-work)
        list[idx] = { ...list[idx], showcase: value, in_hall: value };
      }
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
      } catch {
        return '';
      }
    },

    badgeImage(badge) {
      if (!badge.image) return '';
      if (badge.image.startsWith('http') || badge.image.startsWith('/')) return badge.image;
      return `/storage/badges/${badge.image}`; // TODO: confirmar prefix real en pre-work
    },
    achievementImage(ach) {
      if (!ach.image) return '';
      if (ach.image.startsWith('http') || ach.image.startsWith('/')) return ach.image;
      return `/storage/achievements/${ach.image}`; // TODO: confirmar prefix real
    },

    badgeRarity(badge) {
      // Si el badge tiene un field de rareza, usarlo. Si no, sin clase.
      if (!badge.rarity) return '';
      const r = String(badge.rarity).toLowerCase();
      if (['legendary', 'epic', 'rare'].includes(r)) return `badge-tile--${r}`;
      return '';
    },

    achThumbStyle(ach) {
      // Color basado en hash del name para consistencia visual
      if (!ach.name) return {};
      const hash = ach.name.split('').reduce((h, c) => h + c.charCodeAt(0), 0);
      const hue = (hash * 37) % 360;
      return {
        background: `linear-gradient(135deg, hsl(${hue}, 40%, 22%), hsl(${(hue + 30) % 360}, 40%, 15%))`
      };
    },
    achThumbLabel(ach) {
      if (!ach.name) return '??';
      return ach.name
        .split(' ')
        .slice(0, 2)
        .map(w => w[0])
        .join('')
        .toUpperCase();
    }
  }
};
</script>
```

#### Style (SCSS scoped)

```vue
<style lang="scss" scoped>
.trophy-room-page {
  min-width: 0;
  max-width: 100%;
}

/* ========== HERO ========== */
.tr-hero {
  position: relative;
  padding: 48px 48px 40px;
  border-bottom: 1px solid rgba(255, 97, 0, 0.1);
  overflow: hidden;
}
.tr-hero-bg {
  position: absolute;
  inset: 0;
  z-index: 0;
  background:
    radial-gradient(ellipse 900px 500px at 15% 50%, rgba(255, 97, 0, 0.14), transparent 65%),
    radial-gradient(ellipse 700px 400px at 90% 50%, rgba(193, 245, 39, 0.06), transparent 65%);
}
.tr-hero-content {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 48px;
  align-items: center;
}
.tr-hero-main { min-width: 0; }
.page-tag {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  margin-bottom: 14px;
}
.page-title {
  font-family: var(--display);
  font-size: 64px;
  line-height: 1;
  letter-spacing: 0.03em;
  color: var(--text);
  margin-bottom: 16px;
}
.page-title-accent {
  color: var(--primary);
  text-shadow: 0 0 24px rgba(255, 97, 0, 0.35);
  border-bottom: 3px solid var(--primary);
  padding-bottom: 2px;
}
.page-subtitle {
  font-size: 13px;
  color: var(--text-muted);
  line-height: 1.7;
  max-width: 560px;
}

.tr-hero-aside {
  display: flex;
  flex-direction: column;
  gap: 18px;
}
.vhall-preview {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  font-size: 11px;
  color: var(--text-muted);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  transition: all 0.15s;
  text-decoration: none;
  width: fit-content;
  align-self: flex-end;
}
.vhall-preview:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.quick-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}
.qstat {
  padding: 14px 12px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  text-align: center;
}
.qstat--highlight {
  border-color: rgba(193, 245, 39, 0.3);
  background: linear-gradient(180deg, rgba(193, 245, 39, 0.06), transparent);
}
.qstat-val {
  font-family: var(--display);
  font-size: 28px;
  color: var(--text);
  line-height: 1;
}
.qstat--highlight .qstat-val {
  color: var(--accent);
  text-shadow: 0 0 12px rgba(193, 245, 39, 0.35);
}
.qstat-lbl {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  margin-top: 4px;
}

/* ========== CONTENT ========== */
.tr-content {
  padding: 40px 48px;
  display: flex;
  flex-direction: column;
  gap: 44px;
}
.tr-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.section-label {
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}
.section-toolbar {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}
.section-count {
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.filter-pills {
  display: flex;
  gap: 6px;
}
.filter-pill {
  padding: 6px 14px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: 20px;
  color: var(--text-muted);
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.15s;
}
.filter-pill:hover { border-color: rgba(255, 97, 0, 0.4); color: var(--text); }
.filter-pill--active {
  background: var(--accent);
  color: var(--bg);
  border-color: var(--accent);
}

/* ========== VAULT TROPHIES ========== */
.trophies-vault {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 14px;
}
.vault-trophy {
  position: relative;
  padding: 20px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: border-color 0.15s;
}
.vault-trophy:hover { border-color: rgba(255, 97, 0, 0.3); }
.vault-trophy--showcased {
  border-color: rgba(193, 245, 39, 0.35);
  box-shadow: 0 0 0 1px rgba(193, 245, 39, 0.08) inset;
}
.vault-trophy--showcased::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(193, 245, 39, 0.04), transparent 60%);
  pointer-events: none;
  border-radius: 6px;
}

.hall-flag {
  position: absolute;
  top: -1px;
  right: -1px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: var(--accent);
  color: var(--bg);
  font-size: 9px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  font-weight: bold;
  border-radius: 0 6px 0 4px;
  z-index: 2;
}
.hall-flag-dot {
  width: 5px;
  height: 5px;
  background: var(--bg);
  border-radius: 50%;
}

.vt-header {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}
.vt-asset {
  width: 64px;
  height: 64px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  filter: drop-shadow(0 0 14px rgba(193, 245, 39, 0.15));
}
.vt-asset img, .vt-asset svg {
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.vt-info { min-width: 0; flex: 1; }
.vt-name {
  font-size: 15px;
  color: var(--text);
  letter-spacing: 0.02em;
  line-height: 1.2;
  margin-bottom: 4px;
}
.vt-sub {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.vt-desc {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.6;
  min-height: 2.4em;
}

.vt-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px dashed var(--border);
}
.vt-meta-stats {
  display: flex;
  gap: 12px;
  align-items: center;
}
.vt-xp {
  font-size: 11px;
  color: var(--accent);
  padding: 3px 8px;
  background: rgba(193, 245, 39, 0.12);
  border-radius: 3px;
  letter-spacing: 0.08em;
}
.vt-series {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.vt-actions { display: flex; gap: 6px; }
.vt-action {
  width: 28px;
  height: 28px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  border-radius: 3px;
}
.vt-action:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.vt-action--remove:hover:not(:disabled) {
  border-color: #e24b4a;
  color: #e24b4a;
}
.vt-action:disabled { opacity: 0.5; cursor: wait; }

/* ========== PROMO HINT ========== */
.promo-hint {
  margin-top: 8px;
  padding: 18px 24px;
  background: linear-gradient(90deg, rgba(193, 245, 39, 0.06), transparent);
  border: 1px dashed rgba(193, 245, 39, 0.25);
  border-radius: 6px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}
.promo-text {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.6;
  flex: 1;
  min-width: 220px;
}
.promo-text strong { color: var(--accent); }
.promo-cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 18px;
  background: var(--accent);
  color: var(--bg);
  font-size: 11px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  text-decoration: none;
  border-radius: 3px;
}

/* ========== ACHIEVEMENTS LIST ========== */
.achievements-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.ach-row {
  display: grid;
  grid-template-columns: 56px 1fr auto auto;
  gap: 16px;
  align-items: center;
  padding: 14px 18px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  transition: border-color 0.15s;
}
.ach-row:hover { border-color: rgba(255, 97, 0, 0.3); }

.ach-thumb {
  width: 56px;
  height: 56px;
  border-radius: 4px;
  overflow: hidden;
  flex-shrink: 0;
}
.ach-thumb img { width: 100%; height: 100%; object-fit: cover; }
.ach-thumb-inner {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  font-size: 14px;
  font-family: var(--display);
  letter-spacing: 0.04em;
}

.ach-info { min-width: 0; }
.ach-name {
  font-size: 13px;
  color: var(--text);
  margin-bottom: 3px;
}
.ach-desc {
  font-size: 11px;
  color: var(--text-muted);
  line-height: 1.5;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.ach-status-badge {
  font-size: 9px;
  padding: 3px 8px;
  background: rgba(193, 245, 39, 0.12);
  color: var(--accent);
  border-radius: 3px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.ach-cta {
  padding: 8px 14px;
  font-size: 10px;
  background: transparent;
  color: var(--text-muted);
  border: 1px solid var(--border);
  border-radius: 3px;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.15s;
  font-family: var(--mono);
}
.ach-cta:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}
.ach-cta--active {
  background: var(--accent);
  color: var(--bg);
  border-color: var(--accent);
}
.ach-cta--active:hover:not(:disabled) {
  background: rgba(193, 245, 39, 0.8);
  border-color: rgba(193, 245, 39, 0.8);
  color: var(--bg);
}
.ach-cta:disabled { opacity: 0.5; cursor: wait; }

/* ========== PLATFORM SECTIONS ========== */
.platform-sections {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.platform-section {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 20px;
}
.platform-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
  padding-bottom: 14px;
  border-bottom: 1px dashed var(--border);
}
.ph-title {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}
.ph-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  flex-shrink: 0;
}
.ph-name {
  font-size: 14px;
  color: var(--text);
  letter-spacing: 0.04em;
  margin-bottom: 2px;
}
.ph-sub {
  font-size: 9px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
}
.ph-count {
  font-family: var(--display);
  font-size: 28px;
  color: var(--primary);
  line-height: 1;
}

.badges-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(56px, 1fr));
  gap: 8px;
}
.badge-tile {
  aspect-ratio: 1;
  background: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
  cursor: default;
  overflow: hidden;
}
.badge-tile:hover {
  border-color: rgba(255, 97, 0, 0.4);
  transform: translateY(-1px);
}
.badge-tile img {
  width: 70%;
  height: 70%;
  object-fit: contain;
}
.badge-tile--rare { border-color: rgba(193, 245, 39, 0.35); }
.badge-tile--legendary { border-color: rgba(255, 97, 0, 0.45); box-shadow: 0 0 14px rgba(255, 97, 0, 0.15); }
.badge-emoji { font-size: 22px; }

/* ========== EMPTY STATES ========== */
.empty-section {
  padding: 32px 24px;
  background: var(--surface);
  border: 1px dashed var(--border);
  border-radius: 6px;
  text-align: center;
}
.empty-section p {
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 10px;
}
.empty-cta {
  display: inline-block;
  font-size: 11px;
  color: var(--primary);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  text-decoration: none;
  border-bottom: 1px solid var(--primary);
  padding-bottom: 2px;
}

/* ========== TERMINAL ========== */
.terminal-strip {
  padding: 18px 48px;
  border-top: 1px dashed rgba(255, 97, 0, 0.1);
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.16em;
  text-transform: uppercase;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
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
  .tr-hero { padding: 28px 24px 32px; }
  .tr-hero-content { grid-template-columns: 1fr; gap: 24px; }
  .page-title { font-size: 42px; }
  .tr-hero-aside { align-items: flex-start; }
  .vhall-preview { align-self: flex-start; }
  .tr-content { padding: 28px 24px; gap: 36px; }
  .terminal-strip { padding: 14px 24px; }

  .ach-row {
    grid-template-columns: 48px 1fr auto;
    gap: 12px;
    padding: 12px 14px;
  }
  .ach-row .ach-status-badge { display: none; }
}

@media (max-width: 600px) {
  .page-title { font-size: 32px; }
  .section-header { flex-direction: column; align-items: flex-start; }
  .trophies-vault { grid-template-columns: 1fr; }
}
</style>
```

**Verificar:** `npm run dev`, abrir `/trophy-room` logueado. Todas las secciones deben renderizar.

**Commit:** `feat: complete Trophy Room redesign with showcase toggle, platform filtering`

---

### Step 2 — Build + deploy

```bash
npm run build
```

Si el build falla: debuggear. Si sale OK:

```bash
cd ~/Documents/trophyroom && git add -A && git commit -m "feat: Phase 9E — Trophy Room redesign complete" && git push origin main && ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build && chown -R www-data:www-data storage bootstrap/cache public/build && chmod -R 775 storage bootstrap/cache && rm -rf storage/framework/views/*"
```

Verificar hashes:

```bash
cd ~/Documents/trophyroom && git log --oneline -1 && ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git log --oneline -1"
```

---

## Verification checklist

Abrir `https://app.ambar.gg/trophy-room` con hard refresh + DevTools cerrado.

### Hero
- [ ] Title "Trophy" con "Room" en naranja + underline
- [ ] Subtitle visible
- [ ] Quick stats (Badges chartreuse glow, Trophies, Custom) con counts reales
- [ ] "Virtual Hall preview" link funciona (va a `/virtual-hall/{username}`)

### Forged trophies
- [ ] Grid de cards, cada una con badge + nombre + fecha + descripción + XP + acción
- [ ] Cards con "IN VIRTUAL HALL" flag chartreuse si showcased
- [ ] Botón toggle: si en hall = X rojo, si no = + naranja
- [ ] Click en toggle: backend call, UI actualiza optimista, si falla revierte
- [ ] Promo hint aparece si hay readyToForge, apunta a `/forge?trophy={id}`

### Custom achievements
- [ ] Rows con thumb (imagen o iniciales), name, desc, "Validated" badge si aplica, botón "In hall"/"Add to hall"
- [ ] Toggle funciona igual que trophies

### Platform badges
- [ ] Filter pills: "All" + una por cada plataforma real del usuario (ej: Discord, Steam)
- [ ] Click en pill filtra la sección
- [ ] Cada plataforma muestra header (logo + name + "synced") + count + grid de tiles
- [ ] Tiles muestran emoji o imagen real del badge
- [ ] Promo hint abajo con plataformas no conectadas

### Terminal strip
- [ ] "trophy_room · vault · N items indexed" con cursor blinkeando

### Funcional
- [ ] Network tab: las 4 API calls en 200
- [ ] Console sin errores nuevos
- [ ] Toggle showcase: si endpoint existe, hace la call real; si no, anotar en reporte

### Mobile 375px
- [ ] Hero: stats se apilan, Virtual Hall preview arriba
- [ ] Trophies vault: columna única
- [ ] Achievements: se apilan sin romper
- [ ] Platform badges: tiles se adaptan (grid auto-fill)

---

## Qué reportar

1. **Pre-work hallazgos** (ANTES de Step 1): endpoints reales, field names reales, endpoint de showcase toggle si existe
2. Commits creados + hashes coincidentes
3. Ajustes hechos al código respecto al brief
4. Si el endpoint de showcase toggle NO existe: marcar como stub + listarlo en backlog
5. Captures desktop + mobile 375px
6. Errores/warnings

---

## Backlog post-9E

Posibles items a resolver después:

- Endpoint real de showcase toggle si no existe (agregar a backend)
- Lazy-load de badges (si un usuario tiene 500 badges, la page puede sobrecargarse)
- Badge detail modal (click en tile abre modal con descripción/rarity)
- Drag to reorder showcase (decidir orden de aparición en Virtual Hall)
- Filtro de trophies por rarity (legendary, rare, common)

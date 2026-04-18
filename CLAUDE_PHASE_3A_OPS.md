# CLAUDE_PHASE_3A_OPS.md — Trophy Room Page Redesign

> **Operational brief for Claude Code.**
> Read `CLAUDE.md` for product context, `CLAUDE_FRONTEND_OPS.md` Section 2 for design system tokens.
> This file covers Phase 3A: the Trophy Room page (user's private vault at `/#/trophy-room`).

---

## Scope

Redesign the Trophy Room page (`TrophyRoom.vue`) — the authenticated user's private vault where they see their badges, trophies, and achievements. The current page has legacy language (NFTs, "You get 50 Ambars"), a flat list layout, and hexagon-wrapped badge images. The new design uses clean cards, grid layouts, and the TrophyRoom 2.0 design system.

**Files to modify:**
```
resources/web/js/pages/TrophyRoom.vue          ← Full template rewrite + scoped styles
```

**Files to CREATE (new components):**
```
resources/web/js/parts/trophy-card.vue         ← New trophy card component (replaces forge-card usage)
resources/web/js/parts/badge-tile.vue           ← New compact badge tile (replaces achievement-card usage)
resources/web/js/parts/achievement-row.vue      ← New achievement list row (replaces validate-card usage)
```

**DO NOT modify:** The old `achievement-card.vue`, `forge-card.vue`, `validate-card.vue` — they're still used by other pages (Forge, Validate, VirtualHall). We create NEW components and use them only in TrophyRoom.vue. The old components get replaced page-by-page in later phases.

**DO NOT modify:** Any `<script>` API calls or data logic in TrophyRoom.vue. The same data (`trophies`, `discord_achievements`, `steam_achievements`, `github_achievements`, `my_achievements`) is fetched the same way. Only the template and how it renders changes.

---

## Design Target (from approved mockup — Captura 4)

```
┌─────────────────────────────────────────────────────────────┐
│  Trophy Room                    [ Virtual Hall preview → ]  │
│  Your vault. Manage badges, forge trophies, curate your     │
│  Virtual Hall.                                              │
│                                                             │
│  ┌──────────┐ ┌──────────┐                                  │
│  │ My vault │ │ Showcase │                                  │
│  └──────────┘ └──────────┘                                  │
│                                                             │
│  FORGED TROPHIES                            4 trophies      │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                    │
│  │ icon     │ │ icon     │ │ icon     │                    │
│  │ Name     │ │ Name     │ │ Name     │                    │
│  │ Brand    │ │ Brand    │ │ Brand    │                    │
│  │ 0/1 bdg  │ │ 2/3 bdg  │ │ 1/2 bdg  │                    │
│  │ ░░░░░░░░ │ │ ████░░░░ │ │ █████░░░ │                    │
│  │ Missing 1│ │ Missing 1│ │ Missing 1│                    │
│  └──────────┘ └──────────┘ └──────────┘                    │
│  ┌──────────────────────────────────────┐                    │
│  │ icon  Name           [Forge now]     │  ← orange border  │
│  │       Brand   3/3 bdg  ████████████  │     when ready     │
│  └──────────────────────────────────────┘                    │
│                                                             │
│  CUSTOM ACHIEVEMENTS                              3         │
│  ┌─────────────────────────────────────────────────┐        │
│  │ 🎯 First PENTA with Ekko!                       │        │
│  │    ✓ Validated · 12 vouches                     │        │
│  ├─────────────────────────────────────────────────┤        │
│  │ 🎮 Primero en torneo con amigos                  │        │
│  │    ✓ Validated · 8 vouches                      │        │
│  ├─────────────────────────────────────────────────┤        │
│  │ ⚗️ First potion I made                           │        │
│  │    2/5 validations needed                       │        │
│  └─────────────────────────────────────────────────┘        │
│                                                             │
│  PLATFORM BADGES                                            │
│  ┌ Discord  4 ─────────────────────────────────────┐        │
│  │ [img][img][img][img]                             │        │
│  └─────────────────────────────────────────────────┘        │
│  ┌ Steam  6 ───────────────────────────────────────┐        │
│  │ [img][img][img][img][img][img]                   │        │
│  └─────────────────────────────────────────────────┘        │
│  ┌ GitHub  3 ──────────────────────────────────────┐        │
│  │ [img][img][img]                                  │        │
│  └─────────────────────────────────────────────────┘        │
│  [Import Discord] [Import Steam] [Import GitHub]            │
└─────────────────────────────────────────────────────────────┘
```

---

## Design System Reference

All values from `CLAUDE_FRONTEND_OPS.md` Section 2:

```scss
$bg: #000003;
$surface: #0e0f11;
$surface-2: #1a1c1f;
$surface-3: #252729;
$primary: #ff6100;    // section labels, trophy ready border
$accent: #c1f527;     // active tabs, progress bars in-progress, buttons
$text: #feeddf;
$text-muted: #9a9590;
$text-dim: #5a5550;
$border: #2a2c2e;
$mono: 'Share Tech Mono', monospace;
```

### Component Patterns
- **Section labels:** `font-size: 11px; color: $primary; text-transform: uppercase; letter-spacing: 0.12em;`
- **Cards:** `background: $surface; border: 1px solid $border; border-radius: 6px;`
- **Card ready-to-forge:** `border-color: $primary;` (orange border when all badges collected)
- **Progress bars:** height 4px, bg `$surface-3`, fill `$accent` (in progress) or `$primary` (complete)
- **Badge tiles:** 56-64px squares, `background: $surface-2; border: 1px solid $border; border-radius: 6px;`
- **XP badge:** small pill, `background: rgba($primary, 0.15); color: $primary; font-size: 11px; padding: 2px 8px; border-radius: 4px;`
- **Tabs:** underline style, active = `$accent` color + border-bottom, inactive = `$text-muted`

---

## Step-by-Step Execution

### Step 1: Create `badge-tile.vue`

**File:** `resources/web/js/parts/badge-tile.vue`

A compact badge tile (56px square with image). Used in the "Platform Badges" section.

```vue
<template>
  <div class="badge-tile" :title="badge.name" @click="openDetail">
    <img
      v-if="badge.image"
      :src="imageUrl"
      :alt="badge.name"
      class="badge-tile__img"
    />
    <div v-else class="badge-tile__placeholder">?</div>
  </div>
</template>

<script>
import store from '../store/store.js';

export default {
  props: {
    badge: { type: Object, required: true },
    service: { type: String, default: 'discord' }
  },
  computed: {
    imageUrl() {
      return `/storage/integrations/${this.service}/${this.badge.image}`;
    }
  },
  methods: {
    openDetail() {
      store.state.modals.trophyRoomBadge.show = true;
      store.state.modals.trophyRoomBadge.data.imageUrl = this.imageUrl;
      store.state.modals.trophyRoomBadge.data.name = this.badge.name;
    }
  }
}
</script>

<style scoped>
.badge-tile {
  width: 56px;
  height: 56px;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: border-color 0.15s;
  overflow: hidden;
}

.badge-tile:hover {
  border-color: #9a9590;
}

.badge-tile__img {
  width: 40px;
  height: 40px;
  object-fit: contain;
}

.badge-tile__placeholder {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 18px;
}
</style>
```

**Verify:** File created, no errors on import.

---

### Step 2: Create `trophy-card.vue`

**File:** `resources/web/js/parts/trophy-card.vue`

A trophy card with image, name, brand, XP badge, progress bar, and badge requirement chips.

```vue
<template>
  <div class="trophy-card" :class="{ 'trophy-card--ready': isReady }">
    <div class="trophy-card__header">
      <div class="trophy-card__icon">
        <img v-if="trophy.image" :src="imageUrl" :alt="trophy.name" />
      </div>
      <div class="trophy-card__xp" v-if="trophy.weight || trophy.price">
        {{ Math.floor(trophy.weight || trophy.price) }} XP
      </div>
    </div>

    <div class="trophy-card__name">{{ trophy.name }}</div>
    <div class="trophy-card__brand" v-if="trophy.description">{{ trophy.description }}</div>

    <div class="trophy-card__progress" v-if="trophy.badges_required !== undefined">
      <div class="trophy-card__progress-info">
        <span>{{ trophy.badges_owned || 0 }}/{{ trophy.badges_required }} badges</span>
        <span>{{ progressPercent }}%</span>
      </div>
      <div class="trophy-card__progress-bar">
        <div class="trophy-card__progress-fill" :style="{ width: progressPercent + '%' }"></div>
      </div>
    </div>

    <div class="trophy-card__action" v-if="isReady && showForgeButton">
      <button class="trophy-card__forge-btn" @click="forgeTrophy">Forge now</button>
    </div>
    <div class="trophy-card__action" v-else-if="!isReady && trophy.badges_required">
      <button class="trophy-card__missing-btn" disabled>
        Missing {{ (trophy.badges_required || 0) - (trophy.badges_owned || 0) }} badge{{ ((trophy.badges_required || 0) - (trophy.badges_owned || 0)) !== 1 ? 's' : '' }}
      </button>
    </div>

    <div class="trophy-card__showcase" v-if="showShowcase">
      <button
        v-if="!trophy.pivot || !trophy.pivot.display"
        class="trophy-card__showcase-btn"
        @click="showcase"
      >Showcase</button>
      <button
        v-else
        class="trophy-card__showcase-btn trophy-card__showcase-btn--active"
        @click="removeShowcase"
      >Remove</button>
    </div>
  </div>
</template>

<script>
import store from '../store/store.js';
import api from '../api/api.js';

export default {
  props: {
    trophy: { type: Object, required: true },
    showForgeButton: { type: Boolean, default: false },
    showShowcase: { type: Boolean, default: true }
  },
  computed: {
    imageUrl() {
      return `/storage/trophies/${this.trophy.image}`;
    },
    progressPercent() {
      if (!this.trophy.badges_required) return 0;
      return Math.round(((this.trophy.badges_owned || 0) / this.trophy.badges_required) * 100);
    },
    isReady() {
      return this.trophy.badges_required && (this.trophy.badges_owned || 0) >= this.trophy.badges_required;
    }
  },
  methods: {
    forgeTrophy() {
      store.state.modals.claimTrophyModal.show = true;
      store.state.modals.claimTrophyModal.data = this.trophy;
    },
    showcase() {
      api.get(`/api/forge/${this.trophy.id}/showcase`).then(resp => {
        if (resp.status === 200) {
          this.trophy.pivot.display = true;
          store.state.notification = {
            message: `${this.trophy.name} has been added to the virtual hall`,
            type: 'success',
            show: true
          };
        }
      });
    },
    removeShowcase() {
      api.get(`/api/forge/${this.trophy.id}/remove`).then(resp => {
        if (resp.status === 200) {
          this.trophy.pivot.display = false;
          store.state.notification = {
            message: `${this.trophy.name} has been removed from the virtual hall`,
            type: 'success',
            show: true
          };
        }
      });
    }
  }
}
</script>

<style scoped>
.trophy-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.trophy-card--ready {
  border-color: #ff6100;
}

.trophy-card__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.trophy-card__icon {
  width: 56px;
  height: 56px;
  background: #1a1c1f;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.trophy-card__icon img {
  width: 44px;
  height: 44px;
  object-fit: contain;
}

.trophy-card__xp {
  background: rgba(255, 97, 0, 0.15);
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 4px;
  letter-spacing: 0.05em;
}

.trophy-card__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 15px;
  font-weight: 400;
}

.trophy-card__brand {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.trophy-card__progress {
  margin-top: 4px;
}

.trophy-card__progress-info {
  display: flex;
  justify-content: space-between;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: #9a9590;
  margin-bottom: 4px;
}

.trophy-card__progress-bar {
  width: 100%;
  height: 4px;
  background: #252729;
  border-radius: 2px;
  overflow: hidden;
}

.trophy-card__progress-fill {
  height: 100%;
  border-radius: 2px;
  background: #c1f527;
  transition: width 0.3s ease;
}

.trophy-card--ready .trophy-card__progress-fill {
  background: #ff6100;
}

.trophy-card__forge-btn {
  width: 100%;
  background: #ff6100;
  color: #000003;
  border: none;
  border-radius: 4px;
  padding: 10px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  cursor: pointer;
  letter-spacing: 0.05em;
  transition: filter 0.15s;
}

.trophy-card__forge-btn:hover {
  filter: brightness(1.1);
}

.trophy-card__missing-btn {
  width: 100%;
  background: #252729;
  color: #5a5550;
  border: none;
  border-radius: 4px;
  padding: 10px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  cursor: default;
}

.trophy-card__showcase-btn {
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
  border-radius: 4px;
  padding: 6px 12px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}

.trophy-card__showcase-btn:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.trophy-card__showcase-btn--active {
  border-color: #ff6100;
  color: #ff6100;
}
</style>
```

**Note:** The `badges_required` and `badges_owned` fields may not exist on the trophy data from the current API. If those props are undefined, the progress bar simply won't render — the card degrades gracefully to just icon + name + description. This can be wired up later when the backend provides badge requirement data.

**Verify:** File created, no errors on import.

---

### Step 3: Create `achievement-row.vue`

**File:** `resources/web/js/parts/achievement-row.vue`

A list-style row for custom achievements (validated/pending).

```vue
<template>
  <div class="ach-row">
    <div class="ach-row__icon">
      <img v-if="achievement.image" :src="imageUrl" :alt="achievement.name" />
      <div v-else class="ach-row__icon-placeholder"></div>
    </div>
    <div class="ach-row__info">
      <div class="ach-row__name">{{ achievement.name }}</div>
      <div class="ach-row__status" v-if="achievement.status === 1">
        <span class="ach-row__validated">✓ Validated</span>
        <span class="ach-row__vouches" v-if="achievement.validations_count"> · {{ achievement.validations_count }} vouches</span>
      </div>
      <div class="ach-row__status" v-else-if="achievement.status === 2">
        <span class="ach-row__pending">Not validated</span>
      </div>
      <div class="ach-row__status" v-else-if="achievement.status === 3">
        <span class="ach-row__rejected">Rejected</span>
      </div>
      <div class="ach-row__status" v-else>
        <span class="ach-row__pending">Pending review</span>
      </div>
    </div>
    <div class="ach-row__actions">
      <button
        v-if="!achievement.display"
        class="ach-row__btn"
        @click="showcase"
      >Showcase</button>
      <button
        v-else
        class="ach-row__btn ach-row__btn--active"
        @click="removeShowcase"
      >Remove</button>
    </div>
  </div>
</template>

<script>
import store from '../store/store.js';
import api from '../api/api.js';

export default {
  props: {
    achievement: { type: Object, required: true }
  },
  computed: {
    imageUrl() {
      return `/storage/achievements/${this.achievement.image}`;
    }
  },
  methods: {
    showcase() {
      api.get(`/api/achievement/${this.achievement.id}/showcase`).then(resp => {
        if (resp.status === 200) {
          this.achievement.display = true;
          store.state.notification = {
            message: `${this.achievement.name} has been added to the virtual hall`,
            type: 'success',
            show: true
          };
        }
      });
    },
    removeShowcase() {
      api.get(`/api/achievement/${this.achievement.id}/removeShowcase`).then(resp => {
        if (resp.status === 200) {
          this.achievement.display = false;
          store.state.notification = {
            message: `${this.achievement.name} has been removed from the virtual hall`,
            type: 'success',
            show: true
          };
        }
      });
    }
  }
}
</script>

<style scoped>
.ach-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  transition: border-color 0.15s;
}

.ach-row:hover {
  border-color: #3a3d40;
}

.ach-row__icon {
  width: 44px;
  height: 44px;
  min-width: 44px;
  background: #1a1c1f;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.ach-row__icon img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.ach-row__icon-placeholder {
  width: 32px;
  height: 32px;
  background: #252729;
  border-radius: 4px;
}

.ach-row__info {
  flex: 1;
  min-width: 0;
}

.ach-row__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ach-row__status {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  margin-top: 2px;
}

.ach-row__validated {
  color: #c1f527;
}

.ach-row__vouches {
  color: #9a9590;
}

.ach-row__pending {
  color: #9a9590;
}

.ach-row__rejected {
  color: #e24b4a;
}

.ach-row__actions {
  flex-shrink: 0;
}

.ach-row__btn {
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
  border-radius: 4px;
  padding: 6px 12px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}

.ach-row__btn:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.ach-row__btn--active {
  border-color: #ff6100;
  color: #ff6100;
}

@media (max-width: 480px) {
  .ach-row__actions {
    display: none;
  }
}
</style>
```

**Verify:** File created, no errors on import.

---

### Step 4: Rewrite TrophyRoom.vue Template

**File:** `resources/web/js/pages/TrophyRoom.vue`

Replace the ENTIRE `<template>` block. In the `<script>`, only change the `components` import section to add the new components and keep all existing ones. Remove old `<style scoped>`.

**Changes to `<script>` (ONLY the imports/components — keep ALL methods/data/mounted/computed):**

Add these imports at the top:
```js
import TrophyCard from "../parts/trophy-card.vue";
import BadgeTile from "../parts/badge-tile.vue";
import AchievementRow from "../parts/achievement-row.vue";
```

Add to the `components` object:
```js
TrophyCard,
BadgeTile,
AchievementRow,
```

Keep ALL existing imports and components — the old ones may still be needed by other code paths.

**New template:**

```vue
<template>
  <div class="main_block">
    <!-- Header -->
    <div class="tr-page-header">
      <div>
        <h1 class="tr-page-title">Trophy Room</h1>
        <p class="tr-page-subtitle">Your vault. Manage badges, forge trophies, curate your Virtual Hall.</p>
      </div>
      <button class="tr-vhall-btn" @click="RedirectToVirtualHall">
        Virtual Hall preview →
      </button>
    </div>

    <!-- Tabs -->
    <div class="tr-tabs">
      <div
        class="tr-tab"
        :class="{ 'tr-tab--active': activeTab === 1 }"
        @click="changeTab(1)"
      >My vault</div>
      <div
        class="tr-tab"
        :class="{ 'tr-tab--active': activeTab === 2 }"
        @click="changeTab(2)"
      >Showcase</div>
    </div>

    <!-- My Vault Tab -->
    <div v-if="activeTab === 1" class="tr-vault">

      <!-- Forged Trophies -->
      <div class="tr-section" v-if="trophies.length || trophiesLoading">
        <div class="tr-section-header">
          <span class="tr-section-label">Forged trophies</span>
          <span class="tr-section-count">{{ trophies.length }} trophies</span>
        </div>
        <Loader v-if="trophiesLoading" />
        <div class="tr-trophy-grid" v-else>
          <TrophyCard
            v-for="trophy in trophies"
            :key="trophy.id"
            :trophy="trophy"
            :show-forge-button="false"
            :show-showcase="true"
          />
        </div>
      </div>

      <!-- Custom Achievements -->
      <div class="tr-section" v-if="my_achievements.length || achievementLoading">
        <div class="tr-section-header">
          <span class="tr-section-label">Custom achievements</span>
          <span class="tr-section-count">{{ my_achievements.length }}</span>
        </div>
        <Loader v-if="achievementLoading" />
        <div class="tr-ach-list" v-else>
          <AchievementRow
            v-for="ach in my_achievements"
            :key="ach.id"
            :achievement="ach"
          />
        </div>
      </div>

      <!-- Platform Badges -->
      <div class="tr-section" v-if="discord_achievements.length || github_achievements.length || steam_achievements.length || badgesLoading">
        <div class="tr-section-header">
          <span class="tr-section-label">Platform badges</span>
        </div>
        <Loader v-if="badgesLoading" />
        <template v-else>
          <!-- Discord -->
          <div class="tr-platform-group" v-if="discord_achievements.length">
            <div class="tr-platform-header">
              <img src="../../../web/images/web/img/icons/discord.svg" alt="Discord" class="tr-platform-icon" />
              <span class="tr-platform-name">Discord</span>
              <span class="tr-platform-count">{{ discord_achievements.length }}</span>
            </div>
            <div class="tr-badge-grid">
              <BadgeTile
                v-for="badge in discord_achievements"
                :key="badge.id"
                :badge="badge"
                service="discord"
              />
            </div>
            <button class="tr-import-btn" @click="ImportData('discord')">Import Discord</button>
          </div>
          <div v-else class="tr-platform-group">
            <button class="tr-import-btn" @click="ImportData('discord')">Import Discord badges</button>
          </div>

          <!-- Steam -->
          <div class="tr-platform-group" v-if="steam_achievements.length">
            <div class="tr-platform-header">
              <img src="../../../web/images/web/img/icons/steam.svg" alt="Steam" class="tr-platform-icon" />
              <span class="tr-platform-name">Steam</span>
              <span class="tr-platform-count">{{ steam_achievements.length }}</span>
            </div>
            <div class="tr-badge-grid">
              <BadgeTile
                v-for="badge in steam_achievements"
                :key="badge.id"
                :badge="badge"
                service="steam"
              />
            </div>
            <button class="tr-import-btn" @click="ImportData('steam')">Import Steam</button>
          </div>
          <div v-else class="tr-platform-group">
            <button class="tr-import-btn" @click="ImportData('steam')">Import Steam badges</button>
          </div>

          <!-- GitHub -->
          <div class="tr-platform-group" v-if="github_achievements.length">
            <div class="tr-platform-header">
              <img src="../../../web/images/web/img/icons/github.svg" alt="GitHub" class="tr-platform-icon" />
              <span class="tr-platform-name">GitHub</span>
              <span class="tr-platform-count">{{ github_achievements.length }}</span>
            </div>
            <div class="tr-badge-grid">
              <BadgeTile
                v-for="badge in github_achievements"
                :key="badge.id"
                :badge="badge"
                service="github"
              />
            </div>
            <button class="tr-import-btn" @click="ImportData('github')">Import GitHub</button>
          </div>
          <div v-else class="tr-platform-group">
            <button class="tr-import-btn" @click="ImportData('github')">Import GitHub badges</button>
          </div>
        </template>
      </div>
    </div>

    <!-- Showcase Tab (same data, filtered to display=true) -->
    <div v-if="activeTab === 2" class="tr-vault">
      <div class="tr-section">
        <div class="tr-section-header">
          <span class="tr-section-label">Showcased items</span>
        </div>
        <p class="tr-empty-text" v-if="!showcasedTrophies.length && !showcasedAchievements.length && !showcasedBadges.length">
          Nothing showcased yet. Go to My Vault and click "Showcase" on items you want to display in your Virtual Hall.
        </p>
        <div class="tr-trophy-grid" v-if="showcasedTrophies.length">
          <TrophyCard
            v-for="trophy in showcasedTrophies"
            :key="trophy.id"
            :trophy="trophy"
            :show-forge-button="false"
            :show-showcase="true"
          />
        </div>
        <div class="tr-ach-list" v-if="showcasedAchievements.length" style="margin-top: 20px;">
          <AchievementRow
            v-for="ach in showcasedAchievements"
            :key="ach.id"
            :achievement="ach"
          />
        </div>
      </div>
    </div>
  </div>
</template>
```

**Add computed properties** to the `<script>` computed section (keep `displayedAchievements` if it exists):
```js
showcasedTrophies() {
  return this.trophies.filter(t => t.pivot && t.pivot.display);
},
showcasedAchievements() {
  return this.my_achievements.filter(a => a.display);
},
showcasedBadges() {
  const all = [...this.discord_achievements, ...this.steam_achievements, ...this.github_achievements];
  return all.filter(b => b.display);
},
```

**New scoped styles:**

```vue
<style scoped>
.tr-page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  gap: 20px;
}

.tr-page-title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 28px;
  font-weight: 400;
  margin: 0 0 6px;
}

.tr-page-subtitle {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  margin: 0;
}

.tr-vhall-btn {
  background: transparent;
  border: 1px solid #ff6100;
  color: #ff6100;
  border-radius: 4px;
  padding: 8px 16px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s;
  flex-shrink: 0;
}

.tr-vhall-btn:hover {
  background: rgba(255, 97, 0, 0.1);
}

.tr-tabs {
  display: flex;
  gap: 0;
  border-bottom: 1px solid #2a2c2e;
  margin-bottom: 28px;
}

.tr-tab {
  padding: 10px 20px;
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: color 0.15s, border-color 0.15s;
}

.tr-tab--active {
  color: #c1f527;
  border-bottom-color: #c1f527;
}

.tr-tab:hover:not(.tr-tab--active) {
  color: #feeddf;
}

.tr-section {
  margin-bottom: 36px;
}

.tr-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.tr-section-label {
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.tr-section-count {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

.tr-trophy-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

.tr-ach-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tr-platform-group {
  margin-bottom: 24px;
}

.tr-platform-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.tr-platform-icon {
  width: 20px;
  height: 20px;
  opacity: 0.7;
}

.tr-platform-name {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
}

.tr-platform-count {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

.tr-badge-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.tr-import-btn {
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
  border-radius: 4px;
  padding: 8px 16px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}

.tr-import-btn:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.tr-empty-text {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  text-align: center;
  padding: 40px 20px;
}

@media (max-width: 640px) {
  .tr-page-header {
    flex-direction: column;
  }

  .tr-trophy-grid {
    grid-template-columns: 1fr;
  }
}
</style>
```

**Verify:** `npm run dev` → navigate to `/#/trophy-room`. You should see:
- Clean title + subtitle + Virtual Hall button
- My Vault / Showcase tabs
- Forged Trophies in grid cards
- Custom Achievements as list rows
- Platform Badges as compact tiles grouped by platform
- No hexagons, no "You get 50 Ambars", no NFT tab, no "Select Your Legendary Achievements"

---

### Step 5: Final Verification

```bash
npm run dev
```

Check:
- [ ] Title says "Trophy Room" with clean subtitle
- [ ] "Virtual Hall preview →" button works (opens in new tab)
- [ ] "My vault" tab shows trophies + achievements + badges
- [ ] "Showcase" tab filters to only displayed items
- [ ] No remaining "Ambar", "NFT", or "50 Ambars" text
- [ ] No hexagon borders on badge images
- [ ] Badge tiles are 56px compact squares grouped by platform
- [ ] Import buttons work (redirect to OAuth flow)
- [ ] Mobile (375px): single column, everything stacks
- [ ] No console errors

Then:
```bash
npm run build
```

Commit:
```
feat: Phase 3A — Trophy Room page redesign with new components
```

Deploy:
```bash
git push origin main
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git pull origin main && npm run build"
```

---

## What This Does NOT Cover

- **Forge page** (`Forge.vue`) — Phase 3 later or Phase 4
- **Virtual Hall** (`VirtualHall.vue`) — Phase 3B, separate brief
- **Profile/Settings** (`Profile.vue`) — Phase 5
- **Badge detail modal** — existing modal still works, just triggered differently
- **Badge requirement chips on trophy cards** — requires backend to provide badge requirement data per trophy. The `trophy-card.vue` supports it but degrades gracefully without it.

---

## Development Rules

1. One change at a time. Modify → verify with `npm run dev` → next.
2. Don't break existing functionality.
3. Vue 3 Options API ONLY.
4. Do NOT modify old card components (achievement-card, forge-card, validate-card) — they're used elsewhere.
5. Mobile-first. Test at 375px.
6. Share Tech Mono is the only font.

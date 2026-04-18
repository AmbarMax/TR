# CLAUDE_PHASE_3C_OPS.md — Forge Page Redesign

> **Operational brief for Claude Code.**
> Read `CLAUDE_FRONTEND_OPS.md` Section 2 for design system tokens.
> This file covers Phase 3C: the Forge page at `/#/forge`.

---

## Scope

Redesign the Forge page (`Forge.vue`) — the catalog where users browse available trophies and forge them when they have all required badges. The current page uses the old `forge-card.vue` component with hexagon borders, "Weight" labels, NFT language, and a flat 4-column row layout. The new design uses a 2-column grid of enhanced trophy cards with badge requirement chips, progress bars, filter pills, and clean typography.

**Files to modify:**
```
resources/web/js/pages/Forge.vue                    ← Full template rewrite + scoped styles
resources/web/js/parts/trophy-card.vue              ← Extend to support badge requirement chips for Forge
```

**DO NOT modify:** `forge-card.vue` (still used elsewhere until those pages are redesigned). Keep ALL `<script>` logic in Forge.vue — API calls, data properties, computed, watchers.

---

## Current API Data Shape

`GET /api/forge` returns:
```json
{
  "trophies": [
    {
      "id": "uuid",
      "name": "MVP Test",
      "description": "Prove your worth across platforms",
      "image": "filename.png",
      "price": 10,
      "receive": 5,
      "is_nft": 0,
      "weight": null,
      "type": "0",
      "badges": [
        {
          "id": "uuid",
          "name": "Skill Validators",
          "image": "filename.png",
          "integration": { "name": "discord" }
        },
        {
          "id": "uuid",
          "name": "Green Coder KNIGHT",
          "image": "filename.png",
          "integration": { "name": "github" }
        }
      ]
    }
  ]
}
```

`GET /api/forge/available-trophies` returns trophies the user has already forged or can forge. This is used to check if a trophy is already claimed.

The user's own badges come from `GET /api/badges` (already used in TrophyRoom.vue). To show which badge requirements are met, the Forge page needs to also fetch the user's badges and cross-reference.

---

## Design Target (from approved mockup — Captura 2)

```
┌─────────────────────────────────────────────────────────────┐
│  Forge                                                      │
│  Trophies created by brands and the community. Meet the     │
│  requirements and forge them to earn XP.                    │
│                                                             │
│  [ALL] [Available] [Almost ready] [Completed]  6 trophies   │
│                                        1 ready to forge     │
│                                                             │
│  ┌─────────────────────┐  ┌─────────────────────┐          │
│  │ icon         1 XP   │  │ icon        10 XP   │          │
│  │ NFT Trophy          │  │ MVP Test             │          │
│  │ TrophyRoom          │  │ TrophyRoom           │          │
│  │                     │  │                      │          │
│  │ The original...     │  │ Prove your worth...  │          │
│  │                     │  │                      │          │
│  │ [S] Steam Year...   │  │ [✓] Skill Valid.     │          │
│  │                     │  │ [✓] Green Coder      │          │
│  │ 0/1 badges  1/1 left│  │ [?] Years of Service │          │
│  │ ░░░░░░░░░░░░░░░░░░░ │  │                      │          │
│  │ [Missing 1 badge]   │  │ 2/3 badges   3/5 left│          │
│  └─────────────────────┘  │ ████████░░░░░░░░░░░░ │          │
│                           │ [Missing 1 badge]    │          │
│                           └─────────────────────┘          │
│                                                             │
│  ┌─────────────────────┐  ┌─────────────────────┐          │
│  │ icon         5 XP   │  │ icon         5 XP   │          │
│  │ Ambar Founders      │  │ Ambar Founders       │          │
│  │ [✓ TABIMOD]         │  │ Throphie             │          │
│  │ [? MVP AMBAR TEST]  │  │ [✓ ADMIN]            │          │
│  │                     │  │ [✓ Green Coder]      │          │
│  │ 1/2 badges  2/5 left│  │                      │          │
│  │ █████░░░░░░░░░░░░░░ │  │ 2/2 badges   5/5 left│         │
│  │ [Missing 1 badge]   │  │ ████████████████████ │          │
│  └─────────────────────┘  │ [===Forge now===]    │  ← orange│
│                           └─────────────────────┘          │
└─────────────────────────────────────────────────────────────┘
```

---

## Design System Reference

```scss
$bg: #000003;
$surface: #0e0f11;
$surface-2: #1a1c1f;
$surface-3: #252729;
$primary: #ff6100;
$accent: #c1f527;
$text: #feeddf;
$text-muted: #9a9590;
$text-dim: #5a5550;
$border: #2a2c2e;
$mono: 'Share Tech Mono', monospace;
```

### Badge Requirement Chips
- **Owned (user has this badge):** `background: rgba($accent, 0.1); border: 1px solid rgba($accent, 0.3); color: $accent;` with checkmark ✓
- **Missing (user doesn't have it):** `background: $surface-3; border: 1px solid $border; color: $text-dim;` with letter initial or ? icon
- Font size: 12px, padding: 4px 10px, border-radius: 4px

### Filter Pills
- Same style as Virtual Hall: pill shape, `$accent` bg when active, `$border` border when inactive
- **All:** shows everything
- **Available:** trophies where user has 0 of required badges
- **Almost ready:** trophies where user has at least 1 but not all required badges
- **Completed:** trophies where user has all required badges (ready to forge)

---

## Step-by-Step Execution

### Step 1: Extend trophy-card.vue with Badge Chips

**File:** `resources/web/js/parts/trophy-card.vue`

Add a new optional prop for badge requirements:

```js
props: {
  trophy: { type: Object, required: true },
  showForgeButton: { type: Boolean, default: false },
  showShowcase: { type: Boolean, default: true },
  showDescription: { type: Boolean, default: false },
  requiredBadges: { type: Array, default: () => [] },
  userBadgeIds: { type: Array, default: () => [] }
},
```

Add a computed for badge status:

```js
badgeChips() {
  if (!this.requiredBadges.length) return [];
  return this.requiredBadges.map(badge => ({
    ...badge,
    owned: this.userBadgeIds.includes(badge.id)
  }));
},
badgesOwned() {
  return this.badgeChips.filter(b => b.owned).length;
},
badgesRequired() {
  return this.requiredBadges.length;
},
```

Update the existing `progressPercent` and `isReady` computed to use these when `requiredBadges` is provided:

```js
progressPercent() {
  if (this.requiredBadges.length) {
    return Math.round((this.badgesOwned / this.badgesRequired) * 100);
  }
  if (!this.trophy.badges_required) return 0;
  return Math.round(((this.trophy.badges_owned || 0) / this.trophy.badges_required) * 100);
},
isReady() {
  if (this.requiredBadges.length) {
    return this.badgesOwned >= this.badgesRequired;
  }
  return this.trophy.badges_required && (this.trophy.badges_owned || 0) >= this.trophy.badges_required;
},
```

Add to the template, AFTER `.trophy-card__brand` and BEFORE `.trophy-card__progress`:

```html
<!-- Description (optional) -->
<div class="trophy-card__desc" v-if="showDescription && trophy.description">
  {{ trophy.description }}
</div>

<!-- Badge Requirement Chips -->
<div class="trophy-card__chips" v-if="badgeChips.length">
  <div
    v-for="chip in badgeChips"
    :key="chip.id"
    class="trophy-card__chip"
    :class="chip.owned ? 'trophy-card__chip--owned' : 'trophy-card__chip--missing'"
  >
    <span class="trophy-card__chip-icon">{{ chip.owned ? '✓' : chip.name.charAt(0).toUpperCase() }}</span>
    <span class="trophy-card__chip-name">{{ chip.name }}</span>
  </div>
</div>
```

Update the progress section to use `badgesOwned` and `badgesRequired` when available:

```html
<div class="trophy-card__progress" v-if="badgesRequired || trophy.badges_required">
  <div class="trophy-card__progress-info">
    <span>{{ badgesRequired ? badgesOwned : (trophy.badges_owned || 0) }}/{{ badgesRequired || trophy.badges_required }} badges</span>
    <span>{{ progressPercent }}%</span>
  </div>
  <div class="trophy-card__progress-bar">
    <div class="trophy-card__progress-fill" :style="{ width: progressPercent + '%' }"></div>
  </div>
</div>
```

Add scoped styles for the new elements:

```css
.trophy-card__desc {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  line-height: 1.5;
  margin-top: 4px;
}

.trophy-card__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
}

.trophy-card__chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 4px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

.trophy-card__chip--owned {
  background: rgba(193, 245, 39, 0.1);
  border: 1px solid rgba(193, 245, 39, 0.3);
  color: #c1f527;
}

.trophy-card__chip--missing {
  background: #252729;
  border: 1px solid #2a2c2e;
  color: #5a5550;
}

.trophy-card__chip-icon {
  font-size: 11px;
}

.trophy-card__chip-name {
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
```

**Verify:** `npm run dev` — TrophyRoom page still works (trophy-card used there without requiredBadges prop, should degrade gracefully).

---

### Step 2: Rewrite Forge.vue

**File:** `resources/web/js/pages/Forge.vue`

**Script changes (keep all existing, add new):**

Replace imports:
```js
import {defineComponent} from "vue";
import TrophyCard from "../parts/trophy-card.vue";
import buttonWhite from "../parts/button.vue";
import store from "../store/store.js";
import api from "../api/api.js";
```

Replace components:
```js
components: {
  TrophyCard,
  buttonWhite,
  store
},
```

Add to `data()`:
```js
activeFilter: 'all',
userBadges: [],
```

Add a method to fetch user badges:
```js
getUserBadges() {
  api.get('/api/badges').then(resp => {
    if (resp.status === 200) {
      this.userBadges = resp.data.data;
    }
  }).catch(e => {
    console.log('badges error', e);
  });
},
setFilter(filter) {
  this.activeFilter = filter;
},
```

In `mounted()`, add after existing calls:
```js
this.getUserBadges();
```

Add computed properties:
```js
userBadgeIds() {
  return this.userBadges.map(b => b.id);
},
filteredTrophies() {
  if (this.activeFilter === 'all') return this.achievements;
  return this.achievements.filter(trophy => {
    const required = trophy.badges ? trophy.badges.length : 0;
    if (required === 0) return this.activeFilter === 'available';
    const owned = trophy.badges.filter(b => this.userBadgeIds.includes(b.id)).length;
    if (this.activeFilter === 'available') return owned === 0;
    if (this.activeFilter === 'almost') return owned > 0 && owned < required;
    if (this.activeFilter === 'completed') return owned >= required;
    return true;
  });
},
readyToForgeCount() {
  return this.achievements.filter(trophy => {
    if (!trophy.badges || !trophy.badges.length) return false;
    const owned = trophy.badges.filter(b => this.userBadgeIds.includes(b.id)).length;
    return owned >= trophy.badges.length;
  }).length;
},
```

**New template:**

```vue
<template>
  <div class="main_block">
    <!-- Header -->
    <div class="forge-header">
      <h1 class="forge-title">Forge</h1>
      <p class="forge-subtitle">Trophies created by brands and the community. Meet the requirements and forge them to earn XP and showcase your achievements.</p>
    </div>

    <!-- Filter Pills + Stats -->
    <div class="forge-controls">
      <div class="forge-pills">
        <button class="forge-pill" :class="{ 'forge-pill--active': activeFilter === 'all' }" @click="setFilter('all')">All</button>
        <button class="forge-pill" :class="{ 'forge-pill--active': activeFilter === 'available' }" @click="setFilter('available')">Available</button>
        <button class="forge-pill" :class="{ 'forge-pill--active': activeFilter === 'almost' }" @click="setFilter('almost')">Almost ready</button>
        <button class="forge-pill" :class="{ 'forge-pill--active': activeFilter === 'completed' }" @click="setFilter('completed')">Completed</button>
      </div>
      <div class="forge-stats">
        <span class="forge-stats__total">{{ achievements.length }} trophies</span>
        <span class="forge-stats__ready" v-if="readyToForgeCount">{{ readyToForgeCount }} ready to forge</span>
      </div>
    </div>

    <!-- Trophy Grid -->
    <div class="forge-grid">
      <TrophyCard
        v-for="trophy in filteredTrophies"
        :key="trophy.id"
        :trophy="trophy"
        :show-forge-button="true"
        :show-showcase="false"
        :show-description="true"
        :required-badges="trophy.badges || []"
        :user-badge-ids="userBadgeIds"
      />
    </div>

    <!-- Empty State -->
    <div class="forge-empty" v-if="!filteredTrophies.length && achievements.length">
      <p>No trophies match this filter.</p>
    </div>
  </div>
</template>
```

**New scoped styles:**

```vue
<style scoped>
.forge-header {
  margin-bottom: 24px;
}

.forge-title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 28px;
  font-weight: 400;
  margin: 0 0 6px;
}

.forge-subtitle {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  margin: 0;
  max-width: 600px;
}

.forge-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 24px;
}

.forge-pills {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.forge-pill {
  padding: 6px 16px;
  border-radius: 20px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.15s;
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
}

.forge-pill--active {
  background: #c1f527;
  color: #000003;
  border-color: #c1f527;
}

.forge-pill:hover:not(.forge-pill--active) {
  border-color: #9a9590;
  color: #feeddf;
}

.forge-stats {
  display: flex;
  gap: 16px;
  align-items: center;
}

.forge-stats__total {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}

.forge-stats__ready {
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}

.forge-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.forge-empty {
  text-align: center;
  padding: 40px;
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
}

@media (max-width: 768px) {
  .forge-grid {
    grid-template-columns: 1fr;
  }

  .forge-controls {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
```

**Verify:** `npm run dev` → navigate to `/#/forge`.

Check:
- [ ] Title says "Forge" with new subtitle (no NFT/mint language)
- [ ] Filter pills work (All / Available / Almost ready / Completed)
- [ ] Trophy cards show in 2-column grid
- [ ] Each card shows: icon, name, XP pill, description, badge requirement chips
- [ ] Owned badges have green checkmark chip, missing have gray initial chip
- [ ] Progress bar shows X/Y badges and percentage
- [ ] "Forge now" button appears (orange) when all badges are owned
- [ ] "Missing N badge" button appears (gray, disabled) when not all badges owned
- [ ] Stats line shows "N trophies" and "N ready to forge"
- [ ] Mobile (375px): single column grid
- [ ] No "Weight", "Ambars", "NFT", "mint" text
- [ ] No hexagon borders
- [ ] No console errors

---

### Step 3: Final Build & Deploy

```bash
npm run build
```

Commit:
```
feat: Phase 3C — Forge page redesign with badge requirement chips and filter pills
```

Deploy:
```bash
git push origin main
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build"
```

---

## What Changed vs Old Forge

| Old | New |
|-----|-----|
| "mint an NFT" in subtitle | Clean description about earning XP |
| `forge-card.vue` with hexagons | `trophy-card.vue` with clean square icons |
| "Weight 10" label | "10 XP" pill |
| NFT/1 supply count | Removed |
| No badge chips | Green ✓ (owned) / gray initial (missing) chips |
| No progress bars | Progress bars with badge count |
| No filter pills | All / Available / Almost ready / Completed |
| 4-column flat layout | 2-column grid |
| All trophies same visual | Orange border + "Forge now" button when ready |

## Development Rules

1. One change at a time. Modify → verify → next.
2. Don't break existing pages that use trophy-card.vue (TrophyRoom, VirtualHall) — new props are optional with defaults.
3. Vue 3 Options API ONLY.
4. Mobile-first. Test at 375px.
5. Share Tech Mono only.

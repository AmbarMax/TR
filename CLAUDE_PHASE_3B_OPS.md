# CLAUDE_PHASE_3B_OPS.md — Virtual Hall (Public Profile) Redesign

> **Operational brief for Claude Code.**
> Read `CLAUDE_FRONTEND_OPS.md` Section 2 for design system tokens.
> This file covers Phase 3B: the Virtual Hall — the public profile page at `/virtual-hall/:username`.

---

## Scope

Redesign the Virtual Hall (`VirtualHall.vue`) — the public-facing profile page that anyone can view without authentication. The current page shows Uru/Ambar/Rune balances, NFT counts, legacy styling, and flat badge lists. The new design shows a clean profile header with stats, featured/showcased items, achievements, trophies, and platform badges grouped by service.

**Files to modify:**
```
resources/web/js/pages/VirtualHall/VirtualHall.vue    ← Full template rewrite + scoped styles
```

**DO NOT modify:**
- `VirtualHall/components/SubscriptionPricesBlock.vue` — keep as-is, still imported but we may hide it for now
- Old card components (`achievement-card.vue`, `forge-card.vue`, `validate-card.vue`) — don't touch
- The `<script>` API calls, data properties, Centrifugo subscriptions, follow logic — keep ALL of it
- The router configuration — the route `/virtual-hall/:username` under `SinglePage` component stays the same

**Reuse these components from Phase 3A:**
```
resources/web/js/parts/trophy-card.vue      ← For forged trophies section
resources/web/js/parts/badge-tile.vue        ← For platform badges section
resources/web/js/parts/achievement-row.vue   ← For achievements section
```

---

## Design Target (from approved mockups — Capturas 1 and 3)

```
┌─────────────────────────────────────────────────────────────────┐
│  ┌──────────────────────────────────────────────────────────┐   │
│  │              BANNER IMAGE (or dark fallback)             │   │
│  │                                                          │   │
│  │   ┌────┐                        trophyroom.gg/Username   │   │
│  │   │AVA │  Username  [D] [S] [G]                          │   │
│  │   └────┘  Bio / description                              │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌──────────┬──────────┬──────────┬──────────┐                  │
│  │ 23       │ 2        │ 3        │ 5        │                  │
│  │ Badges   │ Trophies │ Achieve. │ Platforms│                  │
│  └──────────┴──────────┴──────────┴──────────┘                  │
│                                                                 │
│  [Follow]  [Copy Link]                                          │
│                                                                 │
│  FEATURED                                    curated by player  │
│  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐                   │
│  │ item   │ │ item   │ │ item   │ │ item   │                   │
│  └────────┘ └────────┘ └────────┘ └────────┘                   │
│                                                                 │
│  ACHIEVEMENTS                                         3         │
│  ┌─────────────────────────────────────────────────────┐        │
│  │ icon  Name                    ✓ Validated · 12      │        │
│  ├─────────────────────────────────────────────────────┤        │
│  │ icon  Name                    ✓ Validated · 8       │        │
│  └─────────────────────────────────────────────────────┘        │
│                                                                 │
│  FORGED TROPHIES                                                │
│  ┌──────────┐ ┌──────────┐                                     │
│  │ trophy   │ │ trophy   │                                     │
│  └──────────┘ └──────────┘                                     │
│                                                                 │
│  PLATFORM BADGES                                                │
│  [All] [Discord] [Steam] [GitHub]                               │
│  ┌ Discord  4 ─────────────────────────────────────┐            │
│  │ [tile][tile][tile][tile]                         │            │
│  └─────────────────────────────────────────────────┘            │
│  ┌ Steam  6 ───────────────────────────────────────┐            │
│  │ [tile][tile][tile][tile][tile][tile]             │            │
│  └─────────────────────────────────────────────────┘            │
│  ┌ GitHub  3 ──────────────────────────────────────┐            │
│  │ [tile][tile][tile]                               │            │
│  └─────────────────────────────────────────────────┘            │
└─────────────────────────────────────────────────────────────────┘
```

---

## Design System Reference

From `CLAUDE_FRONTEND_OPS.md` Section 2:

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

---

## Step-by-Step Execution

### Step 1: Update VirtualHall.vue — Script Changes (imports only)

**File:** `resources/web/js/pages/VirtualHall/VirtualHall.vue`

Add these imports at the top of the `<script>` (keep ALL existing imports):

```js
import TrophyCard from "../../parts/trophy-card.vue";
import BadgeTile from "../../parts/badge-tile.vue";
import AchievementRow from "../../parts/achievement-row.vue";
```

Add to the `components` object (keep all existing components):

```js
TrophyCard,
BadgeTile,
AchievementRow,
```

Add these computed properties (keep all existing computed):

```js
totalBadges() {
  return this.discord_badges.length + this.steam_badges.length + this.github_badges.length;
},
totalPlatforms() {
  let count = 0;
  if (this.discord_badges.length) count++;
  if (this.steam_badges.length) count++;
  if (this.github_badges.length) count++;
  return count;
},
featuredItems() {
  const badges = [...this.discord_badges, ...this.steam_badges, ...this.github_badges].filter(b => b.display);
  const trophyItems = this.trophies.filter(t => t.pivot && t.pivot.display);
  const achItems = this.achievements.filter(a => a.display);
  return [...trophyItems, ...achItems, ...badges].slice(0, 4);
},
```

Add a data property for badge filter:

```js
badgeFilter: 'all',
```

Add a method for badge filtering:

```js
setBadgeFilter(filter) {
  this.badgeFilter = filter;
},
```

**DO NOT modify:** `getUserData()`, `copyLink()`, `followAction()`, `subscribeCentrifugoBalances()`, or the `mounted()` hook. All stay exactly as they are.

**Verify:** `npm run dev` — no errors, page still loads.

---

### Step 2: Rewrite VirtualHall.vue Template

Replace the ENTIRE `<template>` block with the following. Every `v-model`, `@click`, `v-if` binding references data/methods that already exist in the `<script>`:

```vue
<template>
  <ambar-notification v-if="notificationModalOpen"></ambar-notification>

  <!-- User found -->
  <div v-if="user !== null" class="vh">

    <!-- Banner + Avatar + Info -->
    <div class="vh-banner">
      <div class="vh-banner__bg">
        <img v-if="user.background" :src="user.background" alt="" class="vh-banner__img" />
      </div>
      <div class="vh-banner__url">trophyroom.gg/{{ user.username }}</div>
    </div>

    <div class="vh-profile">
      <div class="vh-avatar">
        <img v-if="user.avatar" :src="user.avatar" alt="" />
        <img v-else src="../../../images/web/img/user.svg" alt="user" />
      </div>
      <div class="vh-profile__info">
        <div class="vh-profile__name-row">
          <h1 class="vh-profile__name">{{ user.username }}</h1>
          <div class="vh-profile__platforms">
            <img v-if="discord_badges.length" src="../../../images/web/img/icons/discord.svg" alt="Discord" class="vh-profile__platform-icon" title="Discord connected" />
            <img v-if="steam_badges.length" src="../../../images/web/img/icons/steam.svg" alt="Steam" class="vh-profile__platform-icon" title="Steam connected" />
            <img v-if="github_badges.length" src="../../../images/web/img/icons/github.svg" alt="GitHub" class="vh-profile__platform-icon" title="GitHub connected" />
          </div>
        </div>
        <p class="vh-profile__bio" v-if="user.description">{{ user.description }}</p>
      </div>
    </div>

    <!-- Stats Bar -->
    <div class="vh-stats">
      <div class="vh-stat">
        <span class="vh-stat__number">{{ totalBadges }}</span>
        <span class="vh-stat__label">Badges</span>
      </div>
      <div class="vh-stat">
        <span class="vh-stat__number">{{ trophies.length }}</span>
        <span class="vh-stat__label">Trophies</span>
      </div>
      <div class="vh-stat">
        <span class="vh-stat__number">{{ achievements.length }}</span>
        <span class="vh-stat__label">Achievements</span>
      </div>
      <div class="vh-stat">
        <span class="vh-stat__number">{{ totalPlatforms }}</span>
        <span class="vh-stat__label">Platforms</span>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="vh-actions">
      <template v-if="followStatus !== null">
        <button
          class="vh-btn"
          :class="followStatus === follow ? 'vh-btn--following' : 'vh-btn--follow'"
          @click="followAction(user.id, user.username)"
          :disabled="followBtnLoading"
        >
          {{ followStatus === follow ? 'Following' : 'Follow' }}
        </button>
      </template>
      <button class="vh-btn vh-btn--secondary" @click="copyLink">
        Copy Link
      </button>
    </div>

    <div class="vh-content">

      <!-- Featured Section -->
      <div class="vh-section" v-if="featuredItems.length">
        <div class="vh-section__header">
          <span class="vh-section__label">Featured</span>
          <span class="vh-section__meta">curated by player</span>
        </div>
        <div class="vh-featured-grid">
          <div class="vh-featured-card" v-for="item in featuredItems" :key="item.id">
            <div class="vh-featured-card__icon">
              <img v-if="item.image" :src="getFeaturedImageUrl(item)" alt="" />
            </div>
            <div class="vh-featured-card__name">{{ item.name }}</div>
            <div class="vh-featured-card__source" v-if="item.integration">{{ item.integration }}</div>
            <div class="vh-featured-card__source" v-else-if="item.description">{{ item.description }}</div>
          </div>
        </div>
      </div>

      <!-- Achievements Section -->
      <div class="vh-section" v-if="achievements.length">
        <div class="vh-section__header">
          <span class="vh-section__label">Achievements</span>
          <span class="vh-section__count">{{ achievements.length }}</span>
        </div>
        <div class="vh-ach-list">
          <div class="vh-ach-row" v-for="ach in achievements" :key="ach.id">
            <div class="vh-ach-row__icon">
              <img v-if="ach.image" :src="'/storage/achievements/' + ach.image" alt="" />
            </div>
            <div class="vh-ach-row__info">
              <div class="vh-ach-row__name">{{ ach.name }}</div>
              <div class="vh-ach-row__status">
                <span v-if="ach.status === 1" class="vh-ach-row__validated">✓ Validated</span>
                <span v-if="ach.validations_count" class="vh-ach-row__vouches"> · {{ ach.validations_count }} vouches</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Forged Trophies Section -->
      <div class="vh-section" v-if="trophies.length">
        <div class="vh-section__header">
          <span class="vh-section__label">Forged trophies</span>
          <span class="vh-section__count">{{ trophies.length }}</span>
        </div>
        <div class="vh-trophy-grid">
          <TrophyCard
            v-for="trophy in trophies"
            :key="trophy.id"
            :trophy="trophy"
            :show-forge-button="false"
            :show-showcase="false"
          />
        </div>
      </div>

      <!-- Platform Badges Section -->
      <div class="vh-section" v-if="totalBadges > 0">
        <div class="vh-section__header">
          <span class="vh-section__label">Platform badges</span>
        </div>

        <!-- Filter Pills -->
        <div class="vh-filter-pills">
          <button class="vh-pill" :class="{ 'vh-pill--active': badgeFilter === 'all' }" @click="setBadgeFilter('all')">All</button>
          <button class="vh-pill" :class="{ 'vh-pill--active': badgeFilter === 'discord' }" @click="setBadgeFilter('discord')" v-if="discord_badges.length">Discord</button>
          <button class="vh-pill" :class="{ 'vh-pill--active': badgeFilter === 'steam' }" @click="setBadgeFilter('steam')" v-if="steam_badges.length">Steam</button>
          <button class="vh-pill" :class="{ 'vh-pill--active': badgeFilter === 'github' }" @click="setBadgeFilter('github')" v-if="github_badges.length">GitHub</button>
        </div>

        <!-- Discord Badges -->
        <div class="vh-platform-group" v-if="discord_badges.length && (badgeFilter === 'all' || badgeFilter === 'discord')">
          <div class="vh-platform-header">
            <img src="../../../images/web/img/icons/discord.svg" alt="Discord" class="vh-platform-icon" />
            <span class="vh-platform-name">Discord</span>
            <span class="vh-platform-count">{{ discord_badges.length }}</span>
          </div>
          <div class="vh-badge-grid">
            <BadgeTile
              v-for="badge in discord_badges"
              :key="badge.id"
              :badge="badge"
              service="discord"
            />
          </div>
        </div>

        <!-- Steam Badges -->
        <div class="vh-platform-group" v-if="steam_badges.length && (badgeFilter === 'all' || badgeFilter === 'steam')">
          <div class="vh-platform-header">
            <img src="../../../images/web/img/icons/steam.svg" alt="Steam" class="vh-platform-icon" />
            <span class="vh-platform-name">Steam</span>
            <span class="vh-platform-count">{{ steam_badges.length }}</span>
          </div>
          <div class="vh-badge-grid">
            <BadgeTile
              v-for="badge in steam_badges"
              :key="badge.id"
              :badge="badge"
              service="steam"
            />
          </div>
        </div>

        <!-- GitHub Badges -->
        <div class="vh-platform-group" v-if="github_badges.length && (badgeFilter === 'all' || badgeFilter === 'github')">
          <div class="vh-platform-header">
            <img src="../../../images/web/img/icons/github.svg" alt="GitHub" class="vh-platform-icon" />
            <span class="vh-platform-name">GitHub</span>
            <span class="vh-platform-count">{{ github_badges.length }}</span>
          </div>
          <div class="vh-badge-grid">
            <BadgeTile
              v-for="badge in github_badges"
              :key="badge.id"
              :badge="badge"
              service="github"
            />
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- User not found -->
  <div v-if="user === null" class="vh-not-found">
    <h2 class="vh-not-found__title">User not found</h2>
    <router-link to="/" class="vh-not-found__link">
      <button-white :text="'Go to homepage'"></button-white>
    </router-link>
  </div>
</template>
```

**Add this method** to the `methods` object for featured item images:

```js
getFeaturedImageUrl(item) {
  if (item.integration) {
    return `/storage/integrations/${item.integration}/${item.image}`;
  } else if (item.type !== undefined) {
    return `/storage/trophies/${item.image}`;
  } else {
    return `/storage/achievements/${item.image}`;
  }
},
```

---

### Step 3: Replace VirtualHall.vue Scoped Styles

Remove ALL existing `<style scoped>` content. Replace with:

```vue
<style scoped>
.vh {
  max-width: 860px;
  margin: 0 auto;
  padding: 0 20px 60px;
  font-family: 'Share Tech Mono', monospace;
}

/* Banner */
.vh-banner {
  position: relative;
  height: 180px;
  background: #0e0f11;
  border-radius: 8px 8px 0 0;
  overflow: hidden;
  margin-bottom: 0;
}

.vh-banner__bg {
  width: 100%;
  height: 100%;
}

.vh-banner__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.vh-banner__url {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(0, 0, 3, 0.7);
  color: #9a9590;
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 4px;
  backdrop-filter: blur(4px);
}

/* Profile */
.vh-profile {
  display: flex;
  align-items: flex-end;
  gap: 16px;
  margin-top: -36px;
  padding: 0 16px;
  margin-bottom: 20px;
  position: relative;
  z-index: 1;
}

.vh-avatar {
  width: 80px;
  height: 80px;
  min-width: 80px;
  border-radius: 50%;
  border: 3px solid #000003;
  overflow: hidden;
  background: #0e0f11;
}

.vh-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.vh-profile__info {
  padding-bottom: 4px;
}

.vh-profile__name-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.vh-profile__name {
  color: #feeddf;
  font-size: 24px;
  font-weight: 400;
  margin: 0;
}

.vh-profile__platforms {
  display: flex;
  gap: 6px;
}

.vh-profile__platform-icon {
  width: 18px;
  height: 18px;
  opacity: 0.5;
}

.vh-profile__bio {
  color: #9a9590;
  font-size: 13px;
  margin: 4px 0 0;
}

/* Stats Bar */
.vh-stats {
  display: flex;
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px 0;
  margin-bottom: 20px;
}

.vh-stat {
  flex: 1;
  text-align: center;
  border-right: 1px solid #2a2c2e;
}

.vh-stat:last-child {
  border-right: none;
}

.vh-stat__number {
  display: block;
  color: #feeddf;
  font-size: 22px;
  font-weight: 400;
}

.vh-stat__label {
  display: block;
  color: #5a5550;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-top: 2px;
}

/* Action Buttons */
.vh-actions {
  display: flex;
  gap: 10px;
  margin-bottom: 32px;
}

.vh-btn {
  padding: 8px 20px;
  border-radius: 4px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.15s;
  border: 1px solid;
}

.vh-btn--follow {
  background: #c1f527;
  color: #000003;
  border-color: #c1f527;
}

.vh-btn--follow:hover {
  filter: brightness(1.1);
}

.vh-btn--following {
  background: transparent;
  color: #c1f527;
  border-color: #c1f527;
}

.vh-btn--secondary {
  background: transparent;
  color: #9a9590;
  border-color: #2a2c2e;
}

.vh-btn--secondary:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.vh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Content */
.vh-content {
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Sections */
.vh-section {
  margin-bottom: 36px;
}

.vh-section__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.vh-section__label {
  color: #ff6100;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.vh-section__meta {
  color: #5a5550;
  font-size: 11px;
  letter-spacing: 0.05em;
}

.vh-section__count {
  color: #5a5550;
  font-size: 12px;
}

/* Featured Grid */
.vh-featured-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
}

.vh-featured-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px;
  text-align: center;
  transition: border-color 0.15s;
}

.vh-featured-card:hover {
  border-color: rgba(255, 97, 0, 0.3);
}

.vh-featured-card__icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 10px;
  background: #1a1c1f;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.vh-featured-card__icon img {
  width: 44px;
  height: 44px;
  object-fit: contain;
}

.vh-featured-card__name {
  color: #feeddf;
  font-size: 13px;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.vh-featured-card__source {
  color: #5a5550;
  font-size: 11px;
  text-transform: capitalize;
}

/* Achievement Rows */
.vh-ach-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.vh-ach-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
}

.vh-ach-row__icon {
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

.vh-ach-row__icon img {
  width: 32px;
  height: 32px;
  object-fit: contain;
}

.vh-ach-row__info {
  flex: 1;
}

.vh-ach-row__name {
  color: #feeddf;
  font-size: 14px;
}

.vh-ach-row__status {
  font-size: 12px;
  margin-top: 2px;
}

.vh-ach-row__validated {
  color: #c1f527;
}

.vh-ach-row__vouches {
  color: #9a9590;
}

/* Trophy Grid */
.vh-trophy-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

/* Filter Pills */
.vh-filter-pills {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.vh-pill {
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

.vh-pill--active {
  background: #c1f527;
  color: #000003;
  border-color: #c1f527;
}

.vh-pill:hover:not(.vh-pill--active) {
  border-color: #9a9590;
  color: #feeddf;
}

/* Platform Groups */
.vh-platform-group {
  margin-bottom: 24px;
}

.vh-platform-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.vh-platform-icon {
  width: 20px;
  height: 20px;
  opacity: 0.7;
}

.vh-platform-name {
  color: #9a9590;
  font-size: 14px;
}

.vh-platform-count {
  color: #5a5550;
  font-size: 12px;
}

.vh-badge-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

/* Not Found */
.vh-not-found {
  text-align: center;
  padding: 80px 20px;
}

.vh-not-found__title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 24px;
  font-weight: 400;
  margin-bottom: 20px;
}

/* Responsive */
@media (max-width: 640px) {
  .vh-banner {
    height: 120px;
  }

  .vh-profile {
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-top: -40px;
  }

  .vh-profile__name-row {
    justify-content: center;
  }

  .vh-stats {
    flex-wrap: wrap;
  }

  .vh-stat {
    flex: 0 0 50%;
    padding: 8px 0;
    border-right: none;
  }

  .vh-stat:nth-child(1),
  .vh-stat:nth-child(2) {
    border-bottom: 1px solid #2a2c2e;
  }

  .vh-actions {
    justify-content: center;
  }

  .vh-featured-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .vh-trophy-grid {
    grid-template-columns: 1fr;
  }
}
</style>
```

**Verify:** `npm run dev` → navigate to `/virtual-hall/SenpaiMax` (or whatever username exists). Check:
- [ ] Banner image displays (or dark fallback if no banner)
- [ ] Avatar + username + platform icons display correctly
- [ ] Stats bar shows correct counts
- [ ] Follow/Copy Link buttons work
- [ ] Featured section shows showcased items
- [ ] Achievements render as clean rows
- [ ] Trophies render in grid cards
- [ ] Platform badges show as compact tiles grouped by platform
- [ ] Filter pills work (All / Discord / Steam / GitHub)
- [ ] No "Uru", "Ambar", "Rune", "NFTs" text anywhere
- [ ] Mobile (375px): everything stacks cleanly
- [ ] No console errors

---

### Step 4: Final Build & Deploy

```bash
npm run build
```

Commit:
```
feat: Phase 3B — Virtual Hall public profile redesign
```

Deploy:
```bash
git push origin main
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git pull origin main && npm run build"
```

---

## What Changed vs Old VirtualHall

| Old | New |
|-----|-----|
| Uru/Ambar/Rune balance display | Stats bar (Badges/Trophies/Achievements/Platforms) |
| NFT count and NFT section | Removed entirely |
| Flat badge list with hexagon wrappers | Compact 56px tiles grouped by platform |
| Achievement cards (old component) | Clean rows with validation status |
| Trophy cards (old forge-card component) | New trophy-card component with XP pills |
| "Ambar" text references | Removed |
| No filter for badges | Platform filter pills |
| No featured/showcased section | Featured section from showcased items |

## What This Does NOT Cover

- **Forge page** (`Forge.vue`) — Phase 3C
- **Profile/Settings** (`Profile.vue`) — Phase 5
- **SubscriptionPricesBlock** — hidden for now (not rendered in new template). Can be re-added later with updated styling.
- **Centrifugo balance updates** — the subscriptions still run in the script but the balance display is removed from the template. No functional impact.

---

## Development Rules

1. One change at a time. Modify → verify → next.
2. Don't break existing functionality.
3. Vue 3 Options API ONLY.
4. Mobile-first. Test at 375px.
5. Share Tech Mono only.

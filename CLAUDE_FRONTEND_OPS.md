# CLAUDE_FRONTEND_OPS.md — TrophyRoom 2.0 Frontend Renovation Brief

> **This document is an operational brief for Claude Code.**
> It contains the results of a full codebase audit plus precise instructions for the UI renovation.
> Read CLAUDE.md (the original) for product context. This file is about *how to execute*.

---

## 0. Pending Task (Do Before UI Work)

**LinkDiscord.vue deploy** — The component + route already exist in main. Needs server-side deploy:

```bash
ssh root@164.92.83.95
cd /var/www/ambar
git pull origin main
npm run build
# Test: curl -s -w "%{redirect_url}" -o /dev/null "https://app.ambar.gg/bot/link?discord_user_id=476083615000821771&guild_id=947900641366401054&discord_username=maximo"
# Expected: redirects to /#/link-discord?discord_user_id=...
```

Do this first, verify, then proceed to Phase 1.

---

## 1. Codebase Audit Results

### File Map (what exists, what matters)

```
resources/views/layouts/web.blade.php     ← Root HTML. Loads Google Fonts + Vite bundle
resources/web/js/app.js                   ← Vue entry point
resources/web/js/App.vue                  ← Root Vue component
resources/web/js/Main.vue                 ← Authenticated layout (sidebar + topbar + <router-view>)
resources/web/js/components/sidebar.vue   ← Left nav (200 lines)
resources/web/js/components/main-header.vue ← Top bar with currencies + user dropdown (~280 lines)
resources/web/js/router/routes.js         ← All route definitions
resources/web/js/store/store.js           ← Vuex store
resources/web/css/style.scss              ← Main stylesheet (45KB, ~2000+ lines)
resources/web/css/parts/_variables.scss   ← SCSS variables (colors + fonts)
resources/web/css/parts/_typography.scss  ← Font-face declarations (all commented out — fonts load via Google Fonts in blade)
resources/web/css/parts/_null.scss        ← CSS reset
tailwind.config.js                        ← Tailwind config (BROKEN for frontend — see below)
vite.config.js                            ← Vite bundler config
```

### Current State Summary

**Colors (old):**
- `$background-global: #18181B` — flat dark gray everywhere
- `$background-elements: #212124` — cards, slightly lighter
- `$accent-green: #CAFB01` — chartreuse, used for active states + SVG fills
- `$main-text: rgba(255, 255, 255, 0.69)` — washed out white
- No orange/brand color exists. Everything is gray + chartreuse.

**Fonts (old):**
- `$mono: 'JetBrains Mono', monospace` — used everywhere
- `$orbitron: 'Orbitron', sans-serif` — used for h3 tags
- Both loaded via Google Fonts `<link>` tags in `web.blade.php`
- `_typography.scss` has all font-face declarations commented out (dead code)

**Tailwind (BROKEN for frontend):**
- `tailwind.config.js` `content` array only includes Blade files and `vendor/` paths
- Does NOT include `resources/web/js/**/*.vue` — Tailwind classes in Vue components are purged/ignored
- Theme has no project colors, just default `Figtree` font
- **Fix required before any Tailwind usage in Vue components**

**Sidebar (`sidebar.vue`):**
- 7 nav items: Trophy Room, Validate, Forge, Reward Chest, Feed, Exchange, Network
- SVG icons with hardcoded colors: `#CAFB01` (active), `#BABABA` (inactive)
- Active state: left border `$accent-green`, bg `$background-elements`
- Logo: `logo.svg` (Ambar logo)
- Footer: Twitter, Discord, Support links (all point to Ambar URLs)
- Mobile: hamburger toggle via Vuex `store.state.activeSideBar`
- Width: 270px fixed, sticky, full viewport height

**Header (`main-header.vue`):**
- Desktop: 3 currency indicators (Uru/Ambar/Rune) with icon images + amounts from Vuex
- Notification bell with unread count
- User avatar + username + dropdown
- Dropdown contains: Account, **Connect Wallet** (MetaMask), Log Out
- Imports `Web3` from 'web3' as a component (!) — legacy, should be removed
- Centrifugo WebSocket subscriptions for real-time balance updates
- Mobile: currencies move to a second row below the header
- Login button (when not authenticated): chartreuse, `JetBrains Mono` font

**Body background:**
- Has two decorative background images positioned absolute
- `background-color: $background-global` (#18181B)

**Title:** `<title>Ambar</title>` in `web.blade.php`

---

## 2. Target Design System (from CLAUDE.md Section 5)

### Color Palette (DEFINITIVE)

```css
:root {
  /* Backgrounds */
  --bg:         #000003;
  --surface:    #0e0f11;
  --surface-2:  #1a1c1f;
  --surface-3:  #252729;

  /* Brand */
  --primary:    #ff6100;    /* Naranja fuego */
  --accent:     #c1f527;    /* Chartreuse */

  /* Text */
  --text:       #feeddf;    /* Warm cream */
  --text-muted: #9a9590;
  --text-dim:   #5a5550;

  /* Borders */
  --border:     #2a2c2e;
}
```

### Color Roles
- **Naranja fuego (#ff6100)**: Brand identity, trophy borders, section labels, primary CTAs
- **Chartreuse (#c1f527)**: Active nav items, secondary buttons, checkmarks, progress, XP badges
- **They don't compete** — naranja is protagonist, chartreuse is supporting

### Typography
- **Share Tech Mono** for everything (headings, body, UI, buttons)
- Replace both JetBrains Mono and Orbitron
- Load via Google Fonts

### Component Patterns
- Border radius: 4-6px
- Cards: `bg: var(--surface)`, `border: 1px solid var(--border)`, `border-radius: 6px`
- Card hover: `border-color: var(--primary)` or `rgba(255,97,0,0.3)`
- Buttons primary: `bg: var(--accent)`, `color: var(--bg)`
- Buttons secondary: `bg: transparent`, `border: 1px solid var(--border)`, `color: var(--text)`
- Section labels: `font-size: 11px`, `color: var(--primary)`, `text-transform: uppercase`, `letter-spacing: 0.12em`
- Badge tiles (compact): 48-64px squares
- Progress bars: gray `#3a3d40` base, chartreuse fill in-progress, naranja fill complete

---

## 3. Phase 1 — Shell & Global Styles (Execute Step by Step)

### Step 1: Update `_variables.scss`

**File:** `resources/web/css/parts/_variables.scss`

Replace entire content with:

```scss
// ============================================
// TrophyRoom 2.0 — Design Tokens
// ============================================

// Backgrounds
$bg:                  #000003;
$surface:             #0e0f11;
$surface-2:           #1a1c1f;
$surface-3:           #252729;

// Brand
$primary:             #ff6100;
$accent:              #c1f527;

// Text
$text:                #feeddf;
$text-muted:          #9a9590;
$text-dim:            #5a5550;

// Borders
$border:              #2a2c2e;

// ============================================
// Legacy aliases (keep until full migration)
// These map old variable names to new tokens
// so existing SCSS doesn't break immediately
// ============================================
$background-global:   $bg;
$background-elements: $surface;
$main-text:           $text-muted;
$accent-green:        $accent;
$auth-bg:             $surface;
$auth-form-bg:        rgba($surface, 0.85);
$auth-text:           $text;
$auth-input-border:   $border;
$auth-input-bg:       rgba($surface-2, 0.5);
$auth-form-links:     $text-muted;
$background-local:    $surface-2;
$background-modal:    rgba($surface-2, 0.85);
$input-bg:            $surface-2;
$button-bg:           $accent;
$button-bg-disabled:  $text-dim;
$button-text:         $bg;
$white:               #fff;

// Typography
$mono:                'Share Tech Mono', monospace;
$orbitron:            'Share Tech Mono', monospace;
```

**Verify:** `npm run dev` — should compile with no errors. The app will look slightly different (darker backgrounds) but nothing should break because legacy aliases preserve all variable references.

---

### Step 2: Update Typography + Blade Title

**File:** `resources/web/css/parts/_typography.scss`

Replace entire content with:

```scss
// TrophyRoom 2.0 — Typography
// Share Tech Mono is loaded via Google Fonts <link> in web.blade.php
// This file is kept for future local font-face declarations if needed
```

**File:** `resources/views/layouts/web.blade.php`

Changes:
1. Replace JetBrains Mono + Orbitron Google Font links with Share Tech Mono
2. Change `<title>` from "Ambar" to "TrophyRoom"

The `<head>` section should become:

```html
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <title>TrophyRoom</title>
    @vite('resources/web/js/app.js')
</head>
```

Note: Share Tech Mono only has one weight (400). No weight range needed in the URL.

**Verify:** `npm run dev` — text should now be Share Tech Mono throughout the app. If anything still shows JetBrains Mono, check for hardcoded `font-family` in component `<style>` blocks.

---

### Step 3: Fix Tailwind Config

**File:** `tailwind.config.js`

Replace entire content with:

```js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/web/js/**/*.vue',
        './resources/web/js/**/*.js',
    ],

    theme: {
        extend: {
            colors: {
                tr: {
                    bg:         '#000003',
                    surface:    '#0e0f11',
                    'surface-2': '#1a1c1f',
                    'surface-3': '#252729',
                    primary:    '#ff6100',
                    accent:     '#c1f527',
                    text:       '#feeddf',
                    'text-muted': '#9a9590',
                    'text-dim': '#5a5550',
                    border:     '#2a2c2e',
                },
            },
            fontFamily: {
                mono: ['"Share Tech Mono"', ...defaultTheme.fontFamily.mono],
            },
            borderRadius: {
                tr: '6px',
            },
        },
    },

    plugins: [forms],
};
```

This enables Tailwind classes like `bg-tr-surface`, `text-tr-primary`, `border-tr-border`, `font-mono`, `rounded-tr` in all Vue components.

**Verify:** `npm run dev` — no errors. Tailwind classes in Vue files will now work.

---

### Step 4: Update `style.scss` — Body + Sidebar + Header Base Styles

**File:** `resources/web/css/style.scss`

**4a. Body background (lines ~11-17):**

Replace:
```scss
body {
    background-color: $background-global;
    background-image: url('/resources/web/images/web/img/backgrounds/bg_1.png'), url('/resources/web/images/web/img/backgrounds/bg_2.png');
    background-repeat: no-repeat;
    background-position: right top, left -300px top 800px;
}
```

With:
```scss
body {
    background-color: $bg;
}
```

Remove the decorative background images — they belong to the old Ambar identity.

**4b. Global h3 (line ~19):**

Replace:
```scss
h3 {
    font-family: Orbitron;
}
```

With:
```scss
h3 {
    font-family: $mono;
}
```

**4c. Sidebar styles (around lines 90-165):**

Replace the `.front-sidebar` block:
```scss
.front-sidebar {
    background-color: $surface;
    display: flex;
    width: 270px;
    min-width: 270px;
    position: sticky;
    height: 100vh;
    min-height: 660px;
    padding: 45px 0;
    flex-direction: column;
    align-items: flex-start;
    top: 0;
    left: 0;
    gap: 60px;
    border-right: 1px solid $border;
}
```

Replace `.sidebar_menu span`:
```scss
.sidebar_menu span {
    color: $text-muted;
    font-family: $mono;
    font-size: 14px;
    font-weight: 400;
    line-height: 24px;
    letter-spacing: 0.02em;
}
```

Replace `.active_item`:
```scss
.active_item {
    border-left: 3px solid $accent;
    background-color: rgba($accent, 0.05) !important;
    padding: 8px 40px 8px 37px !important;
}

.active_item span {
    color: $accent !important;
}
```

Replace `.sidebar_footer_links` — keep the structure but it'll get new content later.

**4d. Header styles (around lines 38-50):**

Replace `.web-header`:
```scss
.web-header {
    background-color: $surface;
    padding: 20px 40px;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-end;
    position: relative;
    border-bottom: 1px solid $border;
}
```

Replace `.header_achievement` styles (around lines 1256-1280):
```scss
.header_achievement {
    display: flex;
    align-items: center;
    gap: 8px;
}

.header_achievement > img {
    width: 20px;
    height: 20px;
}

.header_achievement > span {
    color: $text;
    font-family: $mono;
    font-size: 13px;
    line-height: 20px;
}
```

Replace `.separator_vertical` (around line 1328):
```scss
.separator_vertical {
    width: 1px;
    height: 24px;
    background-color: $border;
    margin: 0 12px;
}
```

Replace `.header_user_info` styles:
```scss
.header_user_info {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.header_user_info > span {
    color: $text;
    font-family: $mono;
    font-size: 14px;
}
```

Replace `.header_dropdown` styles:
```scss
.header_dropdown {
    position: absolute;
    top: 100%;
    right: 40px;
    z-index: 100;
    background-color: $surface-2;
    border: 1px solid $border;
    border-radius: 6px;
    padding: 8px 0;
    min-width: 200px;
}

.header_dropdown ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.header_dropdown ul li {
    padding: 0;
}

.header_dropdown_link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    color: $text-muted;
    font-family: $mono;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.15s;
}

.header_dropdown_link svg {
    stroke: $text-muted;
    width: 18px;
    height: 18px;
}

.header_dropdown ul li:hover {
    background-color: $surface-3;
}

.header_dropdown ul li:hover svg {
    stroke: $text;
}

.header_dropdown ul li:hover span {
    color: $text;
}
```

**Verify:** `npm run dev` — sidebar should be darker with border, header should be darker with border, text should be warm cream. Active nav item should still be chartreuse.

---

### Step 5: Update `sidebar.vue`

**File:** `resources/web/js/components/sidebar.vue`

Changes:
1. Replace hardcoded SVG fill colors with `currentColor` pattern
2. Update social links in footer (or remove temporarily)

For the SVG icons, the approach is:
- Active state: SVGs use `fill="currentColor"` and the `<a>` parent with `.active_item` class sets `color: var(--accent)`
- Inactive state: SVGs use `fill="currentColor"` and default color is `var(--text-muted)`

**However**, the current SVGs use `v-if/v-else` to swap between colored/gray versions. A cleaner approach for now (minimal change, don't break things):

Replace all `fill="#CAFB01"` with `fill="currentColor"` in the active SVGs.
Replace all `fill="#BABABA"` with `fill="currentColor"` in the inactive SVGs.
Replace all `stroke="#CAFB01"` with `stroke="currentColor"` in active SVGs.
Replace all `stroke="#BABABA"` with `stroke="currentColor"` in inactive SVGs.

Then in `style.scss`, the `.sidebar_menu a` style needs color:
```scss
.sidebar_menu a {
    padding: 8px 40px;
    display: flex;
    flex-direction: row;
    gap: 12px;
    align-items: center;
    justify-content: left;
    color: $text-muted;
    transition: color 0.15s;
}

.sidebar_menu a:hover {
    color: $text;
}
```

And `.active_item` already sets `color: $accent` via the span rule, but add:
```scss
.active_item {
    color: $accent !important;
    /* ...existing styles... */
}
```

Update the footer social links — change URLs:
```js
redirectToTwitter(){
    window.open('https://twitter.com/TrophyRoomGG', '_blank');
},
redirectToDiscord(){
    window.open('https://discord.gg/3sGk8uGQBT', '_blank');
},
redirectToSupport(){
    window.open('https://trophyroom.gg/support', '_blank');
},
```

(If the Twitter handle doesn't exist yet, leave as-is and add a TODO comment.)

**Verify:** `npm run dev` — nav icons should inherit text color, active items chartreuse, inactive items muted gray.

---

### Step 6: Update `main-header.vue`

**File:** `resources/web/js/components/main-header.vue`

Changes:
1. **Remove "Connect Wallet" entirely** from the dropdown menu
2. **Remove Web3 import** — delete `import { Web3 } from 'web3';` and remove from `components`
3. **Remove MetaMask methods** — delete `checkAuth()`, `initMetaMaskConnection()`, and the `window.ethereum` listener in `mounted()`
4. **Remove wallet-related template** — the entire `<li>` block with "Connect Wallet" / "0x... Connected"
5. **Update login button font** — in `<style scoped>`, change `.login-btn` `font-family` from `JetBrains Mono` to `'Share Tech Mono', monospace`

The dropdown should only have:
- Account (link to /profile)
- Log Out

**Verify:** `npm run dev` — dropdown should show only Account + Log Out. No console errors about Web3/ethereum.

---

### Step 7: Final Verification

```bash
npm run dev
```

Check at these breakpoints:
- Desktop (1440px): sidebar visible, 3 currencies in topbar, no Connect Wallet
- Tablet (968px): sidebar hidden, hamburger menu, currencies in second row
- Mobile (375px): everything stacked, no overflow

Check:
- [ ] Font is Share Tech Mono everywhere (no JetBrains Mono remnants)
- [ ] Background is near-black (#000003), not dark gray (#18181B)
- [ ] Sidebar has right border, darker background
- [ ] Active nav item: chartreuse left border + chartreuse text/icon
- [ ] Inactive nav items: muted gray text/icons
- [ ] Header has bottom border, darker background
- [ ] Currency indicators show correctly
- [ ] Dropdown has no "Connect Wallet"
- [ ] Page title shows "TrophyRoom"
- [ ] No console errors

Then: `npm run build` to verify production build works.

---

## 4. Bot API Endpoints (REAL — Updated)

The CLAUDE.md section 8 has outdated bot endpoints. These are the real ones in production:

```
# ── Authenticated with bot_api_key (middleware: bot.api) ──
GET  /api/bot/rules                    ← Active rules for the guild
POST /api/bot/badges/grant             ← Grant badge to user
POST /api/bot/channels/sync            ← Sync guild channels
GET  /api/bot/channels                 ← List cached channels
POST /api/bot/users/link               ← Link Discord ↔ TR user
GET  /api/bot/users/lookup/{id}        ← Lookup TR user by Discord ID
GET  /api/bot/polls/pending            ← Pending polls to publish
POST /api/bot/polls/{id}/published     ← Mark poll as published
POST /api/bot/polls/{id}/close         ← Close poll
GET  /api/bot/events/pending           ← Pending events
POST /api/bot/events/{id}/scheduled    ← Mark event as scheduled
POST /api/bot/events/{id}/complete     ← Complete event

# ── Link Flow (no bot auth) ──
GET  /bot/link?discord_user_id=X&guild_id=Y&discord_username=Z  ← Redirect to frontend
POST /api/bot/link/confirm             ← Frontend confirms link (JWT auth)

# ── Bot Setup OAuth ──
GET  /api/bot/setup/authorize          ← Redirect to Discord to install bot
GET  /api/bot/setup/callback           ← Discord callback post-installation
```

**DB tables (bot-related):** `guild_connections`, `guild_channels`, `badge_rules`, `user_links`, `bot_polls`, `bot_events`, plus `BadgeUser` model.

---

## 5. Phase 7 — Brand Admin Dashboard (New)

### Overview

Dashboard for brands/clients who hire TrophyRoom. Accessible at `/#/brand-dashboard`. For now, any admin user can access it (role check can be refined later).

### Route Setup

Add to `resources/web/js/router/routes.js`:
```js
{
    path: '/brand-dashboard',
    name: 'brand-dashboard',
    component: () => import('../pages/BrandDashboard/BrandDashboard.vue'),
    meta: { requiresAuth: true }
}
```

### Page Structure

Create `resources/web/js/pages/BrandDashboard/` with:

```
BrandDashboard/
├── BrandDashboard.vue        ← Main container with tab navigation
├── DashboardOverview.vue     ← Stats + guild status
├── BadgeManager.vue          ← CRUD badges + rules
├── PollManager.vue           ← Create/manage polls
└── EventManager.vue          ← Create/manage events
```

### Tab 1: Overview (`DashboardOverview.vue`)

Shows:
- Guild connection status (connected guild name, bot status)
- Synced channels count
- Stats cards: linked users, badges granted, completion rates
- Recent activity feed (last 10 badge grants)

API calls:
- `GET /api/bot/channels` — channel count + list
- `GET /api/bot/rules` — active rules count
- Badge stats from existing `/api/badges` endpoint

### Tab 2: Badge Manager (`BadgeManager.vue`)

**Badge CRUD:**
- List existing badges (from `/api/badges` or admin endpoint)
- Create badge: name, image upload, description, type
- Edit/delete badges

**Badge Rules:**
- List rules from `GET /api/bot/rules`
- Create rule form:
  - Trigger type dropdown: `voice_minutes`, `message_count`, `reaction`, `event_join`, `poll_answer`, `role_obtain`
  - Channel selector dropdown (populated from `GET /api/bot/channels`)
  - Threshold input (number)
  - Associated badge (dropdown of existing badges)
  - Active/inactive toggle
- Activate/deactivate rules inline

### Tab 3: Poll Manager (`PollManager.vue`)

- Create poll: title, options (dynamic add/remove), destination channel (dropdown), duration, associated badge
- View pending polls from `GET /api/bot/polls/pending`
- View active/closed polls with results
- Close poll action via `POST /api/bot/polls/{id}/close`

### Tab 4: Event Manager (`EventManager.vue`)

- Create event: title, description, channel (dropdown), start/end dates, associated badge
- View pending events from `GET /api/bot/events/pending`
- View scheduled/completed events
- Mark complete via `POST /api/bot/events/{id}/complete`

### Design

Follow the TrophyRoom design system from Section 2 of this document:
- Dark backgrounds (`--surface`, `--surface-2`)
- Cards with `--border` borders and 6px radius
- Section labels in `--primary` (naranja fuego), uppercase, 11px
- Primary CTAs in `--accent` (chartreuse)
- Forms: inputs with `--surface-2` bg, `--border` border, `--text` color
- Share Tech Mono everywhere
- Mobile-first: single column on mobile, grid on desktop

### Sidebar Addition

Add a nav item in `sidebar.vue` for "Brand Dashboard" — only visible to admin users (check `store.state.user.roles` for admin role). Place it below "Network" and above the footer. Use a grid/dashboard-style SVG icon.

---

## 6. Development Rules (Reinforced)

1. **One change at a time.** Modify → verify with `npm run dev` → commit → next.
2. **Don't break existing functionality.** API calls, Vuex store, routing, auth must keep working.
3. **Vue 3 Options API ONLY.** Do not use Composition API or `<script setup>`.
4. **Bootstrap → Tailwind** where touched. No new Bootstrap usage.
5. **No backend changes.** No Laravel/PHP modifications.
6. **No admin panel changes** (the Blade admin at `/admin`).
7. **Mobile-first.** Test at 375px width. Users come from Discord mobile.
8. **Commit messages:** `feat:`, `fix:`, `refactor:` prefixes. One concern per commit.
9. **When in doubt, stop and ask.** Don't guess product decisions.
10. **Share Tech Mono is the only font.** No secondary font. It only has weight 400.

---

## 7. Execution Order

```
1. Deploy LinkDiscord.vue (server: git pull + npm run build)
2. Phase 1 Step 1: _variables.scss → verify
3. Phase 1 Step 2: _typography.scss + web.blade.php → verify
4. Phase 1 Step 3: tailwind.config.js → verify
5. Phase 1 Step 4: style.scss (body, sidebar, header styles) → verify
6. Phase 1 Step 5: sidebar.vue (SVG colors + links) → verify
7. Phase 1 Step 6: main-header.vue (remove wallet, update font) → verify
8. Phase 1 Step 7: Full verification pass
9. Commit: "feat: Phase 1 — shell & global styles with TrophyRoom identity"
10. Phase 7: Brand Dashboard (after Phase 1 is stable)
```

After Phase 1 is complete and deployed, continue with Phases 2-6 from CLAUDE.md Section 10.

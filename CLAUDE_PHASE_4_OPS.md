# CLAUDE_PHASE_4_OPS.md — Feed & Social Redesign

> **Operational brief for Claude Code.**
> Read `CLAUDE_FRONTEND_OPS.md` Section 2 for design system tokens.
> This covers Phase 4: Feed page + social components redesign.

---

## Scope

Redesign the Feed page and its subcomponents to match the TrophyRoom 2.0 design system. The Feed currently has two tabs (Followers / My feed), post cards with achievement sharing, Ambar donations, and comments. The redesign applies the new palette, typography, and card patterns while keeping all existing functionality intact.

**Files to modify:**
```
resources/web/js/pages/Feed/Feed.vue                          ← Template + scoped styles (keep script)
resources/web/js/pages/Feed/components/feed-card.vue          ← Main post card (460 lines — biggest file)
resources/web/js/pages/Feed/components/feed-comment.vue       ← Comment component
resources/web/js/pages/Feed/components/Followers.vue           ← Followers tab
resources/web/js/pages/Feed/components/My-feed.vue             ← My feed tab
resources/web/js/pages/Feed/components/List.vue                ← List component
```

**DO NOT modify:** Any `<script>` logic, API calls, Vuex interactions, donation flow, comment submission, or follow/unfollow logic. Only templates and `<style>`.

---

## Design Target

The Feed should feel like a modern social feed with dark cards, clean typography, and the TrophyRoom color system. Key changes:

### Feed.vue (wrapper)
- Title "Feed" + subtitle with TrophyRoom design system (same pattern as Forge/TrophyRoom headers)
- Tabs: "Followers" / "My feed" — use the same underline tab style as TrophyRoom page (accent color active, muted inactive, border-bottom)
- Remove any legacy padding/margin issues

### feed-card.vue (post cards)
- **Card:** `background: $surface; border: 1px solid $border; border-radius: 6px; padding: 20px;`
- **Header:** avatar (40px circle) + username (clickable, links to virtual hall) + date (right-aligned, `$text-dim`)
- **Content:** Achievement name in `$text` 16px, description in `$text-muted` 14px
- **Image:** If present, full width inside card, border-radius 4px, max-height 300px object-fit cover
- **Actions row:** 
  - Ambar donation: icon (02.svg) + count + "Donate" text in `$primary` — replaces "Add Ambars"
  - Delete button (trash icon) — only for own posts, `$text-dim` color
- **Comments section:** Clean rows below the card content
- Remove hexagon borders from any badge/achievement images in posts

### feed-comment.vue
- Avatar (32px circle) + username + comment text
- Input: `$surface-2` bg, `$border` border, `$text` color, submit arrow in `$primary`

### Followers.vue / My-feed.vue / List.vue
- Apply design tokens to any cards, text, borders
- Follower cards: avatar + username + follow/unfollow button with accent/secondary styles

---

## Execution Approach

**IMPORTANT: Claude Code should read each file's `<script>` first to understand data bindings and methods, then rewrite only `<template>` and `<style scoped>`, preserving all `v-model`, `@click`, `v-if`, `v-for`, `:class`, and event handlers exactly as they are.**

### Step-by-step:
1. Start with `Feed.vue` — apply header + tab styles (same as other pages)
2. Then `feed-card.vue` — the biggest file, restyle the card layout
3. Then `feed-comment.vue` — small, apply input and comment row styles
4. Then `Followers.vue`, `My-feed.vue`, `List.vue` — apply design tokens
5. Verify with `npm run dev` after each file
6. Single commit: `feat: Phase 4 — Feed & Social redesign`
7. Deploy: `git push && ssh reset --hard && npm run build`

### Design tokens reference:
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

### What to remove/rename:
- "Add Ambars" → "Donate" or "Donate Ambar" with the Ambar icon (02.svg)
- Any hexagon wrappers on images
- Any hardcoded colors (#CAFB01 → use $accent, #212124 → use $surface, #BABABA → use $text-muted)
- Any JetBrains Mono or Orbitron font references

### What to keep:
- Donation flow (click → modal or inline input → API call)
- Comment submission
- Delete post functionality
- Follow/unfollow from feed
- Tab switching
- All v-for loops and data bindings

---

## Rules
1. One file at a time. Verify after each.
2. Don't touch `<script>` blocks.
3. Vue 3 Options API.
4. Share Tech Mono only.
5. Mobile-first.

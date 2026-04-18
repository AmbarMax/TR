# CLAUDE_PHASE_4A_OPS.md — Feed Restyling + Wall of Fame

> **Operational brief for Claude Code.**
> Read `CLAUDE_FRONTEND_OPS.md` Section 2 for design system tokens.
> Phase 4A: Visual redesign of Feed page + Wall of Fame sidebar component.
> Phase 4B (separate brief) will handle the new composer, Validate absorption, and backend changes.

---

## Scope

Restyle the Feed page and all subcomponents to match TrophyRoom 2.0 design system. Add a Wall of Fame sidebar component (hardcoded data for now). Rename tabs. No script changes. No backend changes.

**Files to modify:**
```
resources/web/js/pages/Feed/Feed.vue                          ← Template + scoped styles
resources/web/js/pages/Feed/components/feed-card.vue          ← Post card (460 lines — biggest)
resources/web/js/pages/Feed/components/feed-comment.vue       ← Comment row + input
resources/web/js/pages/Feed/components/Followers.vue          ← "Following" tab wrapper
resources/web/js/pages/Feed/components/My-feed.vue            ← "My posts" tab wrapper
resources/web/js/pages/Feed/components/List.vue               ← List component (minor)
```

**New file to create:**
```
resources/web/js/pages/Feed/components/WallOfFame.vue         ← Wall of Fame sidebar
```

**DO NOT modify:** Any `<script>` blocks, API calls, Vuex/store interactions, donation flow, comment submission, delete post, follow/unfollow logic, or pagination. Template changes must preserve ALL existing `v-model`, `@click`, `v-if`, `v-for`, `:class`, refs, and event handlers exactly as they are.

---

## Design Tokens Reference

```scss
$bg: #000003;
$surface: #0e0f11;
$surface-2: #1a1c1f;
$surface-3: #252729;
$primary: #ff6100;    // Orange — brand, labels, CTAs, donate
$accent: #c1f527;     // Chartreuse — active nav, progress
$text: #feeddf;       // Warm cream
$text-muted: #9a9590;
$text-dim: #5a5550;
$border: #2a2c2e;
$mono: 'Share Tech Mono', monospace;  // ONLY font, weight 400 only
```

Patterns:
- Cards: `background: $surface; border: 1px solid $border; border-radius: 6px;`
- Section labels: `font-size: 11px; text-transform: uppercase; color: $primary; letter-spacing: 0.12em;`
- Inputs: `background: $surface-2; border: 1px solid $border; color: $text; border-radius: 4px;`

---

## Step-by-step Execution

### Step 1 — Feed.vue (wrapper)

Rewrite `<template>` and `<style scoped>`. Keep `<script>` untouched.

**Template changes:**
- Wrap in `<div class="feed-page">` instead of `<div class="main_block">`
- Title section: `<h1 class="feed-title">Feed</h1>` + `<p class="feed-subtitle">Share your feats, clips, and forged trophies with the community</p>`
- **Rename tabs:** Change `Followers` → `Following` and `My feed` → `My posts` in the template text only. Keep `activeTab === 1` and `activeTab === 2` logic identical.
- Tabs: use underline style with accent active color
- Add 2-column layout wrapper: feed content on the left, `<WallOfFame />` on the right
- Import WallOfFame component (this is the ONLY script addition allowed — adding the import and registering the component)

**Template structure:**
```html
<div class="feed-page">
  <div class="feed-header">
    <h1 class="feed-title">Feed</h1>
    <p class="feed-subtitle">Share your feats, clips, and forged trophies with the community</p>
  </div>
  <div class="feed-tabs">
    <div :class="{ active: activeTab === 1 }" class="feed-tab" @click="changeTab(1)">Following</div>
    <div :class="{ active: activeTab === 2 }" class="feed-tab" @click="changeTab(2)">My posts</div>
  </div>
  <div class="feed-layout">
    <div class="feed-content">
      <div v-if="activeTab === 1"><Followers /></div>
      <div v-if="activeTab === 2"><MyFeed /></div>
    </div>
    <div class="feed-sidebar">
      <WallOfFame />
    </div>
  </div>
</div>
```

**Scoped styles:**
```css
.feed-page {
  padding: 0;
}
.feed-header {
  margin-bottom: 20px;
}
.feed-title {
  font-family: 'Share Tech Mono', monospace;
  font-size: 22px;
  font-weight: 400;
  color: #feeddf;
  margin: 0 0 4px 0;
}
.feed-subtitle {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  color: #9a9590;
  line-height: 1.5;
  margin: 0;
}
.feed-tabs {
  display: flex;
  border-bottom: 1px solid #2a2c2e;
  margin-bottom: 20px;
  gap: 0;
}
.feed-tab {
  padding: 10px 16px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  color: #9a9590;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: color 0.2s, border-color 0.2s;
}
.feed-tab:hover {
  color: #feeddf;
}
.feed-tab.active {
  color: #c1f527;
  border-bottom-color: #c1f527;
}
.feed-layout {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}
.feed-content {
  flex: 1;
  min-width: 0;
}
.feed-sidebar {
  width: 220px;
  flex-shrink: 0;
}

@media (max-width: 968px) {
  .feed-layout {
    flex-direction: column;
  }
  .feed-sidebar {
    width: 100%;
    order: -1;
  }
}
```

**Script addition (ONLY change to script):**
Add import and register WallOfFame:
```js
import WallOfFame from "./components/WallOfFame.vue";
```
Add `WallOfFame` to the `components: {}` object.

**Verify:** `npm run dev` — page loads, tabs switch, layout is 2-column on desktop, stacked on mobile.

---

### Step 2 — WallOfFame.vue (NEW component)

Create `resources/web/js/pages/Feed/components/WallOfFame.vue`.

This is a **frontend-only component with hardcoded data** for now. No API calls. No store interaction. A backend endpoint will be added in a future phase.

```vue
<template>
  <div class="wof">
    <div class="wof-label">Wall of fame</div>
    <div class="wof-sub">Weekly top achievers</div>

    <div class="wof-entry" v-for="(user, idx) in topUsers" :key="idx">
      <div class="wof-rank" :class="{ gold: idx === 0 }">{{ idx + 1 }}</div>
      <div class="wof-avatar">{{ user.initials }}</div>
      <div class="wof-info">
        <div class="wof-name">{{ user.username }}</div>
        <div class="wof-stats">
          <span class="wof-trophies">{{ user.trophies }} troph.</span>
          <span class="wof-xp">{{ user.xp }} XP</span>
        </div>
      </div>
    </div>

    <div class="wof-divider"></div>
    <div class="wof-footer">Resets every Monday</div>
  </div>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
  data() {
    return {
      topUsers: [
        { initials: "KZ", username: "KazuXtreme", trophies: 4, xp: 890 },
        { initials: "NV", username: "NovaPlays", trophies: 3, xp: 720 },
        { initials: "RL", username: "RuneLord", trophies: 3, xp: 650 },
        { initials: "MR", username: "MikeRush", trophies: 2, xp: 480 },
        { initials: "AX", username: "AxelForge", trophies: 2, xp: 410 },
      ],
    };
  },
});
</script>

<style scoped>
.wof {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 14px;
  position: sticky;
  top: 20px;
}
.wof-label {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  color: #ff6100;
  letter-spacing: 0.12em;
  margin-bottom: 4px;
}
.wof-sub {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
  margin-bottom: 14px;
}
.wof-entry {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}
.wof-rank {
  font-family: 'Share Tech Mono', monospace;
  font-size: 18px;
  color: #2a2c2e;
  width: 20px;
  text-align: center;
}
.wof-rank.gold {
  color: #ff6100;
}
.wof-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px;
  color: #9a9590;
  flex-shrink: 0;
}
.wof-info {
  flex: 1;
  min-width: 0;
}
.wof-name {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: #feeddf;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.wof-stats {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
  display: flex;
  gap: 6px;
}
.wof-trophies {
  color: #ff6100;
}
.wof-xp {
  color: #c1f527;
}
.wof-divider {
  height: 1px;
  background: #2a2c2e;
  margin: 12px 0;
}
.wof-footer {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
  text-align: center;
}
</style>
```

**Verify:** `npm run dev` — Wall of Fame appears in sidebar. Sticky on scroll. Responsive stacking on mobile.

---

### Step 3 — feed-card.vue (post card restyling)

This is the biggest file (460 lines). **Read the entire `<script>` first** to understand all data bindings, computed properties, methods, refs, and event handlers. Then rewrite ONLY `<template>` and `<style scoped>`.

**CRITICAL:** Preserve every single:
- `v-if="actionStatuses.default"`, `v-if="actionStatuses.send"`, `v-if="actionStatuses.sendSuccess"`
- `@click="addAmbars"`, `@click="sendAmbars(item.id, data.balance)"`, `@click="increaseDecreaseAmbars('+')"`, `@click="increaseDecreaseAmbars('-')"`, `@click="openDeletePostModal(item.id)"`
- `@click="navigateToVirtualHall(item.creator.username)"`
- `v-model="newMessage"`, `@input="changeHeight"`, `ref="textarea"`, `@keydown="event => handleEnterMessage(event, item.id)"`
- `@click="sendMessage(item.id)"`, `@click="readMoreToggle"`, `@click="showMoreComments(item.id)"`
- `ref="cardText"`, `ref="arrowDown"`, `:ref="'scrollable_'+index"`, `@scroll="handleScroll"`
- `:id="item.id+'feed-dom-obj'"`, `:class="{'scroll-height': item.comments_count > 3}"`
- `v-for="comment of item.comments"` on `<feed-comment>`
- `checkShowMoreComments(item)`, `checkTotalCommentsText(item)` conditional rendering
- `getImageUrl()` for images, `getAvatar()` for comment input avatar
- All `:src` bindings for creator avatars (both `v-if="!item.creator.avatar"` and `v-else`)

**Template structure target:**
```
div.feed-card
  div.fc-header
    div.fc-user (avatar + username, clickable → virtual hall)
    span.fc-date (right-aligned)
    button.fc-delete (trash, v-if myFeed || isModerator)
  h2.fc-title (item.entity.name)
  p.fc-text (item.entity.description, with read more toggle)
  div.fc-image (getImageUrl(), no hexagon wrapper)
  div.fc-actions
    [default state] fc-balance row: ambar icon + donations count + "Donate" button (or people count if myFeed)
    [send state] fc-send row: ambar icon + minus/plus buttons + count + Send button
    [success state] fc-success row: ambar icon + count + "You donated" message
  div.fc-comments
    feed-comment loop
    show more comments button
    new comment input (avatar + textarea + send)
```

**Style targets:**
- Card: `background: #0e0f11; border: 1px solid #2a2c2e; border-radius: 6px; padding: 16px; margin-bottom: 16px;`
- Header: avatar 36px circle, username in `#feeddf` 13px, date in `#5a5550` 11px right-aligned
- Avatar: `background: #1a1c1f; border: 1px solid #2a2c2e;` — if has image, show image; if not, show default svg
- Title: `color: #feeddf; font-size: 15px;`
- Description text: `color: #9a9590; font-size: 12px; line-height: 1.5;`
- "Read more" toggle: `color: #5a5550; font-size: 11px;`
- Image: `width: 100%; border-radius: 4px; max-height: 400px; object-fit: cover;` — NO hexagon wrapper, no fixed 560px width
- Donate button text: change "Add Ambars" → "Donate" with `color: #ff6100;`
- Ambar icon: keep existing `ambar.svg` reference
- +/- operator buttons: `color: #c1f527;`
- Send button (buttonWhite): keep component, it will be restyled globally later
- Delete button: `color: #5a5550;` on hover `color: #ff6100;`
- Comments section border: `border-top: 1px solid #1a1c1f;`
- New comment textarea: `background: #1a1c1f; border: 1px solid #2a2c2e; color: #feeddf; font-size: 13px;`
- Send icon in comment: `color: #ff6100;` — keep existing svg reference
- Scrollbar (`.scroll-height`): thumb `#ff6100` instead of `#CAFB01`

**What to remove:**
- Any hardcoded `#CAFB01` → use `#c1f527` for accent or `#ff6100` for primary
- Any hardcoded `#212124`, `#BABABA`, `#1E1E1E`
- Fixed `width: 560px` on images → use `width: 100%`
- Any hexagon wrappers on images

**Verify:** `npm run dev` — cards display correctly, donations work (all 3 states), comments load, show more works, delete works, read more toggle works, images display.

---

### Step 4 — feed-comment.vue

Rewrite `<template>` and `<style scoped>`. Keep `<script>` untouched.

**Preserve:** All `v-if` for avatar, `@click="navigateToVirtualHall"`, `@click="openDeleteCommentModal"`, `v-if="this.isModerator()"`, all `:src` bindings.

**Style targets:**
- Comment row: `display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #1a1c1f;`
- Avatar: `width: 28px; height: 28px; border-radius: 50%; cursor: pointer;`
- Username: `color: #feeddf; font-size: 12px; cursor: pointer;`
- Comment body: `color: #9a9590; font-size: 12px; line-height: 1.4;`
- Date: `color: #5a5550; font-size: 10px;`
- Delete button (moderator): `color: #5a5550;` on hover `color: #ff6100;`

**Verify:** `npm run dev` — comments display, moderator delete button visible for Master users, clicking username opens virtual hall.

---

### Step 5 — Followers.vue and My-feed.vue

These are thin wrappers. Rewrite ONLY `<style scoped>` for both. Keep `<template>` and `<script>` untouched.

**Followers.vue styles:**
```css
.feed-list {
  display: flex;
  flex-direction: column;
  gap: 0;
}
.item-wrapper {
  width: 100%;
}
@media (max-width: 642px) {
  .item-wrapper {
    width: 100%;
  }
}
```

**My-feed.vue styles:** Same as Followers.vue.

**Verify:** `npm run dev` — both tabs show cards, infinite scroll works.

---

### Step 6 — List.vue

Rewrite `<template>` and `<style scoped>`. Keep `<script>` untouched.

This component appears to be a legacy follower list item. Apply design tokens:
- Background: `#0e0f11`
- Border: `1px solid #2a2c2e` instead of `1px solid white`
- Text colors: `#feeddf` for username, `#9a9590` for secondary text
- Remove any hardcoded `rgba(186, 186, 186, 0.15)` backgrounds

**Verify:** `npm run dev` — list renders if used anywhere.

---

### Step 7 — Final verification and commit

1. Check all pages work: Feed → Following tab, My posts tab
2. Check Wall of Fame appears on desktop, stacks on mobile
3. Check donations flow works (all 3 states)
4. Check comments: load, show more, submit, delete (moderator)
5. Check images display without hexagons
6. Check infinite scroll on both tabs
7. Check mobile responsiveness at 375px

**Commit:**
```bash
git add -A
git commit -m "feat: Phase 4A — Feed restyling + Wall of Fame sidebar"
```

**Deploy:**
```bash
git push origin main
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build"
```

---

## Rules Reminder
1. One file at a time. Verify `npm run dev` after each.
2. Don't touch `<script>` blocks (except Feed.vue WallOfFame import).
3. Vue 3 Options API.
4. Share Tech Mono only, weight 400.
5. Mobile-first (375px).
6. No hexagons on any images.
7. Deploy with `git reset --hard`, never `git pull`.

# CLAUDE_PHASE_7B_OPS.md — Trophy Management for Brand Dashboard

> **Operational brief for Claude Code.**
> Phase 7B adds Trophy CRUD to the Brand Dashboard so brand clients can create and manage trophies.
> Read CLAUDE.md, CLAUDE_FRONTEND_OPS.md, and CLAUDE_BACKEND_OPS.md first for full context.

---

## 0. Context

The Brand Dashboard (`/#/brand-dashboard`) already has 4 tabs working:
- Overview (stats)
- Badges (badge rules for Discord bot)
- Polls (Discord polls)
- Events (Discord events)

Backend endpoints exist under `/api/brand/*` with JWT + `brand.admin` middleware.

**Phase 7B adds a 5th tab: Trophies.**

Trophies are the core B2B product of TrophyRoom. Brands create trophies with requirements (specific badges + Ambar currency cost). Gamers who meet all requirements can "forge" (claim) the trophy and earn Uru as reward. This is currently done in the old admin panel (`/admin/`) via Blade + vanilla JS.

---

## 1. Step 0 — Examine Existing Code (DO THIS FIRST)

Before writing any code, examine these files to understand the existing Trophy model, database schema, and admin CRUD patterns:

```
# Trophy model
app/Models/Trophy.php (or similar name — might be Nft.php or Achievement.php)

# Admin controller for trophies
app/Http/Controllers/Admin/TrophyController.php (or similar)

# Admin blade view
resources/views/admin/trophies/index.blade.php

# Admin JS for trophies
resources/admin/js/pages/trophies.js

# Migration files
database/migrations/*trophy* or *nft*

# Assignment of trophies (admin)
resources/admin/js/pages/assignment-of-trophies.js
app/Http/Controllers/Admin/AssignmentOfTrophiesController.php (or similar)
```

**What to look for:**
1. **Table name and columns** — what fields exist (title, image, description, type, price, reward, availability, weight, etc.)
2. **Relationships** — how trophies relate to badges (requirements). Is it a pivot table? JSON column? Separate table?
3. **Image storage** — where trophy images are stored (storage path, disk)
4. **Type field** — the old system has "NFT Trophy" and "NFT Key" types. What are the actual values in the DB?
5. **Forge/claim logic** — how does a user claim a trophy? What controller handles it? How are balances deducted?
6. **Existing API endpoints** — check `routes/api.php` for any existing trophy endpoints the Vue frontend already uses

**Output a summary of what you found before proceeding.**

---

## 2. Backend Endpoints to Create

All endpoints under `/api/brand/trophies` prefix, using the existing `brand.admin` middleware group.

### 2.1 List Trophies

**GET /api/brand/trophies**
- Returns trophies belonging to the authenticated brand/user
- Include: badge requirements, image URL, availability count, type, pricing
- Response: JSON array of trophies with their relationships

### 2.2 Create Trophy

**POST /api/brand/trophies**
- Body: `{ title, description, image (file), type, price (Ambar cost), reward (Uru reward), weight (XP value), availability (max claims), badge_ids (array of required badge IDs) }`
- `type` should accept: `trophy`, `key` (replacing legacy `NFT Trophy` / `NFT Key`)
- Image upload: follow the same storage pattern as existing trophy creation in admin
- Badge requirements: attach the badge IDs as requirements (same mechanism admin uses)
- Response: created trophy object with relationships

### 2.3 Update Trophy

**PUT /api/brand/trophies/{id}**
- Body: same fields as create (all optional)
- Can update title, description, pricing, requirements, availability
- Image upload: only if new file provided
- Response: updated trophy object

### 2.4 Delete Trophy

**DELETE /api/brand/trophies/{id}**
- Soft delete or hard delete (match existing admin pattern)
- Only allow if trophy belongs to the authenticated user's org
- Response: `{ success: true }`

### 2.5 Trophy Stats

**GET /api/brand/trophies/{id}/stats**
- Returns: total forged count, remaining availability, list of recent claimers
- Optional — can be included in the main GET response instead

---

## 3. Frontend — New Tab

### 3.1 Add Tab to BrandDashboard.vue

Add "Trophies" as the 5th tab (between Overview and Badges makes sense, since trophies are the main product).

New tab order: Overview | **Trophies** | Badges | Polls | Events

### 3.2 Create TrophyManager.vue

File: `resources/web/js/pages/BrandDashboard/TrophyManager.vue`

**Layout — two sections:**

**Section A: Trophy List**
- Grid or list of existing trophies
- Each trophy card shows: image (thumbnail), title, type badge, price/reward, requirements count, availability (claimed/total), status
- Actions per trophy: Edit, Delete
- "Create Trophy" button at top

**Section B: Trophy Form (create/edit)**
- Can be a modal or inline form panel
- Fields:
  - Title (text input)
  - Description (textarea)
  - Image (file upload with preview)
  - Type (dropdown: Trophy / Key)
  - Price in Ambar (number input)
  - Reward in Uru (number input)  
  - Weight / XP (number input)
  - Availability (number input — max times it can be forged)
  - Required Badges (multi-select from existing badges, show as chips)

**Required Badges selector:**
- Load badges from `GET /api/brand/badges`
- Show as searchable multi-select or checkbox list
- Selected badges shown as removable chips below the selector
- Each chip shows badge image (tiny) + name

### 3.3 API Module

Use the same `botApi.js` pattern (bare axios without logout interceptor) since we're calling `/api/brand/*` endpoints.

Actually — check if the `/api/brand/trophies` endpoints use JWT auth properly (they should, since they're in the `brand.admin` middleware group). If they do, the regular `api.js` module MIGHT work. But to be safe, use `botApi.js` or create a dedicated `brandApi.js`.

---

## 4. Design

Follow TrophyRoom design system:
- Cards: `--surface` bg, `--border` border, 6px radius
- Trophy cards should have a subtle `--primary` (naranja fuego) left border to distinguish them from regular items
- Section label: "TROPHIES" in `--primary`, uppercase, 11px
- CTAs: chartreuse for "Create Trophy", secondary style for Edit
- Type badge: small pill showing "TROPHY" or "KEY"
- Price/reward: show as "🔥 500 Ambar → 💜 200 Uru" style display (use currency icons from the topbar)
- Requirements: show required badges as tiny circular thumbnails (32px) with count
- Availability: progress bar or "3/10 claimed" text

---

## 5. Language Rules

- NO "NFT" anywhere — replace with "Trophy" or "Key"
- NO "mint" — use "forge" or "claim"
- "Price" = cost in Ambar to forge
- "Reward" = Uru earned when forging
- "Weight" = XP value of the trophy
- "Requirements" = badges needed to be eligible
- "Availability" = max number of times it can be forged (limited supply)

---

## 6. Execution Order

```
1. Examine existing Trophy model, admin controller, migrations, and blade views
2. Output a summary of the schema and relationships found
3. Create BrandTrophiesController.php with CRUD endpoints
4. Add routes to routes/api.php in the brand group
5. Test endpoints on server (php artisan route:list --path=brand/trophies)
6. Create TrophyManager.vue component
7. Add Trophies tab to BrandDashboard.vue
8. Wire up API calls in TrophyManager.vue
9. Test full flow: create, list, edit, delete
10. Commit, push, deploy
```

---

## 7. Important Notes

- The trophy model name might not be `Trophy`. It could be `Nft`, `NftTrophy`, or something else from the legacy codebase. **Examine before assuming.**
- Badge requirements might use a pivot table (`trophy_badges`, `nft_requirements`, etc.) or a JSON column. **Examine before assuming.**
- The admin uses a completely different auth system (`Auth::guard('admin')`). The Brand Dashboard uses JWT + `'Master user'` role. Keep these separate.
- Don't break the existing Forge.vue frontend or the admin panel. The new endpoints are additive.
- Trophy images are probably stored in `storage/app/public/` somewhere. Follow the same pattern.

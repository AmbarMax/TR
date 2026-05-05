# TrophyRoom — CLAUDE.md

Persistent context for Claude Code autonomous sessions on the TrophyRoom repo. Read this first, before touching any file.

**Last updated:** 2026-05-05

---

## What is TrophyRoom

A gaming achievement aggregator and gamification platform. Users connect their accounts across gaming platforms (Steam, Riot/LoL, Discord, Overwolf for Valorant) and non-gaming services (Strava), achievements sync automatically, and everything is showcased in a unified profile (the "Virtual Hall"). The platform also serves brands and game studios who run trophy campaigns targeting gamers.

A B2B gamification-as-a-service angle is on the radar as a potential expansion.

**Business entity:** Ambar Labs Inc. (C-Corp, Delaware)
**Live URL:** https://app.trophyroom.gg
**Repo:** git@github.com:AmbarMax/TR.git
**Founder/CTO:** Máximo Machado (max@ambar.gg)

---

## Tech stack

- **Backend:** Laravel 10, PHP 8.2
- **Frontend:** Vue 3 (Options API only — no Composition API, no `<script setup>`), Vuex, Vite 4
- **Styling:** Tailwind CSS + Bootstrap 4 legacy (do not add new Bootstrap; new code uses Tailwind or scoped CSS)
- **Database:** MySQL (db: `ambar`, user: `deployer`, password in server `.env`)
- **Server:** Ubuntu 22.04, Apache, SSL via Let's Encrypt
- **Real-time:** Centrifugo (on droplet)
- **Auth:** JWT (tymon/jwt-auth) + Laravel Sanctum + optional 2FA
- **Node:** v16 on server

### Design system — "Cinematic Ritual"

- Background: `#000003`
- Primary orange: `#ff6100`
- Accent chartreuse: `#c1f527`
- Fonts: `Share Tech Mono` (primary) + `VT323` (display accents)
- Typography: weight 400 only on Share Tech Mono
- Aesthetic: dark, CRT-inspired with scanlines/vignette/flicker overlays as `position: fixed` decorative layers

---

## Server access

- **IP:** `164.92.83.95` (hostname: `ambar-prod`)
- **Project path:** `/var/www/ambar` (NOT `/var/www/throphyroom` — that's the legacy directory, do NOT touch it)
- **SSH:** `ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95`
- **Backups:** `/root/web3-backup-20260415.tar.gz` (legacy Web3 code)
- **Local repo path on Mac:** `~/Documents/trophyroom`

---

## Deploy workflow — CANONICAL

```bash
# Local: edit, commit, push
cd ~/Documents/trophyroom
git add <files>
git commit -m "..."
git push origin main

# Server: NEVER git pull. Always reset --hard.
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 \
  "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build 2>&1 | tail -10"
```

**Why `git reset --hard origin/main`?** `git pull` fails intermittently on this server. `reset --hard` is deterministic.

**`public/build/` is gitignored.** Build always happens on the server. Do not try to commit `public/build/`.

**Verification step.** After every deploy, grep the server for a unique string from the change to confirm the code arrived. If grep returns 0, the deploy did not apply.

```bash
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 \
  "grep -c 'UNIQUE_STRING' /var/www/ambar/path/to/file"
```

---

## Architecture — Integration pattern

All platform integrations follow the **Adapter pattern**:

```
app/Http/Apis/Integrations/
├── ApiIntegrationInterface.php    ← Contract: getBadges(), syncBadges(), setAuthData()
├── Steam/
│   ├── SteamApi.php
│   └── SteamAdapter.php
├── Discord/
├── Riot/
├── Strava/
└── Overwolf/                      (Valorant via overlay)
```

**Orchestrator:** `app/Services/Api/BadgeService.php`
- `setApiIntegration()` switches on adapter type
- `syncBadges()` calls `getBadges()` and runs `synchronize()` to upsert into DB
- `synchronize()` matches by name, creates new badges via `FileService` for images

**Active integrations:** Steam, Riot/LoL, Overwolf (Valorant), Discord, Strava, Google OAuth.

**To add a new integration:**
1. Create `{Platform}Api.php` and `{Platform}Adapter.php` implementing the interface
2. Add const to `IntegrationType.php`
3. Add case to `BadgeService::setApiIntegration()`
4. Add to `config/integrations.php`
5. Add controller + routes in `routes/api.php`
6. Run/update `IntegrationSeeder`

---

## Onboarding architecture (live in production)

**Database fields on `users`:**
- `onboarding_steps` (JSON) — keys: `welcome_seen`, `platform_connected`, `sync_seen`, `hall_personalized`, plus a final claim step
- `onboarding_completed` (boolean)
- `onboarding_skipped_at` (timestamp, nullable)

**Welcome Trophy:** seeded with fixed UUID `00000000-0000-4000-8000-000000000001`. Idempotent claim via `OnboardingController::claimWelcomeTrophy` (route `POST /api/onboarding/claim-welcome-trophy`).

**Preset assets on server:** 6 avatars (512×512) + 6 banners (1920×400) at `storage/app/public/onboarding/`.

**Flow:**
1. New signup → `OnboardingWizard.vue` auto-opens (4 steps: Welcome → Connect → Sync result → Personalize avatar/banner)
2. After wizard, redirect to user's Virtual Hall with `?onboarding=highlights`
3. `PlayerHallView.vue` runs Driver.js highlight tour (3 steps: identity → stats grid → trophies section)
4. Last step opens `WelcomeTrophyClaim.vue` modal → user claims first trophy → redirect to `/trophy-room`

**Three verbs:** CONNECT → PERSONALIZE → PARTICIPATE.

**Returning incomplete users** see card-based prompts (not the full wizard). Legacy users (created before onboarding flag) see nothing.

**Reset onboarding for testing (Bastian as the canonical test account):**
```bash
mysql -u deployer -p'<see .env>' ambar -e "UPDATE users SET onboarding_steps = '{\"welcome_seen\": \"2026-05-04T18:00:00+00:00\", \"platform_connected\": \"2026-05-04T18:00:00+00:00\", \"sync_seen\": \"2026-05-04T18:00:00+00:00\", \"hall_personalized\": \"2026-05-04T18:00:00+00:00\"}', onboarding_completed = 0 WHERE username = 'Bastian';"
```

**Tinker bypass (skip wizard, jump to highlights):**
```php
$user->onboarding_steps = ['platform_connected' => now()->toIso8601String()];
```

---

## Driver.js — known gotcha

The popover that driver.js v1 injects must keep `position: fixed`. Any CSS rule in the app that matches `.driver-popover` and changes its `position` (to `relative` or `static`) silently breaks the highlight tour — the popover flows in the document flow instead of anchoring to the viewport.

**If applying custom styles to the popover** (e.g. cinematic corner brackets via `::before`/`::after`), do NOT touch `position`, or use `position: fixed !important` to defend against future regressions.

The fix is in `resources/web/js/pages/Hall/PlayerHallView.vue` line ~803 with an explicit comment. Don't remove it.

---

## Economy system

- **Ambar (XP):** social currency. Earned by importing badges, connecting platforms, receiving follows/donations. Spent on forging trophies and donating to posts.
- **Uru:** reward currency. Earned only by forging trophies. Spent in the Exchange.
- **Rune:** legacy/hidden. Still in DB but not shown in UI. Do not eliminate from schema.

Currencies are NOT shown in the header. Bell icon redirects to dashboard. Notification API: endpoint `/api/notification` (singular), field is `description` (not `message`), data lives at `response.data[0].data`.

---

## Working methodology

This project uses a **dual-Claude workflow**:

1. **Supervisor/architect** (Claude in claude.ai web): designs mockups, writes operational briefs, audits Claude Code's outputs, integrates corrections from a parallel AI Máximo also consults.
2. **Executor** (Claude Code CLI with `--dangerously-skip-permissions`): reads this file, executes briefs step by step, verifies with `npm run dev` or grep, commits and pushes.

**For visual decisions:** standalone HTML mockups are produced first, approved, then implemented in Vue. Reference for the current visual direction: `onboarding-mockup.html` (Cinematic Ritual, ~1.9MB with base64 assets).

**Sequential execution.** One change at a time. Verify before moving on. Commit per concern.

---

## Hard rules — non-negotiable

### Code
1. **Vue 3 Options API only.** No Composition API, no `<script setup>`.
2. **Share Tech Mono** is the primary font; `VT323` for display accents only.
3. **One change at a time.** Modify → verify → commit → next.
4. **Do not break existing functionality.** API calls, Vuex, routing, auth must keep working.
5. **No new Bootstrap.** Migrate touched components to Tailwind or scoped CSS.
6. **Do not modify `auth` system** unless explicitly tasked.
7. **Do not modify the integration Adapter pattern** — extend it.
8. **Do not modify legacy old card components** (`achievement-card.vue`, `forge-card.vue`, `validate-card.vue`) — they're being replaced page by page.
9. **Mobile-first.** Test at 375px. Users come from Discord mobile.
10. **Commit messages:** `feat:`, `fix:`, `refactor:` prefixes. One concern per commit.

### Deploy
11. **Always `git reset --hard origin/main`** on server. Never `git pull`.
12. **Verify with `grep`** that files arrived on server before browser testing.
13. **Project path is `/var/www/ambar`.** Never `/var/www/throphyroom` (legacy).
14. **`public/build/` is gitignored.** Build runs on server.

### Database
15. **Never delete from production DB** without explicit confirmation.
16. **Never run destructive migrations in production** without backup confirmation.

### Files / secrets
17. **Never commit `.env`.**
18. **Do not touch `app/Web3/`** — already deleted, backup at `/root/web3-backup-20260415.tar.gz`.

### Design
19. **Mockup first** for new pages or major redesigns. Approved before any Vue work.
20. **Design tokens are definitive.** No palette/font changes without discussion.

---

## Current state (as of 2026-05-05)

**Onboarding — closed.** Wizard, highlight tour, Welcome Trophy claim all working. Last bug fixed in commit `351fbea` (driver.js popover `position: relative` regression in `PlayerHallView.vue`).

**Last stable commit on main:** `351fbea`.

---

## On the horizon (priority order)

1. **Vue component refactor to fully match the approved Cinematic Ritual mockup.** PlayerHallView and surrounding components.
2. **Mascot PNG processing.** 5 T-Rex mascots (`trex_welcoming`, `trex_pointing`, `trex_celebrating`, `trex_thinking`, `trex_victory`) currently lack alpha channel — JPEG renamed to `.png`. Need background removal + true transparency. Note: `trex_welcoming` is classic CRT pixel art; the other four are 3D-textured pixel art. Style consistency decision deferred.
3. **`DiscordSFTPService` connecting to dead host** (`v-buf-04.sparkedhost.us`). Fills logs every 30 minutes. Needs replacement or disable.
4. **Remove/redirect legacy Web3 Vue pages** (Forge, Exchange, MyChests).
5. **Set up Supervisor for queue workers** on the droplet. Currently `steam-sync` runs on `sync` connection as temporary measure.
6. **Rotate Steam API key** once account recovery completes; update `STEAM_SECRET` in `.env` and run `php artisan config:cache`.
7. **Valorant integration via Overwolf overlay** (deferred; RSO requirements make direct Riot API path impractical).

---

## Pending technical debt

### From Brief 9N-D era
1. **`trophies.is_active` + `starts_at` / `ends_at` missing.** Campaign lifecycle real does not exist in schema. Brand Halls show "active" trophies using `deleted_at IS NULL` as proxy. Blocker for scheduled campaigns / time-limited drops.
2. **`chests.user_id` missing.** No FK to brand owner, only `key_id` → `keys`. Cannot filter chests by brand Hall. Endpoint `GET /api/users/{username}/active-items` returns only trophies until this FK is added (with backfill).
3. **Two parallel follow concepts.** `followers` (legacy user-user social graph) coexists with `hall_followers` (subscribe to a Hall). Brand stats read from `hall_followers`. Player Hall display decision pending: show `followers_count`, `hall_followers_count`, or both?

### From Brief 9N-C cleanup
4. **Endpoint `/api/users/{username}/follow-status` missing.** `BrandHallView.vue` always starts with `isFollowing = false`. Either create endpoint or add flag to `/api/users/{username}` when JWT is present.
5. **`HallController::activeItems` select too narrow.** Returns only `id, name, image, description, type, created_at`. UI expects `receive` (XP) and pursuing/conquerors count. Cards render "+0 XP" / "0 pursuing" for everything.
6. **`PlayerHallView.vue` depends on legacy endpoint `/api/virtual-hall/{username}`.** New `HallResource` (`/api/users/{username}`) does not expose rich collections (`badges`, `trophies`, `achievements`). Blocks deletion of `VirtualHallController.php`. Decision: extend `HallResource` with `whenLoaded()` collections, or create dedicated `/api/users/{username}/profile-data`.
7. **Busy-loops syncing localStorage in 6 files.** Antipattern. Replace with `computed` + fallback to `localStorage` without `while`. Files:
   - `resources/web/js/components/main-header.vue:127`
   - `resources/web/js/pages/Notifications.vue:102`
   - `resources/web/js/pages/Network/Followers.vue:71`
   - `resources/web/js/pages/Network/Followings.vue:69`
   - `resources/web/js/pages/Feed/components/feed-card.vue:222` (sendAmbars)
   - `resources/web/js/pages/Feed/components/feed-card.vue:249` (sendMessage)

---

## Stack gotchas (real bugs that bit us)

- **Spatie Permission events are off by default.** `config/permission.php` has `'events_enabled' => false`. With that off, `RoleAttached` / `RoleDetached` / `PermissionAttached` / `PermissionDetached` do **not** fire. Already enabled in this repo. If a permission listener does not run, check this first.
- **OAuth + session state do not mix.** OAuth callbacks in stateless API routes cannot rely on session-based state. Solution: pass user ID as OAuth `state` parameter.
- **npm packages need explicit install.** Some packages (e.g. `driver.js@1`) are not picked up by general `npm install` and need explicit `npm install <pkg>@<version>`.
- **SCP from correct context.** Run SCP from local Mac terminal, not from inside an active SSH session.
- **`package-lock.json` git conflicts on server.** Resolution: `git checkout -- package-lock.json` before pulling, or remove local server copies.
- **Driver.js `onDestroyed` + `isLastStep()` does not trigger correctly** for final-step modal transitions. Use alternative completion logic.
- **Wizard state must be read on mount.** If not, the wizard always starts at Step 1 regardless of backend `onboarding_steps` state.
- **Fixed `z-index` layers can break third-party position calculations.** Adding full-viewport fixed overlays (scanlines/vignette/flicker) created stacking context changes that nearly broke driver.js highlight positioning. The actual culprit was different (see Driver.js gotcha above), but the lesson stands.
- **Notification API quirks.** Endpoint is `/api/notification` (singular), field is `description` (not `message`), data at `response.data[0].data`.

---

## Shims and temporary code

- **`ProfileResource.is_staff_legacy`.** Bridge for staff checks during the role system migration. Pattern: `is_staff_legacy = hasAnyRole(['tr_moderator','tr_admin','tr_superadmin'])`. Removed in Brief 9N-C cleanup once `$store.getters.isStaff` covers all cases.
- **`steam-sync` job runs on `sync` connection.** Until Supervisor is set up for queue workers on the droplet.

---

## Useful server commands

```bash
# Tail Laravel log
tail -f /var/www/ambar/storage/logs/laravel.log

# Clear Laravel caches
cd /var/www/ambar && php artisan config:clear && php artisan cache:clear && php artisan route:clear

# Cache config (after .env change)
cd /var/www/ambar && php artisan config:cache

# Apache reload
systemctl reload apache2

# DB shell
mysql -u deployer -p'<see .env>' ambar
```

---

## Documents

- `TROPHYROOM_WORKING_GUIDE.md` — methodology and project overview for new chat sessions.
- `NEXT_SESSION_PROMPT.md` — handoff prompt updated at end of each session.
- `onboarding-mockup.html` — approved Cinematic Ritual reference mockup.

# TrophyRoom 2.0 — Project Status

> Last updated: 2026-04-17  
> Server: `164.92.83.95` (`/var/www/ambar`) — Apache, PHP 8.2, Laravel 10  
> Bot: `/opt/tr-discord-bot/` — Python 3.10, discord.py 2.x, systemd `tr-bot.service`  
> Repo: `git@github.com:AmbarMax/TR.git` (branch: `main`)  
> Live: `https://app.ambar.gg`

---

## Completed Phases

### Pre-2.0 — Core Platform (legacy, all deployed)
- User auth: JWT login/register/logout, 2FA, password reset
- Profile: avatar, background, country, social links
- Integrations: GitHub badges, Steam profile badges (broken — see bugs), Discord role/badge sync
- Feed, follower graph, achievement sharing
- Forge: trophy claim flow (Ambar cost → Uru reward), chest/key system
- Virtual hall (`/virtual-hall/:username`) — public profile, no auth required
- Bot user linking (`/bot/link`) — Discord → TrophyRoom account association

---

## UI Renovation Phases (from CLAUDE_FRONTEND_OPS.md)

### Phase 1 — Shell & Global Styles ✓ Complete
- Design tokens (`_variables.scss`): new palette (`$bg #000003`, `$primary #ff6100`, `$accent #c1f527`, `$text #feeddf`)
- `sidebar.vue`: TrophyRoom SVG icons, orange active state, TrophyRoom social links, Brand Dashboard nav item
- `main-header.vue`: removed MetaMask/Connect Wallet, updated font, cleaned up Web3 import
- `style.scss`: body background, sidebar/header base styles rewritten for new identity
- `web.blade.php`: title changed from "Ambar" to "TrophyRoom", Google Fonts updated
- Logo: replaced Ambar logo with `tr-isologo.png` across all components
- Favicon: regenerated from `tr-isologo.png` with transparency

### Phase 2 — Public Pages ✗ Pending
Renovation of unauthenticated pages: landing/home, login, register, forgot-password, reset-password, virtual hall. Apply new design tokens, typography, and layout to all pages a non-logged-in user sees.

### Phase 3 — User Profile ✗ Pending
Redesign of `/#/profile` and `/#/trophy-room` (own profile view) and `/virtual-hall/:username` (public view). Badge grid, trophy showcase, achievement timeline, integration connection cards.

### Phase 4 — Social & Feed ✗ Pending
Redesign of `/#/feed`, followers/following pages, achievement sharing modals. Apply new card styles, typography, and interaction patterns.

### Phase 5 — Settings & Integrations ✗ Pending
Redesign of settings page (password, 2FA, avatar/background upload) and integration connection flow (Steam, GitHub, Discord, Riot, Strava connect/disconnect cards).

### Phase 6 — Admin Polish ✗ Pending
Light cleanup of the legacy Blade admin at `/admin/`: fix broken trophy creation (blocked by Web3 coupling), update language (NFT → Trophy, mint → forge), minimal visual alignment. Does NOT require full redesign.

---

### Phase 7A — Brand Dashboard Backend + Frontend
- Route group `GET|POST|PUT|DELETE /api/brand/*` behind `JwtMiddleware` + `brand.admin` middleware
- 27 brand API routes operational (see full list below)
- Brand Dashboard SPA at `/#/brand-dashboard`, access-gated to `Master user` role
- Tabs: Overview, Trophies, Badges, Polls, Events

### Phase 7B — Trophy Management (Brand)
- `BrandTrophiesController`: full CRUD + stats endpoint
- `TrophyManager.vue`: create/edit/delete trophies, badge requirements as chips, image upload with preview
- Web3 decoupled from trophy creation — trophies are DB objects, not NFTs

### Phase 8 — Guild Connection (Discord OAuth)
- `BrandGuildController`: connect/select/disconnect guild via Discord OAuth
- OAuth flow: `GET /api/brand/guild/connect?token=JWT` → Discord → callback → guild picker modal
- State-based routing in `DiscordController::handleDiscordCallback` distinguishes brand flow (`state=brand_guild:*`) from user login flow
- `DashboardOverview.vue`: connect button, guild card, disconnect, guild selector modal

### Phase 8 — Badge Manager
- `BrandBadgesController`: full CRUD, images stored at `storage/integrations/brand/`
- `BadgeManager.vue`: create form with image upload + preview, inline edit, delete with confirm, scrollable list

### Phase 8 — Badge Rules
- `BrandRulesController`: create/toggle rules (trigger_type, threshold, channel_id, badge_id)
- All channel dropdowns use `channel.channel_id` (Discord snowflake), not internal UUID

### Phase 8 — Polls
- `BrandPollsController`: create, close, delete, results aggregation
- `BotPollController`: pending, markPublished, recordVote (persists to `bot_poll_votes`)
- `bot_poll_votes` table with unique constraint (poll_id + discord_user_id)
- `PollManager.vue`: create form, status badges, lazy-loaded results panel with progress bars, CSV download, close/delete buttons
- Bot (`cogs/polls.py`): publishes polls to Discord channels, `PollView` callback reports votes to `POST /bot/polls/{id}/vote`

### Phase 8 — Events
- `BrandEventsController`: create, delete; index excludes completed events
- `BotEventController`: pending, markScheduled, complete
- `EventManager.vue`: flatpickr date/time pickers, status badges, delete with confirm; no channel field (events are Discord Scheduled Events, not text-channel posts)
- Bot (`cogs/events.py`): publishes draft events as Discord Scheduled Events (`EntityType.external`), marks scheduled via `POST /bot/events/{id}/scheduled`

### Bot — Active Cogs
| Cog | Function |
|-----|----------|
| `tracking.py` | Voice minutes, message count, reactions → badge rule progress |
| `commands.py` | Slash commands |
| `sync.py` | Channel sync on guild join/update |
| `polls.py` | Publish polls, track votes, report to API |
| `events.py` | Publish events as Discord Scheduled Events |

---

## What Is Operational in Production

All of the above is deployed on `app.ambar.gg`. The server is at the same commit as `main` (`800a5d8`). All migrations have run including `bot_poll_votes`.

**Verified working:**
- Brand Dashboard full tab navigation
- Guild connection OAuth (requires Discord app redirect URI `https://app.ambar.gg/api/discord/callback`)
- Badge CRUD with image upload
- Poll creation, publishing via bot, vote reporting
- Event creation, Discord Scheduled Event publishing via bot
- Trophy CRUD with badge requirements
- Bot running as systemd service, all 5 cogs loaded

---

## Known Bugs

### Critical
- **Web3 bindings still in `AppServiceProvider`** — `TrophyNFT`, `KeyNFT`, `Pinata` bindings are registered at boot. Env vars are empty so they don't crash startup, but any code path that resolves these bindings will 500. Admin trophy creation is still broken. `app/Web3/` folder not yet removed. (CLAUDE.md Task 0 — not started)

### Steam Integration
- **Wrong achievement source** — `SteamApi::getBadges()` fetches profile badges (card sets, community badges via `IPlayerService/GetBadges/v1` + HTML scraping with Goutte) instead of per-game achievements. Methods `getOwnedGames()`, `getPlayerAchievements($appid)`, `getGameSchema($appid)` exist but are unused. (CLAUDE.md Task 1 — not started)
- **Env vs config inconsistency** — `SteamApi::setUserId()` reads `env('STEAM_SECRET')` directly; other methods use `config('services.steam.*')`. Should be normalized to config.

### Discord OAuth (Guild Connect)
- **Single redirect URI constraint** — The registered redirect URI in the Discord app is `https://app.ambar.gg/api/discord/callback`. This URI is shared between user login (Socialite) and brand guild connect (manual OAuth). The `state` parameter differentiates them. If the Discord app's redirect URI list ever changes, both flows break simultaneously.
- **Bot rename pending** — Discord bot username is still "Ambar Bot". Must be changed to "TrophyRoom" manually in Discord Developer Portal → app → Bot tab. (Cannot be done via API or code.)

### Brand Dashboard — Minor
- **Poll `require_specific_answer` not wired in UI** — The DB column and bot logic exist (only specific answers trigger badge progress), but `PollManager.vue` create form has no field for it. All polls currently treat any answer as qualifying.
- **Event `ends_at` not required in schema but required in validation** — If a brand creates an event without an end time, the bot's `create_scheduled_event()` will fail for `EntityType.external` (Discord requires end_time for external events). The frontend enforces it, but a direct API call without `ends_at` passes Laravel validation (`nullable`) and will produce a bot error.
- **Guild connection shows first-connected guild only** — `BrandGuildController::index()` returns the first active guild for the user's `org_id`. Multiple guilds per brand is not supported in the current data model (one brand = one `org_id` = one guild connection).

### Polls
- **Closed polls still show vote buttons in Discord** — When a brand closes a poll via the dashboard, the bot's `PollView` instance in memory is not notified. Users can still click buttons on the Discord message until the bot restarts. A proper fix requires the bot to edit/disable the Discord message on close.

---

## Pending Features (Backlog)

### From CLAUDE.md Roadmap
| # | Task | Effort |
|---|------|--------|
| 0 | Decouple Web3 from all Admin services (`AppServiceProvider`, `AdminTrophyService`, `AdminExchangeService`, `AdminChestService`, `AdminKeyService`, `AdminItemService`, `TrophyService`, `AbstractTrophyService`, `FileService`) | M |
| 1 | Fix Steam achievements: rewrite `SteamApi::getBadges()` to use `getOwnedGames()` + `getPlayerAchievements($appid)` per-game | M |
| 2 | Riot Games / LoL integration (`lol-challenges-v1` + `champion-mastery-v4`) | L |
| 3 | Strava integration | L |
| 4 | Delete `app/Web3/` folder (after Task 0) | S |

### Brand Dashboard — Remaining Gaps
- **Poll `require_specific_answer` field** in create form (which option must be chosen to trigger badge)
- **Disable Discord poll buttons on close** — bot should edit the message to remove buttons or mark them disabled when `POST /api/brand/polls/{id}/close` is called
- **Event location field** — currently hardcoded to `event.description || event.title` in `events.py`; brand should be able to specify a custom location string
- **Bot API key display** — brands need to see their `bot_api_key` (the key the bot uses to authenticate to `/api/bot/*`) somewhere in the dashboard so they can configure the bot. Currently there is no UI for this.
- **Multiple guilds per brand** — data model only supports one active guild per `org_id`
- **Poll vote deduplication in Discord** — `PollView.responses` dict is in-memory only; if the bot restarts, a user can vote again. Should be backed by `bot_poll_votes` uniqueness check via API before registering the vote.

### Platform-wide
- **Overwolf / Valorant integration** — mentioned in product description, not started
- **Notification system** — `NotificationController` exists but the frontend notification panel is minimal
- **Admin panel Web3 cleanup** — the Blade + vanilla JS admin at `/admin/` still has NFT/mint language and broken trophy creation

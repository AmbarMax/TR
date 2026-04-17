# CLAUDE_BACKEND_OPS.md — Brand Admin API Endpoints

> **This document is an operational brief for Claude Code.**
> It describes the Laravel backend endpoints needed for the Brand Admin Dashboard (Phase 7).
> The frontend components already exist at `resources/web/js/pages/BrandDashboard/`.
> Read CLAUDE.md and CLAUDE_FRONTEND_OPS.md for full context.

---

## 0. Problem

The existing bot API endpoints (`/api/bot/*`) use `bot_api_key` authentication (middleware `bot.api`).
The Brand Dashboard frontend needs endpoints that accept **JWT user authentication** (the same auth used by the rest of the Vue frontend).

The solution: create a new set of `/api/brand/*` endpoints that:
1. Use JWT auth (same as other frontend API calls)
2. Require the user to have an `admin` role
3. Delegate to the same underlying logic as the bot endpoints

---

## 1. Server & Project Info

- **Server:** 164.92.83.95 (DigitalOcean)
- **Project path:** /var/www/ambar
- **Framework:** Laravel (PHP)
- **Auth:** JWT (via existing middleware)
- **Deploy:** `git push` → SSH → `git pull` → `npm run build` (frontend) / `php artisan` commands as needed
- **Dev path (local):** ~/Documents/trophyroom

---

## 2. Database Tables (already exist)

These tables were created during the bot backend build. They already exist in the database:

- `guild_connections` — guild linked to a brand/user
- `guild_channels` — cached Discord channels for a guild
- `badge_rules` — rules that trigger badge grants (voice_minutes, message_count, etc.)
- `user_links` — Discord ↔ TrophyRoom user links
- `bot_polls` — polls created by brand admin
- `bot_events` — events created by brand admin
- `BadgeUser` — pivot model for badge grants

---

## 3. Endpoints to Create

All endpoints below should:
- Live under the route prefix `/api/brand/`
- Use JWT authentication middleware (same as `/api/profile`, `/api/badges`, etc.)
- Require admin role (check `$user->roles` for 'admin')
- Return JSON responses

### 3.1 Rules

**GET /api/brand/rules**
- Returns active badge rules for the authenticated user's guild
- Response: JSON array of rules with their associated badges and channels
- Mirrors: `GET /api/bot/rules`

**POST /api/brand/rules**
- Creates a new badge rule
- Body: `{ trigger_type, channel_id, threshold, badge_id, active }`
- `trigger_type` is one of: `voice_minutes`, `message_count`, `reaction`, `event_join`, `poll_answer`, `role_obtain`
- `channel_id` references a `guild_channels` record
- `badge_id` references an existing badge
- `active` is boolean (default true)
- Response: the created rule object

**PUT /api/brand/rules/{id}**
- Updates a rule (mainly to toggle active/inactive)
- Body: `{ active }` (boolean)
- Response: the updated rule object

### 3.2 Channels

**GET /api/brand/channels**
- Returns cached Discord channels for the user's guild
- Response: JSON array of channels
- Mirrors: `GET /api/bot/channels`

### 3.3 Badges

**GET /api/brand/badges**
- Returns badges owned/managed by the brand
- Can use existing `/api/badges` endpoint or create a brand-specific one
- Response: JSON array of badges

**POST /api/brand/badges**
- Creates a new badge
- Body: `{ name, image (file upload), description, type }`
- Response: the created badge object

### 3.4 Polls

**GET /api/brand/polls**
- Returns all polls (pending, active, closed) for the user's guild
- Response: JSON array of polls with their options and results
- Mirrors: `GET /api/bot/polls/pending` but returns ALL polls

**POST /api/brand/polls**
- Creates a new poll
- Body: `{ title, options (array of strings), channel_id, duration_minutes, badge_id }`
- Response: the created poll object

**POST /api/brand/polls/{id}/close**
- Closes an active poll
- Response: the closed poll with results
- Mirrors: `POST /api/bot/polls/{id}/close`

### 3.5 Events

**GET /api/brand/events**
- Returns all events for the user's guild
- Response: JSON array of events

**POST /api/brand/events**
- Creates a new event
- Body: `{ title, description, channel_id, start_date, end_date, badge_id }`
- Response: the created event object

**POST /api/brand/events/{id}/complete**
- Marks an event as completed
- Response: the completed event
- Mirrors: `POST /api/bot/events/{id}/complete`

### 3.6 Stats (Overview)

**GET /api/brand/stats**
- Returns dashboard statistics for the user's guild
- Response: `{ linked_users: int, active_rules: int, synced_channels: int, badges_granted: int, recent_activity: array }`
- This is a new endpoint that aggregates data from multiple tables

---

## 4. Implementation Steps

### Step 1: Create Brand Controller(s)

Create one or more controllers in `app/Http/Controllers/Api/Brand/`:

Option A (single controller):
- `BrandDashboardController.php` with methods for each endpoint

Option B (multiple controllers, cleaner):
- `BrandRulesController.php`
- `BrandChannelsController.php`
- `BrandPollsController.php`
- `BrandEventsController.php`
- `BrandStatsController.php`

**Recommended: Option B** — follows the same pattern as the bot controllers.

### Step 2: Create Routes

Add to `routes/api.php` (or wherever the API routes are defined):

```php
Route::prefix('brand')->middleware(['auth:api', 'admin'])->group(function () {
    // Stats
    Route::get('/stats', [BrandStatsController::class, 'index']);

    // Rules
    Route::get('/rules', [BrandRulesController::class, 'index']);
    Route::post('/rules', [BrandRulesController::class, 'store']);
    Route::put('/rules/{id}', [BrandRulesController::class, 'update']);

    // Channels
    Route::get('/channels', [BrandChannelsController::class, 'index']);

    // Badges
    Route::get('/badges', [BrandBadgesController::class, 'index']);
    Route::post('/badges', [BrandBadgesController::class, 'store']);

    // Polls
    Route::get('/polls', [BrandPollsController::class, 'index']);
    Route::post('/polls', [BrandPollsController::class, 'store']);
    Route::post('/polls/{id}/close', [BrandPollsController::class, 'close']);

    // Events
    Route::get('/events', [BrandEventsController::class, 'index']);
    Route::post('/events', [BrandEventsController::class, 'store']);
    Route::post('/events/{id}/complete', [BrandEventsController::class, 'complete']);
});
```

### Step 3: Admin Middleware

If an `admin` middleware doesn't exist yet, create one:

```php
// app/Http/Middleware/AdminMiddleware.php
public function handle($request, Closure $next)
{
    $user = auth()->user();
    if (!$user || !$user->hasRole('admin')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    return $next($request);
}
```

Register it in `app/Http/Kernel.php` under `$routeMiddleware`.

**Important:** Check how roles work in the existing codebase. The user model may use a roles relationship, a roles JSON column, or a package like Spatie permissions. Match the existing pattern.

### Step 4: Update Frontend API Calls

After the backend endpoints are created, update the frontend components in `resources/web/js/pages/BrandDashboard/` to call `/api/brand/*` instead of `/api/bot/*`.

The components use the existing `api` module (in `resources/web/js/api/`) which handles JWT auth headers automatically.

### Step 5: Test & Deploy

1. Test each endpoint locally with `php artisan serve` or via the API module
2. `git add`, `git commit`, `git push`
3. SSH to server: `cd /var/www/ambar && git pull origin main && npm run build`
4. Test the Brand Dashboard in browser

---

## 5. Development Rules

1. **One endpoint at a time.** Create, test, then move on.
2. **Match existing patterns.** Look at how the bot controllers are structured and follow the same conventions.
3. **Use existing models.** Don't create new models — use the existing ones (Badge, BadgeRule, BotPoll, BotEvent, GuildChannel, GuildConnection, UserLink).
4. **Don't modify bot endpoints.** The bot API must keep working independently.
5. **Commit messages:** `feat: add brand rules endpoint`, `feat: add brand polls endpoint`, etc.
6. **When in doubt, ask.** Don't guess about business logic.

---

## 6. Execution Order

```
1. Examine existing bot controllers and models to understand the patterns
2. Create admin middleware (if needed)
3. Create BrandStatsController + route → test
4. Create BrandChannelsController + route → test
5. Create BrandRulesController + routes (index, store, update) → test
6. Create BrandPollsController + routes (index, store, close) → test
7. Create BrandEventsController + routes (index, store, complete) → test
8. Update frontend components to use /api/brand/* endpoints
9. Full test of Brand Dashboard
10. Deploy to production
```

# TrophyRoom Bot API — Laravel Implementation Instructions

## Context
TrophyRoom is a unified achievements platform. We're building a Discord bot system where brands install a bot in their Discord server, and the bot tracks user engagement (voice time, messages, polls, events) and reports to TrophyRoom's Laravel backend via REST API. The bot grants badges to users when they meet conditions defined by the brand in TR's admin dashboard.

6 new database tables have already been migrated:
- `guild_connections` — links a Discord guild to a TR client/org
- `guild_channels` — cached list of channels per guild
- `badge_rules` — rules that define when a badge is earned (e.g., 30 min in voice channel X)
- `user_links` — maps Discord user IDs to TR user IDs
- `bot_polls` — polls created from TR admin, published by the bot
- `bot_events` — scheduled events created from TR admin

## Project location
`/var/www/ambar/`

## Tech stack
- Laravel 10, PHP 8.2, MySQL
- UUIDs for all primary keys (use the existing `App\Http\Traits\UUID` trait)
- Existing patterns: `BaseRepository`, `ApiIntegrationInterface`, adapter pattern

## What to build

### 1. Eloquent Models

Create these models in `app/Models/`:

**GuildConnection.php**
- Uses UUID trait, fillable: guild_id, guild_name, org_id, bot_api_key, bot_connected_at, channel_cache_updated_at, active
- Relations: hasMany GuildChannel, hasMany BadgeRule, hasMany UserLink, hasMany BotPoll, hasMany BotEvent

**GuildChannel.php**
- Uses UUID trait, fillable: guild_id, channel_id, name, type, category
- Relations: belongsTo GuildConnection (foreign key: guild_id, owner key: guild_id)

**BadgeRule.php**
- Uses UUID trait, fillable: badge_id, guild_id, platform, trigger_type, trigger_config, name, description, active
- Cast trigger_config as array
- Relations: belongsTo Badge, belongsTo GuildConnection (via guild_id)

**UserLink.php**
- Uses UUID trait, fillable: discord_user_id, tr_user_id, guild_id, discord_username, linked_at
- Cast linked_at as datetime
- Relations: belongsTo User (foreign key: tr_user_id), belongsTo GuildConnection (via guild_id)

**BotPoll.php**
- Uses UUID trait, fillable: guild_id, badge_rule_id, title, options, channel_id, discord_message_id, duration_hours, require_specific_answer, status
- Cast options as array
- Relations: belongsTo GuildConnection (via guild_id), belongsTo BadgeRule

**BotEvent.php**
- Uses UUID trait, fillable: guild_id, badge_rule_id, title, description, channel_id, discord_event_id, starts_at, ends_at, status
- Cast starts_at and ends_at as datetime
- Relations: belongsTo GuildConnection (via guild_id), belongsTo BadgeRule

### 2. Bot API Middleware

Create `app/Http/Middleware/BotApiMiddleware.php`:
- Reads `Authorization: Bearer {bot_api_key}` from request header
- Looks up the `guild_connections` table for a matching `bot_api_key` where `active = true`
- If found, injects the `GuildConnection` model into the request (`$request->guildConnection`)
- If not found, returns 401 JSON response

Register it in `app/Http/Kernel.php` under `$middlewareAliases` as `'bot.api'`.

### 3. API Routes

Add to `routes/api.php` — OUTSIDE the existing JWT middleware group:

```php
Route::prefix('bot')->middleware('bot.api')->group(function () {
    // Badge granting
    Route::post('/badges/grant', [App\Http\Controllers\Api\Bot\BotBadgeController::class, 'grant']);
    
    // Rules
    Route::get('/rules', [App\Http\Controllers\Api\Bot\BotRuleController::class, 'index']);
    
    // Channels
    Route::post('/channels/sync', [App\Http\Controllers\Api\Bot\BotChannelController::class, 'sync']);
    Route::get('/channels', [App\Http\Controllers\Api\Bot\BotChannelController::class, 'index']);
    
    // User linking
    Route::post('/users/link', [App\Http\Controllers\Api\Bot\BotUserController::class, 'link']);
    Route::get('/users/lookup/{discord_user_id}', [App\Http\Controllers\Api\Bot\BotUserController::class, 'lookup']);
    
    // Polls
    Route::get('/polls/pending', [App\Http\Controllers\Api\Bot\BotPollController::class, 'pending']);
    Route::post('/polls/{id}/published', [App\Http\Controllers\Api\Bot\BotPollController::class, 'markPublished']);
    Route::post('/polls/{id}/close', [App\Http\Controllers\Api\Bot\BotPollController::class, 'close']);
    
    // Events
    Route::get('/events/pending', [App\Http\Controllers\Api\Bot\BotEventController::class, 'pending']);
    Route::post('/events/{id}/scheduled', [App\Http\Controllers\Api\Bot\BotEventController::class, 'markScheduled']);
    Route::post('/events/{id}/complete', [App\Http\Controllers\Api\Bot\BotEventController::class, 'complete']);
});
```

### 4. Controllers

Create in `app/Http/Controllers/Api/Bot/`:

**BotBadgeController.php**
```php
public function grant(Request $request)
{
    // Validate: tr_user_id (required, exists:users,id), badge_id (required, exists:badges,id), metadata (optional, array)
    // Check that the badge_id belongs to a badge_rule linked to this guild
    // Create badge_user pivot record (if not already exists)
    // Return 201 with badge info
}
```

**BotRuleController.php**
```php
public function index(Request $request)
{
    // $request->guildConnection is available from middleware
    // Return all active badge_rules for this guild with their badge info
    // Include trigger_type, trigger_config, badge name, badge image
}
```

**BotChannelController.php**
```php
public function sync(Request $request)
{
    // Receives array of channels: [{channel_id, name, type, category}, ...]
    // Deletes all existing channels for this guild
    // Inserts new ones
    // Updates channel_cache_updated_at on guild_connection
    // Return 200
}

public function index(Request $request)
{
    // Return all cached channels for this guild
}
```

**BotUserController.php**
```php
public function link(Request $request)
{
    // Validate: discord_user_id (required), tr_user_id (required, exists:users,id), discord_username (optional)
    // Create or update user_links record
    // Return 201
}

public function lookup($discord_user_id, Request $request)
{
    // Find user_link by discord_user_id and guild_id
    // Return tr_user_id and discord_username, or 404
}
```

**BotPollController.php**
```php
public function pending(Request $request)
{
    // Return all bot_polls with status 'draft' for this guild
    // Include channel info and badge_rule info
}

public function markPublished(Request $request, $id)
{
    // Validate: discord_message_id (required)
    // Update poll status to 'active', save discord_message_id
}

public function close(Request $request, $id)
{
    // Update poll status to 'closed'
}
```

**BotEventController.php**
```php
public function pending(Request $request)
{
    // Return all bot_events with status 'draft' for this guild
}

public function markScheduled(Request $request, $id)
{
    // Validate: discord_event_id (required)
    // Update event status to 'scheduled', save discord_event_id
}

public function complete(Request $request, $id)
{
    // Update event status to 'completed'
}
```

### 5. Guild Connection OAuth Setup

Create `app/Http/Controllers/Api/Bot/GuildSetupController.php`:

This handles the OAuth flow when a client clicks "Connect Discord" in TR admin:

```php
public function redirectToDiscord(Request $request)
{
    // Build Discord OAuth2 URL for bot installation:
    // https://discord.com/api/oauth2/authorize?client_id={BOT_CLIENT_ID}&permissions=1099511627776&scope=bot%20applications.commands&redirect_uri={callback_url}&response_type=code&state={encrypted_org_id}
    // Permissions needed: Read Messages, Send Messages, Manage Events, Connect to Voice, View Channels
    // Return redirect
}

public function handleCallback(Request $request)
{
    // Discord returns: code, guild_id, permissions
    // Decrypt org_id from state
    // Generate a random bot_api_key (use Str::random(64))
    // Create GuildConnection record
    // Redirect back to TR admin with success
}
```

Route (outside JWT middleware):
```php
Route::get('bot/setup/authorize', [GuildSetupController::class, 'redirectToDiscord'])->name('bot.setup.authorize');
Route::get('bot/setup/callback', [GuildSetupController::class, 'handleCallback'])->name('bot.setup.callback');
```

### Important notes
- Follow the existing code style in the project
- All models use the UUID trait from `App\Http\Traits\UUID`
- Use `$table->uuid()` column type for all foreign keys referencing UUIDs
- guild_id foreign keys reference `guild_connections.guild_id` (string), NOT the UUID primary key
- Return JSON responses for all bot API endpoints
- The badge_user pivot table has columns: id, user_id, badge_id, display, deleted_at, created_at, updated_at, is_share
- When granting a badge, check if the badge_user record already exists to avoid duplicates
- The `badges.id` column is UUID type

# TrophyRoom 2.0 — CLAUDE.md

## What is TrophyRoom?

A unified achievement showcase platform where users display accomplishments across gaming platforms (Steam, Discord, Riot Games, Valorant via Overwolf) and non-gaming services (Strava, GitHub). Users connect their accounts, achievements sync automatically, and everything lives in one profile — a trophy case for your digital life.

**Business entity:** Ambar Labs (C-Corp, Delaware)  
**Live URL:** https://app.ambar.gg  
**Repo:** git@github.com:AmbarMax/throphyroom.git

---

## Tech Stack

- **Backend:** Laravel 10, PHP 8.2
- **Database:** MySQL (db: `ambar`, user: `deployer`)
- **Server:** Ubuntu 22.04, Apache, SSL — `164.92.83.95` (hostname: `ambar-prod`)
- **Project path on server:** `/var/www/throphyroom/`
- **Frontend:** Vite (Vue/Blade — check `resources/`)
- **Auth:** JWT (tymon/jwt-auth) + Laravel Sanctum + optional 2FA

---

## Architecture — Integration Pattern

All platform integrations follow the **Adapter pattern**:

```
app/Http/Apis/Integrations/
├── ApiIntegrationInterface.php    ← Contract: getBadges(), syncBadges(), setAuthData()
├── Steam/
│   ├── SteamApi.php               ← HTTP client (talks to Steam Web API)
│   └── SteamAdapter.php           ← Implements interface, delegates to SteamApi
├── Discord/
│   ├── DiscordApi.php
│   └── DiscordAdapter.php
├── Github/
│   ├── GithubApi.php
│   └── GithubAdapter.php
```

**Orchestrator:** `app/Services/Api/BadgeService.php`
- `setApiIntegration()` — switch on adapter type → sets `$integrationType`
- `syncBadges()` — calls adapter's `getBadges()`, runs `synchronize()` to upsert into DB
- `synchronize()` — matches by name, creates new badges via `FileService` for images

**Enums:**
- `IntegrationType`: github, steam, discord
- `BadgeType`: Common (0), DiscordRole (1), DiscordBadge (2), DiscordBotBadge (3)

**Config:** `config/integrations.php` — names, image URLs, Discord badge mappings

### To add a new integration:

1. Create `app/Http/Apis/Integrations/{Platform}/{Platform}Api.php`
2. Create `app/Http/Apis/Integrations/{Platform}/{Platform}Adapter.php` implementing `ApiIntegrationInterface`
3. Add const to `IntegrationType.php`
4. Add case to `BadgeService::setApiIntegration()` switch
5. Add to `config/integrations.php` name array and image config
6. Add controller + routes in `routes/api.php`
7. Run `IntegrationSeeder` or manually insert integration row

---

## Current State & Known Issues

### CRITICAL — Web3 Decoupling Needed

`AppServiceProvider.php` registers bindings for `TrophyNFT`, `KeyNFT`, and `Pinata` that depend on blockchain env vars (`BLOCKCHAIN_PROVIDER_URL`, `TROPHY_NFT_ADDRESS`, `OWNER_ADDRESS`, etc.). These vars are empty/missing, causing **500 errors** when admin tries to create trophies.

**Decision (confirmed by founder):** Trophies are database objects in 2.0, NOT NFTs. All Web3 dependencies must be removed.

Affected files:
- `app/Providers/AppServiceProvider.php` — remove Web3 bindings
- `app/Services/Admin/AdminTrophyService.php` — decouple from TrophyNFT
- `app/Services/Admin/AdminExchangeService.php` — decouple from Web3
- `app/Services/Admin/AdminChestService.php` — decouple from Web3
- `app/Services/Admin/AdminKeyService.php` — decouple from KeyNFT
- `app/Services/Admin/AdminItemService.php` — decouple from Web3
- `app/Services/Api/TrophyService.php` — decouple from TrophyNFT
- `app/Services/AbstractServices/AbstractTrophyService.php` — decouple
- `app/Services/FileService.php` — check Pinata dependency

**Do NOT delete** `app/Web3/` folder yet — just decouple. Delete in a separate cleanup task.

### Steam Bug

`SteamApi::getBadges()` calls `getListOfAchievements()` which fetches **profile badges** (card sets, community badges) via `IPlayerService/GetBadges/v1` and scrapes HTML with Goutte.

The methods to fetch **per-game achievements** already exist but are unused:
- `getOwnedGames()` — lists all games
- `getPlayerAchievements($appid)` — achievements for specific game
- `getGameSchema($appid)` — achievement definitions (names, icons)
- `getGlobalAchievementPercentages($appid)` — global unlock %

Fix: rewrite `getBadges()` to use the per-game methods instead of the badge/scraping approach.

### Steam Auth

`SteamApi::setUserId()` uses `env('STEAM_SECRET')` directly. Should use `config('services.steam.client_secret')`. Some methods already use config(), others use env() — inconsistent.

---

## Deploy Workflow

```bash
# Local: edit code, commit, push
git push origin main

# Server: SSH in and pull
ssh root@164.92.83.95
cd /var/www/throphyroom
git pull origin main
php artisan config:clear
php artisan cache:clear
# If migrations: php artisan migrate --force
```

SSH from Mac requires key at `~/.ssh/id_ed25519_do_control` or entering via DigitalOcean web console.

---

## Database Stats (as of 2026-04-14)

- Users: 459
- Badges: 127
- Integrations: 3 (GitHub, Steam, Discord)

---

## Roadmap — TrophyRoom 2.0

### Task 0: Decouple Web3 from Trophies
### Task 1: Fix Steam Achievements (per-game instead of profile badges)
### Task 2: Add Riot Games / LoL Integration (lol-challenges-v1 + champion-mastery-v4)
### Task 3: Add Strava Integration
### Task 4: Remove Web3 folder entirely (cleanup)

See TASKS.md for detailed autonomous task definitions.

---

## Constraints

- **Do NOT touch** the integration Adapter pattern — it works, extend it
- **Do NOT delete** data from production DB without explicit confirmation
- **Do NOT modify** auth system (JWT/Sanctum) unless specifically tasked
- **PHP warnings** about curl/mbstring modules are harmless — ignore them
- **Test on server** after every change — no local dev environment currently set up

---

## Deuda técnica para Brief 9N-D

Items no resueltos durante Brief 9N-B (Halls + Forge v2). Bloquean trabajo futuro y no se pueden ignorar al planear 9N-D.

1. **`trophies.is_active` + `starts_at` / `ends_at` missing.** El campaign lifecycle real no existe en el schema. Brand Halls muestran trophies "activos" usando `deleted_at IS NULL` como proxy — no hay ventana temporal. Blocker para scheduled campaigns / time-limited drops.

2. **`chests.user_id` missing.** `chests` no tiene FK al brand owner, solo `key_id` → `keys`. Imposible filtrar chests por brand Hall. El endpoint `GET /api/users/{username}/active-items` devuelve solo trophies hasta que se agregue esa FK (con su backfill correspondiente).

3. **Dos conceptos paralelos de follow.** `followers` (user-user legacy, generic social graph) coexiste con `hall_followers` (nuevo, específico para subscribe a un Hall). Brand stats leen de `hall_followers`. Queda pendiente la decisión para Player Hall del Step 22: ¿mostrar `followers_count` genérico, `hall_followers_count` nuevo, o ambos? Definir antes de empezar Player Hall UI.

---

## Gotchas del stack

Comportamientos no obvios que ya nos mordieron. Primera cosa a chequear si algo "debería funcionar y no funciona".

- **Spatie Permission events.** `config/permission.php` trae `'events_enabled' => false` por default. Con eso apagado, `RoleAttached` / `RoleDetached` / `PermissionAttached` / `PermissionDetached` **no se disparan** — cualquier listener nuestro recibe cero. Ya está activado en este repo (commit Step 11) pero si aparece un listener nuevo y no se ejecuta, esto es lo primero que hay que chequear.

---

## Shims temporales

Campos / comportamientos puestos a propósito para puentear transiciones. Cada uno lista cuándo se elimina.

- **`ProfileResource.is_staff_legacy`.** Propósito: mantener la moderación del feed y los checks de staff del sidebar funcionando entre el deploy backend (Step 15 del Brief 9N-B) y el deploy frontend (Step 27). Los 8 lugares del frontend que leían `user.roles.some(r => r.name === 'Master user')` empiezan a devolver `false` después del Step 12 porque `roles` ahora es una lista de strings. El shim `is_staff_legacy = hasAnyRole(['tr_moderator','tr_admin','tr_superadmin'])` permite migrar el frontend en dos saltos: primero de `role.name` → `user.is_staff_legacy`, después de ahí → `$store.getters.isStaff` (composable). **Cuándo se borra:** Brief 9N-C cleanup, junto con `AdminMiddleware` custom y `Role.php` custom, una vez verificado que el composable cubre todos los casos.

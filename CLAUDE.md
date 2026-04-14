# CLAUDE.md — TrophyRoom

> This file is the single source of truth for any AI agent (Claude Code) working on this codebase.
> Read it fully before making any changes.

## Project Overview

TrophyRoom is a gamification and achievement platform that connects brands, game studios, and players. Users link their gaming accounts (Steam, Discord, GitHub), earn badges/trophies, and participate in an achievement economy (exchange, forge, chests/keys).

**Company:** Ambar Labs (C-Corp, Delaware)
**Founders:** Máximo (CEO/Tech), Ana Laura Laglère (COO)
**Live URL:** https://app.ambar.gg (migrating to app.trophyroom.gg)

## Stack

| Layer       | Tech                                      |
|-------------|-------------------------------------------|
| Backend     | Laravel 10 (PHP 8.2)                      |
| Frontend    | Vue 3 + Vuex + Vue Router                 |
| Admin       | Laravel Blade + jQuery + DataTables       |
| Build       | Vite 4 + laravel-vite-plugin              |
| CSS         | Tailwind 3 + Bootstrap 4 + SCSS           |
| Database    | MySQL (local, user: deployer)             |
| Auth        | JWT (tymon/jwt-auth via JwtMiddleware)     |
| Web server  | Apache 2 + mod_ssl (Let's Encrypt)        |
| Server      | Ubuntu on DigitalOcean droplet            |
| Node        | v16.20.2                                  |
| Websockets  | Centrifugo (proxied via Apache)           |
| Repo        | github.com/AmbarMax/TR (private, SSH)     |

## Directory Structure

```
/var/www/ambar/                  # Project root on server
├── app/
│   ├── Console/
│   ├── Enums/
│   ├── Events/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/             # REST API (Auth, Feed, Forge, Integrations, Key, User, Validate, Exchange)
│   │   │   └── Admin/           # Admin panel controllers
│   │   └── Middleware/
│   │       └── JwtMiddleware.php
│   ├── Jobs/
│   ├── Mail/
│   ├── Models/                  # Eloquent models (see list below)
│   ├── Notifications/
│   ├── Observers/
│   ├── Providers/
│   ├── Repositories/
│   ├── Services/
│   ├── View/
│   └── Web3/
├── bootstrap/
├── config/
│   ├── integrations.php         # Steam, Discord, GitHub config
│   ├── jwt.php
│   ├── currencies.php
│   ├── countries.php
│   └── ... (standard Laravel)
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── public/                      # Apache DocumentRoot
│   ├── build/                   # Vite output (committed)
│   ├── images/
│   ├── admin/
│   └── web/
├── resources/
│   ├── web/js/                  # Vue 3 SPA (player-facing)
│   │   ├── app.js               # Entry point
│   │   ├── App.vue / Auth.vue / Main.vue / SinglePage.vue
│   │   ├── api/api.js           # Axios HTTP client
│   │   ├── components/          # Shared Vue components + modals
│   │   ├── pages/               # Route-level pages (Feed, Exchange, etc.)
│   │   ├── router/              # Vue Router config
│   │   ├── store/               # Vuex store
│   │   ├── services/
│   │   └── config.js
│   ├── admin/                   # Admin panel assets
│   │   ├── js/                  # jQuery + page-specific scripts
│   │   └── scss/
│   └── css/
├── routes/
│   ├── api.php                  # API routes (JWT-protected)
│   ├── web.php                  # Web/SPA routes
│   ├── admin.php                # Admin routes
│   ├── auth.php
│   └── channels.php
├── storage/
├── vendor/                      # Composer (do NOT commit)
├── node_modules/                # npm (do NOT commit)
├── throphyroom/                 # LEGACY — old copy, DO NOT TOUCH
├── vite.config.js
├── tailwind.config.js
├── composer.json
├── package.json
├── docker-compose.yml           # NOT used in production
├── .env                         # Server config (NEVER commit)
└── CLAUDE.md                    # This file
```

## Models

Achievement, Admin, AuthIntegration, Badge, BadgeUser, Balance, Chest, Comment, Country, Currency, DiscordBotBadge, DiscordRole, Donation, Exchange, Follower, Integration, Item, Key, Notification, Post, Role, Trophy, User

## Integrations

- **Steam** — OAuth login + badge/achievement import via Steam Web API
- **Discord** — OAuth login + bot integration (role sync, badge sync)
- **GitHub** — OAuth login + badge import
- **Centrifugo** — WebSocket server for real-time notifications (proxied through Apache on ws://164.92.83.95:8000)
- **Web3/NFT** — Voucher signing (signVoucher.js), MetaMask connect, ethers.js (legacy, may be deprecated)

## Routes Overview

- `routes/api.php` — All REST API endpoints, protected by JwtMiddleware. Prefixed `/api`.
- `routes/web.php` — SPA entry + public pages.
- `routes/admin.php` — Admin panel (Blade-based).
- `routes/auth.php` — Authentication flows.

## Vite Entry Points

Defined in `vite.config.js`:
- `resources/web/js/app.js` — Main Vue SPA
- `resources/admin/js/app.js` — Admin panel JS
- `resources/admin/scss/app.scss` — Admin styles
- `resources/web/css/style.scss` — Web styles
- `resources/admin/js/pages/*.js` — Individual admin page scripts (users, trophies, balance, exchanges, items, chests, keys, admins, assignment-of-trophies)

## Deploy Workflow

This project runs directly on the droplet. No Docker in production.

```bash
# SSH into server
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95

# Go to project
cd /var/www/ambar

# Pull latest
git pull origin main

# Install dependencies (if changed)
composer install --no-dev --optimize-autoloader
npm install

# Build frontend
npm run build

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrate (if needed — ALWAYS use --force in prod)
php artisan migrate --force

# Restart queue workers (if applicable)
# php artisan queue:restart
```

**Apache does NOT need restart for PHP/asset changes.** Only restart if you change Apache config:
```bash
systemctl restart apache2
```

## Critical Rules — DO NOT VIOLATE

1. **NEVER commit `.env`** — contains production secrets (DB, API keys, tokens).
2. **NEVER modify `/var/www/ambar/throphyroom/`** — legacy directory, ignore completely.
3. **NEVER run `php artisan migrate:fresh` or `migrate:rollback`** on production — data loss.
4. **NEVER push directly to `main` without testing** — this IS the production branch.
5. **NEVER modify Apache config** without explicit instruction from Máximo.
6. **NEVER install new Composer/npm packages** without explicit approval.
7. **All work on feature branches** — create from `main`, PR when done:
   ```bash
   git checkout -b feature/short-description
   # ... work ...
   git push origin feature/short-description
   ```
8. **Keep `public/build/` committed** — the server does not run `npm run build` automatically.
9. **Test locally before pushing** — run `npm run build` and verify no Vite errors.
10. **Do not delete or rename Models** without understanding the full migration + relationship chain.

## Environment

The `.env` file configures:
- `APP_URL=https://app.ambar.gg` (will change to app.trophyroom.gg)
- `DB_CONNECTION=mysql` on localhost
- JWT auth via `tymon/jwt-auth`
- OAuth callbacks for Steam, Discord, GitHub (all pointing to app.ambar.gg)
- SendGrid for email
- Centrifugo for WebSockets

## Known Technical Debt

- **`throphyroom/` directory** — old copy of entire project nested inside. Should be deleted eventually but is not causing harm.
- **Node 16** — EOL. Should upgrade to 18+ but may break dependencies.
- **Bootstrap 4 + Tailwind 3** — mixed CSS frameworks. Admin uses Bootstrap, web uses Tailwind. Don't mix them further.
- **Web3/NFT code** — `signVoucher.js`, ethers.js, MetaMask integration. Legacy feature, may be deprecated. Don't build on top of it without asking.
- **Centrifugo binary in repo** — was removed in latest commit but WebSocket config remains.
- **`docker-compose.yml`** exists but Docker is NOT used in production.

## Task Template for Autonomous Work

When Máximo assigns a task, it will follow this format:

```
## Task: [title]

**Objective:** What needs to happen.

**Files likely involved:**
- path/to/file1.php
- path/to/file2.vue

**Constraints:**
- Don't touch X
- Must maintain backward compatibility with Y

**Success criteria:**
- [ ] Feature works as described
- [ ] No Vite build errors
- [ ] No new Laravel errors in `storage/logs/laravel.log`
- [ ] Changes committed to feature branch with descriptive message
```

## Useful Commands

```bash
# Check Laravel logs
tail -f /var/www/ambar/storage/logs/laravel.log

# Check Apache error logs
tail -f /var/log/apache2/error.log

# Run artisan commands
cd /var/www/ambar && php artisan [command]

# List all routes
php artisan route:list

# Clear all caches
php artisan optimize:clear

# Check disk space
df -h

# Check MySQL
mysql -u deployer -p ambar
```

## Contact

All decisions go through Máximo. If something is ambiguous, stop and ask — do not guess.

# CLAUDE_BRAND_DASHBOARD_OPS.md

**Brief operativo para Claude Code · Brand Experience v.2**
**Fecha:** 2026-05-05
**Tesis de referencia:** `BRAND_EXPERIENCE_V2_THESIS.md`
**Mockup de referencia:** `brand-dashboard-mockup-v2.html`
**Branch:** `feature/brand-dashboard-v2`
**Stable commit anchor (pre-v.2):** `c0abee2`

---

## CÓMO LEER ESTE DOCUMENTO

Este brief está dividido en **4 bloques** que se ejecutan **estrictamente en orden**. Cada bloque termina con un commit firmado y un check de verificación. NO pasar al siguiente bloque sin verificar el anterior.

- **BLOQUE 1 — Backend** (migration + controller + endpoints)
- **BLOQUE 2 — Mocks frontend** (data realista para development)
- **BLOQUE 3 — Sidebar refactor** (guards por account_type)
- **BLOQUE 4 — BrandDashboard.vue + componentes hijos**

**Reglas globales:**
1. Working directory: `~/Documents/trophyroom` (local) y `/var/www/ambar` (producción)
2. Deploy: `git reset --hard origin/main` (NUNCA `git pull`)
3. Antes de cada commit, correr el verification checklist del bloque
4. Si un step rompe algo no relacionado, PARAR y reportar — no improvisar fixes
5. Mockup HTML es source of truth visual. Si dudás de un detalle, abrir `brand-dashboard-mockup-v2.html` y mirar
6. Cualquier divergencia con la tesis debe ser reportada antes de implementarla

---

# BLOQUE 1 — BACKEND

## 1.1 — Migration: `campaign_id` en `trophies`

**Archivo nuevo:** `database/migrations/{timestamp}_add_campaign_id_to_trophies_table.php`

**Contenido:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trophies', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->nullable()->after('id');
            $table->index('campaign_id');
        });
    }

    public function down(): void
    {
        Schema::table('trophies', function (Blueprint $table) {
            $table->dropIndex(['campaign_id']);
            $table->dropColumn('campaign_id');
        });
    }
};
```

**Verificación:**
- `php artisan migrate --pretend` muestra el ALTER esperado
- Correr `php artisan migrate` en local
- En `Trophy` model NO agregar relación `campaign()` aún — el campo es hook puro, no tiene tabla destino todavía

**Commit:** `feat(trophies): add nullable campaign_id hook for future grouping`

---

## 1.2 — `BrandAnalyticsController` nuevo

**Archivo nuevo:** `app/Http/Controllers/Api/Brand/BrandAnalyticsController.php`

**Namespace:** `App\Http\Controllers\Api\Brand`
**Middleware:** `auth:sanctum` + verificación `account_type = brand` OR rol `tr_admin|tr_superadmin` (override staff)

**Métodos (5 endpoints):**

### 1.2.1 — `GET /api/brand/analytics/performance`

**Propósito:** datos para los 4 hero cards del Performance Overview + sparkline.

**Response shape EXACTO:**
```json
{
  "active_pursuers": {
    "value": 847,
    "delta_7d": 12.4,
    "delta_label": "+12.4% vs last 7d"
  },
  "trophies_forged": {
    "value": 63,
    "delta_30d": 18.2,
    "sparkline": [2, 1, 3, 2, 4, 3, 5, 2, 3, 4, 6, 4, 5, 7, 3, 4, 5, 8, 6, 5, 7, 4, 6, 9, 5, 7, 8, 6, 9, 4]
  },
  "badges_granted": {
    "value": 2143,
    "delta_30d": 8.7
  },
  "cpt": {
    "locked": true,
    "label": "Coming soon",
    "tooltip": "Cost per trophy will be available with billing in v.3"
  }
}
```

**Lógica de queries:**
- `active_pursuers`: count distinct user_ids con interacción en trofeos del brand en los últimos 7 días (definir "interacción" = badge granted OR trophy forged OR pursuit started)
- `trophies_forged.value`: count en `trophy_user` donde trophy.brand_user_id = current_brand_id
- `trophies_forged.sparkline`: array de 30 enteros con count agrupado por día, los últimos 30 días, el más antiguo primero
- `badges_granted.value`: count en `badge_user` donde badge pertenece a un trofeo del brand
- `delta_*`: % de cambio vs período anterior (7d → 7d previos, 30d → 30d previos)

**Cache:** TTL 5 minutos, key `brand:{brand_id}:analytics:performance`

---

### 1.2.2 — `GET /api/brand/analytics/secondary-metrics`

**Propósito:** strip de 4 datos secundarios.

**Response shape EXACTO:**
```json
{
  "total_badges_granted": {
    "value": 2143,
    "label": "verified actions"
  },
  "cross_hall_overlap": [
    { "brand_username": "doritos", "overlap_percent": 43 },
    { "brand_username": "redbull", "overlap_percent": 28 },
    { "brand_username": "samsung", "overlap_percent": 12 }
  ],
  "multi_platform_users_percent": 58,
  "achievement_velocity": {
    "value": 3.4,
    "label": "per pursuer per day"
  }
}
```

**Lógica:**
- `cross_hall_overlap`: top 3 brands con mayor % de users compartidos. Query: users que tienen badge de current_brand AND badge de other_brand, dividido por users totales del current_brand
- `multi_platform_users_percent`: % de active_pursuers que tienen ≥2 platforms conectadas (`auth_integrations` con ≥2 providers distintos)
- `achievement_velocity`: badges_granted_30d / active_pursuers_30d / 30

**Cache:** TTL 5 minutos, key `brand:{brand_id}:analytics:secondary`

---

### 1.2.3 — `GET /api/brand/analytics/audience`

**Propósito:** 4 cards de Audience Intelligence.

**Response shape EXACTO:**
```json
{
  "platforms_breakdown": [
    { "platform": "steam", "user_count": 412, "percent": 48.6 },
    { "platform": "riot", "user_count": 287, "percent": 33.9 },
    { "platform": "discord", "user_count": 98, "percent": 11.6 },
    { "platform": "strava", "user_count": 50, "percent": 5.9 }
  ],
  "top_achievements": [
    { "badge_id": 123, "badge_name": "Diamond IV LoL", "grants": 342, "platform": "riot" },
    { "badge_id": 456, "badge_name": "100h CS:GO", "grants": 287, "platform": "steam" },
    { "badge_id": 789, "badge_name": "Discord Verified", "grants": 215, "platform": "discord" }
  ],
  "keywords_cross_discord": [
    { "keyword": "ranked", "mentions": 1432 },
    { "keyword": "competitive", "mentions": 891 },
    { "keyword": "tournament", "mentions": 654 }
  ],
  "funnel": {
    "started_pursuit": 1247,
    "earned_first_badge": 893,
    "forged_trophy": 63,
    "conversion_start_to_forge_percent": 5.05
  }
}
```

**Lógica:**
- `platforms_breakdown`: agrupar active_pursuers por su provider primario en `auth_integrations`
- `top_achievements`: badges del brand con más grants, top 3
- `keywords_cross_discord`: si el brand tiene Discord conectado, top 3 keywords de mensajes en sus canales (LIKE básico, sin NLP). Si no tiene Discord conectado, devolver `[]` y el frontend muestra estado vacío
- `funnel`: 3 etapas con counts absolutos. **NO incluir `viewed_trophy`** — no hay tracking de pageviews de trophy detail en v.2. El funnel arranca desde `started_pursuit`. Conversion final = forged / started * 100

**Cache:** TTL 5 minutos, key `brand:{brand_id}:analytics:audience`

---

### 1.2.4 — `GET /api/brand/analytics/campaigns`

**Propósito:** lista de "campaigns" (= trofeos del brand) para la tabla del dashboard.

**Query params:** `?sort=created_at|pursuers|forges|conversion` (default: `created_at desc`)

**Response shape EXACTO:**
```json
{
  "data": [
    {
      "trophy_id": 42,
      "name": "Domina LoL",
      "status": "active",
      "created_at": "2026-04-12T14:30:00Z",
      "pursuers": 412,
      "forges": 28,
      "conversion_percent": 6.79,
      "thumbnail_url": "https://..."
    }
  ],
  "meta": {
    "total": 7,
    "per_page": 10,
    "current_page": 1
  }
}
```

**Lógica:**
- `pursuers`: count distinct users con badge granted del trophy
- `forges`: count en `trophy_user` para ese trophy
- `conversion_percent`: forges / pursuers * 100
- `status`: derivado del trophy actual (`active` si tiene `published_at` no null Y no expirado, `draft` si null, `expired` si pasó deadline)

**Cache:** TTL 2 minutos (más frecuente porque la tabla se ve más seguido), key `brand:{brand_id}:analytics:campaigns:{sort}`

---

### 1.2.5 — `GET /api/brand/analytics/activity`

**Propósito:** activity feed cronológico de los últimos eventos.

**Query params:** `?limit=20` (default 20, max 50)

**Response shape EXACTO:**
```json
{
  "data": [
    {
      "id": "evt_xyz",
      "type": "trophy_forged",
      "icon": "T",
      "actor": { "username": "faraday", "avatar_url": "https://..." },
      "target": { "trophy_name": "Domina LoL", "trophy_id": 42 },
      "timestamp": "2026-05-05T13:45:00Z",
      "human_time": "2 min ago"
    },
    {
      "id": "evt_abc",
      "type": "badge_granted",
      "icon": "B",
      "actor": { "username": "jdoe", "avatar_url": "https://..." },
      "target": { "badge_name": "Diamond IV", "platform": "riot" },
      "timestamp": "2026-05-05T13:30:00Z",
      "human_time": "17 min ago"
    }
  ]
}
```

**Tipos de eventos:**
- `trophy_forged` — alguien completó un trofeo del brand
- `badge_granted` — alguien obtuvo un badge asociado a trofeos del brand
- `pursuer_started` — alguien empezó pursuit de un trofeo
- `cross_hall_hit` — un user del brand también obtuvo badge de otro brand (señal de overlap)

**Cache:** TTL 30 segundos (queremos sensación de "live"), key `brand:{brand_id}:analytics:activity:{limit}`

---

## 1.3 — Rutas

**Archivo:** `routes/api.php`

**Agregar:**
```php
use App\Http\Controllers\Api\Brand\BrandAnalyticsController;

Route::middleware(['auth:sanctum'])->prefix('brand/analytics')->group(function () {
    Route::get('/performance', [BrandAnalyticsController::class, 'performance']);
    Route::get('/secondary-metrics', [BrandAnalyticsController::class, 'secondaryMetrics']);
    Route::get('/audience', [BrandAnalyticsController::class, 'audience']);
    Route::get('/campaigns', [BrandAnalyticsController::class, 'campaigns']);
    Route::get('/activity', [BrandAnalyticsController::class, 'activity']);
});
```

**Authorization en controller (cada método):**
```php
if ($user->account_type !== 'brand' && !$user->hasAnyRole(['tr_admin', 'tr_superadmin'])) {
    return response()->json(['error' => 'Forbidden'], 403);
}
```

---

## 1.4 — Índices necesarios

Antes de cerrar el bloque, agregar índices si no existen:

```php
// En migration aparte si no existen ya:
Schema::table('badge_user', function (Blueprint $table) {
    $table->index(['badge_id', 'created_at']);
    $table->index(['user_id', 'created_at']);
});

Schema::table('trophy_user', function (Blueprint $table) {
    $table->index(['trophy_id', 'created_at']);
});
```

**Verificar primero si ya existen** con `SHOW INDEXES FROM badge_user;` antes de crear duplicados.

---

## 1.5 — `BrandStatsController` viejo

**NO BORRAR todavía.** Marcarlo como deprecated:

```php
/**
 * @deprecated Use BrandAnalyticsController. To be removed once dashboard v.2 is green in production.
 */
class BrandStatsController extends Controller
```

Se borra al final del bloque 4, no ahora.

---

## ✅ VERIFICACIÓN BLOQUE 1

Antes de commit final del bloque:

1. [ ] `php artisan migrate` corrió clean en local
2. [ ] `curl` con token a cada uno de los 5 endpoints devuelve 200 con shape correcto (al menos para el user de testing brand)
3. [ ] `curl` sin token devuelve 401
4. [ ] `curl` con token de player (no brand, no staff) devuelve 403
5. [ ] `curl` con token de staff (rol tr_admin) devuelve 200 aunque `account_type = player`
6. [ ] Cache funciona: segunda llamada idéntica responde más rápido (verificar con timing)
7. [ ] `php artisan route:list | grep brand/analytics` muestra las 5 rutas

**Commit final del bloque:**
```
feat(brand-analytics): new BrandAnalyticsController with 5 endpoints

- POST campaign_id nullable hook on trophies
- 5 analytics endpoints under /api/brand/analytics/*
- Cache layer with 30s-5m TTLs by endpoint
- Staff role override for QA access
- BrandStatsController marked deprecated (to delete in bloque 4)
```

**Deploy backend a producción** después de este commit (frontend todavía no rompe nada porque no consume estos endpoints).

---

# BLOQUE 2 — MOCKS FRONTEND

## 2.1 — Por qué este bloque existe

El frontend del Bloque 4 se construye en paralelo conceptual al backend. Si el backend tiene un bug o lentitud, no queremos bloquear el desarrollo visual. Los mocks viven en el repo durante el desarrollo y **se borran antes del primer deploy a producción**.

## 2.2 — Archivo

**Archivo nuevo:** `resources/web/js/mocks/brandDashboard.js`

**Contenido:**
```javascript
/**
 * MOCK DATA — BRAND DASHBOARD v.2
 *
 * ⚠️  ESTE ARCHIVO SE BORRA ANTES DEL PRIMER DEPLOY A PRODUCCIÓN.
 * ⚠️  Se usa solo durante development cuando los endpoints reales
 *     están en construcción o no responden con datos suficientes.
 *
 * Para activar mocks en componente:
 *   import { mockPerformance } from '@/mocks/brandDashboard'
 *   const useMocks = import.meta.env.VITE_USE_BRAND_MOCKS === 'true'
 *
 * Para desactivar: borrar VITE_USE_BRAND_MOCKS de .env y este archivo entero.
 */

export const mockPerformance = {
  active_pursuers: { value: 847, delta_7d: 12.4, delta_label: '+12.4% vs last 7d' },
  trophies_forged: {
    value: 63,
    delta_30d: 18.2,
    sparkline: [2,1,3,2,4,3,5,2,3,4,6,4,5,7,3,4,5,8,6,5,7,4,6,9,5,7,8,6,9,4]
  },
  badges_granted: { value: 2143, delta_30d: 8.7 },
  cpt: { locked: true, label: 'Coming soon', tooltip: 'Cost per trophy in v.3' }
}

export const mockSecondaryMetrics = {
  total_badges_granted: { value: 2143, label: 'verified actions' },
  cross_hall_overlap: [
    { brand_username: 'doritos', overlap_percent: 43 },
    { brand_username: 'redbull', overlap_percent: 28 },
    { brand_username: 'samsung', overlap_percent: 12 }
  ],
  multi_platform_users_percent: 58,
  achievement_velocity: { value: 3.4, label: 'per pursuer per day' }
}

export const mockAudience = {
  platforms_breakdown: [
    { platform: 'steam', user_count: 412, percent: 48.6 },
    { platform: 'riot', user_count: 287, percent: 33.9 },
    { platform: 'discord', user_count: 98, percent: 11.6 },
    { platform: 'strava', user_count: 50, percent: 5.9 }
  ],
  top_achievements: [
    { badge_id: 123, badge_name: 'Diamond IV LoL', grants: 342, platform: 'riot' },
    { badge_id: 456, badge_name: '100h CS:GO', grants: 287, platform: 'steam' },
    { badge_id: 789, badge_name: 'Discord Verified', grants: 215, platform: 'discord' }
  ],
  keywords_cross_discord: [
    { keyword: 'ranked', mentions: 1432 },
    { keyword: 'competitive', mentions: 891 },
    { keyword: 'tournament', mentions: 654 }
  ],
  funnel: {
    started_pursuit: 1247,
    earned_first_badge: 893,
    forged_trophy: 63,
    conversion_start_to_forge_percent: 5.05
  }
}

export const mockCampaigns = {
  data: [
    { trophy_id: 42, name: 'Domina LoL', status: 'active', created_at: '2026-04-12T14:30:00Z', pursuers: 412, forges: 28, conversion_percent: 6.79, thumbnail_url: '' },
    { trophy_id: 43, name: 'Conecta Discord', status: 'active', created_at: '2026-04-08T09:15:00Z', pursuers: 287, forges: 215, conversion_percent: 74.91, thumbnail_url: '' },
    { trophy_id: 44, name: '100h en Steam', status: 'active', created_at: '2026-03-22T18:00:00Z', pursuers: 198, forges: 41, conversion_percent: 20.71, thumbnail_url: '' },
    { trophy_id: 45, name: 'Promo Verano LATAM', status: 'draft', created_at: '2026-05-01T10:00:00Z', pursuers: 0, forges: 0, conversion_percent: 0, thumbnail_url: '' },
    { trophy_id: 46, name: 'Ronda eSports Q1', status: 'expired', created_at: '2026-01-15T12:00:00Z', pursuers: 654, forges: 89, conversion_percent: 13.61, thumbnail_url: '' }
  ],
  meta: { total: 5, per_page: 10, current_page: 1 }
}

export const mockActivity = {
  data: [
    { id: 'evt_1', type: 'trophy_forged', icon: 'T', actor: { username: 'faraday', avatar_url: '' }, target: { trophy_name: 'Domina LoL', trophy_id: 42 }, timestamp: '2026-05-05T13:45:00Z', human_time: '2 min ago' },
    { id: 'evt_2', type: 'badge_granted', icon: 'B', actor: { username: 'jdoe', avatar_url: '' }, target: { badge_name: 'Diamond IV', platform: 'riot' }, timestamp: '2026-05-05T13:30:00Z', human_time: '17 min ago' },
    { id: 'evt_3', type: 'pursuer_started', icon: 'P', actor: { username: 'mariaSV', avatar_url: '' }, target: { trophy_name: 'Conecta Discord', trophy_id: 43 }, timestamp: '2026-05-05T13:12:00Z', human_time: '35 min ago' },
    { id: 'evt_4', type: 'cross_hall_hit', icon: 'X', actor: { username: 'gamer42', avatar_url: '' }, target: { other_brand: 'doritos' }, timestamp: '2026-05-05T12:48:00Z', human_time: '59 min ago' },
    { id: 'evt_5', type: 'trophy_forged', icon: 'T', actor: { username: 'pixelhunt', avatar_url: '' }, target: { trophy_name: '100h en Steam', trophy_id: 44 }, timestamp: '2026-05-05T11:30:00Z', human_time: '2h ago' },
    { id: 'evt_6', type: 'badge_granted', icon: 'B', actor: { username: 'kr1stal', avatar_url: '' }, target: { badge_name: 'Steam 100h', platform: 'steam' }, timestamp: '2026-05-05T10:15:00Z', human_time: '3h ago' },
    { id: 'evt_7', type: 'pursuer_started', icon: 'P', actor: { username: 'nokdmkc', avatar_url: '' }, target: { trophy_name: 'Domina LoL', trophy_id: 42 }, timestamp: '2026-05-05T09:42:00Z', human_time: '4h ago' },
    { id: 'evt_8', type: 'cross_hall_hit', icon: 'X', actor: { username: 'lisa_g', avatar_url: '' }, target: { other_brand: 'redbull' }, timestamp: '2026-05-05T08:30:00Z', human_time: '5h ago' },
    { id: 'evt_9', type: 'trophy_forged', icon: 'T', actor: { username: 'darko', avatar_url: '' }, target: { trophy_name: 'Conecta Discord', trophy_id: 43 }, timestamp: '2026-05-05T07:18:00Z', human_time: '6h ago' },
    { id: 'evt_10', type: 'badge_granted', icon: 'B', actor: { username: 'shen_ko', avatar_url: '' }, target: { badge_name: 'Discord Verified', platform: 'discord' }, timestamp: '2026-05-05T06:00:00Z', human_time: '8h ago' },
    { id: 'evt_11', type: 'pursuer_started', icon: 'P', actor: { username: 'velveth', avatar_url: '' }, target: { trophy_name: '100h en Steam', trophy_id: 44 }, timestamp: '2026-05-04T22:45:00Z', human_time: '15h ago' }
  ]
}
```

## 2.3 — Variable de entorno

**Archivo:** `.env.local` (NO `.env` de producción)

```
VITE_USE_BRAND_MOCKS=true
```

## 2.4 — Servicio API con switch

**Archivo nuevo:** `resources/web/js/services/brandAnalytics.js`

```javascript
import axios from 'axios'
import {
  mockPerformance,
  mockSecondaryMetrics,
  mockAudience,
  mockCampaigns,
  mockActivity
} from '@/mocks/brandDashboard'

const useMocks = import.meta.env.VITE_USE_BRAND_MOCKS === 'true'

const fakeDelay = (data, ms = 400) =>
  new Promise(resolve => setTimeout(() => resolve({ data }), ms))

export const fetchPerformance = () =>
  useMocks ? fakeDelay(mockPerformance) : axios.get('/api/brand/analytics/performance')

export const fetchSecondaryMetrics = () =>
  useMocks ? fakeDelay(mockSecondaryMetrics) : axios.get('/api/brand/analytics/secondary-metrics')

export const fetchAudience = () =>
  useMocks ? fakeDelay(mockAudience) : axios.get('/api/brand/analytics/audience')

export const fetchCampaigns = (sort = 'created_at') =>
  useMocks ? fakeDelay(mockCampaigns) : axios.get('/api/brand/analytics/campaigns', { params: { sort } })

export const fetchActivity = (limit = 20) =>
  useMocks ? fakeDelay(mockActivity) : axios.get('/api/brand/analytics/activity', { params: { limit } })
```

## ✅ VERIFICACIÓN BLOQUE 2

1. [ ] `resources/web/js/mocks/brandDashboard.js` existe y exporta los 5 mocks
2. [ ] `resources/web/js/services/brandAnalytics.js` existe y switchea por env var
3. [ ] `.env.local` tiene `VITE_USE_BRAND_MOCKS=true` (NO commitear `.env.local`)
4. [ ] `.env.example` documenta la variable
5. [ ] Importar en consola de un componente cualquiera y verificar que `fetchPerformance()` resuelve con mock data

**Commit:**
```
feat(brand-mocks): add temporary mock layer for dashboard development

⚠️  brandDashboard.js debe borrarse antes del primer deploy a producción.
   Variable VITE_USE_BRAND_MOCKS controla activación.
```

---

# BLOQUE 3 — SIDEBAR REFACTOR

## 3.1 — Estado actual

El sidebar ya tiene getters `isBrand` e `isStaff` en Vuex store. Lo que falta es:
- Guard para items player (Trophy Room, Forge, Achievements, Rewards) cuando `account_type = brand`
- Override por rol staff: si user tiene `tr_admin|tr_superadmin`, ve TODO independiente de account_type
- Sección "Coming soon" con Billing y AI Builder locked, solo visible para brand

## 3.2 — Componente

**Archivo:** `resources/web/js/components/Sidebar.vue` (path puede variar — confirmar con `grep -r "isBrand" resources/web/js/`)

**Lógica nueva en computed:**

```javascript
computed: {
  ...mapGetters(['user', 'isBrand', 'isStaff']),

  isStaffOverride() {
    return this.user?.roles?.some(r => ['tr_admin', 'tr_superadmin'].includes(r.name))
  },

  showPlayerItems() {
    // Player items: Trophy Room, Forge, Achievements, Rewards
    // Visible si NO es brand, OR si es staff (override)
    return !this.isBrand || this.isStaffOverride
  },

  showBrandItems() {
    // Brand items: Dashboard (brand), Studio, Brand Hall
    // Visible si es brand, OR si es staff (override)
    return this.isBrand || this.isStaffOverride
  },

  showBrandComingSoon() {
    // Billing, AI Builder (locked)
    // Solo visible si es brand puro (staff no necesita ver el locked)
    return this.isBrand && !this.isStaffOverride
  }
}
```

**Template (estructura aproximada, adaptar a estilo existente):**

```vue
<template>
  <aside class="sidebar">
    <div class="sidebar-logo">...</div>

    <!-- BRAND SECTION -->
    <template v-if="showBrandItems">
      <div class="nav-section-label">Brand</div>
      <router-link to="/dashboard" class="nav-item">Dashboard</router-link>
      <router-link to="/studio" class="nav-item">Studio</router-link>
      <router-link to="/feed" class="nav-item">Feed</router-link>
      <router-link :to="`/${user.username}`" class="nav-item">Brand Hall</router-link>
    </template>

    <!-- PLAYER SECTION -->
    <template v-if="showPlayerItems">
      <div class="nav-section-label">Player</div>
      <router-link to="/dashboard" class="nav-item" v-if="!isBrand">Dashboard</router-link>
      <router-link to="/trophy-room" class="nav-item">Trophy Room</router-link>
      <router-link to="/forge" class="nav-item">Forge</router-link>
      <router-link to="/achievements" class="nav-item">Achievements</router-link>
      <router-link to="/rewards" class="nav-item">Rewards</router-link>
      <router-link to="/feed" class="nav-item" v-if="!isBrand">Feed</router-link>
    </template>

    <!-- COMING SOON (brand only, no staff) -->
    <template v-if="showBrandComingSoon">
      <div class="nav-section-label">Coming soon</div>
      <a class="nav-item locked"><span>Billing</span></a>
      <a class="nav-item locked"><span>AI Builder</span></a>
    </template>

    <!-- STAFF BADGE -->
    <div v-if="isStaffOverride" class="staff-badge">
      ◆ STAFF VIEW · seeing all sections
    </div>

    <!-- ACCOUNT PILL existente -->
    <div class="acct-pill">...</div>
  </aside>
</template>
```

## 3.3 — Router (`/dashboard` resolver)

**Esta sección requiere discovery antes de implementar. No improvisar.**

### 3.3.1 — Discovery obligatorio

Antes de tocar router, ejecutar y reportar al supervisor (este Claude) ANTES de seguir:

```bash
# 1. Encontrar el archivo de router
find resources/web/js -name "*.js" -path "*router*" 2>/dev/null
find resources/web/js -name "router.js" 2>/dev/null
find resources/web/js -name "index.js" -path "*router*" 2>/dev/null

# 2. Ver cómo está declarada la ruta /dashboard hoy
grep -rn "path.*dashboard" resources/web/js/router/ 2>/dev/null
grep -rn "DashboardOverview" resources/web/js/router/ 2>/dev/null

# 3. Verificar versión de Vue Router en uso
grep "vue-router" package.json
```

Reportar al supervisor:
- Path exacto del router
- Cómo está declarada `/dashboard` actualmente (component import, lazy, named, etc.)
- Versión de vue-router (3.x o 4.x — Vue 3 con Options API puede estar usando vue-router 4 OR vue-router 3 con compat layer)
- Si hay otros guards/middleware activos en esa ruta

### 3.3.2 — Decisión post-discovery

**El supervisor (Claude chat) decide cuál de estos tres patrones usar basándose en el output del discovery. NO elegir sin esa decisión.**

Las tres opciones posibles (referencia, no a implementar):

- **Opción A — Component dinámico inline:** función que retorna import dinámico según `account_type`. Funciona en vue-router 4. Requiere acceso al store antes del render.
- **Opción B — Beforeenter redirect:** `/dashboard` redirige a `/dashboard/brand` o `/dashboard/player` según account_type, y son dos rutas físicas distintas.
- **Opción C — Mismo componente, render condicional interno:** mantener una sola ruta `/dashboard`, usar `DashboardOverview.vue` como wrapper que internamente renderiza `<BrandDashboard />` o `<PlayerDashboardContent />` según `user.account_type`.

**Mi sospecha (a confirmar con discovery):** opción C es la que menos rompe lo existente, porque no tocás el router en absoluto — solo metés un `v-if` en el componente de dashboard. Pero hasta no ver el archivo, no decido.

### 3.3.3 — Implementar

Después de la decisión del supervisor, implementar el patrón aprobado y commitear con mensaje descriptivo de qué patrón se eligió y por qué.

## ✅ VERIFICACIÓN BLOQUE 3

1. [ ] User player NO ve Studio, Brand Hall (en sidebar)
2. [ ] User brand NO ve Trophy Room, Forge, Achievements, Rewards
3. [ ] User brand SÍ ve "Coming soon" con Billing y AI Builder locked
4. [ ] User staff (rol tr_admin) con account_type=player ve TODO el sidebar
5. [ ] User staff con account_type=brand ve TODO menos "Coming soon" (porque tiene override)
6. [ ] `/dashboard` resuelve a BrandDashboard si user es brand, DashboardOverview si player
7. [ ] El staff badge aparece solo para staff override
8. [ ] No hay regresión en navegación player existente

**Commit:**
```
refactor(sidebar): account_type guards with staff role override

- Player items hidden for brand accounts
- Brand items hidden for player accounts
- tr_admin and tr_superadmin see both sections (staff override)
- Locked items (Billing, AI Builder) only for pure brand accounts
- /dashboard route resolves by account_type
```

---

# BLOQUE 4 — BrandDashboard.vue + COMPONENTES HIJOS

## 4.1 — Estructura de archivos

```
resources/web/js/pages/
├── BrandDashboard.vue                          # Container principal
└── components/brand-dashboard/
    ├── PerformanceOverview.vue                 # 4 hero cards + sparkline
    ├── SecondaryMetricsStrip.vue               # Strip de 4 datos
    ├── AudienceIntelligence.vue                # 4 cards de audience
    ├── CampaignsTable.vue                      # Tabla izquierda (1.6fr)
    ├── ActivityFeed.vue                        # Feed derecha (1fr)
    └── LockedProFeatures.vue                   # CTA final
```

## 4.2 — Orden de implementación

**Implementar EN ESTE ORDEN, commit individual por componente:**

1. `BrandDashboard.vue` (container vacío con grid layout)
2. `PerformanceOverview.vue` (sin sparkline real, placeholder de div)
3. `PerformanceOverview.vue` (con sparkline real usando SVG inline o lib existente)
4. `SecondaryMetricsStrip.vue`
5. `AudienceIntelligence.vue`
6. `CampaignsTable.vue`
7. `ActivityFeed.vue`
8. `LockedProFeatures.vue`
9. Pulido visual + responsive + estados de loading/error

**Cada componente debe:**
- Importar su fetcher desde `@/services/brandAnalytics`
- Manejar estados: loading, error, empty, success
- Usar variables CSS del design system existente (`--bg`, `--accent`, `--primary`)
- Coincidir con el mockup HTML 1:1 visualmente

## 4.3 — `BrandDashboard.vue` (container)

```vue
<template>
  <div class="brand-dashboard">
    <header class="brand-dashboard-header">
      <h1>Dashboard</h1>
      <div class="session-info">@{{ user.username }} brand session</div>
    </header>

    <PerformanceOverview />
    <SecondaryMetricsStrip />
    <AudienceIntelligence />

    <div class="dual-row">
      <CampaignsTable class="campaigns-col" />
      <ActivityFeed class="activity-col" />
    </div>

    <LockedProFeatures />
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import PerformanceOverview from './components/brand-dashboard/PerformanceOverview.vue'
import SecondaryMetricsStrip from './components/brand-dashboard/SecondaryMetricsStrip.vue'
import AudienceIntelligence from './components/brand-dashboard/AudienceIntelligence.vue'
import CampaignsTable from './components/brand-dashboard/CampaignsTable.vue'
import ActivityFeed from './components/brand-dashboard/ActivityFeed.vue'
import LockedProFeatures from './components/brand-dashboard/LockedProFeatures.vue'

export default {
  name: 'BrandDashboard',
  components: {
    PerformanceOverview,
    SecondaryMetricsStrip,
    AudienceIntelligence,
    CampaignsTable,
    ActivityFeed,
    LockedProFeatures
  },
  computed: {
    ...mapGetters(['user'])
  }
}
</script>

<style scoped>
.brand-dashboard {
  padding: 24px 32px;
  max-width: 1400px;
  margin: 0 auto;
}

.dual-row {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 16px;
  margin: 24px 0;
}

@media (max-width: 1024px) {
  .dual-row {
    grid-template-columns: 1fr;
  }
}
</style>
```

## 4.4 — Especificación por componente

**Para cada uno de los 6 componentes hijos:** abrir `brand-dashboard-mockup-v2.html`, identificar la sección correspondiente, replicar HTML/CSS adaptado a Vue. **No reinventar el visual — el mockup es source of truth.**

Notas específicas:

### `PerformanceOverview.vue`
- 4 cards en grid `repeat(4, 1fr)` con gap 16px
- Sparkline en card 2: SVG inline con path `polyline`, glow chartreuse, dot final con `<circle>` destacado
- Card 4 (CPT) tiene clase `locked` con borde dashed y label "Coming soon"
- En mobile: grid 2x2

### `SecondaryMetricsStrip.vue`
- Strip horizontal con 4 grupos separados por divider vertical
- En mobile: stack vertical
- `cross_hall_overlap` muestra los top 3 brand inline: `@doritos 43% · @redbull 28% · @samsung 12%`

### `AudienceIntelligence.vue`
- 4 cards en grid `repeat(4, 1fr)`
- `platforms_breakdown`: bar chart horizontal con 4 filas (steam/riot/discord/strava), barra rellena por percent
- `top_achievements`: lista ordenada con badge name + grants count
- `keywords_cross_discord`: 3 chips con keyword + mentions. Si `[]`, mostrar mensaje "Connect Discord to unlock"
- `funnel`: 3 etapas verticales con barra que decrece (started → earned_first_badge → forged), conversion final destacada

### `CampaignsTable.vue`
- Tabla con columnas: thumbnail | name | status (chip) | pursuers | forges | conversion %
- Sortable por click en header (toggle asc/desc)
- Status chip: `active` chartreuse, `draft` gris, `expired` rojizo
- Paginación si más de 10 (pero v.2 muestra solo top 10 sin paginar)

### `ActivityFeed.vue`
- Lista vertical scrollable, max-height 600px
- Cada item: icon + actor + acción + timestamp
- Auto-refresh cada 30s (consistente con cache TTL backend)
- Loading state: skeleton de 5 items

### `LockedProFeatures.vue`
- Sección con 6 chips locked: Real CPT · Audience targeting · AI builder · Sector benchmarking · Scheduled launches · Custom reports
- CTA "Contact us to activate" → mailto o link a form
- Visual: borde dashed accent, opacity reducido

## 4.5 — Cleanup final

Cuando los 6 componentes estén verdes:

1. **Borrar `BrandStatsController` viejo:**
   ```bash
   rm app/Http/Controllers/Api/BrandStatsController.php
   # Y rutas asociadas en routes/api.php
   ```

2. **Borrar mocks:**
   ```bash
   rm resources/web/js/mocks/brandDashboard.js
   ```

3. **Actualizar service para que no importe mocks:**
   ```javascript
   // resources/web/js/services/brandAnalytics.js
   import axios from 'axios'

   export const fetchPerformance = () => axios.get('/api/brand/analytics/performance')
   // ... resto sin switch
   ```

4. **Borrar `VITE_USE_BRAND_MOCKS` de `.env.local` y `.env.example`**

5. **Verificar grep:** `grep -r "VITE_USE_BRAND_MOCKS\|brandDashboard.js\|mockPerformance" resources/` debe devolver vacío

## ✅ VERIFICACIÓN BLOQUE 4

1. [ ] BrandDashboard.vue carga sin errores en consola
2. [ ] Los 6 componentes hijos renderizan
3. [ ] Cada componente tiene loading + error + empty states
4. [ ] Visual coincide con mockup HTML (revisar lado a lado)
5. [ ] Responsive funciona (mobile <1024px stack vertical)
6. [ ] Con `VITE_USE_BRAND_MOCKS=true` muestra mocks
7. [ ] Con `VITE_USE_BRAND_MOCKS=false` consume backend real
8. [ ] Activity feed se auto-refresca cada 30s
9. [ ] Campaigns table sortable funcional
10. [ ] Cleanup completado: mocks borrados, BrandStatsController borrado, env var borrada

**Commit final del bloque:**
```
feat(brand-dashboard-v2): full UI implementation + cleanup

- 6 child components matching approved mockup
- Loading/error/empty states throughout
- Auto-refresh on activity feed (30s)
- Sortable campaigns table
- Responsive breakpoints
- Removed deprecated BrandStatsController
- Removed mock layer (VITE_USE_BRAND_MOCKS)
```

**Deploy a producción.**

---

# CIERRE — DEPLOY FINAL

Después del Bloque 4:

1. `git checkout main && git merge feature/brand-dashboard-v2`
2. SSH a producción
3. `cd /var/www/ambar && git reset --hard origin/main`
4. `composer install --no-dev --optimize-autoloader`
5. `php artisan migrate --force`
6. `php artisan config:cache && php artisan route:cache`
7. `npm install && npm run build`
8. Verificación en navegador con cuenta `@hepamida` (brand) y cuenta `@faraday` (player)
9. Verificación con cuenta staff (debe ver ambos sidebars)

## CRITERIOS DE ÉXITO FINALES

- Brand login → ve dashboard con datos reales (no mocks) en <2 segundos
- Player login → cero regresión, ve su dashboard player como antes
- Staff login → ve todo el sidebar, puede hacer QA de ambos flujos
- Mockup HTML y producción son visualmente equivalentes
- Console: cero errores
- Network tab: cada endpoint responde 200, segunda llamada idéntica usa cache (<50ms)

---

**FIN DEL BRIEF**

Reportar al supervisor (este Claude) cualquier divergencia con la tesis o con el mockup antes de implementar workarounds.

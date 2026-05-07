# BRAND DASHBOARD TESTING SEEDER — Instrucciones

**Archivo:** `database/seeders/BrandDashboardTestingSeeder.php`
**Uso:** garantizar data realista en local para testear los 5 endpoints de `BrandAnalyticsController` durante development.

⚠️ **No correr en producción.** El seeder tiene un guard que aborta si detecta `APP_ENV=production`, pero la regla operativa es: este archivo solo existe para development.

---

## Qué crea

| Recurso | Cantidad | Notas |
|---|---|---|
| Brand principal | 1 (`@testbrand`) | account_type=brand, verified, accent_color #ff6100 |
| Cross-brands | 3 (`@doritos_test`, `@redbull_test`, `@samsung_test`) | Solo para que `cross_hall_overlap` tenga data |
| Players | 50 (`@tp_001` a `@tp_050`) | account_type=player |
| Trophies del brand | 4 (3 active + 1 draft) | Domina LoL · Conecta Discord · 100h en Steam · Promo Verano LATAM |
| Badges | 8 | Variedad de plataformas (steam, riot, discord) |
| Badge grants | ~250 | Distribuidos en últimos 30 días con tendencia ascendente que matchea el sparkline del mockup |
| Trophy forges | ~85 | Weighted: Conecta Discord 41, Domina LoL 28, 100h Steam 16 |
| auth_integrations | ~80 registros | 48% steam, 34% riot, 12% discord, 6% strava. ~58% multi-platform |
| Cross-hall overlap | Real | 43% players también tienen badge de @doritos_test, 28% de @redbull_test, 12% de @samsung_test |

---

## Cómo correrlo

### 1. Copiar el archivo al proyecto

```bash
cp /path/to/BrandDashboardTestingSeeder.php database/seeders/BrandDashboardTestingSeeder.php
```

### 2. Verificar que `APP_ENV` no es `production`

```bash
grep APP_ENV .env
# Debe decir: APP_ENV=local (o development, staging, etc.)
```

### 3. Verificar que las migraciones están al día

```bash
php artisan migrate:status
# Si falta alguna, correrlas primero
```

**Importante:** la migration de `campaign_id` (Bloque 1, paso 1.1 del brief) debe estar aplicada antes, porque el seeder seta `campaign_id => null` explícitamente.

### 4. Correr el seeder

```bash
php artisan db:seed --class=BrandDashboardTestingSeeder
```

Output esperado:
```
🌱 Seeding Brand Dashboard testing data...
✓ Brand creado: @testbrand (id: XX)
✓ Cross-brands creados: doritos_test, redbull_test, samsung_test
✓ Players creados: 50
✓ auth_integrations seeded (mezcla steam/riot/discord/strava)
✓ Trophies creados: 4 (3 active + 1 draft)
✓ Badges creados: 8
✓ Badge grants seeded: ~250
✓ Trophy forges seeded: ~85
✓ Cross-hall overlap seeded
═══════════════════════════════════════════════════════
✅ SEEDING COMPLETO
═══════════════════════════════════════════════════════
Brand de testing: @testbrand
Email: testbrand@trophyroom.local
Password: testbrand123
```

### 5. Probar endpoints

```bash
# Login con el brand
TOKEN=$(curl -s -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"testbrand@trophyroom.local","password":"testbrand123"}' \
  | jq -r '.token')

# Probar cada endpoint
curl -s http://localhost/api/brand/analytics/performance \
  -H "Authorization: Bearer $TOKEN" | jq .

curl -s http://localhost/api/brand/analytics/secondary-metrics \
  -H "Authorization: Bearer $TOKEN" | jq .

curl -s http://localhost/api/brand/analytics/audience \
  -H "Authorization: Bearer $TOKEN" | jq .

curl -s http://localhost/api/brand/analytics/campaigns \
  -H "Authorization: Bearer $TOKEN" | jq .

curl -s http://localhost/api/brand/analytics/activity \
  -H "Authorization: Bearer $TOKEN" | jq .
```

---

## Cómo limpiar

### Opción A — Reset completo (recomendado)

```bash
php artisan migrate:fresh
# Después correr seeders base si los hay
```

### Opción B — Borrar solo data del seeder

```sql
-- Conectarse a MySQL local
DELETE FROM badge_user WHERE user_id IN (
  SELECT id FROM users WHERE username LIKE 'tp_%' OR username = 'testbrand'
);
DELETE FROM trophy_user WHERE user_id IN (
  SELECT id FROM users WHERE username LIKE 'tp_%' OR username = 'testbrand'
);
DELETE FROM trophy_badge WHERE trophy_id IN (
  SELECT id FROM trophies WHERE user_id IN (
    SELECT id FROM users WHERE username = 'testbrand'
       OR username LIKE '%_test'
  )
);
DELETE FROM trophies WHERE user_id IN (
  SELECT id FROM users WHERE username = 'testbrand' OR username LIKE '%_test'
);
DELETE FROM badges WHERE name LIKE 'Cross badge%' OR name IN (
  'Diamond IV LoL', 'Master Tier LoL', 'Discord Verified',
  '100h Steam', '500h Steam', 'Steam Achievement Hunter',
  'LoL Ranked Win', 'Discord 30d Active'
);
DELETE FROM auth_integrations WHERE user_id IN (
  SELECT id FROM users WHERE username LIKE 'tp_%'
);
DELETE FROM users WHERE username LIKE 'tp_%' OR username = 'testbrand'
                     OR username LIKE '%_test';
```

---

## Ajustes que pueden ser necesarios

El seeder asume cierta estructura de DB. Si Claude Code corre el seeder y falla, los puntos más probables de ajuste son:

### 1. Tabla `auth_integrations`
El seeder asume columnas: `user_id`, `provider`, `provider_user_id`, `created_at`, `updated_at`.
Si la tabla real tiene más columnas con `NOT NULL` (tipo `access_token`, `refresh_token`), agregar valores fake en el método `insertAuthIntegration()`.

### 2. Tabla `trophies` — campo `user_id` vs `brand_user_id`
El seeder asume que el "dueño" del trophy se guarda en `trophies.user_id`. Si el campo real se llama `brand_user_id` o `brand_id`, ajustar en `createTrophies()` y `seedCrossHallOverlap()`.

### 3. Pivot `trophy_badge`
El seeder asume que existe la tabla `trophy_badge` con `trophy_id` y `badge_id`. Si el nombre del pivot es distinto (ej. `badge_trophy`), ajustar.

### 4. Campo `status` en trophies
El seeder seta `status => 'active'|'draft'`. Si el modelo no tiene ese campo y deriva el estado de `published_at`, eliminar la línea `'status' => $data['status']`.

### 5. Campo `accent_color`, `tagline`, `is_featured`, `verified_at` en users
Si alguno de estos no existe en `users`, comentarlo en `createTestBrand()`.

**Si Claude Code se topa con cualquiera de estos casos al correr el seeder:**
1. Reportar al supervisor (este Claude) qué falló
2. Mostrar el `\Schema::getColumnListing('tabla')` o el output del error
3. Esperar instrucciones — NO improvisar fixes

---

## Cuándo borrar el seeder

El archivo `BrandDashboardTestingSeeder.php` se borra cuando:
1. El dashboard v.2 está deployado a producción
2. La cuenta `@hepamida` real tiene suficiente data como para validar visualmente los componentes
3. Ya no necesitamos un environment de development fresco

Hasta entonces, el seeder vive en `database/seeders/` y no se llama desde `DatabaseSeeder.php` (es opt-in: solo corre si lo invocás explícito).

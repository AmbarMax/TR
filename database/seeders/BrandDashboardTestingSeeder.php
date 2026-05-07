<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Trophy;
use App\Models\Badge;
use App\Models\AuthIntegration;
use App\Models\Integration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * BRAND DASHBOARD V.2 — TESTING SEEDER
 *
 * ⚠️  USO LOCAL SOLAMENTE. NO CORRER EN PRODUCCIÓN.
 *
 * Este seeder garantiza que los 5 endpoints de BrandAnalyticsController
 * devuelvan data realista durante el development del dashboard. Sin esto,
 * los endpoints devuelven [], 0 o null y no se puede validar que el
 * frontend renderice correctamente.
 *
 * Crea:
 * - 1 brand de testing (@testbrand)
 * - 50 players con account_type='player'
 * - 4 trophies del brand (3 active, 1 draft)
 * - 8 badges asociados (variedad de plataformas)
 * - ~250 badge grants distribuidos en los últimos 30 días
 * - ~85 trophy forges distribuidos en los últimos 30 días
 * - 3 brands extra para cross_hall_overlap (@doritos_test, @redbull_test, @samsung_test)
 * - Overlap real entre @testbrand y los otros 3
 * - auth_integrations con providers variados (steam, riot, discord, strava)
 *
 * Para correr:
 *   php artisan db:seed --class=BrandDashboardTestingSeeder
 *
 * Para limpiar (volver a estado base):
 *   php artisan db:seed --class=BrandDashboardTestingSeeder --option=cleanup
 *   (o reiniciar la DB con migrate:fresh)
 *
 * SAFETY CHECK: el seeder aborta si detecta APP_ENV=production.
 */
class BrandDashboardTestingSeeder extends Seeder
{
    private const TEST_BRAND_USERNAME = 'testbrand';
    private const TEST_PLAYER_PREFIX = 'tp_'; // testing player prefix
    private const CROSS_BRANDS = ['doritos_test', 'redbull_test', 'samsung_test'];

    public function run(): void
    {
        // ============================================================
        // SAFETY: Nunca correr en producción
        // ============================================================
        if (app()->environment('production')) {
            $this->command->error('🛑 ABORT: BrandDashboardTestingSeeder no debe correrse en producción.');
            return;
        }

        // Requiere que IntegrationSeeder haya corrido antes. Es un paso de
        // setup separado por diseño: no queremos side-effects del seeder de
        // testing sobre data estable.
        $expected = ['github', 'discord', 'steam', 'riot', 'strava'];
        $existing = Integration::pluck('name')->toArray();
        $missing = array_diff($expected, $existing);
        if (!empty($missing)) {
            $this->command->error('🛑 Faltan integrations: ' . implode(', ', $missing));
            $this->command->error('Corré primero: php artisan db:seed --class=IntegrationSeeder');
            return;
        }

        // Requiere que el rol Spatie brand_admin exista. Sin esto, el assignRole
        // de los brands de testing falla con un error críptico de Spatie.
        if (!\Spatie\Permission\Models\Role::where('name', 'brand_admin')->exists()) {
            $this->command->error('🛑 Falta el rol Spatie "brand_admin".');
            $this->command->error('Corré primero los seeders de roles (probablemente RolePermissionSeeder o similar).');
            return;
        }

        $this->command->info('🌱 Seeding Brand Dashboard testing data...');

        DB::beginTransaction();
        try {
            // ============================================================
            // 1. CREAR BRAND PRINCIPAL DE TESTING
            // ============================================================
            $brand = $this->createTestBrand();
            $this->command->info("✓ Brand creado: @{$brand->username} (id: {$brand->id})");

            // ============================================================
            // 2. CREAR 3 BRANDS EXTRA PARA CROSS-HALL OVERLAP
            // ============================================================
            $crossBrands = collect(self::CROSS_BRANDS)->map(function ($username) {
                return $this->createCrossBrand($username);
            });
            $this->command->info("✓ Cross-brands creados: " . $crossBrands->pluck('username')->join(', '));

            // ============================================================
            // 3. CREAR 50 PLAYERS
            // ============================================================
            $players = $this->createPlayers(50);
            $this->command->info("✓ Players creados: {$players->count()}");

            // ============================================================
            // 4. ASIGNAR AUTH_INTEGRATIONS A PLAYERS (variedad de plataformas)
            // ============================================================
            $this->seedAuthIntegrations($players);
            $this->command->info("✓ auth_integrations seeded (mezcla steam/riot/discord/strava)");

            // ============================================================
            // 5. CREAR 4 TROPHIES DEL BRAND
            // ============================================================
            $trophies = $this->createTrophies($brand);
            $this->command->info("✓ Trophies creados: {$trophies->count()} (3 active + 1 draft)");

            // ============================================================
            // 6. CREAR 8 BADGES Y ASOCIARLOS A LOS TROPHIES
            // ============================================================
            $badges = $this->createBadges($trophies);
            $this->command->info("✓ Badges creados: {$badges->count()}");

            // ============================================================
            // 7. SEED BADGE GRANTS (~250 distribuidos en últimos 30 días)
            // ============================================================
            $this->seedBadgeGrants($players, $badges);
            $grantsCount = DB::table('badge_user')
                ->whereIn('badge_id', $badges->pluck('id'))
                ->count();
            $this->command->info("✓ Badge grants seeded: {$grantsCount}");

            // ============================================================
            // 8. SEED TROPHY FORGES (~85 distribuidos en últimos 30 días)
            // ============================================================
            $this->seedTrophyForges($players, $trophies);
            $forgesCount = DB::table('trophy_user')
                ->whereIn('trophy_id', $trophies->pluck('id'))
                ->count();
            $this->command->info("✓ Trophy forges seeded: {$forgesCount}");

            // ============================================================
            // 8b. SEED PURSUITS (~40 distribuidos en 30 días, solo trofeos activos)
            // ============================================================
            $this->seedPursuits($players, $trophies);
            $pursuitsCount = DB::table('pursuits')
                ->whereIn('trophy_id', $trophies->pluck('id'))
                ->count();
            $this->command->info("✓ Pursuits seeded: {$pursuitsCount}");

            // ============================================================
            // 9. SEED CROSS-HALL OVERLAP
            // (algunos players también tienen badges de otros brands)
            // ============================================================
            $this->seedCrossHallOverlap($players, $crossBrands);
            $this->command->info("✓ Cross-hall overlap seeded");

            DB::commit();

            $this->command->info('');
            $this->command->info('═══════════════════════════════════════════════════════');
            $this->command->info('✅ SEEDING COMPLETO');
            $this->command->info('═══════════════════════════════════════════════════════');
            $this->command->info("Brand de testing: @{$brand->username}");
            $this->command->info("Email: {$brand->email}");
            $this->command->info("Password: testbrand123");
            $this->command->info('');
            $this->command->info('Endpoints a probar:');
            $this->command->info('  GET /api/brand/analytics/performance');
            $this->command->info('  GET /api/brand/analytics/secondary-metrics');
            $this->command->info('  GET /api/brand/analytics/audience');
            $this->command->info('  GET /api/brand/analytics/campaigns');
            $this->command->info('  GET /api/brand/analytics/activity');
            $this->command->info('═══════════════════════════════════════════════════════');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ ERROR durante seeding: {$e->getMessage()}");
            $this->command->error($e->getTraceAsString());
            throw $e;
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function createTestBrand(): User
    {
        $brand = User::updateOrCreate(
            ['username' => self::TEST_BRAND_USERNAME],
            [
                'name' => 'Test Brand',
                'email' => 'testbrand@trophyroom.local',
                'password' => Hash::make('testbrand123'),
                'account_type' => 'brand',
                'verified_at' => now(),
                'accent_color' => '#ff6100',
                'tagline' => 'Brand de testing para dashboard v.2',
                'is_featured' => true,
                'created_at' => now()->subMonths(3),
            ]
        );
        $brand->assignRole('brand_admin');
        return $brand;
    }

    private function createCrossBrand(string $username): User
    {
        $brand = User::updateOrCreate(
            ['username' => $username],
            [
                'name' => ucfirst(str_replace('_test', '', $username)),
                'email' => "{$username}@trophyroom.local",
                'password' => Hash::make('testpass123'),
                'account_type' => 'brand',
                'verified_at' => now(),
                'created_at' => now()->subMonths(rand(2, 6)),
            ]
        );
        $brand->assignRole('brand_admin');
        return $brand;
    }

    private function createPlayers(int $count)
    {
        $players = collect();
        for ($i = 1; $i <= $count; $i++) {
            $username = self::TEST_PLAYER_PREFIX . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
            $player = User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => "Test Player {$i}",
                    'email' => "{$username}@trophyroom.local",
                    'password' => Hash::make('testpass123'),
                    'account_type' => 'player',
                    'created_at' => now()->subDays(rand(1, 90)),
                ]
            );
            $players->push($player);
        }
        return $players;
    }

    private function seedAuthIntegrations($players): void
    {
        // Distribución objetivo (matchea el mockup):
        // steam: 48% (~24 players)
        // riot: 34% (~17 players)
        // discord: 12% (~6 players)
        // strava: 6% (~3 players)
        // Algunos players tienen ≥2 platforms (multi-platform metric = 58%)

        $providers = [
            'steam' => 24,
            'riot' => 17,
            'discord' => 6,
            'strava' => 3,
        ];

        $playerArray = $players->shuffle()->values();
        $idx = 0;

        // Asignar provider primario a cada uno
        foreach ($providers as $provider => $count) {
            for ($i = 0; $i < $count && $idx < $playerArray->count(); $i++) {
                $this->insertAuthIntegration($playerArray[$idx], $provider);
                $idx++;
            }
        }

        // ~58% de los players reciben un segundo provider (multi-platform)
        $multiPlatformCount = (int) round($players->count() * 0.58);
        $multiPlatformPlayers = $players->shuffle()->take($multiPlatformCount);

        foreach ($multiPlatformPlayers as $player) {
            $existingProvider = DB::table('auth_integrations')
                ->where('user_id', $player->id)
                ->value('name');

            if (!$existingProvider) continue;

            $alternatives = array_diff(['steam', 'riot', 'discord', 'strava'], [$existingProvider]);
            $newProvider = $alternatives[array_rand($alternatives)];

            // Secundaria: entre 1 semana y ~2 meses después de la primaria,
            // para que "primary provider by earliest created_at" sea unívoco.
            $this->insertAuthIntegration($player, $newProvider, rand(7, 60));
        }
    }

    private function insertAuthIntegration(User $user, string $provider, int $daysAfter = 0): void
    {
        // auth_integrations.id es UUID — usamos Eloquent para que el trait
        // UUID lo genere. integration_id queda null (decisión A2): los
        // queries del controller miran name, no FK.
        // $daysAfter > 0 simula que un user conectó su segunda plataforma
        // días/semanas después de la primera, lo que permite que el endpoint
        // /audience identifique correctamente el "primary provider" via
        // earliest created_at (default 0 = creado en $user->created_at).
        AuthIntegration::updateOrCreate(
            ['user_id' => $user->id, 'name' => $provider],
            [
                'integration_id' => null,
                'created_at' => $user->created_at->copy()->addDays($daysAfter),
                'updated_at' => now(),
            ]
        );
    }

    private function createTrophies(User $brand)
    {
        $trophyData = [
            [
                'name' => 'Domina LoL',
                'description' => 'Llegá a Diamond IV en League of Legends',
                'status' => 'active',
                'price' => 150,
                'receive' => 75,
                'created_at' => now()->subDays(23),
                'published_at' => now()->subDays(22),
            ],
            [
                'name' => 'Conecta Discord',
                'description' => 'Unite a nuestro servidor de Discord',
                'status' => 'active',
                'price' => 20,
                'receive' => 10,
                'created_at' => now()->subDays(27),
                'published_at' => now()->subDays(26),
            ],
            [
                'name' => '100h en Steam',
                'description' => 'Acumulá 100 horas en cualquier juego de Steam',
                'status' => 'active',
                'price' => 80,
                'receive' => 40,
                'created_at' => now()->subDays(44),
                'published_at' => now()->subDays(43),
            ],
            [
                'name' => 'Promo Verano LATAM',
                'description' => 'Próximamente — campaña en preparación',
                'status' => 'draft',
                'price' => 200,
                'receive' => 100,
                'created_at' => now()->subDays(4),
                'published_at' => null,
            ],
        ];

        $trophies = collect();
        foreach ($trophyData as $data) {
            // El flag 'status' del array $trophyData es solo contexto en
            // memoria (decisión A3): trophies no tiene columna status, el
            // estado se deriva en el controller a partir de published_at.
            $trophy = Trophy::updateOrCreate(
                ['name' => $data['name'], 'user_id' => $brand->id],
                [
                    'description' => $data['description'],
                    'user_id' => $brand->id,
                    'image' => '', // varchar NOT NULL; UI usa SVG placeholder cuando vacío
                    'price' => $data['price'],
                    'receive' => $data['receive'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['created_at'],
                    'published_at' => $data['published_at'],
                    'campaign_id' => null, // hook nullable, no se llena en seeder
                ]
            );
            $trophies->push($trophy);
        }

        return $trophies;
    }

    private function createBadges($trophies)
    {
        $badgeData = [
            ['name' => 'Diamond IV LoL', 'platform' => 'riot', 'trophy_idx' => 0],
            ['name' => 'Master Tier LoL', 'platform' => 'riot', 'trophy_idx' => 0],
            ['name' => 'Discord Verified', 'platform' => 'discord', 'trophy_idx' => 1],
            ['name' => '100h Steam', 'platform' => 'steam', 'trophy_idx' => 2],
            ['name' => '500h Steam', 'platform' => 'steam', 'trophy_idx' => 2],
            ['name' => 'Steam Achievement Hunter', 'platform' => 'steam', 'trophy_idx' => 2],
            ['name' => 'LoL Ranked Win', 'platform' => 'riot', 'trophy_idx' => 0],
            ['name' => 'Discord 30d Active', 'platform' => 'discord', 'trophy_idx' => 1],
        ];

        // Map platform name → integration UUID. Las 5 integrations son
        // requisito (verificado en el safety check al inicio del run()).
        $integrationIds = Integration::pluck('id', 'name')->toArray();

        $badges = collect();
        foreach ($badgeData as $data) {
            $trophy = $trophies[$data['trophy_idx']];
            $badge = Badge::updateOrCreate(
                ['name' => $data['name']],
                [
                    'integration_id' => $integrationIds[$data['platform']],
                    'type' => 0, // BadgeType::Common (no Eloquent cast en Badge model)
                    'description' => "Test badge: {$data['name']}",
                    'created_at' => $trophy->created_at,
                ]
            );

            // Asociar badge al trophy via pivot badge_trophy
            DB::table('badge_trophy')->updateOrInsert(
                ['trophy_id' => $trophy->id, 'badge_id' => $badge->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            $badges->push($badge);
        }

        return $badges;
    }

    private function seedBadgeGrants($players, $badges): void
    {
        // Distribución temporal: ~250 grants en 30 días, con tendencia ascendente
        // que matchee el sparkline del mockup [2,1,3,2,4,3,5,2,3,4,6,4,5,7,3,4,5,8,6,5,7,4,6,9,5,7,8,6,9,4]
        $dailyDistribution = [2,1,3,2,4,3,5,2,3,4,6,4,5,7,3,4,5,8,6,5,7,4,6,9,5,7,8,6,9,4];

        $playerArray = $players->shuffle()->values();
        $badgeArray = $badges->values();

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $grantsToday = $dailyDistribution[29 - $daysAgo] ?? 3;
            // Multiplicar para llegar a ~250 total (sparkline suma ~150)
            $grantsToday = (int) round($grantsToday * 1.7);

            for ($g = 0; $g < $grantsToday; $g++) {
                $player = $playerArray->random();
                $badge = $badgeArray->random();

                $grantedAt = now()->subDays($daysAgo)
                    ->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
                if ($grantedAt->isFuture()) $grantedAt = now();

                DB::table('badge_user')->insertOrIgnore([
                    'user_id' => $player->id,
                    'badge_id' => $badge->id,
                    'created_at' => $grantedAt,
                    'updated_at' => $grantedAt,
                ]);
            }
        }
    }

    private function seedTrophyForges($players, $trophies): void
    {
        // ~85 forges en 30 días con distribución ASCENDENTE en el tiempo
        // (días recientes tienen más forges que días antiguos). Esto da
        // shape al sparkline del Performance Overview del Brand Dashboard,
        // necesario para validar gradientes/glows/dot final del componente.
        // El draft 'Promo Verano LATAM' se filtra por nombre.
        $activeTrophies = $trophies->reject(fn($t) => $t->name === 'Promo Verano LATAM');

        $forgeWeights = [
            'Domina LoL' => 28,
            'Conecta Discord' => 41,
            '100h en Steam' => 16,
        ];

        // Distribución ascendente — mismo shape que seedBadgeGrants.
        // dayIdx=0 → hace 30 días (más antiguo); dayIdx=29 → hace 1 día.
        $dailyDistribution = [2,1,3,2,4,3,5,2,3,4,6,4,5,7,3,4,5,8,6,5,7,4,6,9,5,7,8,6,9,4];

        // Pool de días pesados: cada día aparece N veces donde N es su peso.
        // array_rand sobre este pool da distribución ascendente.
        $weightedDays = [];
        for ($dayIdx = 0; $dayIdx < 30; $dayIdx++) {
            $weight = $dailyDistribution[$dayIdx];
            for ($i = 0; $i < $weight; $i++) {
                $weightedDays[] = 30 - $dayIdx; // idx 0 → 30 días atrás, idx 29 → 1 día atrás
            }
        }

        $playerArray = $players->shuffle()->values();

        foreach ($activeTrophies as $trophy) {
            $count = $forgeWeights[$trophy->name] ?? 10;
            $usedPlayerIds = [];

            for ($i = 0; $i < $count; $i++) {
                // Cada player solo forja 1 vez el mismo trophy (defensa proactiva
                // contra el unique constraint en (user_id, trophy_id)).
                $available = $playerArray->whereNotIn('id', $usedPlayerIds);
                if ($available->isEmpty()) break;

                $player = $available->random();
                $usedPlayerIds[] = $player->id;

                $daysAgo = $weightedDays[array_rand($weightedDays)];
                $forgedAt = now()->subDays($daysAgo)
                    ->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
                if ($forgedAt->isFuture()) $forgedAt = now();

                DB::table('trophy_user')->insertOrIgnore([
                    'user_id' => $player->id,
                    'trophy_id' => $trophy->id,
                    'created_at' => $forgedAt,
                    'updated_at' => $forgedAt,
                ]);
            }
        }
    }

    private function seedPursuits($players, $trophies): void
    {
        // ~40 pursuits distribuidos entre players y los 3 trofeos activos.
        // El draft 'Promo Verano LATAM' no tiene pursuits (no está publicado).
        // pursuits ya tiene UNIQUE (user_id, trophy_id) en schema, usamos
        // insertOrIgnore para defenderse contra colisiones de random.
        $weights = [
            'Domina LoL' => 18,
            'Conecta Discord' => 14,
            '100h en Steam' => 8,
        ];

        $activeTrophies = $trophies->reject(fn($t) => $t->name === 'Promo Verano LATAM');

        foreach ($activeTrophies as $trophy) {
            $count = $weights[$trophy->name] ?? 5;

            for ($i = 0; $i < $count; $i++) {
                $player = $players->random();
                $startedAt = now()->subDays(rand(0, 29))
                    ->setTime(rand(0, 23), rand(0, 59), rand(0, 59));
                if ($startedAt->isFuture()) $startedAt = now();

                DB::table('pursuits')->insertOrIgnore([
                    'user_id' => $player->id,
                    'trophy_id' => $trophy->id,
                    'created_at' => $startedAt,
                    'updated_at' => $startedAt,
                ]);
            }
        }
    }

    private function seedCrossHallOverlap($players, $crossBrands): void
    {
        // Crear badges fake para los cross-brands y otorgárselos a algunos players de @testbrand
        // Distribución matchea el mockup: doritos 43%, redbull 28%, samsung 12%

        $overlapTargets = [
            'doritos_test' => 0.43,
            'redbull_test' => 0.28,
            'samsung_test' => 0.12,
        ];

        $steamIntegrationId = Integration::where('name', 'steam')->value('id');

        foreach ($overlapTargets as $brandUsername => $overlapPct) {
            $brand = $crossBrands->firstWhere('username', $brandUsername);
            if (!$brand) continue;

            // Crear 1 badge fake del cross-brand
            $crossBadge = Badge::updateOrCreate(
                ['name' => "Cross badge {$brandUsername}"],
                [
                    'integration_id' => $steamIntegrationId,
                    'type' => 0, // BadgeType::Common
                    'description' => "Cross-hall test badge for {$brandUsername}",
                ]
            );

            // Asociar este badge a un trophy fake del cross-brand
            $crossTrophy = Trophy::updateOrCreate(
                ['name' => "Cross trophy {$brandUsername}", 'user_id' => $brand->id],
                [
                    'description' => 'Cross-hall test trophy',
                    'user_id' => $brand->id,
                    'image' => '',
                    'price' => 50,
                    'receive' => 25,
                    'created_at' => now()->subDays(60),
                    'published_at' => now()->subDays(59),
                ]
            );

            DB::table('badge_trophy')->updateOrInsert(
                ['trophy_id' => $crossTrophy->id, 'badge_id' => $crossBadge->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Otorgar el badge a un % de players de @testbrand
            $overlapPlayerCount = (int) round($players->count() * $overlapPct);
            $overlapPlayers = $players->shuffle()->take($overlapPlayerCount);

            foreach ($overlapPlayers as $player) {
                $crossGrantedAt = now()->subDays(rand(1, 30));
                if ($crossGrantedAt->isFuture()) $crossGrantedAt = now();

                DB::table('badge_user')->insertOrIgnore([
                    'user_id' => $player->id,
                    'badge_id' => $crossBadge->id,
                    'created_at' => $crossGrantedAt,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

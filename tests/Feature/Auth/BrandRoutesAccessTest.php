<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BrandRoutesAccessTest extends TestCase
{
    use RefreshDatabase;

    // Subset of the 27 brand routes — one representative per controller,
    // covering all four verbs. Full dynamic coverage via route:list was
    // considered but path params (uuid placeholders) and body validation
    // add noise without changing the 403 signal we are testing here.
    private const BRAND_ENDPOINTS = [
        ['GET',    '/api/brand/stats'],
        ['GET',    '/api/brand/badges'],
        ['POST',   '/api/brand/badges'],
        ['GET',    '/api/brand/trophies'],
        ['POST',   '/api/brand/trophies'],
        ['DELETE', '/api/brand/trophies/11111111-1111-1111-1111-111111111111'],
        ['GET',    '/api/brand/polls'],
        ['GET',    '/api/brand/events'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function makeUserWithRole(string $role, string $accountType = 'player'): User
    {
        $user = User::factory()->create(['account_type' => $accountType]);
        $user->assignRole($role);
        return $user;
    }

    private function authHeaders(User $user): array
    {
        return [
            'Authorization' => 'Bearer ' . JWTAuth::fromUser($user),
            'Accept'        => 'application/json',
        ];
    }

    public function test_member_cannot_access_brand_routes(): void
    {
        $member = $this->makeUserWithRole('member');
        $headers = $this->authHeaders($member);

        foreach (self::BRAND_ENDPOINTS as [$method, $path]) {
            $response = $this->call($method, $path, [], [], [], $this->transformHeaders($headers));
            $this->assertSame(
                403,
                $response->status(),
                "Expected 403 for member on {$method} {$path}, got {$response->status()}"
            );
        }
    }

    public function test_brand_admin_can_access_brand_routes(): void
    {
        $user = $this->makeUserWithRole('brand_admin', 'brand');
        $response = $this->withHeaders($this->authHeaders($user))->get('/api/brand/stats');

        $this->assertNotEquals(403, $response->status(), 'brand_admin should not get 403');
        $this->assertNotEquals(401, $response->status(), 'brand_admin should be authenticated');
    }

    public function test_tr_admin_can_access_brand_routes(): void
    {
        $user = $this->makeUserWithRole('tr_admin');
        $response = $this->withHeaders($this->authHeaders($user))->get('/api/brand/stats');

        $this->assertNotEquals(403, $response->status(), 'tr_admin should not get 403');
        $this->assertNotEquals(401, $response->status(), 'tr_admin should be authenticated');
    }

    public function test_tr_superadmin_can_access_brand_routes(): void
    {
        $user = $this->makeUserWithRole('tr_superadmin');
        $response = $this->withHeaders($this->authHeaders($user))->get('/api/brand/stats');

        $this->assertNotEquals(403, $response->status(), 'tr_superadmin should not get 403');
        $this->assertNotEquals(401, $response->status(), 'tr_superadmin should be authenticated');
    }

    public function test_unauthenticated_cannot_access_brand_routes(): void
    {
        $response = $this->getJson('/api/brand/stats');

        // Some JWT middleware setups return 403 without a token rather than
        // the "correct" 401 — both mean the request was rejected pre-auth.
        $this->assertContains(
            $response->status(),
            [401, 403],
            "Expected 401 or 403 without token, got {$response->status()}"
        );
    }

    /**
     * Laravel's $this->call expects headers in the transformed HTTP_ form.
     */
    private function transformHeaders(array $headers): array
    {
        $server = [];
        foreach ($headers as $name => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $value;
        }
        return $server;
    }
}

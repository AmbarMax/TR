<?php

namespace Tests\Unit\Rules;

use App\Rules\UsernameNotReserved;
use Database\Seeders\ReservedUsernamesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UsernameNotReservedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReservedUsernamesSeeder::class);
    }

    /**
     * @dataProvider usernameCases
     */
    public function test_reserved_username_validation(string $value, bool $shouldFail, string $reason): void
    {
        $v = Validator::make(
            ['username' => $value],
            ['username' => [new UsernameNotReserved()]]
        );

        $this->assertSame(
            $shouldFail,
            $v->fails(),
            "Expected ".($shouldFail ? 'fail' : 'pass')." for '$value' ($reason)"
        );
    }

    public static function usernameCases(): array
    {
        return [
            'exact system reserved'        => ['admin',              true,  'system reserved'],
            'case-insensitive upper'       => ['ADMIN',              true,  'upper case should normalize'],
            'case-insensitive mixed'       => ['AdMiN',              true,  'mixed case should normalize'],
            'leading and trailing spaces'  => ['  trophy  ',         true,  'whitespace should be trimmed'],
            'product reserved (chest)'     => ['chest',              true,  'product entity'],
            'team personal (max)'          => ['max',                true,  'team handle'],
            'trademark (nike)'             => ['nike',               true,  'brand trademark'],
            'trademark hyphenated'         => ['coca-cola',          true,  'hyphenated brand'],
            'normal username with digits'  => ['johndoe42',          false, 'common pattern allowed'],
            'normal with underscore'       => ['usuario_normal_123', false, 'underscore allowed'],
            'normal with dash'             => ['cool-dude',          false, 'dash allowed'],
        ];
    }
}

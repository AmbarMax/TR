<?php

namespace Database\Seeders;

use App\Models\Trophy;
use Illuminate\Database\Seeder;

class WelcomeTrophySeeder extends Seeder
{
    /**
     * Creates the system-owned "Welcome Trophy" that all new users
     * claim at the end of the onboarding wizard. Uses a fixed UUID
     * so the seeder is idempotent — re-running it doesn't create
     * duplicates.
     *
     * Note: Trophy uses the UUID trait which auto-generates an id when
     * none is set. Trophy also has $guarded = ['id'], so we can't pass
     * id via mass-assignment. Instead, we look up the fixed UUID first
     * and only create a new Trophy if missing — explicitly setting the
     * id attribute before save so the boot hook doesn't overwrite it.
     */
    public function run(): void
    {
        $welcomeTrophyId = '00000000-0000-4000-8000-000000000001';

        $trophy = Trophy::find($welcomeTrophyId);

        $attributes = [
            'user_id'     => null,                    // system-owned
            'name'        => 'Welcome Trophy',
            'image'       => 'welcome-trophy.png',    // file should exist in storage/app/public/trophies/
            'price'       => 0,                        // free
            'receive'     => 100,                      // 100 XP token nice round
            'description' => 'For taking the first step. Your hall is now alive.',
            'type'        => 'trophy',
            'weight'      => 999,                     // sorted last in lists where weight matters
            'max_supply'  => 0,                        // 0 = unlimited
            'is_nft'      => 0,
            'minted'      => 0,
            'key_id'      => null,
        ];

        if ($trophy) {
            $trophy->fill($attributes);
            $trophy->save();
            return;
        }

        $trophy = new Trophy($attributes);
        $trophy->id = $welcomeTrophyId;
        $trophy->save();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Integration;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('integrations.name') as $integration){
            Integration::create([
                'name' => $integration
            ]);
        }
    }
}

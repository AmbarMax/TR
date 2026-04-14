<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
                'name' => 'User',
                'username' => 'usercorpsoft',
                'email' => 'user@corpsoft.io',
                'phone_number' => '+1233210000',
                'password' => Hash::make('secret123')
            ]);
    }
}

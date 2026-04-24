<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservedUsernamesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ---------------------------------------------------------------
        // System — generic infra / auth / framework routes
        // ---------------------------------------------------------------
        $system = [
            'admin', 'administrator', 'api', 'app', 'auth', 'dashboard',
            'help', 'login', 'logout', 'me', 'notifications', 'profile',
            'settings', 'signup', 'sign-up', 'signin', 'sign-in',
            'signout', 'sign-out', 'support', 'system', 'user', 'users',
        ];

        // ---------------------------------------------------------------
        // Product — TrophyRoom-specific words and URL shortcuts
        // ---------------------------------------------------------------
        $product = [
            'brand', 'brands', 'feed', 'forge', 'hall', 'halls',
            'tr', 'trophy', 'trophyroom', 'virtual-hall', 'vh',
            'h', 'b', 'i', 'o', 'g', 'v',
        ];

        // ---------------------------------------------------------------
        // Trademark — top priority brands (edit freely as onboarding grows)
        // ---------------------------------------------------------------
        $trademark = [
            // FMCG / beverage / snack
            'nike', 'adidas', 'coca-cola', 'cocacola', 'pepsi', 'doritos',
            'redbull', 'mcdonalds', 'burger-king', 'starbucks', 'kfc',
            'subway', 'taco-bell', 'oreo', 'kitkat', 'snickers', 'mars',
            'hershey',

            // Big tech / platforms
            'samsung', 'apple', 'microsoft', 'google', 'amazon',
            'facebook', 'meta', 'twitter', 'x', 'instagram', 'tiktok',
            'snapchat', 'youtube', 'twitch', 'discord', 'slack',
            'spotify', 'netflix',

            // Entertainment / media
            'disney', 'pixar', 'marvel', 'dc',

            // Gaming (publishers, platforms, titles)
            'riot', 'blizzard', 'ea', 'ubisoft', 'sony', 'playstation',
            'xbox', 'nintendo', 'valve', 'steam', 'epic', 'fortnite',
            'minecraft', 'valorant', 'league', 'lol', 'dota', 'csgo',
            'overwatch',

            // Automotive
            'ferrari', 'mercedes', 'bmw', 'audi', 'toyota', 'honda',
            'ford', 'tesla',

            // Services / fintech
            'uber', 'lyft', 'airbnb', 'paypal', 'visa', 'mastercard',
            'amex',

            // Hardware / semi / peripherals
            'ibm', 'oracle', 'intel', 'amd', 'nvidia', 'qualcomm',
            'dell', 'hp', 'lenovo', 'asus', 'acer', 'razer', 'logitech',
            'corsair', 'hyperx', 'alienware',
        ];

        $rows = [];
        foreach ($system as $u)    { $rows[] = $this->row($u, 'system',    $now); }
        foreach ($product as $u)   { $rows[] = $this->row($u, 'system',    $now); }
        foreach ($trademark as $u) { $rows[] = $this->row($u, 'trademark', $now); }

        DB::table('reserved_usernames')->insertOrIgnore($rows);
    }

    private function row(string $username, string $reason, Carbon $now): array
    {
        return [
            'username'   => strtolower(trim($username)),
            'reason'     => $reason,
            'notes'      => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}

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
            // Core app / auth
            'admin', 'administrator', 'api', 'app', 'auth', 'dashboard',
            'help', 'login', 'logout', 'me', 'notifications', 'profile',
            'settings', 'signup', 'sign-up', 'signin', 'sign-in',
            'signout', 'sign-out', 'support', 'system', 'user', 'users',

            // Auth flows
            '2fa', 'reset-password', 'forgot-password', 'verify-email',
            'reset-2fa',

            // Subdomains / SEO / marketing
            'www', 'mail', 'email', 'blog', 'news', 'about', 'contact',
            'terms', 'privacy', 'legal', 'faq', 'press', 'careers',

            // Static assets / storage
            'static', 'assets', 'public', 'storage', 'uploads', 'cdn',

            // Reserved words / safety
            'root', 'null', 'undefined', 'test', 'demo', 'guest',
            'anonymous', 'anon',
        ];

        // ---------------------------------------------------------------
        // Product — TrophyRoom-specific words and URL shortcuts
        // ---------------------------------------------------------------
        $product = [
            // Core product concepts
            'brand', 'brands', 'feed', 'forge', 'hall', 'halls',
            'tr', 'trophy', 'trophyroom', 'virtual-hall', 'vh',

            // URL single-letter shortcuts
            'h', 'b', 'i', 'o', 'g', 'v',

            // Product entities / routes
            'chest', 'chests', 'exchange', 'exchanges', 'item', 'items',
            'key', 'keys', 'badge', 'badges', 'integration', 'integrations',
            'currency', 'currencies', 'follower', 'followers', 'following',

            // Legacy product (redirects to trophyroom)
            'ambar',

            // Staff / moderation labels
            'moderator', 'moderators', 'mod', 'mods', 'staff', 'team',
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

            // Integrations connected to TR (riot/google/facebook/discord/steam
            // already listed above — Gaming/Big tech subgroups)
            'github', 'strava', 'overwolf',

            // AI / Anthropic ecosystem
            'claude', 'anthropic', 'openai', 'chatgpt', 'gpt',

            // Team / personal handles
            'max', 'tabi', 'ambar-labs',
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

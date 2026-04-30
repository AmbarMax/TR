<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // JSON allows future flexibility — adding new steps without
            // schema migrations. Keys: platform_connected, hall_personalized,
            // hall_explored, trophy_claimed (more can be added).
            $table->json('onboarding_steps')->nullable()->after('account_type');

            // Boolean for fast WHERE queries. True only when user has
            // completed the full wizard (or has been retroactively flagged).
            $table->boolean('onboarding_completed')->default(false)->after('onboarding_steps');

            // Tracks whether user opted out (closed wizard with "I'll explore first")
            // Useful for analytics: how many skip vs complete vs partial
            $table->timestamp('onboarding_skipped_at')->nullable()->after('onboarding_completed');

            $table->index('onboarding_completed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['onboarding_completed']);
            $table->dropColumn(['onboarding_steps', 'onboarding_completed', 'onboarding_skipped_at']);
        });
    }
};

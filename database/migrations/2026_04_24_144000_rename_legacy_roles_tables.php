<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Runs BEFORE Spatie's create_permission_tables migration
        // (timestamp 144000 < 144235). Clears the way so Spatie can
        // create its own `roles` table without colliding with the legacy
        // custom-role tables seeded back in 2023.
        if (Schema::hasTable('user_role') && ! Schema::hasTable('model_has_roles')) {
            Schema::rename('roles', 'legacy_roles');
            Schema::rename('user_role', 'legacy_user_role');
            Log::info('[9N-B-Step6-Rename] renamed legacy tables: roles -> legacy_roles, user_role -> legacy_user_role');
        } else {
            Log::info('[9N-B-Step6-Rename] skipped rename (legacy pivot missing or Spatie pivot already present)');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('legacy_roles') && Schema::hasTable('legacy_user_role')) {
            Schema::rename('legacy_roles', 'roles');
            Schema::rename('legacy_user_role', 'user_role');
            Log::info('[9N-B-Step6-Rename] reverted rename: legacy_roles -> roles, legacy_user_role -> user_role');
        }
    }
};

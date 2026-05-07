<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cleanup duplicados en trophy_user, preservando MIN(id) por par.
        DB::statement('
            DELETE t1 FROM trophy_user t1
            INNER JOIN trophy_user t2
                ON t1.user_id = t2.user_id
                AND t1.trophy_id = t2.trophy_id
                AND t1.id > t2.id
        ');

        // Cleanup duplicados en badge_user, mismo criterio.
        DB::statement('
            DELETE b1 FROM badge_user b1
            INNER JOIN badge_user b2
                ON b1.user_id = b2.user_id
                AND b1.badge_id = b2.badge_id
                AND b1.id > b2.id
        ');

        Schema::table('trophy_user', function (Blueprint $table) {
            $table->unique(['user_id', 'trophy_id'], 'trophy_user_user_trophy_unique');
        });

        Schema::table('badge_user', function (Blueprint $table) {
            $table->unique(['user_id', 'badge_id'], 'badge_user_user_badge_unique');
        });
    }

    public function down(): void
    {
        Schema::table('trophy_user', function (Blueprint $table) {
            $table->dropUnique('trophy_user_user_trophy_unique');
        });

        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropUnique('badge_user_user_badge_unique');
        });
    }
};

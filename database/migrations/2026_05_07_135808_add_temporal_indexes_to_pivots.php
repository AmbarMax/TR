<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->index(['badge_id', 'created_at'], 'badge_user_badge_created_idx');
            $table->index(['user_id', 'created_at'], 'badge_user_user_created_idx');
        });

        Schema::table('trophy_user', function (Blueprint $table) {
            $table->index(['trophy_id', 'created_at'], 'trophy_user_trophy_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropIndex('badge_user_badge_created_idx');
            $table->dropIndex('badge_user_user_created_idx');
        });

        Schema::table('trophy_user', function (Blueprint $table) {
            $table->dropIndex('trophy_user_trophy_created_idx');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trophies', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('weight');
            $table->index('published_at');
        });

        DB::statement('UPDATE trophies SET published_at = created_at WHERE published_at IS NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::table('trophies', function (Blueprint $table) {
            $table->dropIndex(['published_at']);
            $table->dropColumn('published_at');
        });
    }
};

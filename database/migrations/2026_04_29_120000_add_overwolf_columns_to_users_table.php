<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('overwolf_id')->nullable()->unique()->after('source');
            $table->string('overwolf_username')->nullable()->after('overwolf_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['overwolf_id', 'overwolf_username']);
        });
    }
};

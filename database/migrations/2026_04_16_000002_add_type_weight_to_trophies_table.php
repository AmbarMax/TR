<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trophies', function (Blueprint $table) {
            $table->string('type')->default('trophy')->after('description');
            $table->unsignedInteger('weight')->default(0)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('trophies', function (Blueprint $table) {
            $table->dropColumn(['type', 'weight']);
        });
    }
};

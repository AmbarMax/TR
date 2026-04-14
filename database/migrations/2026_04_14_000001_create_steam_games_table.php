<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('steam_games', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('appid')->unique()->index();
            $table->string('name');
            $table->string('img_icon_url')->nullable();
            $table->string('img_logo_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steam_games');
    }
};

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
        Schema::create('steam_achievements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('steam_game_id');
            $table->string('api_name');
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('icon_gray_url')->nullable();
            $table->decimal('global_percent', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('steam_game_id')->references('id')->on('steam_games')->onDelete('cascade');
            $table->unique(['steam_game_id', 'api_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steam_achievements');
    }
};

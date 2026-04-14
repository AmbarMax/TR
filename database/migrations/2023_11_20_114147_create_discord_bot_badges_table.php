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
        Schema::create('discord_bot_badges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('discord_id');
            $table->string('prefix');
            $table->uuid('badge_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('badge_id')->references('id')->on('badges');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discord_bot_badges');
    }
};

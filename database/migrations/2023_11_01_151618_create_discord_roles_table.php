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
        Schema::create('discord_roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('discord_id');
            $table->integer('color');
            $table->boolean('hoist');
            $table->string('icon')->nullable();
            $table->string('unicode_emoji')->nullable();
            $table->integer('position');
            $table->string('permissions');
            $table->boolean('managed');
            $table->boolean('mentionable');
            $table->json('tags')->nullable();
            $table->integer('flags');
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
        Schema::dropIfExists('discord_roles');
    }
};

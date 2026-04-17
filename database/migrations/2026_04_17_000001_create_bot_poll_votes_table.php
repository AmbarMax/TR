<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_poll_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('poll_id');
            $table->string('guild_id');
            $table->string('discord_user_id');
            $table->string('answer');
            $table->timestamps();

            $table->unique(['poll_id', 'discord_user_id']);

            $table->foreign('poll_id')
                ->references('id')
                ->on('bot_polls')
                ->onDelete('cascade');

            $table->foreign('guild_id')
                ->references('guild_id')
                ->on('guild_connections')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_poll_votes');
    }
};

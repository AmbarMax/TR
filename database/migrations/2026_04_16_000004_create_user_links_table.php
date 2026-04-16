<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('discord_user_id');
            $table->uuid('tr_user_id');
            $table->string('guild_id');
            $table->string('discord_username')->nullable();
            $table->timestamp('linked_at');
            $table->timestamps();

            $table->foreign('tr_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('guild_id')
                ->references('guild_id')
                ->on('guild_connections')
                ->onDelete('cascade');

            $table->unique(['discord_user_id', 'guild_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_links');
    }
};

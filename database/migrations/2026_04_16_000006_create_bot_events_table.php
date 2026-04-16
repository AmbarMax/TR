<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('guild_id');
            $table->uuid('badge_rule_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('channel_id')->nullable();
            $table->string('discord_event_id')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 20)->default('draft')->comment('draft, scheduled, active, completed');
            $table->timestamps();

            $table->foreign('guild_id')
                ->references('guild_id')
                ->on('guild_connections')
                ->onDelete('cascade');

            $table->foreign('badge_rule_id')
                ->references('id')
                ->on('badge_rules')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_events');
    }
};

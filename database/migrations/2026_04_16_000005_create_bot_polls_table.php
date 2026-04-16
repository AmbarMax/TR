<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_polls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('guild_id');
            $table->uuid('badge_rule_id')->nullable();
            $table->string('title');
            $table->json('options')->comment('[{"label": "Option A", "value": "a"}, ...]');
            $table->string('channel_id')->nullable();
            $table->string('discord_message_id')->nullable();
            $table->integer('duration_hours')->default(24);
            $table->string('require_specific_answer')->nullable()->comment('null = any answer counts');
            $table->string('status', 20)->default('draft')->comment('draft, active, closed');
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
        Schema::dropIfExists('bot_polls');
    }
};

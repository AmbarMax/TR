<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('badge_id');
            $table->string('guild_id');
            $table->string('platform', 20)->default('discord');
            $table->string('trigger_type', 50)->comment('voice_minutes, message_count, reaction, event_join, poll_answer, role_obtain');
            $table->json('trigger_config')->comment('{"channel_id": "...", "threshold": 30}');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('badge_id')
                ->references('id')
                ->on('badges')
                ->onDelete('cascade');

            $table->foreign('guild_id')
                ->references('guild_id')
                ->on('guild_connections')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_rules');
    }
};

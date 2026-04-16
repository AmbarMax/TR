<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guild_channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('guild_id');
            $table->string('channel_id');
            $table->string('name');
            $table->string('type', 20)->comment('text, voice, stage, forum');
            $table->string('category')->nullable();
            $table->timestamps();

            $table->foreign('guild_id')
                ->references('guild_id')
                ->on('guild_connections')
                ->onDelete('cascade');

            $table->unique(['guild_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guild_channels');
    }
};

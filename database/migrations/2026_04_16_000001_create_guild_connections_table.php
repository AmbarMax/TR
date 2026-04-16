<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guild_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('guild_id')->unique();
            $table->string('guild_name')->nullable();
            $table->uuid('org_id')->nullable()->comment('Future: client/org in TR');
            $table->string('bot_api_key', 64)->unique();
            $table->timestamp('bot_connected_at')->nullable();
            $table->timestamp('channel_cache_updated_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guild_connections');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Social links + featured slots on users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_twitter')->nullable()->after('description');
            $table->string('social_twitch')->nullable()->after('social_twitter');
            $table->string('social_youtube')->nullable()->after('social_twitch');
            $table->string('social_instagram')->nullable()->after('social_youtube');
            $table->string('social_discord_tag')->nullable()->after('social_instagram');
            $table->string('social_website')->nullable()->after('social_discord_tag');
            $table->json('featured_slots')->nullable()->after('social_website');
        });

        // Battle pass levels
        Schema::create('battle_pass_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('number')->unique();
            $table->string('name');
            $table->integer('cost_uru')->default(0);
            $table->json('rewards')->nullable(); // [{"type":"ambar","amount":50},{"type":"key","key_id":"uuid","quantity":1}]
            $table->timestamps();
        });

        // User battle pass progress
        Schema::create('battle_pass_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('level_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('level_id')->references('id')->on('battle_pass_levels')->onDelete('cascade');
            $table->unique(['user_id', 'level_id']);
        });

        // Shop items (managed by TrophyRoom team only)
        Schema::create('shop_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->integer('price_uru');
            $table->integer('stock')->default(-1); // -1 = unlimited
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Shop purchases
        Schema::create('shop_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('shop_item_id');
            $table->integer('price_paid');
            $table->string('status')->default('pending'); // pending, delivered, cancelled
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shop_item_id')->references('id')->on('shop_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_purchases');
        Schema::dropIfExists('shop_items');
        Schema::dropIfExists('battle_pass_user');
        Schema::dropIfExists('battle_pass_levels');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'social_twitter', 'social_twitch', 'social_youtube',
                'social_instagram', 'social_discord_tag', 'social_website',
                'featured_slots'
            ]);
        });
    }
};

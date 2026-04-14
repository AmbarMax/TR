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
        Schema::create('badge_trophy', function (Blueprint $table) {
            $table->id();
            $table->uuid('trophy_id');
            $table->uuid('badge_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('trophy_id')->references('id')->on('trophies');
            $table->foreign('badge_id')->references('id')->on('badges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_trophy');
    }
};

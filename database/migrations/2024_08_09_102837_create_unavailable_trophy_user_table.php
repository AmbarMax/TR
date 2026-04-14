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
        Schema::create('unavailable_trophy_user', function (Blueprint $table) {
            $table->id();

            $table->uuid('user_id');
            $table->uuid('trophy_id');
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('trophy_id')->references('id')->on('trophies');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unavailable_trophy_user');
    }
};

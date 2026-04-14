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
        Schema::create('key_user', function (Blueprint $table) {
            $table->id();

            $table->uuid('user_id');
            $table->uuid('key_id');
            $table->softDeletes();
            $table->timestamps();
            $table->bigInteger('token_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('key_id')->references('id')->on('keys');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_user');
    }
};

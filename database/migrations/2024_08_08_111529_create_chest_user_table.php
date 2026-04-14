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
        Schema::create('chest_user', function (Blueprint $table) {
            $table->id();

            $table->uuid('chest_id');
            $table->uuid('user_id');
            $table->boolean('is_open')->default(false);

            $table->foreign('chest_id')->references('id')->on('chests')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chest_user');
    }
};

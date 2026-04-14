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
        Schema::dropIfExists('chests');

        Schema::create('chests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('image_closed')->nullable();
            $table->string('image_open')->nullable();
            $table->string('description')->nullable();
            $table->integer('quantity')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_nft')->default(true);

            $table->uuid('key_id');
            $table->foreign('key_id')->references('id')->on('keys');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chests');
    }
};

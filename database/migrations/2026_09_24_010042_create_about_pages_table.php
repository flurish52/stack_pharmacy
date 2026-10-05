<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_about_pages_table.php
    public function up(): void
    {
        // about_pages
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->text('story')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->string('image_public_id')->nullable(); // was hero_image_url
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};

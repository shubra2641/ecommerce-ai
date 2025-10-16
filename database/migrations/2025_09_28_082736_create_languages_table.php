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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Language name (e.g., English, Arabic)
            $table->string('code', 5)->unique(); // Language code (e.g., en, ar)
            $table->string('flag')->nullable(); // Flag icon
            $table->enum('direction', ['ltr', 'rtl'])->default('ltr'); // Text direction
            $table->boolean('is_default')->default(false); // Default language
            $table->boolean('is_active')->default(true); // Active status
            $table->integer('sort_order')->default(0); // Display order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};

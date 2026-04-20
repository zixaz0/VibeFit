<?php
// database/migrations/2024_01_01_000002_create_food_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('food_name');
            $table->text('food_description')->nullable();
            $table->integer('calories');
            $table->decimal('protein', 6, 2)->nullable();  // gram
            $table->decimal('carbs', 6, 2)->nullable();    // gram
            $table->decimal('fat', 6, 2)->nullable();      // gram
            $table->string('image_path')->nullable();
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack'])->default('snack');
            $table->text('ai_analysis')->nullable(); // raw response dari Claude
            $table->date('logged_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_logs');
    }
};

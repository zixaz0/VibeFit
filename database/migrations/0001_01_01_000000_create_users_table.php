<?php
// database/migrations/2024_01_01_000001_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->decimal('weight', 5, 2)->nullable(); // kg
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->integer('daily_calorie_target')->default(2000);
            $table->boolean('diet_mode')->default(false);
            $table->integer('diet_calorie_cut')->default(500); // dipangkas berapa kalori saat diet
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

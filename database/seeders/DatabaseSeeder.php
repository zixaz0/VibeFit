<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed database dengan user demo (opsional, untuk testing).
     * Jalankan: php artisan db:seed
     */
    public function run(): void
    {
        // Buat user demo jika belum ada
        User::firstOrCreate(
            ['email' => 'demo@calorielens.com'],
            [
                'name'                 => 'Demo User',
                'password'             => Hash::make('password'),
                'birth_date'           => '1995-06-15',
                'gender'               => 'male',
                'weight'               => 70.0,
                'height'               => 170.0,
                'daily_calorie_target' => 2500,
                'diet_mode'            => true,
                'diet_calorie_cut'     => 500,
            ]
        );
    }
}

<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'birth_date',
        'gender',
        'weight',
        'height',
        'daily_calorie_target',
        'diet_mode',
        'diet_calorie_cut',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'   => 'hashed',
            'birth_date' => 'date',
            'diet_mode'  => 'boolean',
        ];
    }

    // Kalori efektif: dikurangi saat diet mode aktif
    public function getEffectiveCalorieTargetAttribute(): int
    {
        if ($this->diet_mode) {
            return max(1200, $this->daily_calorie_target - $this->diet_calorie_cut);
        }
        return $this->daily_calorie_target;
    }

    // Hitung BMR pakai Mifflin-St Jeor
    public function getBmrAttribute(): ?int
    {
        if (!$this->weight || !$this->height || !$this->birth_date || !$this->gender) {
            return null;
        }
        $age = $this->birth_date->age;
        if ($this->gender === 'male') {
            return (int) round(10 * $this->weight + 6.25 * $this->height - 5 * $age + 5);
        }
        return (int) round(10 * $this->weight + 6.25 * $this->height - 5 * $age - 161);
    }

    public function foodLogs()
    {
        return $this->hasMany(FoodLog::class);
    }

    public function todayCalories(): int
    {
        return $this->foodLogs()
            ->whereDate('logged_date', today())
            ->sum('calories');
    }

    public function todayLogs()
    {
        return $this->foodLogs()
            ->whereDate('logged_date', today())
            ->latest()
            ->get();
    }
}

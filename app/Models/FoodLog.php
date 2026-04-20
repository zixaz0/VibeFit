<?php
// app/Models/FoodLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodLog extends Model
{
    protected $fillable = [
        'user_id',
        'food_name',
        'food_description',
        'calories',
        'protein',
        'carbs',
        'fat',
        'image_path',
        'meal_type',
        'ai_analysis',
        'logged_date',
    ];

    protected function casts(): array
    {
        return [
            'logged_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMealTypeLabelAttribute(): string
    {
        return match($this->meal_type) {
            'breakfast' => '🌅 Sarapan',
            'lunch'     => '☀️ Makan Siang',
            'dinner'    => '🌙 Makan Malam',
            'snack'     => '🍎 Snack',
            default     => 'Lainnya',
        };
    }
}

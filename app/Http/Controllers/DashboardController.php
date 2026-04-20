<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user          = Auth::user();
        $todayLogs     = $user->todayLogs();
        $todayCalories = $todayLogs->sum('calories');
        $target        = $user->effective_calorie_target;
        $remaining     = max(0, $target - $todayCalories);
        $percentage    = $target > 0 ? min(100, round(($todayCalories / $target) * 100)) : 0;

        // Breakdown per meal type
        $breakdown = $todayLogs->groupBy('meal_type')->map(fn($logs) => $logs->sum('calories'));

        // Weekly summary (7 hari terakhir)
        $weekly = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $cal  = $user->foodLogs()->whereDate('logged_date', $date)->sum('calories');
            $weekly->push([
                'date'     => $date->format('D'),
                'calories' => $cal,
                'full_date'=> $date->format('Y-m-d'),
            ]);
        }

        return view('dashboard', compact(
            'user', 'todayLogs', 'todayCalories',
            'target', 'remaining', 'percentage',
            'breakdown', 'weekly'
        ));
    }
}

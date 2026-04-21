<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProfileComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $incomplete = !$user->birth_date
                || !$user->gender
                || !$user->weight
                || !$user->height
                || !$user->daily_calorie_target;

            // Jangan redirect kalau sudah di halaman profil (infinite loop)
            $isProfileRoute = $request->routeIs('profile.*');

            if ($incomplete && !$isProfileRoute) {
                return redirect()->route('profile')
                    ->with('warning', 'Lengkapi profil kamu terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
<?php
// app/Http/Middleware/Authenticate.php
// Ini sudah ada di Laravel by default, tapi pastikan redirect ke 'login'

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}

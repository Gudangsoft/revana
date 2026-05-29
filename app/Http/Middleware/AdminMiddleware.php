<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        // Perbarui cache sesi supaya tidak kedaluwarsa saat admin aktif browsing
        $user = auth()->user();
        $cacheKey = 'admin_session:' . $user->id;
        Cache::put($cacheKey, session()->getId(), now()->addMinutes(config('session.lifetime', 120)));

        return $next($request);
    }
}

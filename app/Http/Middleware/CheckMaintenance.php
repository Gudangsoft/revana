<?php

namespace App\Http\Middleware;

use App\Services\FeatureSettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenance
{
    /**
     * Handle an incoming request.
     * Blocks non-admin users when maintenance mode is enabled via admin UI.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (FeatureSettingService::get('maintenance_mode', '0') === '1') {
            // Allow admins through
            if (auth()->check() && method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin()) {
                return $next($request);
            }

            $message = FeatureSettingService::get('maintenance_message', 'Sistem sedang dalam pemeliharaan.');

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 503);
            }

            return response()->view('errors.maintenance', ['message' => $message], 503);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\FeatureSettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleCapability
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('rolecap:pic,fasttrack')
     * Checks if the given role has the given capability enabled.
     */
    public function handle(Request $request, Closure $next, string $role, string $capability): Response
    {
        if (!FeatureSettingService::roleHasCapability($role, $capability)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke fitur ini. Hubungi administrator.',
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses ke fitur "' . ucfirst(str_replace('_', ' ', $capability)) . '". Hubungi administrator.');
        }

        return $next($request);
    }
}

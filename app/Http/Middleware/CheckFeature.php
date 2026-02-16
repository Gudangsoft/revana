<?php

namespace App\Http\Middleware;

use App\Services\FeatureSettingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('feature:fasttrack') checks feat_fasttrack_enabled
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!FeatureSettingService::isEnabled($feature)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Fitur ini sedang dinonaktifkan.'], 403);
            }

            return redirect()->back()
                ->with('error', 'Fitur "' . ucfirst(str_replace('_', ' ', $feature)) . '" sedang dinonaktifkan oleh administrator.');
        }

        return $next($request);
    }
}

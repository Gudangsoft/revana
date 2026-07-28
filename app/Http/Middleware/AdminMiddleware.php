<?php

namespace App\Http\Middleware;

use App\Support\PointsAutoSync;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->hasAdminAccess()) {
            abort(403, 'Unauthorized access');
        }

        $user = auth()->user();
        $cacheKey = 'admin_session:' . $user->id;
        Cache::put($cacheKey, session()->getId(), now()->addMinutes(config('session.lifetime', 120)));

        return $next($request);
    }

    /**
     * Jaring pengaman "auto-sync tanpa cron": kalau cron scheduler server tidak
     * aktif (points:auto-sync tidak pernah terpanggil otomatis), sinkronisasi tetap
     * jalan sendiri lewat request admin biasa — dibatasi maksimal 1x/15 menit
     * (PointsAutoSync::runIfDue()) supaya tidak membebani setiap request. Dijalankan
     * di terminate() (setelah response terkirim ke browser) supaya admin TIDAK
     * merasakan delay sama sekali, meski operasinya sendiri sudah ringan.
     */
    public function terminate(Request $request, Response $response): void
    {
        PointsAutoSync::runIfDue();
    }
}

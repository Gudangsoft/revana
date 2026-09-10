<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\PointsAutoSync;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized access');
        }

        $user = auth()->user();
        $hasAccess = $user->hasAdminAccess();

        // Mode "Login As" (impersonasi guard web — reviewer / user biasa): user
        // aktif memang bukan admin, TAPI admin ASLI di balik sesi ini tetap
        // boleh membuka halaman admin (dilaporkan user 10 Sept 2026 — sebelumnya
        // /admin/settings dll. 403 saat sedang Login As). ID admin asli disimpan
        // saat loginAs oleh UserController (`admin_user_impersonating`) &
        // ReviewerController (`admin_impersonating`). Impersonasi PIC/Marketing
        // pakai guard terpisah, jadi $user di sini sudah admin & tidak masuk ke
        // sini.
        if (!$hasAccess) {
            $impersonatorId = session('admin_user_impersonating') ?? session('admin_impersonating');
            if ($impersonatorId) {
                $impersonator = User::find($impersonatorId);
                $hasAccess = $impersonator && $impersonator->hasAdminAccess();
            }
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized access');
        }

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

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PicMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $pic = auth()->guard('pic')->user();
        
        if (!$pic) {
            return redirect()->route('pic.login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!$pic->is_active) {
            auth()->guard('pic')->logout();
            return redirect()->route('pic.login')->with('error', 'Akun Anda tidak aktif.');
        }

        // If specific roles are provided, check if user has the role
        if (!empty($roles)) {
            if (!in_array($pic->role, $roles)) {
                return redirect()->route('pic.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
            }
        }

        return $next($request);
    }
}

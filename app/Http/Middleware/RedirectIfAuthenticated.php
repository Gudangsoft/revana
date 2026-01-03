<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect based on guard
                if ($guard === 'pic') {
                    $pic = Auth::guard('pic')->user();
                    if ($pic && $pic->isAuthor()) {
                        return redirect()->route('pic.author.dashboard');
                    }
                    return redirect()->route('pic.author.dashboard');
                }
                if ($request->user()->role === 'admin') {
                    return redirect('/admin/dashboard');
                } elseif ($request->user()->role === 'reviewer') {
                    return redirect('/reviewer/dashboard');
                }
                return redirect('/login');
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // Check if request is for PIC routes
            if ($request->is('pic/*')) {
                return route('pic.login');
            }
            
            // Check if request is for Marketing routes
            if ($request->is('marketing/*')) {
                return route('marketing.login');
            }
            
            return route('login');
        }
    }
}

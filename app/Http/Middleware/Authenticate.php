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
            
            return route('login');
        }
    }
}

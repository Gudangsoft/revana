<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share settings to all views
        View::composer('*', function ($view) {
            $settings = [
                'app_name' => env('APP_NAME', 'REVANA'),
                'tagline' => Setting::get('tagline', 'Review Validation & Analytics'),
                'address' => Setting::get('address', ''),
                'contact' => Setting::get('contact', ''),
                'logo' => Setting::get('logo', ''),
                'favicon' => Setting::get('favicon', ''),
            ];
            
            $view->with('appSettings', $settings);
        });
    }
}

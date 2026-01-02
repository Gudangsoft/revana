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
                'app_name' => Setting::get('app_name', env('APP_NAME', 'SIPERA')),
                'tagline' => Setting::get('tagline', 'Sistem Informasi Peer Review Artikel'),
                'address' => Setting::get('address', ''),
                'contact' => Setting::get('contact', ''),
                'logo' => Setting::get('logo', ''),
                'favicon' => Setting::get('favicon', ''),
            ];
            
            // Share with both names for compatibility
            $view->with('appSettings', $settings);
            $view->with('settings', $settings);
        });
    }
}

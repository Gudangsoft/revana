<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\FeatureSettingService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
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
        // Use Bootstrap pagination style (numbers instead of arrows)
        Paginator::useBootstrapFive();

        // Custom Blade directives for feature toggles
        // Usage: @feature('fasttrack') ... @endfeature
        Blade::if('feature', function (string $feature) {
            return FeatureSettingService::isEnabled($feature);
        });
        
        // Share settings to all views
        View::composer('*', function ($view) {
            $settings = [
                'app_name' => Setting::get('app_name', env('APP_NAME', 'SIPERA')),
                'full_name' => Setting::get('full_name', 'Sistem Informasi Peer Review Artikel'),
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

<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\FeatureSettingService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
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

        // Role capability directive
        // Usage: @rolecap('marketing', 'fasttrack') ... @endrolecap
        Blade::if('rolecap', function (string $role, string $capability) {
            return FeatureSettingService::roleHasCapability($role, $capability);
        });
        
        // Share settings to all views (cached 10 menit)
        View::composer('*', function ($view) {
            $settings = Cache::remember('app_settings', 600, function () {
                $language = Setting::get('app_language', 'id');
                return [
                    'app_name' => Setting::get('app_name', env('APP_NAME', 'SIPERA')),
                    'full_name' => Setting::get('full_name', 'Sistem Informasi Peer Review Artikel'),
                    'tagline' => Setting::get('tagline', 'Sistem Informasi Peer Review Artikel'),
                    'address' => Setting::get('address', ''),
                    'contact' => Setting::get('contact', ''),
                    'logo' => Setting::get('logo', ''),
                    'favicon' => Setting::get('favicon', ''),
                    'language' => $language,
                ];
            });

            App::setLocale($settings['language']);
            $view->with('appSettings', $settings);
            $view->with('settings', $settings);
        });
    }
}

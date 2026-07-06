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

        $this->applyStoredMailSettings();

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
        
        // Share settings to all views (cached per-tenant 10 menit)
        View::composer('*', function ($view) {
            // Cache key unik per tenant agar setting tidak bocor antar tenant
            $tenant    = app()->bound('tenant') ? app('tenant') : null;
            $cacheKey  = 'app_settings_' . ($tenant?->subdomain ?? 'master');

            $settings = Cache::remember($cacheKey, 600, function () use ($tenant) {
                // Jika di tenant dan ada branding, override app_name dari branding
                $tenantAppName = $tenant?->branding['app_name'] ?? null;

                try {
                    $language = Setting::get('app_language', 'id');
                    return [
                        'app_name' => $tenantAppName ?: Setting::get('app_name', env('APP_NAME', 'SIPERA')),
                        'full_name' => Setting::get('full_name', 'Sistem Informasi Peer Review Artikel'),
                        'tagline'  => $tenant?->branding['tagline'] ?? Setting::get('tagline', 'Sistem Informasi Peer Review Artikel'),
                        'address'  => Setting::get('address', ''),
                        'contact'  => Setting::get('contact', ''),
                        'logo'     => $tenant?->branding['logo_url'] ?? Setting::get('logo', ''),
                        'favicon'  => Setting::get('favicon', ''),
                        'language' => $language,
                    ];
                } catch (\Throwable) {
                    // Fallback jika tabel settings belum ada (tenant baru)
                    return [
                        'app_name' => $tenantAppName ?: env('APP_NAME', 'SIPERA'),
                        'full_name' => 'Sistem Informasi Peer Review Artikel',
                        'tagline'  => $tenant?->branding['tagline'] ?? '',
                        'address'  => '',
                        'contact'  => '',
                        'logo'     => $tenant?->branding['logo_url'] ?? '',
                        'favicon'  => '',
                        'language' => 'id',
                    ];
                }
            });

            App::setLocale($settings['language']);
            $view->with('appSettings', $settings);
            $view->with('settings', $settings);
        });
    }

    /**
     * Override konfigurasi mail runtime dari Setting (DB) — dikelola lewat
     * admin/email-settings, lebih andal daripada bergantung ke .env di server.
     */
    private function applyStoredMailSettings(): void
    {
        try {
            $host = Setting::get('mail_host');
            if (empty($host)) return; // belum pernah disimpan lewat form — biarkan pakai .env/config default

            config([
                'mail.default'                 => 'smtp',
                'mail.mailers.smtp.transport'  => 'smtp',
                'mail.mailers.smtp.host'       => $host,
                'mail.mailers.smtp.port'       => (int) Setting::get('mail_port', 465),
                'mail.mailers.smtp.username'   => Setting::get('mail_username'),
                'mail.mailers.smtp.password'   => Setting::get('mail_password'),
                'mail.mailers.smtp.encryption' => Setting::get('mail_encryption', 'ssl'),
                'mail.from.address'            => Setting::get('mail_from_address') ?: Setting::get('mail_username'),
                'mail.from.name'               => Setting::get('mail_from_name') ?: config('app.name'),
            ]);
        } catch (\Throwable) {
            // Tabel settings belum ada (mis. sebelum migrate) — abaikan, pakai config bawaan.
        }
    }
}

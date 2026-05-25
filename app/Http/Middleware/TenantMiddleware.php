<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host         = $request->getHost();
        $masterDomain = config('tenants.master_domain', 'portal.apji.org');

        // Lokal / IP langsung / domain master → lewat tanpa perubahan
        $masterHosts = array_filter([
            $masterDomain,
            'localhost',
            '127.0.0.1',
            env('APP_MASTER_HOST'),
        ]);
        if (in_array($host, $masterHosts) || filter_var($host, FILTER_VALIDATE_IP)) {
            return $next($request);
        }

        // Cari tenant berdasarkan subdomain atau custom domain
        $subdomain = explode('.', $host)[0];
        $tenant = Tenant::where('subdomain', $subdomain)
                        ->orWhere('custom_domain', $host)
                        ->first();

        if (!$tenant) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        if ($tenant->isSuspended()) {
            abort(503, 'Akses sistem ini telah ditangguhkan. Hubungi administrator APJI.');
        }

        if ($tenant->isExpired()) {
            abort(402, 'Masa aktif sistem ini telah berakhir. Hubungi administrator APJI.');
        }

        // Switch koneksi database ke tenant
        $this->switchDatabase($tenant);

        // Isolasi penyimpanan file per tenant
        $this->switchStorage($tenant);

        // Binding tenant ke container
        app()->instance('tenant', $tenant);

        // Share ke semua view Blade
        view()->share('currentTenant', $tenant);

        // Share branding jika ada
        $branding = $tenant->branding ?? [];
        if (!empty($branding['app_name'])) {
            Config::set('app.name', $branding['app_name']);
        }
        view()->share('tenantBranding', $branding);

        return $next($request);
    }

    private function switchDatabase(Tenant $tenant): void
    {
        $connection = config('database.connections.mysql');

        Config::set('database.connections.tenant', array_merge($connection, [
            'database' => $tenant->db_name,
            'username' => $tenant->db_user ?: $connection['username'],
            'password' => $tenant->db_password ?: $connection['password'],
        ]));

        Config::set('database.default', 'tenant');
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function switchStorage(Tenant $tenant): void
    {
        $tenantPath = storage_path('app/tenants/' . $tenant->subdomain);

        Config::set('filesystems.disks.public.root', $tenantPath . '/public');
        Config::set('filesystems.disks.public.url', url('storage/tenants/' . $tenant->subdomain));
        Config::set('filesystems.disks.local.root', $tenantPath);
    }
}

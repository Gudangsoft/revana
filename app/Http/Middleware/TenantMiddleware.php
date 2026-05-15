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

        // Portal utama → lewat tanpa perubahan
        if ($host === $masterDomain) {
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

        // Binding tenant ke container — akses via app('tenant') atau helper tenant()
        app()->instance('tenant', $tenant);

        // Share ke semua view Blade
        view()->share('currentTenant', $tenant);

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
}

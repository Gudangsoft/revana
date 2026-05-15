<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TenantImpersonateController extends Controller
{
    /**
     * Super admin memulai sesi impersonate ke tenant tertentu.
     * Buat token sementara → redirect ke subdomain tenant.
     */
    public function start(Tenant $tenant)
    {
        $token = Str::uuid()->toString();

        Cache::put('impersonate_' . $token, [
            'tenant_id'      => $tenant->id,
            'tenant_subdomain' => $tenant->subdomain,
            'admin_id'       => Auth::id(),
            'admin_name'     => Auth::user()->name,
            'back_url'       => route('admin.tenants.show', $tenant),
        ], now()->addMinutes(5));

        Log::info("Impersonate started: admin #{Auth::id()} → tenant {$tenant->subdomain}", [
            'token' => $token,
        ]);

        // Bangun URL tenant
        $masterDomain = config('tenants.master_domain', 'portal.apji.org');
        $parts        = explode('.', $masterDomain);
        array_shift($parts);
        $tenantHost = $tenant->custom_domain
            ?: $tenant->subdomain . '.' . implode('.', $parts);

        $scheme = request()->secure() ? 'https' : 'http';

        return redirect("{$scheme}://{$tenantHost}/impersonate/{$token}");
    }

    /**
     * Halaman di sisi tenant: validasi token → auto-login sebagai admin pertama.
     * Route: GET /impersonate/{token}  (tersedia di semua subdomain)
     */
    public function enter(string $token)
    {
        $data = Cache::pull('impersonate_' . $token);

        if (!$data) {
            abort(403, 'Token impersonate tidak valid atau sudah kedaluwarsa (maks 5 menit).');
        }

        // Cari user admin pertama di database tenant ini
        $adminUser = DB::table('users')
            ->where('role', 'admin')
            ->orderBy('id')
            ->first();

        if (!$adminUser) {
            abort(404, 'Tidak ada akun admin di tenant ini.');
        }

        // Login sebagai admin tenant
        Auth::loginUsingId($adminUser->id);

        // Simpan info impersonasi di session
        session([
            'impersonating'       => true,
            'impersonate_by'      => $data['admin_name'],
            'impersonate_back_url' => $data['back_url'],
        ]);

        Log::info("Impersonate entered: tenant admin #{$adminUser->id} ({$adminUser->email})");

        return redirect('/admin/dashboard')
            ->with('success', "Mode Impersonate aktif. Login sebagai: {$adminUser->name} ({$adminUser->email})");
    }

    /**
     * Akhiri sesi impersonate → kembali ke portal super admin.
     */
    public function stop(Request $request)
    {
        $backUrl = session('impersonate_back_url', config('tenants.master_domain') . '/admin/tenants');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($backUrl);
    }
}

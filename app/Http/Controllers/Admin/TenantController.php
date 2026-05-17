<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function __construct(private TenantManager $manager) {}

    public function tutorial()
    {
        return view('admin.tenants.tutorial');
    }

    public function index()
    {
        $tenants  = Tenant::orderByDesc('created_at')->get();
        $features = config('tenants.features', []);
        $plans    = config('tenants.plans', []);
        return view('admin.tenants.index', compact('tenants', 'features', 'plans'));
    }

    public function create()
    {
        $features            = config('tenants.features', []);
        $plans               = config('tenants.plans', []);
        $dbAdminUser         = env('DB_ADMIN_USERNAME');
        $dbAdminConfigured   = !empty($dbAdminUser);
        return view('admin.tenants.create', compact('features', 'plans', 'dbAdminConfigured', 'dbAdminUser'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTenantRequest($request);

        try {
            $tenant = $this->manager->create($validated);
            return redirect()->route('admin.tenants.show', $tenant)
                ->with('success', "Tenant \"{$tenant->name}\" berhasil dibuat. Database: {$tenant->db_name}");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal membuat tenant: ' . $e->getMessage());
        }
    }

    public function storeAjax(Request $request)
    {
        $validated = $this->validateTenantRequest($request);
        $result    = $this->manager->createWithSteps($validated);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function systemCheck()
    {
        // Hanya cek konfigurasi — TIDAK koneksi ke DB agar tidak hang
        $adminUser    = env('DB_ADMIN_USERNAME');
        $adminConfigured = $adminUser !== null && $adminUser !== '';

        return response()->json([
            'admin_configured' => [
                'ok'    => $adminConfigured,
                'label' => $adminConfigured
                    ? "DB_ADMIN_USERNAME: {$adminUser} (dikonfigurasi)"
                    : 'DB_ADMIN_USERNAME belum dikonfigurasi di .env',
            ],
            'all_ok' => $adminConfigured,
        ]);
    }

    public function testCurrentDb()
    {
        try {
            DB::purge('mysql_admin');
            DB::connection('mysql_admin')->getPdo();
            return response()->json(['success' => true, 'message' => 'Koneksi berhasil']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Koneksi gagal: ' . $e->getMessage()]);
        }
    }

    public function testDbAdmin(Request $request)
    {
        $username = trim($request->input('db_admin_username', ''));
        $password = $request->input('db_admin_password', '');

        if (!$username) {
            return response()->json(['success' => false, 'message' => 'Username tidak boleh kosong']);
        }

        return $this->tryConnectAdmin($username, $password);
    }

    public function saveDbAdmin(Request $request)
    {
        $username = trim($request->input('db_admin_username', ''));
        $password = $request->input('db_admin_password', '');

        if (!$username) {
            return response()->json(['success' => false, 'message' => 'Username tidak boleh kosong']);
        }

        // Test koneksi dulu
        $test = $this->tryConnectAdmin($username, $password);
        $testData = $test->getData(true);
        if (!$testData['success']) {
            return $test;
        }

        // Simpan ke .env
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return response()->json(['success' => false, 'message' => 'File .env tidak ditemukan di server']);
        }

        $env = file_get_contents($envPath);
        foreach ([
            'DB_ADMIN_USERNAME' => $username,
            'DB_ADMIN_PASSWORD' => $password,
        ] as $key => $value) {
            if (preg_match("/^{$key}=/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        }
        file_put_contents($envPath, $env);

        // Clear config cache agar env() terbaca ulang
        try { \Artisan::call('config:clear'); } catch (\Throwable) {}

        return response()->json(['success' => true, 'message' => "Berhasil! {$username} disimpan sebagai DB admin"]);
    }

    private function tryConnectAdmin(string $username, string $password): \Illuminate\Http\JsonResponse
    {
        try {
            // Set timeout koneksi pendek agar tidak hang lama
            Config::set('database.connections.mysql_admin', array_merge(
                config('database.connections.mysql_admin'),
                [
                    'username' => $username,
                    'password' => $password,
                    'options'  => [\PDO::ATTR_CONNECT_TIMEOUT => 5],
                ]
            ));
            DB::purge('mysql_admin');
            DB::connection('mysql_admin')->getPdo();

            // Cek privilege CREATE
            $canCreate = false;
            try {
                $testDb = 'sipera_priv_test_' . time();
                DB::connection('mysql_admin')->statement("CREATE DATABASE IF NOT EXISTS `{$testDb}`");
                DB::connection('mysql_admin')->statement("DROP DATABASE IF EXISTS `{$testDb}`");
                $canCreate = true;
            } catch (\Throwable) {}

            if ($canCreate) {
                return response()->json(['success' => true, 'message' => "Koneksi OK — {$username} punya privilege CREATE DATABASE"]);
            }
            return response()->json(['success' => false, 'message' => "Terhubung tapi {$username} tidak punya privilege CREATE DATABASE. Coba user root."]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Koneksi gagal: ' . $e->getMessage()]);
        }
    }

    private function validateTenantRequest(Request $request): array
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'institution' => 'nullable|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:30',
            'subdomain'   => 'required|string|max:50|regex:/^[a-z0-9\-]+$/|unique:tenants,subdomain',
            'plan'        => 'required|in:' . implode(',', array_keys(config('tenants.plans', []))),
            'admin_name'  => 'nullable|string|max:255',
            'admin_email' => 'nullable|email|max:255',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $planDuration = config("tenants.plans.{$validated['plan']}.duration");
        $validated['status']        = $validated['plan'] === 'trial' ? 'trial' : 'active';
        $validated['trial_ends_at'] = $validated['plan'] === 'trial' && $planDuration ? now()->addDays($planDuration) : null;
        $validated['expires_at']    = ($validated['plan'] !== 'trial' && $planDuration) ? now()->addDays($planDuration) : null;

        return $validated;
    }

    public function show(Tenant $tenant)
    {
        $features = config('tenants.features', []);
        $plans    = config('tenants.plans', []);
        $stats    = [];

        try {
            $stats = $this->manager->stats($tenant);
        } catch (\Exception $e) {
            $stats = ['error' => $e->getMessage()];
        }

        return view('admin.tenants.show', compact('tenant', 'features', 'plans', 'stats'));
    }

    public function toggleFeature(Request $request, Tenant $tenant, string $feature)
    {
        $allFeatures = array_keys(config('tenants.features', []));
        if (!in_array($feature, $allFeatures)) {
            return back()->with('error', 'Fitur tidak dikenal.');
        }

        $newState = $tenant->toggleFeature($feature);
        $label    = config("tenants.features.{$feature}.label", $feature);
        $status   = $newState ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Fitur \"{$label}\" berhasil {$status} untuk {$tenant->name}.");
    }

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);
        return back()->with('success', "Tenant \"{$tenant->name}\" berhasil disuspend.");
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);
        return back()->with('success', "Tenant \"{$tenant->name}\" berhasil diaktifkan.");
    }

    public function migrate(Tenant $tenant)
    {
        $result = $this->manager->migrate($tenant);

        if ($result['success']) {
            return back()->with('success', "Migration berhasil untuk {$tenant->name}.");
        }

        return back()->with('error', "Migration gagal: {$result['output']}");
    }

    public function migrateAll()
    {
        $results = $this->manager->migrateAll();
        $failed  = collect($results)->filter(fn($r) => !$r['success'])->count();
        $total   = count($results);

        if ($failed === 0) {
            return back()->with('success', "Migration berhasil untuk semua {$total} tenant.");
        }

        return back()->with('error', "Migration selesai: {$failed} dari {$total} tenant gagal. Cek log server.");
    }

    public function monitoring()
    {
        $tenants  = Tenant::orderByDesc('created_at')->get();
        $statsList = [];
        foreach ($tenants as $t) {
            try {
                $statsList[$t->id] = $this->manager->stats($t);
            } catch (\Exception $e) {
                $statsList[$t->id] = ['error' => $e->getMessage(), 'db_ok' => false];
            }
        }
        return view('admin.tenants.monitoring', compact('tenants', 'statsList'));
    }

    public function renew(Request $request, Tenant $tenant)
    {
        $request->validate(['days' => 'required|integer|min:1|max:3650']);
        $this->manager->renew($tenant, (int) $request->days);
        return back()->with('success', "Masa aktif \"{$tenant->name}\" diperpanjang {$request->days} hari.");
    }

    public function changePlan(Request $request, Tenant $tenant)
    {
        $request->validate([
            'plan' => 'required|in:' . implode(',', array_keys(config('tenants.plans', []))),
        ]);
        $this->manager->changePlan($tenant, $request->plan);
        return back()->with('success', "Paket \"{$tenant->name}\" diubah ke " . ucfirst($request->plan) . '.');
    }

    public function updateBranding(Request $request, Tenant $tenant)
    {
        $request->validate([
            'app_name'      => 'nullable|string|max:100',
            'logo_url'      => 'nullable|url|max:500',
            'primary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'tagline'       => 'nullable|string|max:200',
        ]);

        $branding = array_filter([
            'app_name'      => $request->app_name,
            'logo_url'      => $request->logo_url,
            'primary_color' => $request->primary_color,
            'tagline'       => $request->tagline,
        ]);

        $tenant->update(['branding' => $branding ?: null]);
        return back()->with('success', "Branding \"{$tenant->name}\" berhasil diperbarui.");
    }

    public function destroy(Request $request, Tenant $tenant)
    {
        $request->validate(['confirm_name' => 'required|same:_tenant_name']);

        // Validasi nama konfirmasi
        if ($request->confirm_name !== $tenant->name) {
            return back()->with('error', 'Nama konfirmasi tidak sesuai.');
        }

        $name = $tenant->name;

        try {
            $this->manager->delete($tenant);
            return redirect()->route('admin.tenants.index')
                ->with('success', "Tenant \"{$name}\" dan seluruh datanya telah dihapus.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal hapus tenant: ' . $e->getMessage());
        }
    }
}

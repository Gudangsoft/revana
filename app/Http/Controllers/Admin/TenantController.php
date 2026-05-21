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

        // Test koneksi + cek privilege CREATE
        $test     = $this->tryConnectAdmin($username, $password);
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
        foreach (['DB_ADMIN_USERNAME' => $username, 'DB_ADMIN_PASSWORD' => $password] as $key => $value) {
            $env = preg_match("/^{$key}=/m", $env)
                ? preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env)
                : $env . "\n{$key}={$value}";
        }
        file_put_contents($envPath, $env);

        // Langsung GRANT CREATE ke app user agar tidak perlu admin lagi untuk tiap database
        $appUser = config('database.connections.mysql.username');
        $grantMsg = '';
        $hosts = ['localhost', '127.0.0.1', '%'];
        try {
            $conn = DB::connection('mysql_admin');
            foreach ($hosts as $h) {
                try {
                    $conn->statement("GRANT CREATE, DROP ON *.* TO '{$appUser}'@'{$h}'");
                } catch (\Throwable) {}
            }
            $conn->statement("FLUSH PRIVILEGES");
            $grantMsg = " dan hak CREATE DATABASE diberikan ke user <strong>{$appUser}</strong>";
        } catch (\Throwable $e) {
            Log::warning("GRANT ke app user gagal: " . $e->getMessage());
            $grantMsg = " (catatan: GRANT otomatis gagal, tapi root tersimpan untuk operasi berikutnya)";
        }

        // Clear config cache
        try { \Artisan::call('config:clear'); } catch (\Throwable) {}

        return response()->json([
            'success' => true,
            'message' => "Berhasil! <strong>{$username}</strong> disimpan sebagai DB admin{$grantMsg}",
        ]);
    }

    private function tryConnectAdmin(string $username, string $password): \Illuminate\Http\JsonResponse
    {
        $host = config('database.connections.mysql_admin.host', '127.0.0.1');
        $port = (int) config('database.connections.mysql_admin.port', 3306);

        // 1. Test apakah port MySQL bisa dijangkau (timeout 5 detik)
        $fp = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$fp) {
            return response()->json([
                'success' => false,
                'message' => "MySQL tidak dapat dijangkau di {$host}:{$port} — {$errstr}",
            ]);
        }
        fclose($fp);

        // 2. Test kredensial via PDO
        try {
            Config::set('database.connections.mysql_admin.username', $username);
            Config::set('database.connections.mysql_admin.password', $password);
            DB::purge('mysql_admin');
            DB::connection('mysql_admin')->getPdo();
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Login gagal: ' . $e->getMessage()]);
        }

        // 3. Cek apakah punya privilege CREATE DATABASE
        try {
            $testDb = 'sipera_priv_test_' . time();
            DB::connection('mysql_admin')->statement("CREATE DATABASE IF NOT EXISTS `{$testDb}`");
            DB::connection('mysql_admin')->statement("DROP DATABASE IF EXISTS `{$testDb}`");
            return response()->json([
                'success' => true,
                'message' => "OK — <strong>{$username}</strong> terhubung dan punya privilege CREATE DATABASE",
            ]);
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => "Terhubung tapi <strong>{$username}</strong> tidak punya privilege CREATE DATABASE. Gunakan user <strong>root</strong>.",
            ]);
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

    public function setupDb(Tenant $tenant)
    {
        try {
            $this->manager->setupDatabase($tenant);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal buat database: ' . $e->getMessage());
        }

        $result = $this->manager->migrate($tenant);
        if (!$result['success']) {
            return back()->with('error', 'Database dibuat tapi migrasi gagal: ' . $result['output']);
        }

        $credentials = $this->manager->seedAdminUser($tenant);
        if ($credentials && $tenant->phone) {
            $this->manager->sendWelcomeWa($tenant, $credentials);
        }

        return back()->with('success', "Database + migrasi berhasil untuk {$tenant->name}. Tenant siap digunakan.");
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

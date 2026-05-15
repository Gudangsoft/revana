<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Http\Request;

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
        $features = config('tenants.features', []);
        $plans    = config('tenants.plans', []);
        return view('admin.tenants.create', compact('features', 'plans'));
    }

    public function store(Request $request)
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

        // Tentukan expires_at dari durasi plan
        $planDuration = config("tenants.plans.{$validated['plan']}.duration", 14);
        $validated['status']        = $validated['plan'] === 'trial' ? 'trial' : 'active';
        $validated['trial_ends_at'] = $validated['plan'] === 'trial' ? now()->addDays($planDuration) : null;
        $validated['expires_at']    = $validated['plan'] !== 'trial' ? now()->addDays($planDuration) : null;

        try {
            $tenant = $this->manager->create($validated);
            return redirect()->route('admin.tenants.show', $tenant)
                ->with('success', "Tenant \"{$tenant->name}\" berhasil dibuat. Database: {$tenant->db_name}");
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal membuat tenant: ' . $e->getMessage());
        }
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Exports\MarketingsExport;
use App\Imports\MarketingsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MarketingController extends Controller
{
    public function index(Request $request)
    {
        $query = Marketing::query();
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Active status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $marketings = $query->latest()->paginate(request()->input('per_page', 20))->withQueryString();

        // Ranking data - Top 10 Marketing dengan point tertinggi
        $topMarketings = Marketing::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->take(10)
            ->get();

        return view('admin.marketings.index', compact('marketings', 'topMarketings'));
    }

    public function create()
    {
        return view('admin.marketings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'additional_phones' => 'nullable|array',
            'additional_phones.*' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
        ]);

        $validated['additional_phones'] = $this->cleanPhones($validated['additional_phones'] ?? []);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        Marketing::create($validated);

        return redirect()->route('admin.marketings.index')
            ->with('success', 'Marketing berhasil ditambahkan');
    }

    public function edit(Marketing $marketing)
    {
        return view('admin.marketings.edit', compact('marketing'));
    }

    public function update(Request $request, Marketing $marketing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'additional_phones' => 'nullable|array',
            'additional_phones.*' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
        ]);

        $validated['additional_phones'] = $this->cleanPhones($validated['additional_phones'] ?? []);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $marketing->update($validated);

        return redirect()->route('admin.marketings.index')
            ->with('success', 'Marketing berhasil diupdate');
    }

    /**
     * Buang entri nomor tambahan yang kosong.
     */
    private function cleanPhones(array $phones): array
    {
        return array_values(array_filter($phones, fn ($p) => filled($p)));
    }

    public function destroy(Marketing $marketing)
    {
        $marketing->delete();

        return redirect()->route('admin.marketings.index')
            ->with('success', 'Marketing berhasil dihapus');
    }

    /**
     * Login as a Marketing (Admin impersonation)
     */
    public function loginAs(Marketing $marketing)
    {
        if (!$marketing->is_active) {
            return redirect()->route('admin.marketings.index')
                ->with('error', 'Marketing tidak aktif, tidak dapat login sebagai Marketing ini.');
        }

        if (empty($marketing->password)) {
            return redirect()->route('admin.marketings.index')
                ->with('error', 'Marketing belum memiliki password, silakan set password terlebih dahulu.');
        }

        // Store original admin user ID in session for potential return
        session(['admin_impersonating' => Auth::id()]);

        // Login as Marketing (marketing guard)
        Auth::guard('marketing')->login($marketing);

        return redirect()->route('marketing.dashboard')
            ->with('success', 'Anda sekarang login sebagai ' . $marketing->name);
    }

    /**
     * Return from Marketing impersonation back to admin
     */
    public function returnToAdmin()
    {
        Auth::guard('marketing')->logout();
        session()->forget('admin_impersonating');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Kembali ke akun admin.');
    }

    /**
     * Export Marketings to Excel
     */
    public function export()
    {
        return Excel::download(new MarketingsExport, 'marketings_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Import Marketings from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new MarketingsImport, $request->file('file'));

            return redirect()->route('admin.marketings.index')
                ->with('success', 'Data marketing berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->route('admin.marketings.index')
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function array(): array
            {
                return [
                    ['John Doe', 'john@example.com', '081234567890', 'password123', 'Aktif'],
                ];
            }
            
            public function headings(): array
            {
                return ['Nama', 'Email', 'Telepon', 'Password', 'Status'];
            }
        }, 'template_marketing.xlsx');
    }

    /**
     * Reset individual Marketing password to default
     */
    public function resetPassword(Marketing $marketing)
    {
        $defaultPassword = 'marketing123';
        $marketing->password = Hash::make($defaultPassword);
        $marketing->save();

        return redirect()->route('admin.marketings.index')
            ->with('success', "Password untuk {$marketing->name} telah direset ke default: {$defaultPassword}");
    }

    /**
     * Reset all Marketing passwords to default
     */
    public function resetAllPasswords()
    {
        $defaultPassword = Hash::make('marketing123');
        
        // Use single query for better performance
        $count = DB::table('marketings')->update([
            'password' => $defaultPassword,
            'updated_at' => now()
        ]);
        
        return redirect()->route('admin.marketings.index')
            ->with('success', "Berhasil! Password {$count} Marketing telah direset ke default: marketing123");
    }
}

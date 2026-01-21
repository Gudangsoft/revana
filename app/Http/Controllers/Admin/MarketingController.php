<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MarketingController extends Controller
{
    public function index()
    {
        $marketings = Marketing::latest()->paginate(20);
        return view('admin.marketings.index', compact('marketings'));
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
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
        ]);

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
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $marketing->update($validated);

        return redirect()->route('admin.marketings.index')
            ->with('success', 'Marketing berhasil diupdate');
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
}

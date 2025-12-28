<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use Illuminate\Http\Request;

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
            'is_active' => 'boolean',
        ]);

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
            'is_active' => 'boolean',
        ]);

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
}

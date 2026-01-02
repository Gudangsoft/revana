<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accreditation;
use Illuminate\Http\Request;

class AccreditationController extends Controller
{
    public function index()
    {
        $accreditations = Accreditation::withCount('journals')->latest()->paginate(20);
        return view('admin.accreditations.index', compact('accreditations'));
    }

    public function create()
    {
        return view('admin.accreditations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accreditations,name',
            'points' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Accreditation::create($validated);

        return redirect()->route('admin.accreditations.index')
            ->with('success', 'Akreditasi berhasil ditambahkan');
    }

    public function edit(Accreditation $accreditation)
    {
        return view('admin.accreditations.edit', compact('accreditation'));
    }

    public function update(Request $request, Accreditation $accreditation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accreditations,name,' . $accreditation->id,
            'points' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $accreditation->update($validated);

        return redirect()->route('admin.accreditations.index')
            ->with('success', 'Akreditasi berhasil diupdate');
    }

    public function destroy(Accreditation $accreditation)
    {
        $journalCount = $accreditation->journals()->count();
        
        if ($journalCount > 0) {
            return redirect()->route('admin.accreditations.index')
                ->with('error', 'Tidak dapat menghapus akreditasi yang masih digunakan oleh ' . $journalCount . ' jurnal');
        }

        $accreditation->delete();

        return redirect()->route('admin.accreditations.index')
            ->with('success', 'Akreditasi berhasil dihapus');
    }
}

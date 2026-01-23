<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisJurnal;
use Illuminate\Http\Request;

class JenisJurnalController extends Controller
{
    public function index()
    {
        $jenisJurnals = JenisJurnal::latest()->paginate(20);
        return view('admin.jenis-jurnals.index', compact('jenisJurnals'));
    }

    public function create()
    {
        return view('admin.jenis-jurnals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:jenis_jurnals',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = true;
        JenisJurnal::create($validated);

        return redirect()->route('admin.jenis-jurnals.index')
            ->with('success', 'Jenis Jurnal berhasil ditambahkan');
    }

    public function edit(JenisJurnal $jenisJurnal)
    {
        return view('admin.jenis-jurnals.edit', compact('jenisJurnal'));
    }

    public function update(Request $request, JenisJurnal $jenisJurnal)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:jenis_jurnals,name,' . $jenisJurnal->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $jenisJurnal->update($validated);

        return redirect()->route('admin.jenis-jurnals.index')
            ->with('success', 'Jenis Jurnal berhasil diupdate');
    }

    public function destroy(JenisJurnal $jenisJurnal)
    {
        $jenisJurnal->delete();
        return redirect()->route('admin.jenis-jurnals.index')
            ->with('success', 'Jenis Jurnal berhasil dihapus');
    }

    public function toggleActive(JenisJurnal $jenisJurnal)
    {
        $jenisJurnal->update(['is_active' => !$jenisJurnal->is_active]);
        return redirect()->route('admin.jenis-jurnals.index')
            ->with('success', 'Status jenis jurnal berhasil diubah');
    }
}

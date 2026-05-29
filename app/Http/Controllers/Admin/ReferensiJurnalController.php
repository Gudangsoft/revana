<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferensiJurnal;
use Illuminate\Http\Request;

class ReferensiJurnalController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferensiJurnal::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_jurnal', 'like', "%{$search}%")
                  ->orWhere('jenis_jurnal', 'like', "%{$search}%")
                  ->orWhere('bidang_ilmu', 'like', "%{$search}%")
                  ->orWhere('referensi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $referensiJurnals = $query->latest()->paginate($request->input('per_page', 20))->withQueryString();

        return view('admin.referensi-jurnals.index', compact('referensiJurnals'));
    }

    public function create()
    {
        return view('admin.referensi-jurnals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jurnal' => 'required|string|max:255',
            'jenis_jurnal' => 'required|string|max:100',
            'bidang_ilmu'  => 'required|string|max:100',
            'tahun'        => 'required|integer|min:1900|max:2100',
            'referensi'    => 'required|string',
        ]);

        ReferensiJurnal::create($validated);

        return redirect()->route('admin.referensi-jurnals.index')
            ->with('success', 'Referensi Jurnal berhasil ditambahkan');
    }

    public function edit(ReferensiJurnal $referensiJurnal)
    {
        return view('admin.referensi-jurnals.edit', compact('referensiJurnal'));
    }

    public function update(Request $request, ReferensiJurnal $referensiJurnal)
    {
        $validated = $request->validate([
            'nama_jurnal' => 'required|string|max:255',
            'jenis_jurnal' => 'required|string|max:100',
            'bidang_ilmu'  => 'required|string|max:100',
            'tahun'        => 'required|integer|min:1900|max:2100',
            'referensi'    => 'required|string',
        ]);

        $referensiJurnal->update($validated);

        return redirect()->route('admin.referensi-jurnals.index')
            ->with('success', 'Referensi Jurnal berhasil diupdate');
    }

    public function destroy(ReferensiJurnal $referensiJurnal)
    {
        $referensiJurnal->delete();

        return redirect()->route('admin.referensi-jurnals.index')
            ->with('success', 'Referensi Jurnal berhasil dihapus');
    }
}

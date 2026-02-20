<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::latest()->paginate(request()->input('per_page', 20));
        return view('admin.kategoris.index', compact('kategoris'));
    }

    public function create()
    {
        return view('admin.kategoris.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:kategoris',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = true;
        Kategori::create($validated);

        return redirect()->route('admin.kategoris.index')
            ->with('success', 'Kategori berhasil ditambahkan');
    }

    public function edit(Kategori $kategori)
    {
        return view('admin.kategoris.edit', compact('kategori'));
    }

    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:kategoris,name,' . $kategori->id,
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $kategori->update($validated);

        return redirect()->route('admin.kategoris.index')
            ->with('success', 'Kategori berhasil diupdate');
    }

    public function destroy(Kategori $kategori)
    {
        $kategori->delete();
        return redirect()->route('admin.kategoris.index')
            ->with('success', 'Kategori berhasil dihapus');
    }

    public function toggleActive(Kategori $kategori)
    {
        $kategori->update(['is_active' => !$kategori->is_active]);
        return redirect()->route('admin.kategoris.index')
            ->with('success', 'Status kategori berhasil diubah');
    }
}

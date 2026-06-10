<?php

namespace App\Http\Controllers\Admin;

use App\Exports\KategorisExport;
use App\Http\Controllers\Controller;
use App\Imports\KategoriImport;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

    public function export()
    {
        return Excel::download(new KategorisExport, 'kategoris.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'File Excel wajib dipilih',
            'file.mimes'    => 'File harus berformat .xlsx, .xls, atau .csv',
            'file.max'      => 'Ukuran file maksimal 2MB',
        ]);

        try {
            $import = new KategoriImport;
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated  = $import->getUpdatedCount();

            $msg = 'Import berhasil!';
            if ($imported > 0) $msg .= " {$imported} data baru ditambahkan.";
            if ($updated  > 0) $msg .= " {$updated} data diperbarui.";
            if ($imported === 0 && $updated === 0) $msg = 'Tidak ada data yang diimport.';

            return redirect()->route('admin.kategoris.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->route('admin.kategoris.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_kategori.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'description', 'is_active']);
            fputcsv($file, ['Nasional', 'Jurnal nasional terakreditasi', 1]);
            fputcsv($file, ['Internasional', 'Jurnal internasional bereputasi', 1]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

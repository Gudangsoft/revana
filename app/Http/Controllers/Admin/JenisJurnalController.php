<?php

namespace App\Http\Controllers\Admin;

use App\Exports\JenisJurnalsExport;
use App\Http\Controllers\Controller;
use App\Imports\JenisJurnalImport;
use App\Models\JenisJurnal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class JenisJurnalController extends Controller
{
    public function index()
    {
        $jenisJurnals = JenisJurnal::latest()->paginate(request()->input('per_page', 20));
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

    public function export()
    {
        return Excel::download(new JenisJurnalsExport, 'jenis-jurnal.xlsx');
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
            $import = new JenisJurnalImport;
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated  = $import->getUpdatedCount();

            $msg = 'Import berhasil!';
            if ($imported > 0) $msg .= " {$imported} data baru ditambahkan.";
            if ($updated  > 0) $msg .= " {$updated} data diperbarui.";
            if ($imported === 0 && $updated === 0) $msg = 'Tidak ada data yang diimport.';

            return redirect()->route('admin.jenis-jurnals.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->route('admin.jenis-jurnals.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_jenis_jurnal.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'description', 'is_active']);
            fputcsv($file, ['Jurnal Nasional', 'Jurnal yang diterbitkan di Indonesia', 1]);
            fputcsv($file, ['Jurnal Internasional', 'Jurnal bereputasi internasional', 1]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

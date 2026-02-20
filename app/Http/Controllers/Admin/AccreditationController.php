<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accreditation;
use App\Exports\AccreditationsExport;
use App\Imports\AccreditationsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class AccreditationController extends Controller
{
    public function index(Request $request)
    {
        $query = Accreditation::withCount('journals');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $accreditations = $query->latest()->paginate(request()->input('per_page', 20));
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

    /**
     * Export accreditations to Excel
     */
    public function export(Request $request)
    {
        $search = $request->search;
        $filename = 'akreditasi_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new AccreditationsExport($search), $filename);
    }

    /**
     * Import accreditations from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ], [
            'file.required' => 'File Excel wajib diunggah',
            'file.mimes' => 'File harus berformat Excel (xlsx, xls) atau CSV',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            $import = new AccreditationsImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();

            $message = "Import berhasil! ";
            if ($imported > 0) {
                $message .= "{$imported} data baru ditambahkan. ";
            }
            if ($updated > 0) {
                $message .= "{$updated} data diperbarui.";
            }
            if ($imported == 0 && $updated == 0) {
                $message = "Tidak ada data yang diimport atau diperbarui.";
            }

            return redirect()->route('admin.accreditations.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('admin.accreditations.index')
                ->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    /**
     * Download template for import
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_akreditasi.xlsx"',
        ];

        // Create a simple template using Maatwebsite Excel
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
            public function array(): array
            {
                return [
                    ['SINTA 1', 100, 'Jurnal terakreditasi SINTA 1', 'Aktif'],
                    ['SINTA 2', 80, 'Jurnal terakreditasi SINTA 2', 'Aktif'],
                    ['SINTA 3', 60, 'Jurnal terakreditasi SINTA 3', 'Aktif'],
                    ['SINTA 4', 40, 'Jurnal terakreditasi SINTA 4', 'Aktif'],
                    ['SINTA 5', 20, 'Jurnal terakreditasi SINTA 5', 'Aktif'],
                    ['SINTA 6', 10, 'Jurnal terakreditasi SINTA 6', 'Aktif'],
                ];
            }

            public function headings(): array
            {
                return ['nama', 'points', 'deskripsi', 'status'];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, 'template_akreditasi.xlsx');
    }
}

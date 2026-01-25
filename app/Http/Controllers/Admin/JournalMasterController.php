<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\Accreditation;
use App\Exports\JournalMastersExport;
use App\Imports\JournalMastersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class JournalMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalMaster::with(['creator', 'slots']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_jurnal', 'like', "%{$search}%")
                  ->orWhere('nama_jurnal', 'like', "%{$search}%")
                  ->orWhere('publisher', 'like', "%{$search}%")
                  ->orWhere('accreditation', 'like', "%{$search}%");
            });
        }

        // Filter by publisher
        if ($request->filled('publisher')) {
            $query->where('publisher', 'like', '%' . $request->publisher . '%');
        }

        // Filter by accreditation
        if ($request->filled('accreditation')) {
            $query->where('accreditation', $request->accreditation);
        }

        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Filter by jenis_jurnal
        if ($request->filled('jenis_jurnal')) {
            $query->where('jenis_jurnal', $request->jenis_jurnal);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $journals = $query->latest()->paginate(20);
        $accreditations = Accreditation::where('is_active', true)->orderBy('points', 'desc')->get();
            
        return view('admin.journal-masters.index', compact('journals', 'accreditations'));
    }

    public function create()
    {
        $accreditations = Accreditation::where('is_active', true)->orderBy('points', 'desc')->get();
        return view('admin.journal-masters.create', compact('accreditations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_jurnal' => 'nullable|string|max:50|unique:journal_masters',
            'nama_jurnal' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'link_jurnal' => 'required|url',
            'accreditation' => 'nullable|string|max:50',
            'kategori' => 'nullable|in:Penelitian,PKM',
            'jenis_jurnal' => 'nullable|in:Jurnal Nasional,Jurnal Internasional',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true;

        JournalMaster::create($validated);

        return redirect()->route('admin.journal-masters.index')
            ->with('success', 'Data Jurnal berhasil ditambahkan');
    }

    public function show(JournalMaster $journalMaster)
    {
        $journalMaster->load(['creator', 'slots.submissions']);
        return view('admin.journal-masters.show', compact('journalMaster'));
    }

    public function edit(JournalMaster $journalMaster)
    {
        $accreditations = Accreditation::where('is_active', true)->orderBy('points', 'desc')->get();
        return view('admin.journal-masters.edit', compact('journalMaster', 'accreditations'));
    }

    public function update(Request $request, JournalMaster $journalMaster)
    {
        $validated = $request->validate([
            'kode_jurnal' => 'required|string|max:50|unique:journal_masters,kode_jurnal,' . $journalMaster->id,
            'nama_jurnal' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'link_jurnal' => 'required|url',
            'accreditation' => 'nullable|string|max:50',
            'kategori' => 'nullable|in:Penelitian,PKM',
            'jenis_jurnal' => 'nullable|in:Jurnal Nasional,Jurnal Internasional',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $journalMaster->update($validated);

        return redirect()->route('admin.journal-masters.index')
            ->with('success', 'Data Jurnal berhasil diperbarui');
    }

    public function destroy(JournalMaster $journalMaster)
    {
        // Check if journal has slots
        if ($journalMaster->slots()->count() > 0) {
            return back()->with('error', 'Jurnal tidak dapat dihapus karena masih memiliki slot');
        }

        $journalMaster->delete();

        return redirect()->route('admin.journal-masters.index')
            ->with('success', 'Data Jurnal berhasil dihapus');
    }

    public function toggleActive(JournalMaster $journalMaster)
    {
        $journalMaster->update(['is_active' => !$journalMaster->is_active]);
        
        $status = $journalMaster->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Jurnal berhasil {$status}");
    }

    /**
     * Bulk delete journal masters permanently
     */
    public function bulkDelete(Request $request)
    {
        // Handle GET request - redirect to index
        if ($request->isMethod('get')) {
            return redirect()->route('admin.journal-masters.index')
                ->with('info', 'Gunakan checkbox untuk memilih jurnal yang akan dihapus');
        }

        $request->validate([
            'journal_ids' => 'required|array|min:1',
            'journal_ids.*' => 'exists:journal_masters,id',
        ], [
            'journal_ids.required' => 'Pilih minimal 1 jurnal untuk dihapus',
            'journal_ids.min' => 'Pilih minimal 1 jurnal untuk dihapus',
        ]);

        try {
            $journalIds = $request->journal_ids;
            
            // Check if any journal has slots
            $journalsWithSlots = JournalMaster::whereIn('id', $journalIds)
                ->has('slots')
                ->pluck('nama_jurnal')
                ->toArray();
            
            if (!empty($journalsWithSlots)) {
                $journalNames = implode(', ', array_slice($journalsWithSlots, 0, 3));
                if (count($journalsWithSlots) > 3) {
                    $journalNames .= ' dan ' . (count($journalsWithSlots) - 3) . ' lainnya';
                }
                
                return back()->with('error', 
                    "Tidak dapat menghapus jurnal: {$journalNames}. Jurnal masih memiliki slot yang terkait. Hapus slot terlebih dahulu."
                );
            }
            
            // Perform permanent deletion
            $deletedCount = JournalMaster::whereIn('id', $journalIds)->forceDelete();
            
            return redirect()->route('admin.journal-masters.index')
                ->with('success', "Berhasil menghapus permanen {$deletedCount} jurnal.");
                
        } catch (\Exception $e) {
            \Log::error('Bulk delete journal masters error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus jurnal: ' . $e->getMessage());
        }
    }

    /**
     * Export journal masters to Excel
     */
    public function export(Request $request)
    {
        $search = $request->search;
        $filename = 'data_jurnal_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new JournalMastersExport($search), $filename);
    }

    /**
     * Import journal masters from Excel
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
            $import = new JournalMastersImport(auth()->id());
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();

            $message = "Import berhasil! ";
            if ($imported > 0) {
                $message .= "{$imported} data jurnal baru ditambahkan. ";
            }
            if ($updated > 0) {
                $message .= "{$updated} data jurnal diperbarui.";
            }
            if ($imported == 0 && $updated == 0) {
                $message = "Tidak ada data yang diimport atau diperbarui.";
            }

            return redirect()->route('admin.journal-masters.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('admin.journal-masters.index')
                ->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    /**
     * Download template for import
     */
    public function downloadTemplate()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
            public function array(): array
            {
                return [
                    ['', 'Jurnal Contoh 1', 'Publisher Contoh', 'https://contoh.jurnal.com', 'SINTA 1', 'Aktif'],
                    ['', 'Jurnal Contoh 2', 'Publisher Lain', 'https://contoh2.jurnal.com', 'SINTA 2', 'Aktif'],
                ];
            }

            public function headings(): array
            {
                return ['kode_jurnal', 'nama_jurnal', 'publisher', 'link_jurnal', 'akreditasi', 'status'];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, 'template_jurnal.xlsx');
    }
}

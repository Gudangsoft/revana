<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalSlot;
use App\Models\JournalMaster;
use App\Exports\JournalSlotsExport;
use App\Imports\JournalSlotsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class JournalSlotController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalSlot::with(['journalMaster', 'creator', 'submissions']);
        
        // Search by journal name, publisher, or kode_slot
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_slot', 'like', "%{$search}%")
                  ->orWhereHas('journalMaster', function($jq) use ($search) {
                      $jq->where('nama_jurnal', 'like', "%{$search}%")
                         ->orWhere('publisher', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by year
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        
        // Filter by month
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('kategori', $request->kategori);
            });
        }
        
        // Filter by jenis jurnal
        if ($request->filled('jenis')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('jenis_jurnal', $request->jenis);
            });
        }
        
        // Filter by akreditasi
        if ($request->filled('akreditasi')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('accreditation', $request->akreditasi);
            });
        }
        
        // Sorting
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'volume_asc':
                    $query->orderBy('volume', 'asc');
                    break;
                case 'volume_desc':
                    $query->orderBy('volume', 'desc');
                    break;
                case 'nomor_asc':
                    $query->orderBy('nomor', 'asc');
                    break;
                case 'nomor_desc':
                    $query->orderBy('nomor', 'desc');
                    break;
                case 'latest':
                    $query->latest();
                    break;
                default:
                    $query->orderBy('volume', 'desc')->orderBy('nomor', 'desc');
            }
        } else {
            $query->orderBy('volume', 'desc')->orderBy('nomor', 'desc');
        }
        
        $slots = $query->paginate(request()->input('per_page', 20));
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $accreditations = \App\Models\Accreditation::where('is_active', true)->orderBy('name')->get();
        $bulanOptions = JournalSlot::getBulanOptions();
        
        // If monitoring tab is active, load monitoring data
        $slotStats = null;
        if ($request->tab == 'monitoring') {
            $monitoringQuery = JournalSlot::with(['journalMaster', 'submissions']);
            
            // Filter by journal
            if ($request->filled('journal_master_id')) {
                $monitoringQuery->where('journal_master_id', $request->journal_master_id);
            }
            
            // Filter by year
            if ($request->filled('tahun')) {
                $monitoringQuery->where('tahun', $request->tahun);
            }
            
            $monitoringSlots = $monitoringQuery->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->get();
            
            $slotStats = $monitoringSlots->map(function($slot) {
                $total = $slot->jumlah_slot;
                $used = $slot->slot_terpakai;
                $available = $total - $used;
                $percentage = $total > 0 ? round(($used / $total) * 100, 1) : 0;
                
                // Determine status color
                $status = 'success'; // Default green
                if ($percentage >= 80) {
                    $status = 'danger'; // Red if >= 80%
                } elseif ($percentage >= 50) {
                    $status = 'warning'; // Yellow if >= 50%
                }
                
                return [
                    'slot' => $slot,
                    'total_slots' => $total,
                    'used_slots' => $used,
                    'available_slots' => $available,
                    'percentage' => $percentage,
                    'status' => $status
                ];
            });
        }
        
        return view('admin.journal-slots.index', compact('slots', 'journals', 'accreditations', 'bulanOptions', 'slotStats'));
    }

    public function create()
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $bulanOptions = JournalSlot::getBulanOptions();
        return view('admin.journal-slots.create', compact('journals', 'bulanOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_slot' => 'nullable|string|max:50|unique:journal_slots',
            'journal_master_id' => 'required|exists:journal_masters,id',
            'volume' => 'required|string|max:50',
            'nomor' => 'required|string|max:50',
            'bulan' => 'required|string|max:20',
            'tahun' => 'required|integer|min:2000|max:2100',
            'jumlah_slot' => 'required|integer|min:1',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['slot_terpakai'] = 0;
        $validated['is_active'] = true;

        JournalSlot::create($validated);

        return redirect()->route('admin.journal-slots.index')
            ->with('success', 'Data Slot berhasil ditambahkan');
    }

    public function show(JournalSlot $journalSlot)
    {
        $journalSlot->load(['journalMaster', 'creator', 'submissions.petugasSubmit']);
        return view('admin.journal-slots.show', compact('journalSlot'));
    }

    public function edit(JournalSlot $journalSlot)
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $bulanOptions = JournalSlot::getBulanOptions();
        return view('admin.journal-slots.edit', compact('journalSlot', 'journals', 'bulanOptions'));
    }

    public function update(Request $request, JournalSlot $journalSlot)
    {
        $validated = $request->validate([
            'kode_slot' => 'required|string|max:50|unique:journal_slots,kode_slot,' . $journalSlot->id,
            'journal_master_id' => 'required|exists:journal_masters,id',
            'volume' => 'required|string|max:50',
            'nomor' => 'required|string|max:50',
            'bulan' => 'required|string|max:20',
            'tahun' => 'required|integer|min:2000|max:2100',
            'jumlah_slot' => 'required|integer|min:' . $journalSlot->slot_terpakai,
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $journalSlot->update($validated);

        return redirect()->route('admin.journal-slots.index')
            ->with('success', 'Data Slot berhasil diperbarui');
    }

    public function destroy(JournalSlot $journalSlot)
    {
        // Check if slot has submissions
        if ($journalSlot->submissions()->count() > 0) {
            return back()->with('error', 'Slot tidak dapat dihapus karena masih memiliki submissions');
        }

        $journalSlot->delete();

        return redirect()->route('admin.journal-slots.index')
            ->with('success', 'Data Slot berhasil dihapus');
    }

    public function toggleActive(JournalSlot $journalSlot)
    {
        $journalSlot->update(['is_active' => !$journalSlot->is_active]);
        
        $status = $journalSlot->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Slot berhasil {$status}");
    }

    // Monitoring view
    public function monitoring(Request $request)
    {
        $query = JournalSlot::with(['journalMaster', 'submissions']);
        
        // Filter by journal
        if ($request->filled('journal_master_id')) {
            $query->where('journal_master_id', $request->journal_master_id);
        }
        
        // Filter by year
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        
        $slots = $query->where('is_active', true)->get();
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        
        // Calculate statistics
        $stats = [
            'total_slots' => $slots->sum('jumlah_slot'),
            'slots_terpakai' => $slots->sum('slot_terpakai'),
            'slots_tersedia' => $slots->sum('jumlah_slot') - $slots->sum('slot_terpakai'),
        ];
        
        // Calculate per slot
        $slotStats = $slots->map(function($slot) {
            $percentage = $slot->jumlah_slot > 0 ? ($slot->slot_terpakai / $slot->jumlah_slot) * 100 : 0;
            
            return [
                'slot' => $slot,
                'total_slots' => $slot->jumlah_slot,
                'used_slots' => $slot->slot_terpakai,
                'available_slots' => $slot->slot_tersedia,
                'percentage' => round($percentage, 1),
                'status' => $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success')
            ];
        })->sortByDesc('percentage');
        
        return view('admin.journal-slots.monitoring', compact('stats', 'slotStats', 'journals'));
    }

    // Get slots by journal (for AJAX)
    public function getByJournal(Request $request)
    {
        $slots = JournalSlot::where('journal_master_id', $request->journal_master_id)
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('nomor', 'desc')
            ->get()
            ->map(function($slot) {
                $sisa = $slot->jumlah_slot - $slot->slot_terpakai;
                return [
                    'id' => $slot->id,
                    'text' => sprintf(
                        'Vol. %s No. %s (%s) - Sisa: %d/%d slot',
                        $slot->volume ?? '-',
                        $slot->nomor ?? '-',
                        $slot->tahun,
                        $sisa > 0 ? $sisa : 0,
                        $slot->jumlah_slot
                    ),
                    'kode_slot' => $slot->kode_slot,
                    'jumlah_slot' => $slot->jumlah_slot,
                    'slot_terpakai' => $slot->slot_terpakai,
                    'sisa' => $sisa > 0 ? $sisa : 0
                ];
            });
            
        return response()->json($slots);
    }

    /**
     * Export journal slots to Excel
     */
    public function export(Request $request)
    {
        $filters = [
            'journal_master_id' => $request->journal_master_id,
            'tahun' => $request->tahun,
            'bulan' => $request->bulan,
        ];
        
        $filename = 'data_slot_' . date('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new JournalSlotsExport($filters), $filename);
    }

    /**
     * Import journal slots from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File Excel wajib diunggah',
            'file.mimes' => 'File harus berformat Excel (xlsx, xls) atau CSV',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            $import = new JournalSlotsImport(auth()->id());
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();
            $errors = $import->getErrors();
            $skipped = $import->getSkipped();

            $message = "Import berhasil! ";
            if ($imported > 0) {
                $message .= "{$imported} data slot baru ditambahkan. ";
            }
            if ($updated > 0) {
                $message .= "{$updated} data slot diperbarui. ";
            }
            if ($imported == 0 && $updated == 0) {
                $message = "Tidak ada data yang diimport atau diperbarui. ";
                if (count($errors) > 0) {
                    $message .= "\n\nJurnal tidak ditemukan:\n" . implode("\n", array_slice($errors, 0, 5));
                    if (count($errors) > 5) {
                        $message .= "\n... dan " . (count($errors) - 5) . " error lainnya.";
                    }
                }
            }

            return redirect()->route('admin.journal-slots.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('admin.journal-slots.index')
                ->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    /**
     * Download template for import
     */
    public function downloadTemplate()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithColumnWidths {
            public function array(): array
            {
                return [
                    ['', 'FUNDAMENTUM : Jurnal Pengabdian Multidisiplin', '1', '1', 'Januari', date('Y'), 30, 'Aktif'],
                    ['', 'FUNDAMENTUM : Jurnal Pengabdian Multidisiplin', '1', '2', 'Februari', date('Y'), 30, 'Aktif'],
                    ['', 'Jurnal Sains dan Kesehatan (JU...', '2', '1', 'Maret', date('Y'), 15, 'Aktif'],
                    ['', '', '', '', '', '', '', ''],
                    ['CATATAN:', '', '', '', '', '', '', ''],
                    ['- kode_slot: Kosongkan, akan auto-generate', '', '', '', '', '', '', ''],
                    ['- nama_jurnal: HARUS SESUAI dengan nama di database (copy-paste dari data jurnal)', '', '', '', '', '', '', ''],
                    ['- volume: Angka volume', '', '', '', '', '', '', ''],
                    ['- nomor: Angka nomor', '', '', '', '', '', '', ''],
                    ['- bulan: Nama bulan (Januari, Februari, dst)', '', '', '', '', '', '', ''],
                    ['- tahun: Tahun dalam angka', '', '', '', '', '', '', ''],
                    ['- jumlah_slot: Total slot tersedia', '', '', '', '', '', '', ''],
                    ['- status: Aktif / Tidak Aktif', '', '', '', '', '', '', ''],
                ];
            }

            public function headings(): array
            {
                return ['kode_slot', 'nama_jurnal', 'volume', 'nomor', 'bulan', 'tahun', 'jumlah_slot', 'status'];
            }

            public function columnWidths(): array
            {
                return [
                    'A' => 15,
                    'B' => 50,
                    'C' => 10,
                    'D' => 10,
                    'E' => 15,
                    'F' => 10,
                    'G' => 15,
                    'H' => 15,
                ];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, 'template_slot.xlsx');
    }
    
    /**
     * Display fasttrack slots for Pengalolaan Jurnal FS
     */
    public function fasttrackSlots(Request $request)
    {
        $query = JournalSlot::with(['journalMaster', 'creator', 'submissions'])
            ->whereHas('journalMaster', function($q) {
                // Filter hanya jurnal yang mendukung fasttrack (misal: kategori tertentu atau flag khusus)
                // Untuk sementara kita ambil semua jurnal, bisa disesuaikan nanti
                $q->where('is_active', true);
            });
        
        // Search by journal name, publisher, or kode_slot
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_slot', 'like', "%{$search}%")
                  ->orWhereHas('journalMaster', function($jq) use ($search) {
                      $jq->where('nama_jurnal', 'like', "%{$search}%")
                         ->orWhere('publisher', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by year
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        
        // Filter by month
        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Sorting
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'volume_asc':
                    $query->orderBy('volume', 'asc');
                    break;
                case 'volume_desc':
                    $query->orderBy('volume', 'desc');
                    break;
                case 'nomor_asc':
                    $query->orderBy('nomor', 'asc');
                    break;
                case 'nomor_desc':
                    $query->orderBy('nomor', 'desc');
                    break;
                case 'latest':
                    $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc');
                    break;
                default:
                    $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc');
            }
        } else {
            $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc');
        }
        
        $slots = $query->paginate(request()->input('per_page', 20))->withQueryString();
        
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        
        $bulanOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return view('admin.fasttrack-management.slots.index', compact('slots', 'journals', 'bulanOptions'));
    }

    /**
     * Sync slot_terpakai for a single slot based on actual submission count.
     */
    public function syncSlot(JournalSlot $journalSlot)
    {
        $before = $journalSlot->slot_terpakai;
        $after  = $journalSlot->recalculate();

        return back()->with('success', "Sync berhasil untuk slot {$journalSlot->kode_slot}. slot_terpakai: {$before} → {$after}.");
    }

    /**
     * Sync slot_terpakai for ALL slots based on actual submission counts.
     */
    public function syncAll()
    {
        $count = JournalSlot::recalculateAll();

        return back()->with('success', "Sync berhasil untuk {$count} slot. Semua nilai slot_terpakai telah diperbarui berdasarkan data submission aktual.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\JournalMaster;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicLoaController extends Controller
{
    public function index(Request $request)
    {
        // Get slots with journal info - focus on slot availability
        $query = JournalSlot::with(['journalMaster'])
            ->where('is_active', true);
        
        // Filter by journal if specified
        if ($request->filled('journal_id')) {
            $query->where('journal_master_id', $request->journal_id);
        }
        
        // Filter by indexation
        if ($request->filled('indexasi')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('accreditation', $request->indexasi);
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
        
        // Filter by kategori
        if ($request->filled('kategori')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('kategori', $request->kategori);
            });
        }
        
        // Filter by jenis
        if ($request->filled('jenis')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('jenis_jurnal', $request->jenis);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'tersedia') {
                $query->whereRaw('slot_terpakai < jumlah_slot');
            } elseif ($request->status === 'penuh') {
                $query->whereRaw('slot_terpakai >= jumlah_slot');
            }
        }
        
        // Filter by volume
        if ($request->filled('volume')) {
            $query->where('volume', $request->volume);
        }
        
        // Filter by nomor
        if ($request->filled('nomor')) {
            $query->where('nomor', $request->nomor);
        }
        
        // Filter by publisher
        if ($request->filled('publisher')) {
            $query->whereHas('journalMaster', function($q) use ($request) {
                $q->where('publisher', 'like', "%{$request->publisher}%");
            });
        }
        
        // Search by journal name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_slot', 'like', "%{$search}%")
                  ->orWhereHas('journalMaster', function($q2) use ($search) {
                      $q2->where('nama_jurnal', 'like', "%{$search}%")
                         ->orWhere('publisher', 'like', "%{$search}%")
                         ->orWhere('accreditation', 'like', "%{$search}%");
                  });
            });
        }
        
        // Sort by latest
        $slots = $query->orderBy('tahun', 'desc')
                      ->orderBy('bulan', 'desc')
                      ->paginate(15)
                      ->withQueryString();
        
        // Get all journals for filter
        $journals = JournalMaster::where('is_active', true)
            ->orderBy('nama_jurnal')
            ->get();
        
        // Get all active slots with journal data for deriving filter options
        $activeSlots = JournalSlot::where('is_active', true)
            ->with('journalMaster')
            ->get();
        
        // Get unique indexations - derived from actual active slot data
        $indexations = $activeSlots->pluck('journalMaster.accreditation')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        
        // Calculate total slots and usage
        $totalSlots = $activeSlots->sum('jumlah_slot');
        $totalTerpakai = $activeSlots->sum('slot_terpakai');
        $totalTersedia = max(0, $totalSlots - $totalTerpakai);
        
        // Statistics - focus on slot availability only
        $stats = [
            'total_slots' => $totalSlots,
            'slot_terpakai' => $totalTerpakai,
            'slot_tersedia' => $totalTersedia,
            'persentase_terpakai' => $totalSlots > 0 ? round(($totalTerpakai / $totalSlots) * 100, 1) : 0,
        ];
        
        // Get year options - derived from actual active slot data
        $tahunOptions = $activeSlots->pluck('tahun')
            ->unique()
            ->sortDesc()
            ->values();
        
        // Get month options - only months with active slots
        $bulanActive = $activeSlots->pluck('bulan')->unique()->toArray();
        $allBulan = JournalSlot::getBulanOptions();
        $bulanOptions = [];
        foreach ($allBulan as $key => $val) {
            if (in_array($key, $bulanActive)) {
                $bulanOptions[$key] = $val;
            }
        }
        
        // Get kategori options - derived from actual active slot data
        $kategoriOptions = $activeSlots->pluck('journalMaster.kategori')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        
        // Get jenis options - derived from actual active slot data
        $jenisOptions = $activeSlots->pluck('journalMaster.jenis_jurnal')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        
        // Get volume options - derived from actual active slot data
        $volumeOptions = $activeSlots->pluck('volume')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        
        // Get nomor options - derived from actual active slot data
        $nomorOptions = $activeSlots->pluck('nomor')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        
        // Get publisher options - derived from actual active slot data
        $publisherOptions = $activeSlots->pluck('journalMaster.publisher')
            ->filter()
            ->unique()
            ->sort()
            ->values();
        
        // Get settings for favicon
        $settings = [
            'favicon' => Setting::get('favicon', ''),
            'logo' => Setting::get('logo', ''),
            'app_name' => env('APP_NAME', 'SIPERA'),
        ];
        
        return view('public.slot-info', compact('slots', 'journals', 'indexations', 'stats', 'settings', 'tahunOptions', 'bulanOptions', 'kategoriOptions', 'jenisOptions', 'volumeOptions', 'nomorOptions', 'publisherOptions'));
    }
    
    public function show(JournalSlot $slot)
    {
        // Load relations
        $slot->load(['journalMaster', 'submissions.author']);
        
        // Get submissions in this slot
        $submissions = $slot->submissions()
            ->with(['author'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('public.loa-detail', compact('slot', 'submissions'));
    }
}

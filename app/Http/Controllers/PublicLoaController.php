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
        
        // Search by journal name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_slot', 'like', "%{$search}%")
                  ->orWhereHas('journalMaster', function($q2) use ($search) {
                      $q2->where('nama_jurnal', 'like', "%{$search}%")
                         ->orWhere('publisher', 'like', "%{$search}%");
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
        
        // Get unique indexations
        $indexations = JournalMaster::select('accreditation')
            ->distinct()
            ->whereNotNull('accreditation')
            ->orderBy('accreditation')
            ->pluck('accreditation');
        
        // Calculate total slots and usage
        $allSlots = JournalSlot::where('is_active', true)->get();
        $totalSlots = $allSlots->sum('jumlah_slot');
        $totalTerpakai = $allSlots->sum('slot_terpakai');
        $totalTersedia = max(0, $totalSlots - $totalTerpakai);
        
        // Statistics - focus on slot availability only
        $stats = [
            'total_slots' => $totalSlots,
            'slot_terpakai' => $totalTerpakai,
            'slot_tersedia' => $totalTersedia,
            'persentase_terpakai' => $totalSlots > 0 ? round(($totalTerpakai / $totalSlots) * 100, 1) : 0,
        ];
        
        // Get year options for filter
        $tahunOptions = JournalSlot::select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');
        
        // Get month options
        $bulanOptions = JournalSlot::getBulanOptions();
        
        // Get settings for favicon
        $settings = [
            'favicon' => Setting::get('favicon', ''),
            'logo' => Setting::get('logo', ''),
            'app_name' => env('APP_NAME', 'SIPERA'),
        ];
        
        return view('public.slot-info', compact('slots', 'journals', 'indexations', 'stats', 'settings', 'tahunOptions', 'bulanOptions'));
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

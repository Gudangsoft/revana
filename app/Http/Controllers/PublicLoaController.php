<?php

namespace App\Http\Controllers;

use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\JournalMaster;
use Illuminate\Http\Request;

class PublicLoaController extends Controller
{
    public function index(Request $request)
    {
        // Get slots with journal info and submission count
        $query = JournalSlot::with(['journalMaster', 'submissions'])
            ->select('journal_slots.*')
            ->withCount('submissions');
        
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
        
        // Search by journal name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_slot', 'like', "%{$search}%")
                  ->orWhereHas('journalMaster', function($q2) use ($search) {
                      $q2->where('nama_jurnal', 'like', "%{$search}%")
                         ->orWhere('rumpun_ilmu', 'like', "%{$search}%");
                  });
            });
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $slots = $query->paginate(10)->withQueryString();
        
        // Get all journals for filter
        $journals = JournalMaster::where('is_active', true)
            ->orderBy('nama_jurnal')
            ->get();
        
        // Get unique indexations
        $indexations = JournalMaster::select('accreditation')
            ->distinct()
            ->whereNotNull('accreditation')
            ->pluck('accreditation');
        
        // Statistics by indexation
        $stats = [
            'total_slots' => JournalSlot::count(),
            'total_journals' => JournalMaster::where('is_active', true)->count(),
            'slot_terisi' => Submission::whereNotNull('journal_slot_id')->count(),
            'nasional' => JournalMaster::where('accreditation', 'NASIONAL')->count(),
            'sinta4' => JournalMaster::where('accreditation', 'SINTA 4')->count(),
            'sinta5' => JournalMaster::where('accreditation', 'SINTA 5')->count(),
            'internasional' => JournalMaster::where('accreditation', 'INTERNASIONAL')->count(),
        ];
        
        return view('public.slot-info', compact('slots', 'journals', 'indexations', 'stats'));
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

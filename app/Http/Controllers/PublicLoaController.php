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
        // Get all submissions with slot and journal info
        $query = Submission::with(['journalSlot.journalMaster'])
            ->whereNotNull('journal_slot_id');
        
        // Filter by slot if specified
        if ($request->filled('slot_id')) {
            $query->where('journal_slot_id', $request->slot_id);
        }
        
        // Filter by journal if specified
        if ($request->filled('journal_id')) {
            $query->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_id);
            });
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $submissions = $query->paginate(20)->withQueryString();
        
        // Get all journals for filter
        $journals = \App\Models\JournalMaster::orderBy('nama_jurnal')->get();
        
        // Get all slots for filter
        $slots = \App\Models\JournalSlot::with('journalMaster')
            ->orderBy('kode_slot', 'desc')
            ->get();
        
        // Statistics
        $stats = [
            'total_submissions' => Submission::whereNotNull('journal_slot_id')->count(),
            'total_slots' => \App\Models\JournalSlot::count(),
            'total_journals' => \App\Models\JournalMaster::where('is_active', true)->count(),
        ];
        
        return view('public.slot-info', compact('submissions', 'journals', 'slots', 'stats'));
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

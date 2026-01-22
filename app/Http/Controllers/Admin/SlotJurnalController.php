<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalSlot;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SlotJurnalController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalSlot::with(['journalMaster', 'submissions']);
        
        // Search by LOA
        if ($request->filled('search_loa')) {
            $searchLoa = $request->search_loa;
            $query->where('kode_loa', 'like', "%{$searchLoa}%");
        }
        
        // Filter by journal
        if ($request->filled('journal_id')) {
            $query->where('journal_master_id', $request->journal_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->whereColumn('jumlah_terisi', '<', 'kapasitas');
            } elseif ($request->status === 'full') {
                $query->whereColumn('jumlah_terisi', '>=', 'kapasitas');
            }
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'kode_loa');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);
        
        $slots = $query->paginate(20)->withQueryString();
        
        // Get all journals for filter
        $journals = \App\Models\JournalMaster::orderBy('nama_jurnal')->get();
        
        // Statistics
        $stats = [
            'total_slots' => JournalSlot::count(),
            'available_slots' => JournalSlot::whereColumn('jumlah_terisi', '<', 'kapasitas')->count(),
            'full_slots' => JournalSlot::whereColumn('jumlah_terisi', '>=', 'kapasitas')->count(),
            'total_submissions' => Submission::count(),
        ];
        
        return view('admin.slot-jurnal.index', compact('slots', 'journals', 'stats'));
    }
}

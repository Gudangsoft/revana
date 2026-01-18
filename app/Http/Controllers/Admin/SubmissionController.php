<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\JournalSlot;
use App\Models\JournalMaster;
use App\Models\User;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::with([
            'journalSlot.journalMaster',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction',
            'creator'
        ]);
        
        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_submit', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_submit', '<=', $request->tanggal_sampai);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by journal
        if ($request->filled('journal_master_id')) {
            $query->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_master_id);
            });
        }
        
        $submissions = $query->latest('tanggal_submit')->paginate(20);
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $statusOptions = Submission::getStatusOptions();
        
        return view('admin.submissions.index', compact('submissions', 'journals', 'statusOptions'));
    }

    public function create()
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $slots = collect();
        $users = User::orderBy('name')->get();
        
        return view('admin.submissions.create', compact('journals', 'slots', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'id_artikel' => 'required|string|max:255',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => 'nullable|url',
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'pic_marketing' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Check slot availability
        $slot = JournalSlot::findOrFail($validated['journal_slot_id']);
        if ($slot->is_full) {
            return back()->with('error', 'Slot sudah penuh');
        }

        $validated['created_by'] = auth()->id();
        $validated['petugas_submit_id'] = auth()->id(); // Otomatis saat login
        $validated['tanggal_submit'] = now()->toDateString();
        $validated['status'] = 'SUBMITTED';

        Submission::create($validated);

        return redirect()->route('admin.submissions.index')
            ->with('success', 'Data Submit berhasil ditambahkan');
    }

    public function show(Submission $submission)
    {
        $submission->load([
            'journalSlot.journalMaster',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction',
            'creator'
        ]);
        
        return view('admin.submissions.show', compact('submission'));
    }

    public function edit(Submission $submission)
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $slots = JournalSlot::where('journal_master_id', $submission->journalSlot->journal_master_id)
            ->where('is_active', true)
            ->get();
        $users = User::orderBy('name')->get();
        $statusOptions = Submission::getStatusOptions();
        
        return view('admin.submissions.edit', compact('submission', 'journals', 'slots', 'users', 'statusOptions'));
    }

    public function update(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'id_artikel' => 'required|string|max:255',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => 'nullable|url',
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'pic_marketing' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            
            // Workflow fields
            'petugas_editor1_id' => 'nullable|exists:users,id',
            'username_editor' => 'nullable|string|max:255',
            'password_editor' => 'nullable|string|max:255',
            
            'petugas_author1_id' => 'nullable|exists:users,id',
            
            'petugas_editor2_id' => 'nullable|exists:users,id',
            
            'petugas_reviewer1_id' => 'nullable|exists:users,id',
            'username_reviewer1' => 'nullable|string|max:255',
            'password_reviewer1' => 'nullable|string|max:255',
            'catatan_reviewer1' => 'nullable|string',
            
            'petugas_reviewer2_id' => 'nullable|exists:users,id',
            'username_reviewer2' => 'nullable|string|max:255',
            'password_reviewer2' => 'nullable|string|max:255',
            'catatan_reviewer2' => 'nullable|string',
            
            'petugas_editor3_id' => 'nullable|exists:users,id',
            
            'petugas_author2_id' => 'nullable|exists:users,id',
            
            'petugas_production_id' => 'nullable|exists:users,id',
            
            'link_publish' => 'nullable|url',
            'status' => 'nullable|string',
        ]);

        // Handle slot change
        if ($validated['journal_slot_id'] != $submission->journal_slot_id) {
            // Check new slot availability
            $newSlot = JournalSlot::findOrFail($validated['journal_slot_id']);
            if ($newSlot->is_full) {
                return back()->with('error', 'Slot tujuan sudah penuh');
            }
            
            // Decrement old slot
            $submission->journalSlot->decrement('slot_terpakai');
            // Increment new slot
            $newSlot->increment('slot_terpakai');
        }

        $submission->update($validated);

        return redirect()->route('admin.submissions.index')
            ->with('success', 'Data Submit berhasil diperbarui');
    }

    public function destroy(Submission $submission)
    {
        $submission->delete();

        return redirect()->route('admin.submissions.index')
            ->with('success', 'Data Submit berhasil dihapus');
    }

    // Process workflow - show process view
    public function process(Submission $submission)
    {
        $submission->load([
            'journalSlot.journalMaster',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction',
            'creator'
        ]);
        
        $users = User::orderBy('name')->get();
        
        return view('admin.submissions.process', compact('submission', 'users'));
    }

    // Update process step
    public function updateProcess(Request $request, Submission $submission)
    {
        $step = $request->input('step');
        
        switch ($step) {
            case 'editor1':
                $validated = $request->validate([
                    'petugas_editor1_id' => 'required|exists:users,id',
                    'username_editor' => 'required|string|max:255',
                    'password_editor' => 'required|string|max:255',
                ]);
                $submission->update(array_merge($validated, ['status' => 'EDITOR1_PROCESS']));
                break;
                
            case 'author1':
                $validated = $request->validate([
                    'petugas_author1_id' => 'required|exists:users,id',
                ]);
                $submission->update($validated);
                break;
                
            case 'editor2':
                $validated = $request->validate([
                    'petugas_editor2_id' => 'required|exists:users,id',
                ]);
                $submission->update($validated);
                break;
                
            case 'reviewer1':
                $validated = $request->validate([
                    'petugas_reviewer1_id' => 'required|exists:users,id',
                    'username_reviewer1' => 'required|string|max:255',
                    'password_reviewer1' => 'required|string|max:255',
                ]);
                $submission->update($validated);
                break;
                
            case 'reviewer2':
                $validated = $request->validate([
                    'petugas_reviewer2_id' => 'required|exists:users,id',
                    'username_reviewer2' => 'required|string|max:255',
                    'password_reviewer2' => 'required|string|max:255',
                ]);
                $submission->update($validated);
                break;
                
            case 'editor3':
                $validated = $request->validate([
                    'petugas_editor3_id' => 'required|exists:users,id',
                ]);
                $submission->update($validated);
                break;
                
            case 'author2':
                $validated = $request->validate([
                    'petugas_author2_id' => 'required|exists:users,id',
                ]);
                $submission->update($validated);
                break;
                
            case 'production':
                $validated = $request->validate([
                    'petugas_production_id' => 'required|exists:users,id',
                    'link_publish' => 'nullable|url',
                ]);
                $submission->update($validated);
                break;
        }
        
        return back()->with('success', 'Data proses berhasil diperbarui');
    }

    // Validate step
    public function validateStep(Request $request, Submission $submission)
    {
        $step = $request->input('step');
        
        switch ($step) {
            case 'editor1':
                $submission->validateEditor1();
                break;
            case 'author1':
                $submission->validateAuthor1();
                break;
            case 'editor2':
                $submission->validateEditor2();
                break;
            case 'reviewer1':
                $submission->validateReviewer1();
                break;
            case 'reviewer2':
                $submission->validateReviewer2();
                break;
            case 'editor3':
                $submission->validateEditor3();
                break;
            case 'author2':
                $submission->validateAuthor2();
                break;
            case 'production':
                $submission->validateProduction();
                break;
        }
        
        return back()->with('success', 'Langkah berhasil divalidasi');
    }

    // Update reviewer notes
    public function updateReviewerNotes(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'catatan_reviewer1' => 'nullable|string',
            'catatan_reviewer2' => 'nullable|string',
        ]);
        
        $submission->update($validated);
        
        return back()->with('success', 'Catatan reviewer berhasil diperbarui');
    }

    // Monitoring view with filter by date
    public function monitoring(Request $request)
    {
        $query = Submission::with([
            'journalSlot.journalMaster',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction',
        ]);
        
        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_submit', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_submit', '<=', $request->tanggal_sampai);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by journal
        if ($request->filled('journal_master_id')) {
            $query->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_master_id);
            });
        }
        
        $submissions = $query->latest('tanggal_submit')->get();
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $statusOptions = Submission::getStatusOptions();
        
        // Statistics
        $stats = [
            'total' => $submissions->count(),
            'submitted' => $submissions->where('status', 'SUBMITTED')->count(),
            'in_process' => $submissions->whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count(),
            'published' => $submissions->where('status', 'PUBLISHED')->count(),
            'rejected' => $submissions->where('status', 'REJECTED')->count(),
        ];
        
        return view('admin.submissions.monitoring', compact('submissions', 'journals', 'statusOptions', 'stats'));
    }
}

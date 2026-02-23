<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionHistory;
use App\Models\JournalSlot;
use App\Models\JournalMaster;
use App\Models\User;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use App\Exports\SubmissionsExport;
use App\Imports\SubmissionsImport;
use Maatwebsite\Excel\Facades\Excel;
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
            'creator',
            'marketing'
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
        
        // Filter by journal (using search term)
        if ($request->filled('journal_search')) {
            $searchTerm = $request->journal_search;
            $query->whereHas('journalSlot.journalMaster', function($q) use ($searchTerm) {
                $q->where('nama_jurnal', 'like', '%' . $searchTerm . '%')
                  ->orWhere('publisher', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Filter by journal master ID (legacy support)
        if ($request->filled('journal_master_id')) {
            $query->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_master_id);
            });
        }
        
        $submissions = $query->latest('tanggal_submit')->paginate(request()->input('per_page', 20));
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $statusOptions = Submission::getStatusOptions();
        
        return view('admin.submissions.index', compact('submissions', 'journals', 'statusOptions'));
    }

    public function create()
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $slots = collect();
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        
        // Data Kategori dan Jenis Jurnal
        $kategoris = \App\Models\Kategori::where('is_active', true)->orderBy('name')->get();
        $jenisJurnals = \App\Models\JenisJurnal::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.submissions.create', compact('journals', 'slots', 'pics', 'marketings', 'kategoris', 'jenisJurnals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'jenis_jurnal_id' => 'nullable|exists:jenis_jurnals,id',
            'id_artikel' => 'required|string|max:255',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => 'nullable|url',
            'file_artikel' => ['nullable', 'file', 'max:10240', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
        ]);

        // Check slot availability
        $slot = JournalSlot::findOrFail($validated['journal_slot_id']);
        if ($slot->is_full) {
            return back()->with('error', 'Slot sudah penuh');
        }

        // Handle file upload
        if ($request->hasFile('file_artikel')) {
            $file = $request->file('file_artikel');
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('submissions/articles', $filename, 'public');
            $validated['file_artikel'] = 'submissions/articles/' . $filename;
            $validated['file_artikel_original_name'] = $originalName;
        }

        $validated['created_by'] = auth()->id();
        // petugas_submit_id is now from pics table (no default)
        $validated['tanggal_submit'] = now()->toDateString();
        $validated['status'] = 'SUBMITTED';
        
        // Set pic_marketing from marketing name for backward compatibility
        if (!empty($validated['marketing_id'])) {
            $marketing = Marketing::find($validated['marketing_id']);
            if ($marketing) {
                $validated['pic_marketing'] = $marketing->name;
            }
        }

        $submission = Submission::create($validated);
        
        // Log history
        $submission->logHistory('submit', 'submitted', 'Artikel baru disubmit', [
            'judul_artikel' => $submission->judul_artikel,
            'nama_penulis' => $submission->nama_penulis,
        ]);
        
        // Award points to Marketing
        $pointMessage = '';
        if (!empty($validated['marketing_id'])) {
            $pointHistory = MarketingPointHistory::awardPoints(
                $validated['marketing_id'],
                $submission->id,
                "Submit artikel: {$submission->kode_submit} - {$submission->judul_artikel}"
            );
            
            if ($pointHistory) {
                $marketing = Marketing::find($validated['marketing_id']);
                $pointMessage = " Marketing {$marketing->name} mendapatkan +{$pointHistory->points_earned} point!";
            }
        }

        // Award points to PIC submit
        if (!empty($validated['petugas_submit_id'])) {
            $picHistory = PicPointHistory::awardPoints(
                $validated['petugas_submit_id'],
                $submission->id,
                'submit',
                "Submit artikel: {$submission->kode_submit} - {$submission->judul_artikel}"
            );
            if ($picHistory) {
                $pic = Pic::find($validated['petugas_submit_id']);
                $pic->increment('total_points', $picHistory->points_earned);
            }
        }

        return redirect()->route('admin.submissions.index')
            ->with('success', 'Data Submit berhasil ditambahkan.' . $pointMessage);
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
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $statusOptions = Submission::getStatusOptions();
        
        // Data Kategori dan Jenis Jurnal
        $kategoris = \App\Models\Kategori::where('is_active', true)->orderBy('name')->get();
        $jenisJurnals = \App\Models\JenisJurnal::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.submissions.edit', compact('submission', 'journals', 'slots', 'users', 'pics', 'marketings', 'statusOptions', 'kategoris', 'jenisJurnals'));
    }

    public function update(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'jenis_jurnal_id' => 'nullable|exists:jenis_jurnals,id',
            'id_artikel' => 'required|string|max:255',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => 'nullable|url',
            'file_artikel' => ['nullable', 'file', 'max:10240', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
            
            // Workflow fields - Editor, Author, Production use pics table
            'petugas_editor1_id' => 'nullable|exists:pics,id',
            'username_editor' => 'nullable|string|max:255',
            'password_editor' => 'nullable|string|max:255',
            
            'petugas_author1_id' => 'nullable|exists:pics,id',
            
            'petugas_editor2_id' => 'nullable|exists:pics,id',
            
            // Reviewers use users table
            'petugas_reviewer1_id' => 'nullable|exists:users,id',
            'username_reviewer1' => 'nullable|string|max:255',
            'password_reviewer1' => 'nullable|string|max:255',
            'catatan_reviewer1' => 'nullable|string',
            
            'petugas_reviewer2_id' => 'nullable|exists:users,id',
            'username_reviewer2' => 'nullable|string|max:255',
            'password_reviewer2' => 'nullable|string|max:255',
            'catatan_reviewer2' => 'nullable|string',
            
            'petugas_editor3_id' => 'nullable|exists:pics,id',
            
            'petugas_author2_id' => 'nullable|exists:pics,id',
            
            'petugas_production_id' => 'nullable|exists:pics,id',
            
            'link_publish' => 'nullable|url',
            'status' => 'nullable|string',
        ]);
        
        // Set pic_marketing from marketing name for backward compatibility
        if (!empty($validated['marketing_id'])) {
            $marketing = Marketing::find($validated['marketing_id']);
            if ($marketing) {
                $validated['pic_marketing'] = $marketing->name;
            }
        }

        // Handle file upload
        if ($request->hasFile('file_artikel')) {
            // Delete old file if exists
            if ($submission->file_artikel && \Storage::disk('public')->exists($submission->file_artikel)) {
                \Storage::disk('public')->delete($submission->file_artikel);
            }
            
            $file = $request->file('file_artikel');
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('submissions/articles', $filename, 'public');
            $validated['file_artikel'] = 'submissions/articles/' . $filename;
            $validated['file_artikel_original_name'] = $originalName;
        }

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
        // Revert marketing points if any were awarded
        if ($submission->marketing_id) {
            $pointHistory = MarketingPointHistory::where('marketing_id', $submission->marketing_id)
                ->where('submission_id', $submission->id)
                ->first();
            
            if ($pointHistory) {
                // Delete the point history record
                $pointHistory->delete();
            }
            
            // Recalculate total_points from actual submission count (minus this one being deleted)
            $marketing = Marketing::find($submission->marketing_id);
            if ($marketing) {
                $remainingSubmissions = Submission::where('marketing_id', $submission->marketing_id)
                    ->where('id', '!=', $submission->id)
                    ->count();
                $marketing->total_points = $remainingSubmissions;
                $marketing->save();
            }
        }
        
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
            'creator',
            'histories.user'
        ]);
        
        $users = User::orderBy('name')->get();
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        
        // Group histories by step
        $historiesByStep = $submission->histories->sortBy('created_at')->groupBy('step');
        
        return view('admin.submissions.process', compact('submission', 'users', 'pics', 'historiesByStep'));
    }

    // Update process step
    public function updateProcess(Request $request, Submission $submission)
    {
        $step = $request->input('step');
        
        switch ($step) {
            case 'editor1':
                $validated = $request->validate([
                    'petugas_editor1_id' => 'required|exists:pics,id',
                    'username_editor' => 'required|string|max:255',
                    'password_editor' => 'required|string|max:255',
                ]);
                $submission->update(array_merge($validated, ['status' => 'EDITOR1_PROCESS']));
                
                $petugas = Pic::find($validated['petugas_editor1_id']);
                $submission->logHistory('editor1', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_editor1_id'],
                    'petugas_name' => $petugas->name,
                ]);
                $submission->logHistory('editor1', 'credential_added', 'Kredensial editor ditambahkan');
                break;
                
            case 'author1':
                $validated = $request->validate([
                    'petugas_author1_id' => 'required|exists:pics,id',
                ]);
                $submission->update($validated);
                
                $petugas = Pic::find($validated['petugas_author1_id']);
                $submission->logHistory('author1', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_author1_id'],
                    'petugas_name' => $petugas->name,
                ]);
                break;
                
            case 'editor2':
                $validated = $request->validate([
                    'petugas_editor2_id' => 'required|exists:pics,id',
                ]);
                $submission->update($validated);
                
                $petugas = Pic::find($validated['petugas_editor2_id']);
                $submission->logHistory('editor2', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_editor2_id'],
                    'petugas_name' => $petugas->name,
                ]);
                break;
                
            case 'reviewer1':
                $validated = $request->validate([
                    'petugas_reviewer1_id' => 'required|exists:users,id',
                    'username_reviewer1' => 'required|string|max:255',
                    'password_reviewer1' => 'required|string|max:255',
                ]);
                $submission->update($validated);
                
                $petugas = User::find($validated['petugas_reviewer1_id']);
                $submission->logHistory('reviewer1', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_reviewer1_id'],
                    'petugas_name' => $petugas->name,
                ]);
                $submission->logHistory('reviewer1', 'credential_added', 'Kredensial reviewer 1 ditambahkan');
                break;
                
            case 'reviewer2':
                $validated = $request->validate([
                    'petugas_reviewer2_id' => 'required|exists:users,id',
                    'username_reviewer2' => 'required|string|max:255',
                    'password_reviewer2' => 'required|string|max:255',
                ]);
                $submission->update($validated);
                
                $petugas = User::find($validated['petugas_reviewer2_id']);
                $submission->logHistory('reviewer2', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_reviewer2_id'],
                    'petugas_name' => $petugas->name,
                ]);
                $submission->logHistory('reviewer2', 'credential_added', 'Kredensial reviewer 2 ditambahkan');
                break;
                
            case 'editor3':
                $validated = $request->validate([
                    'petugas_editor3_id' => 'required|exists:pics,id',
                ]);
                $submission->update($validated);
                
                $petugas = Pic::find($validated['petugas_editor3_id']);
                $submission->logHistory('editor3', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_editor3_id'],
                    'petugas_name' => $petugas->name,
                ]);
                break;
                
            case 'author2':
                $validated = $request->validate([
                    'petugas_author2_id' => 'required|exists:pics,id',
                ]);
                $submission->update($validated);
                
                $petugas = Pic::find($validated['petugas_author2_id']);
                $submission->logHistory('author2', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_author2_id'],
                    'petugas_name' => $petugas->name,
                ]);
                break;
                
            case 'production':
                $validated = $request->validate([
                    'petugas_production_id' => 'required|exists:pics,id',
                    'link_publish' => 'nullable|url',
                ]);
                $submission->update($validated);
                
                $petugas = Pic::find($validated['petugas_production_id']);
                $submission->logHistory('production', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_production_id'],
                    'petugas_name' => $petugas->name,
                ]);
                break;
        }
        
        return back()->with('success', 'Data proses berhasil diperbarui');
    }

    // Validate step
    public function validateStep(Request $request, Submission $submission)
    {
        $step = $request->input('step');
        $notes = $request->input('notes', '');
        
        // Map step to petugas field for point awarding
        $stepToPetugasField = [
            'editor1' => 'petugas_editor1_id',
            'author1' => 'petugas_author1_id',
            'editor2' => 'petugas_editor2_id',
            'editor3' => 'petugas_editor3_id',
            'author2' => 'petugas_author2_id',
            'production' => 'petugas_production_id',
        ];
        
        switch ($step) {
            case 'editor1':
                $submission->validateEditor1();
                $submission->logHistory('editor1', 'approved', $notes ?: 'Editor 1 divalidasi');
                break;
            case 'author1':
                $submission->validateAuthor1();
                $submission->logHistory('author1', 'approved', $notes ?: 'Author 1 divalidasi');
                break;
            case 'editor2':
                $submission->validateEditor2();
                $submission->logHistory('editor2', 'approved', $notes ?: 'Editor 2 divalidasi');
                break;
            case 'reviewer1':
                $submission->validateReviewer1();
                $submission->logHistory('reviewer1', 'approved', $notes ?: 'Reviewer 1 divalidasi');
                break;
            case 'reviewer2':
                $submission->validateReviewer2();
                $submission->logHistory('reviewer2', 'approved', $notes ?: 'Reviewer 2 divalidasi');
                break;
            case 'editor3':
                $submission->validateEditor3();
                $submission->logHistory('editor3', 'approved', $notes ?: 'Editor 3 divalidasi');
                break;
            case 'author2':
                $submission->validateAuthor2();
                $submission->logHistory('author2', 'approved', $notes ?: 'Author 2 divalidasi');
                break;
            case 'production':
                $submission->validateProduction();
                $submission->logHistory('production', 'approved', $notes ?: 'Production divalidasi - Artikel Published', [
                    'link_publish' => $submission->link_publish
                ]);
                break;
        }
        
        // Award points to PIC if step is handled by PIC (not reviewer)
        if (isset($stepToPetugasField[$step])) {
            $petugasId = $submission->{$stepToPetugasField[$step]};
            if ($petugasId) {
                $pointHistory = PicPointHistory::awardPoints(
                    $petugasId,
                    $submission->id,
                    $step,
                    "Validasi {$submission->kode_submit} - " . PicPointHistory::getLabelForStep($step)
                );
                
                if ($pointHistory) {
                    $pic = Pic::find($petugasId);
                    
                    return back()->with([
                        'success' => 'Langkah berhasil divalidasi!',
                        'point_awarded' => true,
                        'pic_name' => $pic ? $pic->name : 'PIC',
                        'points_earned' => $pointHistory->points_earned,
                        'step_label' => PicPointHistory::getLabelForStep($step),
                        'total_points' => $pic ? $pic->total_points : 0,
                    ]);
                }
            }
        }
        
        return back()->with('success', 'Langkah berhasil divalidasi');
    }

    // Request revision for a step
    public function requestRevision(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'step' => 'required|string',
            'notes' => 'required|string|max:1000',
        ]);
        
        $step = $validated['step'];
        $notes = $validated['notes'];
        
        // Log revision request
        $submission->logHistory($step, 'revision_request', $notes);
        
        return back()->with('success', 'Permintaan revisi berhasil dicatat');
    }

    // Submit revision for a step
    public function submitRevision(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'step' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $step = $validated['step'];
        $notes = $validated['notes'] ?? 'Revisi telah dikerjakan';
        
        // Log revision submit
        $submission->logHistory($step, 'revision_submit', $notes);
        
        return back()->with('success', 'Revisi berhasil dikirim');
    }

    // Update reviewer notes
    public function updateReviewerNotes(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'catatan_reviewer1' => 'nullable|string',
            'catatan_reviewer2' => 'nullable|string',
        ]);
        
        // Log note changes
        if (!empty($validated['catatan_reviewer1']) && $validated['catatan_reviewer1'] !== $submission->catatan_reviewer1) {
            $submission->logHistory('reviewer1', 'note_added', $validated['catatan_reviewer1']);
        }
        if (!empty($validated['catatan_reviewer2']) && $validated['catatan_reviewer2'] !== $submission->catatan_reviewer2) {
            $submission->logHistory('reviewer2', 'note_added', $validated['catatan_reviewer2']);
        }
        
        $submission->update($validated);
        
        return back()->with('success', 'Catatan reviewer berhasil diperbarui');
    }

    // Show history for a submission
    public function history(Submission $submission)
    {
        $submission->load(['histories.user', 'journalSlot.journalMaster']);
        
        // Group histories by step
        $historiesByStep = $submission->histories->sortBy('created_at')->groupBy('step');
        
        return view('admin.submissions.history', compact('submission', 'historiesByStep'));
    }

    // Monitoring view with filter by date
    public function monitoring(Request $request)
    {
        $query = Submission::with([
            'journalSlot.journalMaster',
            'marketing',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction',
        ])
        // Exclude fasttrack submissions from regular submissions monitoring
        ->where(function($q) {
            $q->where('process_type', '!=', 'fasttrack')
              ->orWhereNull('process_type');
        });
        
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
        
        // Get paginated submissions
        $submissions = $query->latest('tanggal_submit')->paginate(request()->input('per_page', 200))->withQueryString();
        
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $statusOptions = Submission::getStatusOptions();
        
        // Get PICs and Users for inline assignment dropdowns
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $users = User::where('role', 'admin')->orderBy('name')->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        
        // Statistics - use single optimized query with conditional aggregation
        $baseQuery = Submission::query()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "SUBMITTED" THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status NOT IN ("SUBMITTED", "PUBLISHED", "REJECTED") THEN 1 ELSE 0 END) as in_process,
                SUM(CASE WHEN status = "PUBLISHED" THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN status = "REJECTED" THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status LIKE "%_SUBMITTED%" THEN 1 ELSE 0 END) as pending_validations
            ')
            ->where(function($q) {
                $q->where('process_type', '!=', 'fasttrack')
                  ->orWhereNull('process_type');
            });
        
        // Apply same filters
        if ($request->filled('tanggal_dari')) {
            $baseQuery->whereDate('tanggal_submit', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $baseQuery->whereDate('tanggal_submit', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $baseQuery->where('status', $request->status);
        }
        if ($request->filled('journal_master_id')) {
            $baseQuery->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_master_id);
            });
        }
        
        $statsResult = $baseQuery->first();
        
        $stats = [
            'total' => $statsResult->total ?? 0,
            'submitted' => $statsResult->submitted ?? 0,
            'in_process' => $statsResult->in_process ?? 0,
            'published' => $statsResult->published ?? 0,
            'rejected' => $statsResult->rejected ?? 0,
        ];
        
        $pendingCount = $statsResult->pending_validations ?? 0;
        $pendingValidations = $submissions->filter(function($s) {
            return str_contains($s->status, '_SUBMITTED');
        });
        
        return view('admin.submissions.monitoring', compact('submissions', 'journals', 'statusOptions', 'stats', 'pics', 'users', 'marketings', 'pendingValidations', 'pendingCount'));
    }

    /**
     * Export submissions to Excel
     */
    public function export(Request $request)
    {
        $filters = [
            'tanggal_dari' => $request->tanggal_dari,
            'tanggal_sampai' => $request->tanggal_sampai,
            'journal_master_id' => $request->journal_master_id,
            'status' => $request->status,
        ];

        $filename = 'submissions_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new SubmissionsExport($filters), $filename);
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('admin.submissions.import');
    }

    /**
     * Import submissions from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new SubmissionsImport(auth()->id());
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();
            $failures = $import->failures();
            $errors = $import->errors();

            $message = "Import selesai! ";
            if ($imported > 0) {
                $message .= "{$imported} data baru ditambahkan. ";
            }
            if ($updated > 0) {
                $message .= "{$updated} data diperbarui. ";
            }
            if (count($failures) > 0) {
                $message .= count($failures) . " baris gagal validasi. ";
            }
            if (count($errors) > 0) {
                $message .= count($errors) . " error ditemukan.";
            }

            if ($imported > 0 || $updated > 0) {
                return redirect()->route('admin.submissions.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('admin.submissions.index')
                    ->with('warning', 'Tidak ada data yang diimport. Pastikan format file sesuai template.');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.submissions.index')
                ->with('error', 'Error saat import: ' . $e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $headers = [
            'id_artikel',
            'judul_artikel',
            'link_artikel',
            'nama_penulis',
            'no_hp_penulis',
            'username_author',
            'password_author',
            'pic_marketing',
            'kode_slot',
            'tanggal_submit',
        ];

        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            // Add sample row
            fputcsv($file, [
                'ART-2026-001',
                'Judul Artikel Contoh',
                'https://link-artikel.com',
                'Nama Penulis',
                '08123456789',
                'username_author',
                'password123',
                'PIC Marketing',
                'SLT20260001',
                date('Y-m-d'),
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_submissions.csv"',
        ]);
    }

    /**
     * Bulk assign petugas to multiple submissions
     */
    public function bulkAssign(Request $request)
    {
        $assignmentType = $request->assignment_type;
        
        // Determine which table to validate against based on assignment type
        $isReviewer = in_array($assignmentType, ['reviewer1', 'reviewer2']);
        
        $request->validate([
            'submission_ids' => 'required|string',
            'assignment_type' => 'required|in:editor1,editor2,editor3,author1,author2,reviewer1,reviewer2,production',
            'petugas_id' => 'required|exists:' . ($isReviewer ? 'users' : 'pics') . ',id',
        ]);

        $submissionIds = json_decode($request->submission_ids, true);
        
        if (empty($submissionIds) || !is_array($submissionIds)) {
            return back()->with('error', 'Tidak ada submission yang dipilih');
        }

        $petugasId = $request->petugas_id;
        $petugas = $isReviewer ? User::find($petugasId) : Pic::find($petugasId);
        
        $updated = 0;
        $submissions = Submission::whereIn('id', $submissionIds)->get();

        foreach ($submissions as $submission) {
            $updateData = [];
            $logStep = $assignmentType;
            
            switch ($assignmentType) {
                case 'editor1':
                    $updateData['petugas_editor1_id'] = $petugasId;
                    if ($request->filled('username_editor')) {
                        $updateData['username_editor'] = $request->username_editor;
                    }
                    if ($request->filled('password_editor')) {
                        $updateData['password_editor'] = $request->password_editor;
                    }
                    // Update status if still SUBMITTED
                    if ($submission->status === 'SUBMITTED') {
                        $updateData['status'] = 'EDITOR1_PROCESS';
                    }
                    break;
                    
                case 'editor2':
                    $updateData['petugas_editor2_id'] = $petugasId;
                    break;
                    
                case 'editor3':
                    $updateData['petugas_editor3_id'] = $petugasId;
                    break;
                    
                case 'author1':
                    $updateData['petugas_author1_id'] = $petugasId;
                    break;
                    
                case 'author2':
                    $updateData['petugas_author2_id'] = $petugasId;
                    break;
                    
                case 'reviewer1':
                    $updateData['petugas_reviewer1_id'] = $petugasId;
                    if ($request->filled('username_reviewer')) {
                        $updateData['username_reviewer1'] = $request->username_reviewer;
                    }
                    if ($request->filled('password_reviewer')) {
                        $updateData['password_reviewer1'] = $request->password_reviewer;
                    }
                    break;
                    
                case 'reviewer2':
                    $updateData['petugas_reviewer2_id'] = $petugasId;
                    if ($request->filled('username_reviewer')) {
                        $updateData['username_reviewer2'] = $request->username_reviewer;
                    }
                    if ($request->filled('password_reviewer')) {
                        $updateData['password_reviewer2'] = $request->password_reviewer;
                    }
                    break;
                    
                case 'production':
                    $updateData['petugas_production_id'] = $petugasId;
                    break;
            }

            if (!empty($updateData)) {
                $submission->update($updateData);
                
                // Log history
                $submission->logHistory($logStep, 'assigned', "Ditugaskan ke {$petugas->name} (Bulk Assignment)", [
                    'petugas_id' => $petugasId,
                    'petugas_name' => $petugas->name,
                    'bulk_assignment' => true,
                ]);
                
                $updated++;
            }
        }

        $typeLabels = [
            'editor1' => 'Editor 1',
            'editor2' => 'Editor 2', 
            'editor3' => 'Editor 3',
            'author1' => 'Author 1',
            'author2' => 'Author 2',
            'reviewer1' => 'Reviewer 1',
            'reviewer2' => 'Reviewer 2',
            'production' => 'Production',
        ];

        return back()->with('success', "{$updated} submission berhasil ditugaskan ke {$petugas->name} sebagai {$typeLabels[$assignmentType]}");
    }

    /**
     * Bulk assign petugas with per-submission credentials
     */
    public function bulkAssignWithCredentials(Request $request)
    {
        $assignmentType = $request->assignment_type;
        
        // All assignment types now use Pic model
        $request->validate([
            'submission_ids' => 'required|string',
            'assignment_type' => 'required|in:editor1,editor2,editor3,author1,author2,reviewer1,reviewer2,production',
            'petugas_id' => 'required|exists:pics,id',
            'credentials' => 'nullable|array',
        ]);

        $submissionIds = json_decode($request->submission_ids, true);
        
        if (empty($submissionIds) || !is_array($submissionIds)) {
            return back()->with('error', 'Tidak ada submission yang dipilih');
        }

        $petugasId = $request->petugas_id;
        $petugas = Pic::find($petugasId);
        $credentials = $request->credentials ?? [];
        
        $updated = 0;
        $submissions = Submission::whereIn('id', $submissionIds)->get();

        foreach ($submissions as $submission) {
            $updateData = [];
            $logStep = $assignmentType;
            
            // Get credentials for this specific submission
            $submissionCredentials = $credentials[$submission->id] ?? [];
            $username = $submissionCredentials['username'] ?? null;
            $password = $submissionCredentials['password'] ?? null;
            
            switch ($assignmentType) {
                case 'editor1':
                    $updateData['petugas_editor1_id'] = $petugasId;
                    if (!empty($username)) {
                        $updateData['username_editor'] = $username;
                    }
                    if (!empty($password)) {
                        $updateData['password_editor'] = $password;
                    }
                    // Update status if still SUBMITTED
                    if ($submission->status === 'SUBMITTED') {
                        $updateData['status'] = 'EDITOR1_PROCESS';
                    }
                    break;
                    
                case 'editor2':
                    $updateData['petugas_editor2_id'] = $petugasId;
                    if (!empty($username)) {
                        $updateData['username_editor'] = $username;
                    }
                    if (!empty($password)) {
                        $updateData['password_editor'] = $password;
                    }
                    break;
                    
                case 'editor3':
                    $updateData['petugas_editor3_id'] = $petugasId;
                    if (!empty($username)) {
                        $updateData['username_editor'] = $username;
                    }
                    if (!empty($password)) {
                        $updateData['password_editor'] = $password;
                    }
                    break;
                    
                case 'author1':
                    $updateData['petugas_author1_id'] = $petugasId;
                    break;
                    
                case 'author2':
                    $updateData['petugas_author2_id'] = $petugasId;
                    break;
                    
                case 'reviewer1':
                    $updateData['petugas_reviewer1_id'] = $petugasId;
                    if (!empty($username)) {
                        $updateData['username_reviewer1'] = $username;
                    }
                    if (!empty($password)) {
                        $updateData['password_reviewer1'] = $password;
                    }
                    break;
                    
                case 'reviewer2':
                    $updateData['petugas_reviewer2_id'] = $petugasId;
                    if (!empty($username)) {
                        $updateData['username_reviewer2'] = $username;
                    }
                    if (!empty($password)) {
                        $updateData['password_reviewer2'] = $password;
                    }
                    break;
                    
                case 'production':
                    $updateData['petugas_production_id'] = $petugasId;
                    break;
            }

            if (!empty($updateData)) {
                $submission->update($updateData);
                
                // Log history
                $submission->logHistory($logStep, 'assigned', "Ditugaskan ke {$petugas->name} (Bulk Assignment)", [
                    'petugas_id' => $petugasId,
                    'petugas_name' => $petugas->name,
                    'bulk_assignment' => true,
                    'has_credentials' => !empty($username) || !empty($password),
                ]);
                
                $updated++;
            }
        }

        $typeLabels = [
            'editor1' => 'Editor 1',
            'editor2' => 'Editor 2', 
            'editor3' => 'Editor 3',
            'author1' => 'Author 1',
            'author2' => 'Author 2',
            'reviewer1' => 'Reviewer 1',
            'reviewer2' => 'Reviewer 2',
            'production' => 'Production',
        ];

        return back()->with('success', "{$updated} submission berhasil ditugaskan ke {$petugas->name} sebagai {$typeLabels[$assignmentType]}");
    }

    /**
     * Quick assign petugas via AJAX (inline dropdown in monitoring table)
     */
    public function quickAssign(Request $request)
    {
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'assignment_type' => 'required|in:submit,editor1,editor2,editor3,author1,author2,reviewer1,reviewer2,production',
            'petugas_id' => 'nullable|integer',
        ]);

        $submission = Submission::findOrFail($request->submission_id);
        $assignmentType = $request->assignment_type;
        $petugasId = $request->petugas_id;
        
        // Determine field name based on assignment type
        $fieldMap = [
            'submit' => 'petugas_submit_id',
            'editor1' => 'petugas_editor1_id',
            'editor2' => 'petugas_editor2_id',
            'editor3' => 'petugas_editor3_id',
            'author1' => 'petugas_author1_id',
            'author2' => 'petugas_author2_id',
            'reviewer1' => 'petugas_reviewer1_id',
            'reviewer2' => 'petugas_reviewer2_id',
            'production' => 'petugas_production_id',
        ];

        $field = $fieldMap[$assignmentType];
        
        // Validate petugas exists if provided
        if ($petugasId) {
            $petugas = Pic::find($petugasId);
            
            if (!$petugas) {
                return response()->json(['success' => false, 'message' => 'Petugas tidak ditemukan']);
            }
        }

        // Update the submission
        $updateData = [$field => $petugasId ?: null];
        
        // If assigning Editor 1 and status is SUBMITTED, update status
        if ($assignmentType === 'editor1' && $petugasId && $submission->status === 'SUBMITTED') {
            $updateData['status'] = 'EDITOR1_PROCESS';
        }
        
        $submission->update($updateData);
        
        // Log history
        if ($petugasId) {
            $petugasName = Pic::find($petugasId)->name;
            $submission->logHistory($assignmentType, 'assigned', "Ditugaskan ke {$petugasName} (Quick Assign)", [
                'petugas_id' => $petugasId,
                'petugas_name' => $petugasName,
            ]);
        } else {
            $submission->logHistory($assignmentType, 'unassigned', "Penugasan dihapus (Quick Assign)", []);
        }

        return response()->json(['success' => true, 'message' => 'Berhasil disimpan']);
    }

    /**
     * Quick assign marketing via AJAX (inline dropdown in monitoring table)
     */
    public function quickAssignMarketing(Request $request)
    {
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'marketing_id' => 'nullable|exists:marketings,id',
        ]);

        $submission = Submission::findOrFail($request->submission_id);
        $oldMarketingId = $submission->marketing_id;
        $submission->marketing_id = $request->marketing_id;
        $submission->save();

        // Award points to marketing if newly assigned
        if ($request->marketing_id && $request->marketing_id != $oldMarketingId) {
            MarketingPointHistory::awardPoints($request->marketing_id, $submission->id);
        }

        return response()->json(['success' => true, 'message' => 'Marketing berhasil diassign']);
    }

    /**
     * Quick update credential via AJAX (inline input in monitoring table)
     */
    public function quickUpdateCredential(Request $request)
    {
        $allowedFields = [
            'username_editor',
            'password_editor',
            'username_reviewer1',
            'password_reviewer1',
            'username_reviewer2',
            'password_reviewer2',
        ];

        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'field' => 'required|in:' . implode(',', $allowedFields),
            'value' => 'nullable|string|max:255',
        ]);

        $submission = Submission::findOrFail($request->submission_id);
        $field = $request->field;
        $value = $request->value;
        
        $submission->update([$field => $value ?: null]);
        
        return response()->json(['success' => true, 'message' => 'Berhasil disimpan']);
    }

    /**
     * Toggle valid field via AJAX (inline checkbox in monitoring/fasttrack table)
     */
    public function toggleValidField(Request $request)
    {
        $allowedFields = [
            'editor1_valid',
            'author1_valid',
            'editor2_valid',
            'reviewer1_valid',
            'reviewer2_valid',
            'editor3_valid',
            'author2_valid',
            'production_valid',
        ];

        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'field' => 'required|in:' . implode(',', $allowedFields),
        ]);

        $submission = Submission::findOrFail($request->submission_id);
        $field = $request->field;
        
        // Toggle the value
        $submission->{$field} = !$submission->{$field};
        
        // Recalculate status based on current validation flags
        $submission->recalculateStatus();
        
        $submission->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Berhasil disimpan',
            'is_valid' => $submission->{$field}
        ]);
    }

    // ==================== FASTTRACK SUBMISSIONS ====================
    
    /**
     * Display fasttrack submissions index
     */
    public function fasttrackIndex(Request $request)
    {
        $query = Submission::with([
                'journalSlot.journalMaster', 
                'marketing', 
                'petugasSubmit',
                'petugasEditor1',
                'petugasAuthor1',
                'petugasEditor2',
                'petugasReviewer1',
                'petugasReviewer2',
                'petugasEditor3',
                'petugasAuthor2',
                'petugasProduction'
            ])
            ->where('process_type', 'fasttrack');
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }
        
        // Filter by journal
        if ($request->filled('journal_master_id')) {
            $query->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_master_id);
            });
        }
        
        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_submit', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_submit', '<=', $request->tanggal_sampai);
        }
        
        $submissions = $query->latest()->paginate(request()->input('per_page', 20))->withQueryString();
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.fasttrack.index', compact('submissions', 'journals', 'marketings', 'pics'));
    }

    /**
     * Show fasttrack create form
     */
    public function fasttrackCreate()
    {
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $slots = JournalSlot::with('journalMaster')
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.fasttrack.create', compact('journals', 'slots', 'marketings', 'pics'));
    }

    /**
     * Store fasttrack submission
     */
    public function fasttrackStore(Request $request)
    {
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'id_artikel' => 'required|string|max:255',
            'judul_artikel' => 'required|string|max:500',
            'link_artikel' => 'nullable|url|max:500',
            'file_artikel' => ['nullable', 'file', 'max:10240', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
            'link_publish' => 'nullable|url|max:500',
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'username_author' => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
        ]);

        // Check slot availability
        $slot = JournalSlot::find($validated['journal_slot_id']);
        if (!$slot || $slot->slot_terpakai >= $slot->jumlah_slot) {
            return back()->with('error', 'Slot jurnal sudah penuh!')->withInput();
        }

        // Handle file upload
        if ($request->hasFile('file_artikel')) {
            $file = $request->file('file_artikel');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/artikel', $filename);
            $validated['file_artikel'] = $filename;
        }

        // Generate kode_submit with FT prefix for fasttrack
        $today = now()->format('Ymd');
        $lastSubmit = Submission::where('kode_submit', 'like', "FT{$today}%")->latest()->first();
        $sequence = $lastSubmit ? (int)substr($lastSubmit->kode_submit, -4) + 1 : 1;
        $validated['kode_submit'] = "FT{$today}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        // Generate kode_loa
        $validated['kode_loa'] = $validated['kode_submit'] . 'SIPERA';
        
        // Set fasttrack specific fields
        $validated['process_type'] = 'fasttrack';
        // Set status based on whether link_publish is provided
        $validated['status'] = !empty($validated['link_publish']) ? 'PUBLISHED' : 'SUBMITTED';
        $validated['tanggal_submit'] = now();
        $validated['created_by'] = auth()->id();

        $submission = Submission::create($validated);

        // Log history
        $submission->logHistory('submit', 'submitted', 'Submission fasttrack dibuat oleh Admin dengan link publish', [
            'link_publish' => $validated['link_publish'],
            'process_type' => 'fasttrack'
        ]);

        // Award points to PIC if assigned
        $pointMessage = '';
        if (isset($validated['petugas_submit_id']) && $validated['petugas_submit_id']) {
            $pic = Pic::find($validated['petugas_submit_id']);
            $pointsToAdd = PicPointHistory::getPointsForStep('submit');
            
            if ($pointsToAdd > 0 && $pic) {
                $pic->total_points = ($pic->total_points ?? 0) + $pointsToAdd;
                $pic->save();
                
                PicPointHistory::create([
                    'pic_id' => $pic->id,
                    'submission_id' => $submission->id,
                    'step' => 'submit',
                    'points_earned' => $pointsToAdd,
                    'description' => "Fasttrack artikel: {$validated['kode_submit']} - {$submission->judul_artikel}",
                ]);
                
                $pointMessage = " (+{$pointsToAdd} point untuk PIC)";
            }
        }
        
        // Award points to Marketing if assigned
        if (isset($validated['marketing_id']) && $validated['marketing_id']) {
            $marketing = Marketing::find($validated['marketing_id']);
            if ($marketing) {
                $marketingPoints = MarketingPointHistory::getPointsForSubmission();
                if ($marketingPoints > 0) {
                    MarketingPointHistory::create([
                        'marketing_id' => $marketing->id,
                        'submission_id' => $submission->id,
                        'points_earned' => $marketingPoints,
                        'description' => "Fasttrack artikel: {$validated['kode_submit']} - {$submission->judul_artikel}",
                    ]);
                    
                    // Sync total_points from actual submission count (1 submission = 1 point)
                    $submissionCount = Submission::where('marketing_id', $marketing->id)->count();
                    $marketing->total_points = $submissionCount;
                    $marketing->save();
                }
            }
        }

        return redirect()->route('admin.fasttrack.monitoring')
            ->with('success', 'Fasttrack submission berhasil ditambahkan dengan kode: ' . $validated['kode_submit'] . $pointMessage);
    }

    /**
     * Display fasttrack monitoring
     */
    /**
     * Show fasttrack submission detail
     */
    public function fasttrackShow(Submission $submission)
    {
        if ($submission->process_type !== 'fasttrack') {
            return redirect()->route('admin.submissions.show', $submission);
        }
        
        $submission->load(['journalSlot.journalMaster', 'marketing', 'petugasSubmit', 'histories']);
        
        return view('admin.fasttrack.show', compact('submission'));
    }

    /**
     * Show fasttrack edit form
     */
    public function fasttrackEdit(Submission $submission)
    {
        if ($submission->process_type !== 'fasttrack') {
            return redirect()->route('admin.submissions.edit', $submission);
        }
        
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $slots = JournalSlot::with('journalMaster')
            ->where('is_active', true)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.fasttrack.edit', compact('submission', 'journals', 'slots', 'marketings', 'pics'));
    }

    /**
     * Update fasttrack submission
     */
    public function fasttrackUpdate(Request $request, Submission $submission)
    {
        if ($submission->process_type !== 'fasttrack') {
            return redirect()->route('admin.submissions.edit', $submission);
        }
        
        $validated = $request->validate([
            'journal_slot_id' => 'required|exists:journal_slots,id',
            'judul_artikel' => 'required|string|max:500',
            'link_publish' => 'nullable|url|max:500',
            'nama_penulis' => 'required|string|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
        ]);

        // Handle slot change
        if ($submission->journal_slot_id != $validated['journal_slot_id']) {
            // Decrement old slot
            $oldSlot = JournalSlot::find($submission->journal_slot_id);
            if ($oldSlot && $oldSlot->slot_terpakai > 0) {
                $oldSlot->decrement('slot_terpakai');
            }
            
            // Check new slot availability
            $newSlot = JournalSlot::find($validated['journal_slot_id']);
            if (!$newSlot || $newSlot->slot_terpakai >= $newSlot->jumlah_slot) {
                return back()->with('error', 'Slot jurnal baru sudah penuh!')->withInput();
            }
            
            // Increment new slot
            $newSlot->increment('slot_terpakai');
        }

        $submission->update($validated);

        // Log history
        $submission->logHistory('fasttrack', 'updated', 'Submission fasttrack diupdate oleh Admin');

        return redirect()->route('admin.fasttrack.monitoring')
            ->with('success', 'Fasttrack submission berhasil diupdate');
    }

    /**
     * Delete fasttrack submission
     */
    public function fasttrackDestroy(Submission $submission)
    {
        if ($submission->process_type !== 'fasttrack') {
            return redirect()->route('admin.submissions.index');
        }
        
        // Decrement slot
        $slot = $submission->journalSlot;
        if ($slot && $slot->slot_terpakai > 0) {
            $slot->decrement('slot_terpakai');
        }
        
        $kode = $submission->kode_submit;
        $submission->delete();
        
        return redirect()->route('admin.fasttrack.monitoring')
            ->with('success', "Fasttrack submission {$kode} berhasil dihapus");
    }
    
    /**
     * Display fasttrack submissions for Pengalolaan Jurnal FS
     */
    public function fasttrackSubmissions(Request $request)
    {
        $query = Submission::with([
                'journalSlot.journalMaster', 
                'marketing', 
                'petugasSubmit',
                'petugasEditor1',
                'petugasAuthor1',
                'petugasEditor2',
                'petugasReviewer1',
                'petugasReviewer2',
                'petugasEditor3',
                'petugasAuthor2',
                'petugasProduction'
            ])
            ->where('process_type', 'fasttrack');
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_submit', 'like', "%{$search}%")
                  ->orWhere('judul_artikel', 'like', "%{$search}%")
                  ->orWhere('nama_penulis', 'like', "%{$search}%");
            });
        }
        
        // Filter by journal
        if ($request->filled('journal_master_id')) {
            $query->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_master_id);
            });
        }
        
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
        
        $submissions = $query->latest()->paginate(request()->input('per_page', 20))->withQueryString();
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $statusOptions = Submission::getStatusOptions();
        
        return view('admin.fasttrack-management.submissions.index', compact('submissions', 'journals', 'marketings', 'pics', 'statusOptions'));
    }
    
    /**
     * Display fasttrack monitoring for Pengalolaan Jurnal FS - using old fasttrackMonitoring logic
     */
    public function fasttrackMonitoring(Request $request)
    {
        $query = Submission::with([
            'journalSlot.journalMaster',
            'marketing',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction',
        ])->where('process_type', 'fasttrack');
        
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
        
        // Get paginated submissions
        $submissions = $query->latest('tanggal_submit')->paginate(request()->input('per_page', 200))->withQueryString();
        
        $journals = JournalMaster::where('is_active', true)->orderBy('nama_jurnal')->get();
        $statusOptions = Submission::getStatusOptions();
        
        // Get PICs and Users for inline assignment dropdowns
        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $users = User::where('role', 'admin')->orderBy('name')->get();
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        
        // Statistics - use single optimized query with conditional aggregation
        $statsQuery = Submission::query()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "SUBMITTED" THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status NOT IN ("SUBMITTED", "PUBLISHED", "REJECTED") THEN 1 ELSE 0 END) as in_process,
                SUM(CASE WHEN status = "PUBLISHED" THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN status = "REJECTED" THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status LIKE "%_SUBMITTED%" THEN 1 ELSE 0 END) as pending_validations
            ')
            ->where('process_type', 'fasttrack');
        
        // Apply same filters for statistics
        if ($request->filled('tanggal_dari')) {
            $statsQuery->whereDate('tanggal_submit', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $statsQuery->whereDate('tanggal_submit', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }
        if ($request->filled('journal_master_id')) {
            $statsQuery->whereHas('journalSlot', function($q) use ($request) {
                $q->where('journal_master_id', $request->journal_master_id);
            });
        }
        
        $statsResult = $statsQuery->first();
        
        $stats = [
            'total' => $statsResult->total ?? 0,
            'submitted' => $statsResult->submitted ?? 0,
            'in_process' => $statsResult->in_process ?? 0,
            'published' => $statsResult->published ?? 0,
            'rejected' => $statsResult->rejected ?? 0,
        ];
        
        $pendingCount = $statsResult->pending_validations ?? 0;
        $pendingValidations = $submissions->filter(function($s) {
            return str_contains($s->status, '_SUBMITTED');
        });
        
        return view('admin.fasttrack-management.monitoring.index', compact('submissions', 'journals', 'statusOptions', 'stats', 'pics', 'users', 'marketings', 'pendingValidations', 'pendingCount'));
    }
}

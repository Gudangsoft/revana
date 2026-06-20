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
use App\Services\FonnteService;
use App\Services\WaNotificationService;
use App\Models\ActivityLog;
use App\Models\Setting;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\EmailTemplate;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

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

        $this->applyProgramFilter($query, $request);

        $submissions = $query->latest('tanggal_submit')->paginate(request()->input('per_page', 50));
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
            'link_artikel' => ['nullable', 'url', Rule::unique('submissions', 'link_artikel')],
            'file_artikel' => ['nullable', 'file', 'max:51200', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
            'nama_penulis'        => 'required|string|max:255',
            'affiliation_penulis' => 'nullable|string|max:255',
            'no_hp_penulis'       => 'nullable|string|max:20',
            'email_penulis'       => 'nullable|email|max:255',
            'co_authors'             => 'nullable|array',
            'co_authors.*.nama'      => 'nullable|string|max:255',
            'co_authors.*.no_hp'     => 'nullable|string|max:20',
            'co_authors.*.email'     => 'nullable|email|max:255',
            'co_authors.*.afiliasi'  => 'nullable|string|max:255',
            'username_author'     => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
            'program_type' => ['nullable', Rule::in(['bkd', 'jafa'])],
        ]);

        // Process co_authors: filter empty, normalize
        $validated['co_authors'] = collect($request->input('co_authors', []))
            ->filter(fn($a) => !empty(trim($a['nama'] ?? '')))
            ->map(fn($a) => [
                'nama'     => trim($a['nama']),
                'no_hp'    => trim($a['no_hp'] ?? ''),
                'email'    => trim($a['email'] ?? ''),
                'afiliasi' => trim($a['afiliasi'] ?? ''),
            ])
            ->values()
            ->toArray() ?: null;

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
        
        // WA notification: submission baru masuk
        app(WaNotificationService::class)->notifyNewSubmission($submission);

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
        // awardPoints() handles: duplicate-check, increment total_points, and cache flush internally.
        if (!empty($validated['petugas_submit_id'])) {
            PicPointHistory::awardPoints(
                $validated['petugas_submit_id'],
                $submission->id,
                'submit',
                "Submit artikel: {$submission->kode_submit} - {$submission->judul_artikel}"
            );
        }

        // Kirim notifikasi WhatsApp ke penulis via Fonnte
        $this->sendWhatsAppNotification($submission);

        // Kirim email acknowledgement ke penulis jika template aktif
        $this->sendPenulisEmail($submission);

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
        
        $activityLogs = ActivityLog::where('subject_type', Submission::class)
            ->where('subject_id', $submission->id)
            ->latest()
            ->get();

        return view('admin.submissions.show', compact('submission', 'activityLogs'));
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
            'link_artikel' => ['nullable', 'url', Rule::unique('submissions', 'link_artikel')->ignore($submission->id)],
            'file_artikel' => ['nullable', 'file', 'max:51200', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
            'nama_penulis'        => 'required|string|max:255',
            'affiliation_penulis' => 'nullable|string|max:255',
            'no_hp_penulis'       => 'nullable|string|max:20',
            'email_penulis'       => 'nullable|email|max:255',
            'username_author'     => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
            'program_type' => ['nullable', Rule::in(['bkd', 'jafa'])],

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

        // Snapshot field penting sebelum update (untuk activity log)
        $trackedFields = array_keys(ActivityLog::SUBMISSION_TRACKED_FIELDS);
        $beforeSnapshot = $submission->only($trackedFields);

        // Deteksi perubahan kredensial author untuk notifikasi WA
        $credentialsChanged = false;
        if (
            (isset($validated['username_author']) && $validated['username_author'] !== $submission->username_author) ||
            (isset($validated['password_author']) && $validated['password_author'] !== $submission->password_author)
        ) {
            $credentialsChanged = true;
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

        // Sync kode_submit prefix when program_type changes
        $newProgramType = $validated['program_type'] ?? null;
        $oldProgramType = $submission->program_type;
        if ($newProgramType !== $oldProgramType) {
            $newPrefix = match($newProgramType) {
                'bkd'  => 'BKD',
                'jafa' => 'JAF',
                default => 'SUB',
            };
            $oldPrefixLen = match(true) {
                str_starts_with($submission->kode_submit, 'BKD')  => 3,
                str_starts_with($submission->kode_submit, 'JAF')  => 3,
                str_starts_with($submission->kode_submit, 'JAFA') => 4,
                str_starts_with($submission->kode_submit, 'SUB')  => 3,
                default => 3,
            };
            $numericSuffix         = substr($submission->kode_submit, $oldPrefixLen);
            $validated['kode_submit'] = $newPrefix . $numericSuffix;
            $validated['kode_loa']    = $validated['kode_submit'] . 'SIPERA';
        }

        $submission->update($validated);

        // Activity log — catat field yang berubah
        $afterSnapshot  = $submission->fresh()->only($trackedFields);
        $changedOld     = ActivityLog::diff($beforeSnapshot, $afterSnapshot);
        $changedNew     = array_intersect_key($afterSnapshot, $changedOld);
        if (!empty($changedOld)) {
            $labels = ActivityLog::SUBMISSION_TRACKED_FIELDS;
            $oldLabeled = array_combine(
                array_map(fn ($k) => $labels[$k] ?? $k, array_keys($changedOld)),
                array_values($changedOld)
            );
            $newLabeled = array_combine(
                array_map(fn ($k) => $labels[$k] ?? $k, array_keys($changedNew)),
                array_values($changedNew)
            );
            ActivityLog::record('updated', $submission, $oldLabeled, $newLabeled);
        }

        // Kirim notifikasi WhatsApp jika kredensial author berubah
        if ($credentialsChanged) {
            $this->sendWhatsAppNotification($submission, true);
        }

        $program = $submission->fresh()->program_type;
        return redirect()->route('admin.submissions.index', array_filter(['program' => $program]))
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
            'petugasValidator',
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
                app(WaNotificationService::class)->notifyReviewerAssigned(
                    $submission, $petugas, 'reviewer1',
                    $validated['username_reviewer1'], $validated['password_reviewer1']
                );
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
                app(WaNotificationService::class)->notifyReviewerAssigned(
                    $submission, $petugas, 'reviewer2',
                    $validated['username_reviewer2'], $validated['password_reviewer2']
                );
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
                
            case 'validator':
                $validated = $request->validate([
                    'petugas_validator_id' => 'required|exists:pics,id',
                ]);
                $submission->update($validated);
                
                $petugas = Pic::find($validated['petugas_validator_id']);
                $submission->logHistory('validator', 'assigned', 'Ditugaskan ke ' . $petugas->name, [
                    'petugas_id' => $validated['petugas_validator_id'],
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
            'editor1'   => 'petugas_editor1_id',
            'author1'   => 'petugas_author1_id',
            'editor2'   => 'petugas_editor2_id',
            'reviewer1' => 'petugas_reviewer1_id',
            'reviewer2' => 'petugas_reviewer2_id',
            'editor3'   => 'petugas_editor3_id',
            'author2'   => 'petugas_author2_id',
            'production' => 'petugas_production_id',
            'validator' => 'petugas_validator_id',
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
                app(WaNotificationService::class)->notifyReviewCompleted($submission, 'reviewer1');
                break;
            case 'reviewer2':
                $submission->validateReviewer2();
                $submission->logHistory('reviewer2', 'approved', $notes ?: 'Reviewer 2 divalidasi');
                app(WaNotificationService::class)->notifyReviewCompleted($submission, 'reviewer2');
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
                $submission->logHistory('production', 'approved', $notes ?: 'Production divalidasi - Artikel dikirim ke Validator', [
                    'link_publish' => $submission->link_publish
                ]);
                break;
            case 'validator':
                $submission->validateValidator();
                $submission->logHistory('validator', 'approved', $notes ?: 'Validator selesai - Artikel Published');
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
            'catatan_validator' => 'nullable|string',
        ]);

        if (!empty($validated['catatan_reviewer1']) && $validated['catatan_reviewer1'] !== $submission->catatan_reviewer1) {
            $submission->logHistory('reviewer1', 'note_added', $validated['catatan_reviewer1']);
        }
        if (!empty($validated['catatan_reviewer2']) && $validated['catatan_reviewer2'] !== $submission->catatan_reviewer2) {
            $submission->logHistory('reviewer2', 'note_added', $validated['catatan_reviewer2']);
        }
        if (!empty($validated['catatan_validator']) && $validated['catatan_validator'] !== $submission->catatan_validator) {
            $submission->logHistory('validator', 'note_added', $validated['catatan_validator']);
        }

        $submission->update($validated);

        return back()->with('success', 'Catatan berhasil diperbarui');
    }

    // Update catatan untuk step manapun (editor1, author1, editor2, editor3, author2, production)
    public function updateStepNotes(Request $request, Submission $submission)
    {
        $validSteps = ['editor1', 'author1', 'editor2', 'editor3', 'author2', 'production'];
        $validated = $request->validate([
            'step' => 'required|string|in:' . implode(',', $validSteps),
            'notes' => 'nullable|string|max:2000',
        ]);

        $step = $validated['step'];
        $notes = $validated['notes'] ?? '';

        if ($notes !== '') {
            $submission->logHistory($step, 'note_added', $notes);
        }

        return back()->with('success', 'Catatan berhasil disimpan');
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
            'petugasValidator',
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

        $this->applyProgramFilter($query, $request);

        // Sort — default Terlama (FIFO)
        $sortBy = $request->input('sort_by', 'date_asc');
        match ($sortBy) {
            'title_asc'  => $query->orderBy('judul_artikel', 'asc')->orderBy('id', 'asc'),
            'title_desc' => $query->orderBy('judul_artikel', 'desc')->orderBy('id', 'asc'),
            'date_desc'  => $query->orderByDesc('tanggal_submit')->orderByDesc('id'),
            default      => $query->orderBy('tanggal_submit', 'asc')->orderBy('id', 'asc'),
        };

        // Get paginated submissions
        $submissions = $query->paginate(request()->input('per_page', 50))->withQueryString();

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
        $this->applyProgramFilter($baseQuery, $request);

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

        $program = $request->input('program');

        return view('admin.submissions.monitoring', compact('submissions', 'journals', 'statusOptions', 'stats', 'pics', 'users', 'marketings', 'pendingValidations', 'pendingCount', 'program'));
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
            'assignment_type' => 'required|in:editor1,editor2,editor3,author1,author2,reviewer1,reviewer2,production,validator',
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
                    
                case 'validator':
                    $updateData['petugas_validator_id'] = $petugasId;
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
            'validator' => 'Validator',
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
            'assignment_type' => 'required|in:editor1,editor2,editor3,author1,author2,reviewer1,reviewer2,production,validator',
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
                    
                case 'validator':
                    $updateData['petugas_validator_id'] = $petugasId;
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
            'validator' => 'Validator',
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
            'assignment_type' => 'required|in:submit,editor1,editor2,editor3,author1,author2,reviewer1,reviewer2,production,validator',
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
            'validator' => 'petugas_validator_id',
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
            $petugas = Pic::find($petugasId);
            $petugasName = $petugas->name;
            $submission->logHistory($assignmentType, 'assigned', "Ditugaskan ke {$petugasName} (Quick Assign)", [
                'petugas_id' => $petugasId,
                'petugas_name' => $petugasName,
            ]);

            // Kirim email notifikasi jika template aktif
            $tpl = EmailTemplate::findActive('assign_' . $assignmentType);
            if ($tpl && $petugas->email) {
                try {
                    $rendered = $tpl->render([
                        'nama_artikel'      => $submission->judul_artikel ?? '-',
                        'kode_submit'       => $submission->kode_submit ?? '-',
                        'id_artikel'        => $submission->id,
                        'nama_jurnal'       => $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-',
                        'url_jurnal'        => $submission->journalSlot?->journalMaster?->link_jurnal ?? '-',
                        'nama_penulis'      => $submission->nama_penulis ?? '-',
                        'username_author'   => $submission->username_author ?? '-',
                        'password_author'   => $submission->password_author ?? '-',
                        'nama_pic'          => $petugasName,
                        'email_pic'         => $petugas->email,
                        'nama_tahap'        => EmailTemplate::$triggerLabels['assign_' . $assignmentType] ?? $assignmentType,
                        'tanggal'           => now()->format('d/m/Y H:i'),
                        'username_editor'   => $submission->username_editor ?? '-',
                        'password_editor'   => $submission->password_editor ?? '-',
                        'username_reviewer1'=> $submission->username_reviewer1 ?? '-',
                        'password_reviewer1'=> $submission->password_reviewer1 ?? '-',
                        'username_reviewer2'=> $submission->username_reviewer2 ?? '-',
                        'password_reviewer2'=> $submission->password_reviewer2 ?? '-',
                        'app_name'          => config('app.name'),
                    ]);
                    $atts = $tpl->attachments;
                    try {
                        Mail::html($rendered['body'], function ($message) use ($rendered, $petugas, $atts) {
                            $message->to($petugas->email, $petugas->name)->subject($rendered['subject']);
                            foreach ($atts as $att) {
                                if (file_exists($att->getFullPath())) {
                                    $message->attach($att->getFullPath(), ['as' => $att->original_name, 'mime' => $att->mime_type]);
                                }
                            }
                        });
                        EmailLog::record('assign_' . $assignmentType, $submission->id, $petugas->email, $petugas->name, $rendered['subject'], 'sent');
                    } catch (\Exception $e) {
                        Log::warning('Email template send failed [assign_' . $assignmentType . ']: ' . $e->getMessage());
                        EmailLog::record('assign_' . $assignmentType, $submission->id, $petugas->email, $petugas->name, $rendered['subject'], 'failed', $e->getMessage());
                    }
                } catch (\Exception $e) {
                    Log::warning('Email template render failed [assign_' . $assignmentType . ']: ' . $e->getMessage());
                }
            }
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
            'link_publish',
            'catatan_reviewer1',
            'catatan_reviewer2',
            'catatan_validator',
            'email_penulis',
            'no_hp_penulis',
        ];

        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'field' => 'required|in:' . implode(',', $allowedFields),
            'value' => 'nullable|string|max:500',
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
            'validator_valid',
        ];

        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'field' => 'required|in:' . implode(',', $allowedFields),
        ]);

        $submission = Submission::findOrFail($request->submission_id);
        $field = $request->field;

        // Toggle the value
        $newValue = !$submission->{$field};
        $submission->{$field} = $newValue;

        // Sync validated_at timestamp
        $validatedAtMap = [
            'editor1_valid'    => 'editor1_validated_at',
            'author1_valid'    => 'author1_validated_at',
            'editor2_valid'    => 'editor2_validated_at',
            'reviewer1_valid'  => 'reviewer1_validated_at',
            'reviewer2_valid'  => 'reviewer2_validated_at',
            'editor3_valid'    => 'editor3_validated_at',
            'author2_valid'    => 'author2_validated_at',
            'production_valid' => 'production_validated_at',
            'validator_valid'  => 'validator_validated_at',
        ];
        if (isset($validatedAtMap[$field])) {
            $tsField = $validatedAtMap[$field];
            if ($newValue && empty($submission->{$tsField})) {
                $submission->{$tsField} = now();
            } elseif (!$newValue) {
                $submission->{$tsField} = null;
            }
        }

        // Point award/revoke mapping (only PIC steps, not reviewer1/2)
        $fieldToStep = [
            'editor1_valid'    => ['step' => 'editor1',    'petugas' => 'petugas_editor1_id'],
            'author1_valid'    => ['step' => 'author1',    'petugas' => 'petugas_author1_id'],
            'editor2_valid'    => ['step' => 'editor2',    'petugas' => 'petugas_editor2_id'],
            'editor3_valid'    => ['step' => 'editor3',    'petugas' => 'petugas_editor3_id'],
            'author2_valid'    => ['step' => 'author2',    'petugas' => 'petugas_author2_id'],
            'production_valid' => ['step' => 'production', 'petugas' => 'petugas_production_id'],
            'validator_valid'  => ['step' => 'validator',  'petugas' => 'petugas_validator_id'],
        ];

        if (isset($fieldToStep[$field])) {
            $stepCfg  = $fieldToStep[$field];
            $petugasId = $submission->{$stepCfg['petugas']};

            if ($petugasId) {
                if ($newValue) {
                    // Validasi diaktifkan → beri point
                    PicPointHistory::awardPoints(
                        $petugasId,
                        $submission->id,
                        $stepCfg['step'],
                        "Validasi {$submission->kode_submit} - " . PicPointHistory::getLabelForStep($stepCfg['step'])
                    );
                } else {
                    // Validasi dibatalkan → cabut point
                    PicPointHistory::revokePoints($petugasId, $submission->id, $stepCfg['step']);
                }
            }
        }

        // Recalculate status based on current validation flags
        $submission->recalculateStatus();

        $submission->save();

        // Kirim email notifikasi validasi jika baru divalidasi & template aktif
        if ($newValue) {
            $stageName = str_replace('_valid', '', $field); // e.g. 'editor1_valid' → 'editor1'
            $tpl = EmailTemplate::findActive('validate_' . $stageName);
            if ($tpl) {
                // Ambil PIC yang terkait tahap ini
                $picFieldMap = [
                    'editor1'    => 'petugas_editor1_id',
                    'author1'    => 'petugas_author1_id',
                    'editor2'    => 'petugas_editor2_id',
                    'reviewer1'  => 'petugas_reviewer1_id',
                    'reviewer2'  => 'petugas_reviewer2_id',
                    'editor3'    => 'petugas_editor3_id',
                    'author2'    => 'petugas_author2_id',
                    'production' => 'petugas_production_id',
                    'validator'  => 'petugas_validator_id',
                ];
                $picField  = $picFieldMap[$stageName] ?? null;
                $petugas   = $picField ? Pic::find($submission->{$picField}) : null;
                if ($petugas && $petugas->email) {
                    try {
                        $rendered = $tpl->render([
                            'nama_artikel'      => $submission->judul_artikel ?? '-',
                            'kode_submit'       => $submission->kode_submit ?? '-',
                            'id_artikel'        => $submission->id,
                            'nama_jurnal'       => $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-',
                            'url_jurnal'        => $submission->journalSlot?->journalMaster?->link_jurnal ?? '-',
                            'nama_penulis'      => $submission->nama_penulis ?? '-',
                            'username_author'   => $submission->username_author ?? '-',
                            'password_author'   => $submission->password_author ?? '-',
                            'nama_pic'          => $petugas->name,
                            'email_pic'         => $petugas->email,
                            'nama_tahap'        => EmailTemplate::$triggerLabels['validate_' . $stageName] ?? $stageName,
                            'tanggal'           => now()->format('d/m/Y H:i'),
                            'username_editor'   => $submission->username_editor ?? '-',
                            'password_editor'   => $submission->password_editor ?? '-',
                            'username_reviewer1'=> $submission->username_reviewer1 ?? '-',
                            'password_reviewer1'=> $submission->password_reviewer1 ?? '-',
                            'username_reviewer2'=> $submission->username_reviewer2 ?? '-',
                            'password_reviewer2'=> $submission->password_reviewer2 ?? '-',
                            'app_name'          => config('app.name'),
                        ]);
                        $atts = $tpl->attachments;
                        try {
                            Mail::html($rendered['body'], function ($message) use ($rendered, $petugas, $atts) {
                                $message->to($petugas->email, $petugas->name)->subject($rendered['subject']);
                                foreach ($atts as $att) {
                                    if (file_exists($att->getFullPath())) {
                                        $message->attach($att->getFullPath(), ['as' => $att->original_name, 'mime' => $att->mime_type]);
                                    }
                                }
                            });
                            EmailLog::record('validate_' . $stageName, $submission->id, $petugas->email, $petugas->name, $rendered['subject'], 'sent');
                        } catch (\Exception $e) {
                            Log::warning('Email template send failed [validate_' . $stageName . ']: ' . $e->getMessage());
                            EmailLog::record('validate_' . $stageName, $submission->id, $petugas->email, $petugas->name, $rendered['subject'], 'failed', $e->getMessage());
                        }
                    } catch (\Exception $e) {
                        Log::warning('Email template render failed [validate_' . $stageName . ']: ' . $e->getMessage());
                    }
                }
            }
        }

        // LOA auto-send hook
        if ($newValue) {
            $stepKey = str_replace('_valid', '', $field); // e.g. 'production_valid' → 'production'
            \App\Http\Controllers\Admin\LoaMasterController::maybeAutoSend($submission, $stepKey . '_valid');
            if ($submission->status === 'PUBLISHED') {
                \App\Http\Controllers\Admin\LoaMasterController::maybeAutoSendOnPublish($submission);
            }
        }

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
            'link_artikel' => ['nullable', 'url', 'max:500', Rule::unique('submissions', 'link_artikel')],
            'file_artikel' => ['nullable', 'file', 'max:51200', function ($attribute, $value, $fail) {
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['doc', 'docx', 'pdf'])) {
                    $fail('File artikel harus berformat: DOC, DOCX, atau PDF.');
                }
            }],
            'link_publish' => 'nullable|url|max:500',
            'nama_penulis'        => 'required|string|max:255',
            'affiliation_penulis' => 'nullable|string|max:255',
            'no_hp_penulis'       => 'nullable|string|max:20',
            'email_penulis'       => 'nullable|email|max:255',
            'username_author'     => 'nullable|string|max:255',
            'password_author' => 'nullable|string|max:255',
            'marketing_id' => 'nullable|exists:marketings,id',
            'petugas_submit_id' => 'nullable|exists:pics,id',
            'notes' => 'nullable|string',
            'program_type' => ['nullable', Rule::in(['bkd', 'jafa'])],
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

        // Kirim email acknowledgement ke penulis jika template aktif
        $this->sendPenulisEmail($submission);

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
        
        $this->applyProgramFilter($query, $request);

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

        $this->applyProgramFilter($query, $request);

        // Sort
        match ($request->input('sort_by', 'date_asc')) {
            'title_asc'  => $query->orderBy('judul_artikel', 'asc')->orderBy('id', 'asc'),
            'title_desc' => $query->orderBy('judul_artikel', 'desc')->orderBy('id', 'asc'),
            'date_desc'  => $query->orderByDesc('tanggal_submit')->orderByDesc('id'),
            default      => $query->orderBy('tanggal_submit', 'asc')->orderBy('id', 'asc'),
        };

        // Get paginated submissions
        $submissions = $query->paginate(request()->input('per_page', 50))->withQueryString();

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
        $this->applyProgramFilter($statsQuery, $request);

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

        $program = $request->input('program');

        return view('admin.fasttrack-management.monitoring.index', compact('submissions', 'journals', 'statusOptions', 'stats', 'pics', 'users', 'marketings', 'pendingValidations', 'pendingCount', 'program'));
    }

    // ==================== WHATSAPP NOTIFICATION ====================

    /**
     * Kirim notifikasi WhatsApp ke penulis setelah submission berhasil disimpan/diupdate.
     * Mengirimkan informasi username & password OJS author.
     * Kegagalan pengiriman WA tidak menggagalkan proses utama.
     *
     * @param Submission $submission
     * @param bool $isUpdate Apakah ini notifikasi update kredensial
     */
    public function resendWhatsApp(Submission $submission)
    {
        if (empty($submission->no_hp_penulis)) {
            return back()->with('error', 'Gagal: No. HP penulis belum diisi pada data submission ini.');
        }

        if (empty($submission->username_author) && empty($submission->password_author)) {
            return back()->with('error', 'Gagal: Username dan password OJS belum diisi pada data submission ini.');
        }

        $this->sendWhatsAppNotification($submission, false);

        return back()->with('success', 'Notifikasi WhatsApp berhasil dikirim ulang ke ' . $submission->no_hp_penulis . '.');
    }

    private function sendPenulisEmail(Submission $submission): void
    {
        if (empty($submission->email_penulis)) {
            return;
        }
        $tpl = EmailTemplate::findActive('notify_penulis');
        if (!$tpl) {
            return;
        }
        $submission->loadMissing('journalSlot.journalMaster');
        $rendered = $tpl->render([
            'nama_artikel'   => $submission->judul_artikel ?? '-',
            'kode_submit'    => $submission->kode_submit ?? '-',
            'id_artikel'     => $submission->id_artikel ?? '-',
            'nama_jurnal'    => $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-',
            'url_jurnal'     => $submission->journalSlot?->journalMaster?->link_jurnal ?? '-',
            'nama_penulis'   => $submission->nama_penulis ?? '-',
            'username_author'=> $submission->username_author ?? '-',
            'password_author'=> $submission->password_author ?? '-',
            'tanggal'        => now()->format('d/m/Y H:i'),
            'app_name'       => config('app.name'),
        ]);
        $recipientEmail = $submission->email_penulis;
        $recipientName  = $submission->nama_penulis ?? $recipientEmail;
        $atts = $tpl->attachments;
        try {
            Mail::html($rendered['body'], function ($message) use ($rendered, $recipientEmail, $recipientName, $atts) {
                $message->to($recipientEmail, $recipientName)->subject($rendered['subject']);
                foreach ($atts as $att) {
                    if (file_exists(storage_path('app/' . $att->file_path))) {
                        $message->attach(storage_path('app/' . $att->file_path), ['as' => $att->original_name]);
                    }
                }
            });
            EmailLog::record('notify_penulis', $submission->id, $recipientEmail, $recipientName, $rendered['subject'], 'sent');
        } catch (\Throwable $e) {
            Log::error('Email notify_penulis gagal', ['submission_id' => $submission->id, 'error' => $e->getMessage()]);
            EmailLog::record('notify_penulis', $submission->id, $recipientEmail, $recipientName, $rendered['subject'], 'failed', $e->getMessage());
        }
    }

    private function sendWhatsAppNotification(Submission $submission, bool $isUpdate = false): void
    {
        try {
            // Pastikan nomor HP penulis tersedia
            if (empty($submission->no_hp_penulis)) {
                Log::info('Fonnte WA skip: no_hp_penulis kosong', [
                    'submission_id' => $submission->id,
                    'kode_submit' => $submission->kode_submit,
                ]);
                return;
            }

            // Pastikan ada kredensial yang dikirim
            if (empty($submission->username_author) && empty($submission->password_author)) {
                Log::info('Fonnte WA skip: username_author & password_author kosong', [
                    'submission_id' => $submission->id,
                    'kode_submit' => $submission->kode_submit,
                ]);
                return;
            }

            $fonnteService = app(FonnteService::class);

            // Cek apakah Fonnte sudah dikonfigurasi
            if (!$fonnteService->isConfigured()) {
                Log::warning('Fonnte WA skip: API token belum dikonfigurasi');
                return;
            }

            // Susun pesan notifikasi
            $message = $this->buildWhatsAppMessage($submission, $isUpdate);

            // Kirim pesan
            $result = $fonnteService->send(
                target: $submission->no_hp_penulis,
                message: $message,
                options: [
                    'countryCode' => '62',
                    'typing' => false,
                    'delay' => '2',
                ]
            );

            if ($result['success']) {
                Log::info('Fonnte WA berhasil dikirim (Admin)', [
                    'submission_id' => $submission->id,
                    'kode_submit' => $submission->kode_submit,
                    'target' => $submission->no_hp_penulis,
                    'is_update' => $isUpdate,
                ]);
            } else {
                Log::warning('Fonnte WA gagal dikirim (Admin)', [
                    'submission_id' => $submission->id,
                    'kode_submit' => $submission->kode_submit,
                    'target' => $submission->no_hp_penulis,
                    'reason' => $result['message'] ?? 'Unknown',
                ]);
            }
        } catch (\Throwable $e) {
            // Catch semua exception — notifikasi WA tidak boleh menggagalkan proses utama
            Log::error('Fonnte WA exception (Admin)', [
                'submission_id' => $submission->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Susun body pesan WhatsApp untuk notifikasi submission.
     *
     * @param Submission $submission
     * @param bool $isUpdate Apakah ini notifikasi update kredensial
     */
    private function buildWhatsAppMessage(Submission $submission, bool $isUpdate = false): string
    {
        $nama = $submission->nama_penulis ?? '-';
        $judul = $submission->judul_artikel ?? '-';
        $kode = $submission->kode_submit ?? '-';
        $username = $submission->username_author ?? '-';
        $password = $submission->password_author ?? '-';
        $linkSubmit = $submission->link_artikel ?? '-';

        if ($submission->relationLoaded('journalSlot') && $submission->journalSlot) {
            $namaJurnal = $submission->journalSlot->journalMaster->nama_jurnal ?? '-';
        } else {
            $submission->load('journalSlot.journalMaster');
            $namaJurnal = $submission->journalSlot->journalMaster->nama_jurnal ?? '-';
        }

        $key = $isUpdate ? 'wa_template_credential_update' : 'wa_template_credential_new';
        $defaultFn = $isUpdate
            ? [\App\Http\Controllers\Admin\SmsGatewayController::class, 'defaultCredentialUpdateTemplate']
            : [\App\Http\Controllers\Admin\SmsGatewayController::class, 'defaultCredentialNewTemplate'];

        $template = Setting::get($key) ?: call_user_func($defaultFn);

        return str_replace(
            ['{nama}', '{kode}', '{judul}', '{namaJurnal}', '{linkSubmit}', '{username}', '{password}'],
            [$nama, $kode, $judul, $namaJurnal, $linkSubmit, $username, $password],
            $template
        );
    }

    private function applyProgramFilter(\Illuminate\Database\Eloquent\Builder $query, Request $request): void
    {
        $program = $request->input('program');
        if ($program === 'bkd') {
            $query->where('program_type', 'bkd')
                  ->where(function ($q) {
                      $q->where('process_type', '!=', 'fasttrack')->orWhereNull('process_type');
                  });
        } elseif ($program === 'jafa') {
            $query->where('program_type', 'jafa')
                  ->where(function ($q) {
                      $q->where('process_type', '!=', 'fasttrack')->orWhereNull('process_type');
                  });
        } else {
            // Normal: hanya submission tanpa program khusus
            $query->whereNull('program_type');
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\ReviewResult;
use App\Models\User;
use App\Notifications\ReviewAssignmentNotification;
use App\Notifications\ReviewApprovedNotification;
use App\Notifications\ReviewRevisionNotification;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReviewAssignmentController extends Controller
{
    public function monitoring()
    {
        $assignments = ReviewAssignment::with([
            'reviewer', 'reviewer2', 'reviewer3', 'reviewer4', 'reviewer5',
            'reviewResults.reviewer',
            'assignedBy'
        ])
            ->latest()
            ->get();

        // Calculate statistics
        $stats = [
            'pending' => $assignments->where('status', 'PENDING')->count(),
            'on_progress' => $assignments->where('status', 'ON_PROGRESS')->count(),
            'submitted' => $assignments->where('status', 'SUBMITTED')->count(),
            'approved' => $assignments->where('status', 'APPROVED')->count(),
        ];

        return view('admin.assignments.monitoring', compact('assignments', 'stats'));
    }

    public function index()
    {
        $assignments = ReviewAssignment::with(['reviewer', 'reviewer2', 'reviewer3', 'reviewer4', 'reviewer5', 'assignedBy'])
            ->latest()
            ->paginate(request()->input('per_page', 20));

        return view('admin.assignments.index', compact('assignments'));
    }

    public function create(Request $request)
    {
        $reviewers = User::where('role', 'reviewer')
            ->select(['id', 'name', 'email', 'institution', 'field_of_study_id', 'article_languages', 'completed_reviews', 'total_points'])
            ->get();
        $fieldOfStudies = \App\Models\FieldOfStudy::active()->ordered()->get();
        
        // Get submissions data for dropdown (limited to avoid memory exhaustion)
        $submissions = \App\Models\Submission::with(['journalSlot:id,journal_master_id', 'journalSlot.journalMaster:id,nama_jurnal'])
            ->select(['id', 'id_artikel', 'judul_artikel', 'link_artikel', 'journal_slot_id'])
            ->whereNotNull('id_artikel')
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();
        
        // Get pre-selected reviewer if coming from review request approval
        $preselectedReviewerId = $request->get('reviewer_id');
        $preselectedReviewer = null;
        $journalCount = $request->get('journal_count', 1); // Default 1 jurnal
        $reviewRequestId = $request->get('review_request_id');
        
        if ($preselectedReviewerId) {
            $preselectedReviewer = User::where('role', 'reviewer')
                ->where('id', $preselectedReviewerId)
                ->with('fieldOfStudy')
                ->first();
        }

        return view('admin.assignments.create', compact('reviewers', 'fieldOfStudies', 'preselectedReviewer', 'journalCount', 'reviewRequestId', 'submissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_title' => 'required|string|max:500',
            'article_number' => 'required|string|max:255',
            'submit_link' => 'required|url',
            'article_file' => 'nullable|file|mimes:doc,docx,pdf|max:10240', // Max 10MB
            'deadline' => 'required|date|after:today',
            'language' => 'required|in:Indonesia,Inggris',
            'field_of_study_id' => 'required|exists:field_of_studies,id',
            'reviewer_id' => 'required|exists:users,id',
            'reviewer_1_username' => 'required|string|max:255',
            'reviewer_1_password' => 'required|string|max:255',
            'reviewer_2_id' => 'nullable|exists:users,id|different:reviewer_id',
            'reviewer_2_username' => 'nullable|string|max:255',
            'reviewer_2_password' => 'nullable|string|max:255',
            'reviewer_3_id' => 'nullable|exists:users,id',
            'reviewer_3_username' => 'nullable|string|max:255',
            'reviewer_3_password' => 'nullable|string|max:255',
            'reviewer_4_id' => 'nullable|exists:users,id',
            'reviewer_4_username' => 'nullable|string|max:255',
            'reviewer_4_password' => 'nullable|string|max:255',
            'reviewer_5_id' => 'nullable|exists:users,id',
            'reviewer_5_username' => 'nullable|string|max:255',
            'reviewer_5_password' => 'nullable|string|max:255',
        ]);

        // Validate no duplicate reviewers
        $reviewerIds = array_filter([
            $request->reviewer_id,
            $request->reviewer_2_id,
            $request->reviewer_3_id,
            $request->reviewer_4_id,
            $request->reviewer_5_id,
        ]);
        
        if (count($reviewerIds) !== count(array_unique($reviewerIds))) {
            return back()->withErrors(['reviewer_id' => 'Tidak boleh memilih reviewer yang sama.'])->withInput();
        }

        // Handle article file upload
        $articleFilePath = null;
        $articleFileOriginalName = null;
        if ($request->hasFile('article_file')) {
            $file = $request->file('article_file');
            $articleFileOriginalName = $file->getClientOriginalName();
            $filename = time() . '_' . $articleFileOriginalName;
            $file->storeAs('assignments/articles', $filename, 'public');
            $articleFilePath = 'assignments/articles/' . $filename;
        }

        $assignment = ReviewAssignment::create([
            'article_title' => $request->article_title,
            'article_number' => $request->article_number,
            'submit_link' => $request->submit_link,
            'article_file' => $articleFilePath,
            'article_file_original_name' => $articleFileOriginalName,
            'account_username' => null,
            'account_password' => null,
            'reviewer_username' => null,
            'reviewer_password' => null,
            'assignment_letter_link' => null,
            'certificate_link' => null,
            'deadline' => $request->deadline,
            'language' => $request->language,
            'field_of_study_id' => $request->field_of_study_id,
            'journal_id' => null,
            'reviewer_id' => $request->reviewer_id,
            'reviewer_1_username' => $request->reviewer_1_username,
            'reviewer_1_password' => $request->reviewer_1_password,
            'reviewer_2_id' => $request->reviewer_2_id,
            'reviewer_2_username' => $request->reviewer_2_username,
            'reviewer_2_password' => $request->reviewer_2_password,
            'reviewer_3_id' => $request->reviewer_3_id,
            'reviewer_3_username' => $request->reviewer_3_username,
            'reviewer_3_password' => $request->reviewer_3_password,
            'reviewer_4_id' => $request->reviewer_4_id,
            'reviewer_4_username' => $request->reviewer_4_username,
            'reviewer_4_password' => $request->reviewer_4_password,
            'reviewer_5_id' => $request->reviewer_5_id,
            'reviewer_5_username' => $request->reviewer_5_username,
            'reviewer_5_password' => $request->reviewer_5_password,
            'assigned_by' => auth()->id(),
            'status' => 'PENDING',
        ]);

        // Send email notification to all assigned reviewers
        $reviewers = [];
        if ($request->reviewer_id) {
            $reviewers[] = User::find($request->reviewer_id);
        }
        if ($request->reviewer_2_id) {
            $reviewers[] = User::find($request->reviewer_2_id);
        }
        if ($request->reviewer_3_id) {
            $reviewers[] = User::find($request->reviewer_3_id);
        }
        if ($request->reviewer_4_id) {
            $reviewers[] = User::find($request->reviewer_4_id);
        }
        if ($request->reviewer_5_id) {
            $reviewers[] = User::find($request->reviewer_5_id);
        }

        foreach ($reviewers as $reviewer) {
            if ($reviewer && $reviewer->email) {
                try {
                    $reviewer->notify(new ReviewAssignmentNotification($assignment));
                    \Log::info('Review assignment notification sent to: ' . $reviewer->email);
                } catch (\Exception $e) {
                    \Log::error('Failed to send review assignment notification to ' . $reviewer->email . ': ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Review berhasil ditugaskan dan notifikasi email telah dikirim ke reviewer');
    }

    /**
     * Store multiple assignments at once (batch)
     */
    public function storeBatch(Request $request)
    {
        // Validate the batch data
        $validated = $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'review_request_id' => 'nullable|exists:review_requests,id',
            'journals' => 'required|array|min:1',
            'journals.*.article_title' => 'required|string|max:500',
            'journals.*.article_number' => 'required|string|max:255',
            'journals.*.submit_link' => 'required|url',
            'journals.*.deadline' => 'required|date|after:today',
            'journals.*.language' => 'required|in:Indonesia,Inggris',
            'journals.*.field_of_study_id' => 'required|exists:field_of_studies,id',
            'journals.*.reviewer_1_username' => 'required|string|max:255',
            'journals.*.reviewer_1_password' => 'required|string|max:255',
        ]);

        $successCount = 0;
        $assignments = [];

        foreach ($request->journals as $index => $journalData) {
            try {
                $assignment = ReviewAssignment::create([
                    'article_title' => $journalData['article_title'],
                    'article_number' => $journalData['article_number'],
                    'submit_link' => $journalData['submit_link'],
                    'deadline' => $journalData['deadline'],
                    'language' => $journalData['language'],
                    'field_of_study_id' => $journalData['field_of_study_id'],
                    'reviewer_id' => $request->reviewer_id,
                    'reviewer_1_username' => $journalData['reviewer_1_username'],
                    'reviewer_1_password' => $journalData['reviewer_1_password'],
                    'assigned_by' => auth()->id(),
                    'status' => 'PENDING',
                    'account_username' => null,
                    'account_password' => null,
                    'reviewer_username' => null,
                    'reviewer_password' => null,
                    'assignment_letter_link' => null,
                    'certificate_link' => null,
                    'journal_id' => null,
                    'reviewer_2_id' => null,
                    'reviewer_3_id' => null,
                    'reviewer_4_id' => null,
                    'reviewer_5_id' => null,
                ]);

                $assignments[] = $assignment;
                $successCount++;
            } catch (\Exception $e) {
                \Log::error('Failed to create assignment ' . ($index + 1) . ': ' . $e->getMessage());
            }
        }

        // Send notification to reviewer
        $reviewer = User::find($request->reviewer_id);
        if ($reviewer && $reviewer->email && count($assignments) > 0) {
            try {
                // Send notification for first assignment (or you can loop for each)
                $reviewer->notify(new ReviewAssignmentNotification($assignments[0]));
                \Log::info('Review assignment notification sent to: ' . $reviewer->email);
            } catch (\Exception $e) {
                \Log::error('Failed to send review assignment notification: ' . $e->getMessage());
            }
        }

        // Update review request status if linked
        if ($request->review_request_id) {
            try {
                $reviewRequest = \App\Models\ReviewRequest::find($request->review_request_id);
                if ($reviewRequest) {
                    $reviewRequest->update(['is_assigned' => true]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to update review request: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.assignments.index')
            ->with('success', "Berhasil menugaskan {$successCount} review kepada {$reviewer->name}. Notifikasi email telah dikirim.");
    }

    public function show(ReviewAssignment $assignment)
    {
        $assignment->load(['reviewer', 'reviewer2', 'reviewer3', 'reviewer4', 'reviewer5', 'assignedBy', 'reviewResults.reviewer']);
        return view('admin.assignments.show', compact('assignment'));
    }

    public function approve(ReviewAssignment $assignment)
    {
        if ($assignment->status !== 'SUBMITTED') {
            return back()->with('error', 'Review belum disubmit');
        }

        $assignment->approve();

        // Send notification to all reviewers who submitted
        $reviewResults = $assignment->reviewResults;
        foreach ($reviewResults as $result) {
            if ($result->reviewer && $result->reviewer->email) {
                $points = $assignment->journal ? $assignment->journal->points : 0;
                $result->reviewer->notify(new ReviewApprovedNotification($assignment, $points));
            }
        }

        ActivityLog::record('review_approved', $assignment, [], [
            'reviewer_count' => $reviewResults->count(),
            'points' => $assignment->journal?->points ?? 0,
        ]);

        return back()->with('success', 'Review berhasil disetujui dan point telah diberikan');
    }

    public function revision(Request $request, ReviewAssignment $assignment)
    {
        $validated = $request->validate([
            'admin_feedback' => 'required|string',
            'reviewer_ids' => 'required|array',
            'reviewer_ids.*' => 'exists:users,id',
        ]);

        $assignment->requestRevision();
        
        // Update admin feedback for selected reviewers only and send notification
        $reviewResults = $assignment->reviewResults()->whereIn('reviewer_id', $validated['reviewer_ids'])->get();
        foreach ($reviewResults as $result) {
            $result->update([
                'admin_feedback' => $validated['admin_feedback'],
            ]);
            
            if ($result->reviewer && $result->reviewer->email) {
                $result->reviewer->notify(new ReviewRevisionNotification($assignment, $validated['admin_feedback']));
            }
        }

        ActivityLog::record('revision_requested', $assignment, [], [
            'admin_feedback' => $validated['admin_feedback'],
            'reviewer_count' => count($reviewResults),
        ]);

        return back()->with('success', 'Permintaan revisi telah dikirim ke ' . count($reviewResults) . ' reviewer');
    }

    public function download(ReviewAssignment $assignment)
    {
        if (!$assignment->reviewResult || !$assignment->reviewResult->file_path) {
            return back()->with('error', 'File review tidak ditemukan');
        }

        $filePath = storage_path('app/' . $assignment->reviewResult->file_path);
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'File tidak ditemukan di server');
        }

        return response()->download($filePath);
    }

    /**
     * Download PDF hasil formulir review
     */
    public function downloadPdf(ReviewAssignment $assignment, ReviewResult $reviewResult)
    {
        // Verify the review result belongs to this assignment
        if ($reviewResult->review_assignment_id !== $assignment->id) {
            return back()->with('error', 'Data review tidak valid');
        }

        // Get reviewer data for signature
        $reviewer = $reviewResult->reviewer;

        // Generate PDF using the same view as reviewer
        $pdf = Pdf::loadView('reviewer.results.pdf', [
            'result' => $reviewResult,
            'reviewer' => $reviewer,
            'assignment' => $assignment
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Download PDF - sanitize filename to remove all invalid characters
        $articleCode = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $reviewResult->article_code ?? $assignment->article_number ?? 'article');
        $reviewerName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $reviewer ? $reviewer->name : 'reviewer');
        $filename = 'Review_' . $articleCode . '_' . $reviewerName . '_' . date('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }

    public function destroy(ReviewAssignment $assignment)
    {
        if ($assignment->status !== 'PENDING') {
            return back()->with('error', 'Hanya assignment dengan status pending yang bisa dihapus');
        }

        $assignment->delete();

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Assignment berhasil dihapus');
    }
}

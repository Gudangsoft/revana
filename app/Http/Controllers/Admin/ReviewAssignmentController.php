<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewAssignmentController extends Controller
{
    public function index()
    {
        $assignments = ReviewAssignment::with(['journal', 'reviewer', 'reviewer2', 'reviewer3', 'reviewer4', 'reviewer5', 'assignedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.assignments.index', compact('assignments'));
    }

    public function create()
    {
        $journals = Journal::all();
        $reviewers = User::where('role', 'reviewer')->get();

        return view('admin.assignments.create', compact('journals', 'reviewers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'article_title' => 'required|string|max:500',
            'article_number' => 'required|string|max:255',
            'submit_link' => 'required|url',
            'account_username' => 'required|string|max:255',
            'account_password' => 'required|string|max:255',
            'reviewer_username' => 'required|string|max:255',
            'reviewer_password' => 'required|string|max:255',
            'deadline' => 'required|date|after:today',
            'language' => 'required|in:Indonesia,Inggris',
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

        ReviewAssignment::create([
            'article_title' => $request->article_title,
            'article_number' => $request->article_number,
            'submit_link' => $request->submit_link,
            'account_username' => $request->account_username,
            'account_password' => $request->account_password,
            'reviewer_username' => $request->reviewer_username,
            'reviewer_password' => $request->reviewer_password,
            'assignment_letter_link' => null,
            'certificate_link' => null,
            'deadline' => $request->deadline,
            'language' => $request->language,
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

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Review berhasil ditugaskan');
    }

    public function show(ReviewAssignment $assignment)
    {
        $assignment->load(['journal', 'reviewer', 'reviewer2', 'reviewer3', 'reviewer4', 'reviewer5', 'assignedBy', 'reviewResult']);
        return view('admin.assignments.show', compact('assignment'));
    }

    public function approve(ReviewAssignment $assignment)
    {
        if ($assignment->status !== 'SUBMITTED') {
            return back()->with('error', 'Review belum disubmit');
        }

        $assignment->approve();

        return back()->with('success', 'Review berhasil disetujui dan point telah diberikan');
    }

    public function revision(Request $request, ReviewAssignment $assignment)
    {
        $validated = $request->validate([
            'admin_feedback' => 'required|string',
        ]);

        $assignment->requestRevision();
        
        if ($assignment->reviewResult) {
            $assignment->reviewResult->update([
                'admin_feedback' => $validated['admin_feedback'],
            ]);
        }

        return back()->with('success', 'Permintaan revisi telah dikirim');
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

    public function destroy(ReviewAssignment $assignment)
    {
        if ($assignment->status !== 'pending') {
            return back()->with('error', 'Hanya assignment dengan status pending yang bisa dihapus');
        }

        $assignment->delete();

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Assignment berhasil dihapus');
    }}
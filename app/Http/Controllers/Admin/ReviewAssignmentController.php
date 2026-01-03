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
        $assignments = ReviewAssignment::with(['journal', 'reviewer', 'reviewer2', 'assignedBy'])
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
            'submit_link' => 'required|url',
            'account_username' => 'required|string|max:255',
            'account_password' => 'required|string|max:255',
            'assignment_letter_link' => 'required|url',
            'certificate_link' => 'required|url',
            'deadline' => 'required|date|after:today',
            'language' => 'required|in:Indonesia,Inggris',
            'reviewer_id' => 'required|exists:users,id',
            'reviewer_2_id' => 'nullable|exists:users,id|different:reviewer_id',
        ]);

        ReviewAssignment::create([
            'article_title' => $request->article_title,
            'submit_link' => $request->submit_link,
            'account_username' => $request->account_username,
            'account_password' => $request->account_password,
            'assignment_letter_link' => $request->assignment_letter_link,
            'certificate_link' => $request->certificate_link,
            'deadline' => $request->deadline,
            'language' => $request->language,
            'journal_id' => null, // Tidak menggunakan journal_id lagi
            'reviewer_id' => $request->reviewer_id,
            'reviewer_2_id' => $request->reviewer_2_id,
            'assigned_by' => auth()->id(),
            'status' => 'PENDING',
        ]);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Review berhasil ditugaskan');
    }

    public function show(ReviewAssignment $assignment)
    {
        $assignment->load(['journal', 'reviewer', 'reviewer2', 'assignedBy', 'reviewResult']);
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
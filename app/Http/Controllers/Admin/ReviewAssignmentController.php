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
        $assignments = ReviewAssignment::with(['journal', 'reviewer', 'assignedBy'])
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
            'journal_id' => 'required|exists:journals,id',
            'reviewer_id' => 'required|exists:users,id',
        ]);

        // Check if assignment already exists
        $exists = ReviewAssignment::where('journal_id', $request->journal_id)
            ->where('reviewer_id', $request->reviewer_id)
            ->whereIn('status', ['PENDING', 'ACCEPTED', 'ON_PROGRESS', 'SUBMITTED', 'REVISION'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Reviewer sudah ditugaskan untuk jurnal ini');
        }

        ReviewAssignment::create([
            'journal_id' => $request->journal_id,
            'reviewer_id' => $request->reviewer_id,
            'assigned_by' => auth()->id(),
            'status' => 'PENDING',
        ]);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Review berhasil ditugaskan');
    }

    public function show(ReviewAssignment $assignment)
    {
        $assignment->load(['journal', 'reviewer', 'assignedBy', 'reviewResult']);
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
<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get assignments where user is any of the reviewers (1-5)
        $assignments = ReviewAssignment::where(function($query) use ($user) {
                $query->where('reviewer_id', $user->id)
                      ->orWhere('reviewer_2_id', $user->id)
                      ->orWhere('reviewer_3_id', $user->id)
                      ->orWhere('reviewer_4_id', $user->id)
                      ->orWhere('reviewer_5_id', $user->id);
            })
            ->with(['journal', 'reviewer', 'reviewer2', 'reviewer3', 'reviewer4', 'reviewer5'])
            ->latest()
            ->paginate(20);

        return view('reviewer.tasks.index', compact('assignments'));
    }

    public function show(ReviewAssignment $assignment)
    {
        // Check if user owns this assignment (as any reviewer 1-5)
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }

        $assignment->load(['journal', 'reviewer', 'reviewer2', 'reviewer3', 'reviewer4', 'reviewer5', 'reviewResult']);
        
        return view('reviewer.tasks.show', compact('assignment'));
    }

    public function accept(ReviewAssignment $assignment)
    {
        // Check if user owns this assignment
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }

        if ($assignment->status !== 'PENDING') {
            return back()->with('error', 'Task tidak dalam status pending');
        }
        
        // Check if expired
        if ($assignment->isExpired()) {
            return back()->with('error', 'Task sudah melewati deadline dan tidak dapat diterima lagi');
        }

        $assignment->accept();

        return back()->with('success', 'Task berhasil diterima');
    }

    public function reject(Request $request, ReviewAssignment $assignment)
    {
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }

        if ($assignment->status !== 'PENDING') {
            return back()->with('error', 'Task tidak dalam status pending');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $assignment->reject($validated['rejection_reason']);

        return redirect()->route('reviewer.tasks.index')
            ->with('success', 'Task berhasil ditolak');
    }

    public function startProgress(ReviewAssignment $assignment)
    {
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }

        if ($assignment->status !== 'ACCEPTED') {
            return back()->with('error', 'Task harus diterima terlebih dahulu');
        }
        
        // Check if expired
        if ($assignment->isExpired()) {
            return back()->with('error', 'Task sudah melewati deadline dan tidak dapat dikerjakan lagi');
        }

        $assignment->startProgress();

        return back()->with('success', 'Review dimulai');
    }

    public function uploadRevision(Request $request, ReviewAssignment $assignment)
    {
        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer) {
            abort(403);
        }

        if (!in_array($assignment->status, ['ON_PROGRESS', 'REVISION'])) {
            return back()->with('error', 'Upload file revisi hanya bisa dilakukan saat status ON_PROGRESS atau REVISION');
        }

        $validated = $request->validate([
            'revision_file' => 'required|file|mimes:pdf,doc,docx,zip,rar|max:10240', // 10MB
        ]);

        // Delete old file if exists
        if ($assignment->revision_file) {
            Storage::disk('public')->delete($assignment->revision_file);
        }

        // Store new file
        $file = $request->file('revision_file');
        $filename = 'revisions/' . time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('revisions', time() . '_' . $file->getClientOriginalName(), 'public');

        $assignment->revision_file = $path;
        $assignment->save();

        return back()->with('success', 'File revisi berhasil diupload');
    }
}

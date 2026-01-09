<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use Illuminate\Http\Request;

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

        $isReviewer = $assignment->reviewer_id === auth()->id() 
                   || $assignment->reviewer_2_id === auth()->id()
                   || $assignment->reviewer_3_id === auth()->id()
                   || $assignment->reviewer_4_id === auth()->id()
                   || $assignment->reviewer_5_id === auth()->id();
                   
        if (!$isReviewer
            ->with('success', 'Task berhasil ditolak');
    }

    public function startProgress(ReviewAssignment $assignment)
    {
        if ($assignment->reviewer_id !== auth()->id() && $assignment->reviewer_2_id !== auth()->id()) {
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
}

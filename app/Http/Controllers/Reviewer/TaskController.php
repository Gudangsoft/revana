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
        
        // Get assignments where user is reviewer 1 or reviewer 2
        $assignments = ReviewAssignment::where(function($query) use ($user) {
                $query->where('reviewer_id', $user->id)
                      ->orWhere('reviewer_2_id', $user->id);
            })
            ->with(['journal', 'reviewer', 'reviewer2'])
            ->latest()
            ->paginate(20);

        return view('reviewer.tasks.index', compact('assignments'));
    }

    public function show(ReviewAssignment $assignment)
    {
        // Check if user owns this assignment (as reviewer 1 or 2)
        if ($assignment->reviewer_id !== auth()->id() && $assignment->reviewer_2_id !== auth()->id()) {
            abort(403);
        }

        $assignment->load(['journal', 'reviewer', 'reviewer2', 'reviewResult']);
        
        return view('reviewer.tasks.show', compact('assignment'));
    }

    public function accept(ReviewAssignment $assignment)
    {
        // Check if user owns this assignment
        if ($assignment->reviewer_id !== auth()->id() && $assignment->reviewer_2_id !== auth()->id()) {
            abort(403);
        }

        if ($assignment->status !== 'PENDING') {
            return back()->with('error', 'Task tidak dalam status pending');
        }

        $assignment->accept();

        return back()->with('success', 'Task berhasil diterima');
    }

    public function reject(Request $request, ReviewAssignment $assignment)
    {
        if ($assignment->reviewer_id !== auth()->id() && $assignment->reviewer_2_id !== auth()->id()) {
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
        if ($assignment->reviewer_id !== auth()->id() && $assignment->reviewer_2_id !== auth()->id()) {
            abort(403);
        }

        if ($assignment->status !== 'ACCEPTED') {
            return back()->with('error', 'Task harus diterima terlebih dahulu');
        }

        $assignment->startProgress();

        return back()->with('success', 'Review dimulai');
    }
}

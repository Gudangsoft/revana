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
        
        $assignments = ReviewAssignment::where('reviewer_id', $user->id)
            ->with('journal')
            ->latest()
            ->paginate(20);

        return view('reviewer.tasks.index', compact('assignments'));
    }

    public function show(ReviewAssignment $assignment)
    {
        // Check if user owns this assignment
        if ($assignment->reviewer_id !== auth()->id()) {
            abort(403);
        }

        $assignment->load(['journal', 'reviewResult']);
        
        return view('reviewer.tasks.show', compact('assignment'));
    }

    public function accept(ReviewAssignment $assignment)
    {
        if ($assignment->reviewer_id !== auth()->id()) {
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
        if ($assignment->reviewer_id !== auth()->id()) {
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
        if ($assignment->reviewer_id !== auth()->id()) {
            abort(403);
        }

        if ($assignment->status !== 'ACCEPTED') {
            return back()->with('error', 'Task harus diterima terlebih dahulu');
        }

        $assignment->startProgress();

        return back()->with('success', 'Review dimulai');
    }
}

<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\ReviewResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewResultController extends Controller
{
    public function create(ReviewAssignment $assignment)
    {
        if ($assignment->reviewer_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($assignment->status, ['ON_PROGRESS', 'REVISION'])) {
            return back()->with('error', 'Review belum dimulai');
        }

        return view('reviewer.results.create', compact('assignment'));
    }

    public function store(Request $request, ReviewAssignment $assignment)
    {
        if ($assignment->reviewer_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'google_drive_link' => 'required|url',
            'recommendation' => 'required|in:ACCEPT,MINOR REVISION,MAJOR REVISION,REJECT',
            'notes' => 'nullable|string',
        ]);

        // Create or update review result
        ReviewResult::updateOrCreate(
            ['review_assignment_id' => $assignment->id],
            [
                'file_path' => $validated['google_drive_link'],
                'recommendation' => $validated['recommendation'],
                'notes' => $validated['notes'],
            ]
        );

        // Update assignment status
        $assignment->submit();

        return redirect()->route('reviewer.tasks.show', $assignment)
            ->with('success', 'Hasil review berhasil disubmit');
    }
}

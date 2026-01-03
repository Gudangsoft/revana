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
            // Basic Information
            'journal_name' => 'required|string|max:255',
            'article_code' => 'required|string|max:255',
            'article_title' => 'required|string',
            'review_date' => 'required|date',
            
            // Section I: Penilaian Substansi (8 aspek with scores 1-5)
            'score_1' => 'required|integer|min:1|max:5',
            'comment_1' => 'required|string',
            'score_2' => 'required|integer|min:1|max:5',
            'comment_2' => 'required|string',
            'score_3' => 'required|integer|min:1|max:5',
            'comment_3' => 'required|string',
            'score_4' => 'required|integer|min:1|max:5',
            'comment_4' => 'required|string',
            'score_5' => 'required|integer|min:1|max:5',
            'comment_5' => 'required|string',
            'score_6' => 'required|integer|min:1|max:5',
            'comment_6' => 'required|string',
            'score_7' => 'required|integer|min:1|max:5',
            'comment_7' => 'required|string',
            'score_8' => 'required|integer|min:1|max:5',
            'comment_8' => 'required|string',
            
            // Section II: Penilaian Teknis (3 kriteria boolean)
            'technical_1' => 'nullable|boolean',
            'technical_2' => 'nullable|boolean',
            'technical_3' => 'nullable|boolean',
            
            // Section III: Saran Perbaikan
            'improvement_suggestions' => 'required|string',
            
            // Section IV: Rekomendasi
            'recommendation' => 'required|in:ACCEPT,MINOR_REVISION,MAJOR_REVISION,REJECT',
        ]);

        // Auto-fill reviewer name from authenticated user
        $validated['reviewer_name'] = auth()->user()->name;
        $validated['reviewer_signature'] = auth()->user()->name;
        $validated['statement_date'] = $validated['review_date'];
        
        // Handle boolean checkboxes (if not checked, set to false)
        $validated['technical_1'] = $request->has('technical_1');
        $validated['technical_2'] = $request->has('technical_2');
        $validated['technical_3'] = $request->has('technical_3');

        // Keep old file_path for backward compatibility (empty or placeholder)
        $validated['file_path'] = 'formulir-review-filled';

        // Create or update review result
        ReviewResult::updateOrCreate(
            ['review_assignment_id' => $assignment->id],
            $validated
        );

        // Update assignment status
        $assignment->submit();

        return redirect()->route('reviewer.tasks.show', $assignment)
            ->with('success', 'Formulir review berhasil disubmit');
    }
}

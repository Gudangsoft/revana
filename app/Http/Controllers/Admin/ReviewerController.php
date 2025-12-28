<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewerController extends Controller
{
    public function index()
    {
        $reviewers = User::where('role', 'reviewer')
            ->with('badges')
            ->withCount('reviewAssignments')
            ->latest()
            ->paginate(20);

        return view('admin.reviewers.index', compact('reviewers'));
    }

    public function show(User $reviewer)
    {
        if (!$reviewer->isReviewer()) {
            abort(404);
        }

        $reviewer->load([
            'badges', 
            'reviewAssignments.journal',
            'reviewAssignments.reviewResult',
            'pointHistories'
        ]);
        
        return view('admin.reviewers.show', compact('reviewer'));
    }
}

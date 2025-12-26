<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalJournals = Journal::count();
        $totalReviewers = User::where('role', 'reviewer')->count();
        $totalReviews = ReviewAssignment::count();
        $pendingReviews = ReviewAssignment::where('status', 'PENDING')->count();
        $submittedReviews = ReviewAssignment::where('status', 'SUBMITTED')->count();
        $pendingRedemptions = RewardRedemption::where('status', 'PENDING')->count();

        $recentAssignments = ReviewAssignment::with(['journal', 'reviewer'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalJournals',
            'totalReviewers',
            'totalReviews',
            'pendingReviews',
            'submittedReviews',
            'pendingRedemptions',
            'recentAssignments'
        ));
    }
}

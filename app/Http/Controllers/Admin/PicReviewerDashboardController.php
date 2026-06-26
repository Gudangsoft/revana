<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PicReviewerDashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('pic_reviewer.dashboard_stats', 60, function () {
            $totalReviewers  = User::where('role', 'reviewer')->count();
            $activeReviewers = User::where('role', 'reviewer')
                ->whereHas('reviewAssignments')
                ->count();

            $pendingReview = ReviewAssignment::where('status', 'PENDING')->count();

            $completedThisMonth = ReviewAssignment::where('status', 'APPROVED')
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count();

            $totalSubmissions = Submission::count();
            $normalCount      = Submission::where(fn($q) =>
                $q->whereNull('process_type')->orWhere('process_type', 'normal')
            )->whereNull('program_type')->count();
            $fastrackCount    = Submission::where('process_type', 'fasttrack')->count();
            $bkdCount         = Submission::where('program_type', 'bkd')->count();
            $jafaCount        = Submission::where('program_type', 'jafa')->count();

            return compact(
                'totalReviewers', 'activeReviewers',
                'pendingReview', 'completedThisMonth',
                'totalSubmissions', 'normalCount', 'fastrackCount', 'bkdCount', 'jafaCount'
            );
        });

        $pendingReviewRequests = Cache::remember('admin.pending_review_requests', 60, function () {
            try {
                return \App\Models\ReviewRequest::where('status', 'pending')->count();
            } catch (\Exception) {
                return 0;
            }
        });

        // Recent pending review assignments
        $recentPending = ReviewAssignment::with(['journal', 'reviewer'])
            ->where('status', 'PENDING')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.pic-reviewer.dashboard', compact(
            'stats', 'pendingReviewRequests', 'recentPending'
        ));
    }
}

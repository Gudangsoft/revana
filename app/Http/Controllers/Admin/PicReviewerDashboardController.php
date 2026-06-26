<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PicReviewerDashboardController extends Controller
{
    public function index()
    {
        // Statistik review
        $stats = Cache::remember('pic_reviewer.dashboard_stats', 60, function () {
            $totalReviewers = User::where('role', 'reviewer')->count();
            $activeReviewers = User::where('role', 'reviewer')->where('is_active', true)->count();

            $pendingReview = Submission::whereHas('reviewAssignments', fn($q) =>
                $q->whereNull('result')->whereNull('reviewed_at')
            )->count();

            $completedThisMonth = Submission::whereHas('reviewAssignments', fn($q) =>
                $q->whereNotNull('reviewed_at')
                  ->whereMonth('reviewed_at', now()->month)
                  ->whereYear('reviewed_at', now()->year)
            )->count();

            $totalSubmissions  = Submission::count();
            $normalCount       = Submission::whereNull('process_type')->orWhere('process_type', 'normal')->count();
            $fastrackCount     = Submission::where('process_type', 'fasttrack')->count();
            $bkdCount          = Submission::where('program_type', 'bkd')->count();
            $jafaCount         = Submission::where('program_type', 'jafa')->count();

            return compact(
                'totalReviewers', 'activeReviewers',
                'pendingReview', 'completedThisMonth',
                'totalSubmissions', 'normalCount', 'fastrackCount', 'bkdCount', 'jafaCount'
            );
        });

        // Pending review requests
        $pendingReviewRequests = Cache::remember('admin.pending_review_requests', 60, function () {
            try {
                return \App\Models\ReviewRequest::where('status', 'pending')->count();
            } catch (\Exception) {
                return 0;
            }
        });

        // Recent submissions needing review
        $recentPending = Submission::with(['journalSlot.journalMaster'])
            ->whereHas('reviewAssignments', fn($q) =>
                $q->whereNull('reviewed_at')
            )
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.pic-reviewer.dashboard', compact(
            'stats', 'pendingReviewRequests', 'recentPending'
        ));
    }
}

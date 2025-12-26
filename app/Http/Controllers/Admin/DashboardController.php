<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Models\RewardRedemption;
use App\Exports\CompletedReviewsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

        // Completed reviews report data
        $completedReviews = ReviewAssignment::with(['journal', 'reviewer', 'reviewResult'])
            ->where('status', 'APPROVED')
            ->orderBy('approved_at', 'desc')
            ->take(20)
            ->get();

        $totalCompletedReviews = ReviewAssignment::where('status', 'APPROVED')->count();

        return view('admin.dashboard', compact(
            'totalJournals',
            'totalReviewers',
            'totalReviews',
            'pendingReviews',
            'submittedReviews',
            'pendingRedemptions',
            'recentAssignments',
            'completedReviews',
            'totalCompletedReviews'
        ));
    }

    public function exportCompletedReviews(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $fileName = 'laporan-review-selesai-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(
            new CompletedReviewsExport($startDate, $endDate),
            $fileName
        );
    }
}

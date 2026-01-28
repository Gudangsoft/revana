<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\Submission;
use App\Models\User;
use App\Models\Assignment;
use App\Models\ReviewRequest;
use App\Exports\CompletedReviewsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index()
    {
        $totalJournals = JournalMaster::count();
        $totalReviewers = User::where('user_type', 'pic')->count();
        $totalSubmissions = Submission::count();
        $pendingSubmissions = Submission::whereIn('status', ['pending', 'new'])->count();
        $submittedReviews = Assignment::where('status', 'submitted')->count();
        $pendingReviewRequests = ReviewRequest::where('status', 'pending')->count() ?? 0;

        $recentSubmissions = Submission::with(['journalSlot.journalMaster'])
            ->latest()
            ->take(10)
            ->get();

        // Completed reviews report data
        $completedReviews = Assignment::with(['submission.journalSlot.journalMaster', 'reviewer', 'result'])
            ->where('status', 'approved')
            ->orderBy('approved_at', 'desc')
            ->take(20)
            ->get();

        $totalCompletedReviews = Assignment::where('status', 'approved')->count();

        return view('admin.dashboard', compact(
            'totalJournals',
            'totalReviewers',
            'totalSubmissions',
            'pendingSubmissions',
            'submittedReviews',
            'pendingReviewRequests',
            'recentSubmissions',
            'completedReviews',
            'totalCompletedReviews'
        ));
    }

    public function exportCompletedReviews(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $fileName = 'laporan-submissions-' . date('Y-m-d-His') . '.xlsx';

        // For now, return a simple response since CompletedReviewsExport needs to be updated
        return response()->json([
            'message' => 'Export akan tersedia setelah sistem review assignment diimplementasikan.'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use App\Models\Submission;
use App\Models\User;
use App\Models\ReviewRequest;
use App\Exports\CompletedReviewsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic counts
        $totalJournals = JournalMaster::count();
        $totalReviewers = User::where('role', 'reviewer')->count();
        $totalSubmissions = Submission::count();
        
        // Submission status counts
        $pendingSubmissions = Submission::where('status', 'pending')->count();
        $newSubmissions = Submission::where('status', 'new')->count();
        $inProgressSubmissions = Submission::where('status', 'in_progress')->count();
        $submittedReviews = Submission::where('status', 'submitted')->count();
        $approvedSubmissions = Submission::where('status', 'approved')->count();
        $rejectedSubmissions = Submission::where('status', 'rejected')->count();
        
        // Process type counts  
        $regularSubmissions = Submission::where('process_type', 'regular')->orWhereNull('process_type')->count();
        $fasttrackSubmissions = Submission::where('process_type', 'fasttrack')->count();
        
        // Journal statistics
        $journalsByAccreditation = JournalMaster::selectRaw('accreditation, COUNT(*) as count')
            ->groupBy('accreditation')
            ->orderBy('count', 'desc')
            ->get();
            
        // Recent submissions
        $recentSubmissions = Submission::with(['journalSlot.journalMaster'])
            ->latest()
            ->take(10)
            ->get();

        // Completed submissions (approved)
        $completedReviews = Submission::with(['journalSlot.journalMaster'])
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get();

        $totalCompletedReviews = $approvedSubmissions;
        
        // Monthly statistics
        $monthlySubmissions = Submission::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'totalJournals',
            'totalReviewers', 
            'totalSubmissions',
            'pendingSubmissions',
            'newSubmissions',
            'inProgressSubmissions',
            'submittedReviews',
            'approvedSubmissions',
            'rejectedSubmissions',
            'regularSubmissions',
            'fasttrackSubmissions',
            'journalsByAccreditation',
            'recentSubmissions',
            'completedReviews',
            'totalCompletedReviews',
            'monthlySubmissions'
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

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
        $pendingSubmissions = Submission::where('status', 'SUBMITTED')->count();
        $newSubmissions = Submission::where('status', 'SUBMITTED')->count();
        $inProgressSubmissions = Submission::whereNotIn('status', ['SUBMITTED', 'PUBLISHED', 'REJECTED'])->count();
        $submittedReviews = Submission::where('status', 'SUBMITTED')->count();
        $approvedSubmissions = Submission::where('status', 'PUBLISHED')->count();
        $rejectedSubmissions = Submission::where('status', 'REJECTED')->count();
        
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
            ->where('status', 'PUBLISHED')
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

    /**
     * Component Overview - Admin interface to monitor what PIC and Marketing users access.
     * Shows shared Blade Components preview and access mapping.
     */
    public function componentOverview()
    {
        // Get a sample submission for component preview
        $sampleSubmission = Submission::with([
            'journalSlot.journalMaster',
            'marketing',
            'petugasSubmit', 'petugasEditor1', 'petugasAuthor1',
            'petugasEditor2', 'petugasReviewer1', 'petugasReviewer2',
            'petugasEditor3', 'petugasAuthor2', 'petugasProduction'
        ])->latest()->first();

        // Marketing pages and their descriptions
        $marketingPages = [
            ['name' => 'Dashboard', 'route' => 'marketing.dashboard', 'icon' => 'bi-speedometer2', 'description' => 'Ringkasan submissions, statistik, progress terbaru', 'components' => ['submission-status', 'submission-progress']],
            ['name' => 'Daftar Submissions', 'route' => 'marketing.submissions.index', 'icon' => 'bi-file-earmark-text', 'description' => 'List semua submission marketing + status & progress', 'components' => ['submission-status', 'submission-progress']],
            ['name' => 'Monitoring Artikel', 'route' => 'marketing.submissions.monitoring', 'icon' => 'bi-bar-chart-line', 'description' => 'Monitoring status & progress semua artikel', 'components' => ['submission-status', 'submission-progress']],
            ['name' => 'Detail Submission', 'route' => 'marketing.submissions.index', 'icon' => 'bi-eye', 'description' => 'Detail submission + Tracking Proses Review + Catatan', 'components' => ['tracking-table']],
            ['name' => 'Daftar Jurnal', 'route' => 'marketing.journals.index', 'icon' => 'bi-journal-text', 'description' => 'List jurnal + kode slot yang bisa diklik', 'components' => ['slot-link']],
            ['name' => 'Slot Jurnal', 'route' => 'marketing.journal-slots.index', 'icon' => 'bi-calendar3', 'description' => 'List slot jurnal + kode slot clickable', 'components' => ['slot-link']],
            ['name' => 'Detail Slot', 'route' => 'marketing.journal-slots.index', 'icon' => 'bi-calendar-check', 'description' => 'Detail slot + submissions di slot tersebut', 'components' => ['submission-status', 'submission-progress']],
        ];

        // PIC pages and their descriptions
        $picPages = [
            ['name' => 'Dashboard', 'route' => '#', 'icon' => 'bi-speedometer2', 'description' => 'Dashboard PIC dengan task overview', 'components' => []],
            ['name' => 'My Tasks', 'route' => '#', 'icon' => 'bi-clipboard-check', 'description' => 'Daftar tugas PIC + progress workflow', 'components' => []],
            ['name' => 'Slot Jurnal', 'route' => '#', 'icon' => 'bi-calendar3', 'description' => 'Kelola slot jurnal + kode slot clickable', 'components' => ['slot-link']],
            ['name' => 'Slot Jurnal (Baru)', 'route' => '#', 'icon' => 'bi-calendar3', 'description' => 'Tampilan baru slot jurnal', 'components' => ['slot-link']],
            ['name' => 'Monitoring Slot', 'route' => '#', 'icon' => 'bi-graph-up', 'description' => 'Monitoring penggunaan slot jurnal', 'components' => ['slot-link']],
            ['name' => 'Detail Slot', 'route' => '#', 'icon' => 'bi-calendar-check', 'description' => 'Detail slot + semua submissions', 'components' => ['submission-status', 'submission-progress']],
            ['name' => 'Fasttrack', 'route' => '#', 'icon' => 'bi-lightning-charge', 'description' => 'Input dan monitoring fasttrack submissions', 'components' => []],
        ];

        // Shared components list
        $sharedComponents = [
            [
                'name' => 'submission-status',
                'file' => 'components/submission-status.blade.php',
                'description' => 'Badge status submission (Published, In Progress, Pending, dll)',
                'usage' => '<x-submission-status :submission="$submission" size="small" />',
                'usedIn' => ['marketing/dashboard', 'marketing/submissions', 'marketing/submissions-monitoring', 'marketing/journal-slots/show'],
            ],
            [
                'name' => 'submission-progress',
                'file' => 'components/submission-progress.blade.php',
                'description' => 'Progress bar submission dengan persentase',
                'usage' => '<x-submission-progress :submission="$submission" :height="8" />',
                'usedIn' => ['marketing/dashboard', 'marketing/submissions', 'marketing/submissions-monitoring', 'marketing/journal-slots/show'],
            ],
            [
                'name' => 'tracking-table',
                'file' => 'components/tracking-table.blade.php',
                'description' => 'Tabel lengkap tracking proses review (9 tahap) dengan petugas, credentials, dan status',
                'usage' => '<x-tracking-table :submission="$submission" />',
                'usedIn' => ['marketing/show-submission'],
            ],
            [
                'name' => 'slot-link',
                'file' => 'components/slot-link.blade.php',
                'description' => 'Link kode slot yang bisa diklik ke halaman detail slot',
                'usage' => '<x-slot-link :slot="$slot" guard="marketing" />',
                'usedIn' => ['marketing/journal-slots/index', 'marketing/journals/index', 'pic/journal-slots/index', 'pic/journal-slots/index-new', 'pic/journal-slots/monitoring'],
            ],
        ];

        return view('admin.component-overview', compact(
            'sampleSubmission',
            'marketingPages',
            'picPages',
            'sharedComponents'
        ));
    }
}

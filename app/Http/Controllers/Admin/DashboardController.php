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
     * Component Overview - Admin visual editor for shared components.
     */
    public function componentOverview()
    {
        $sampleSubmission = Submission::with([
            'journalSlot.journalMaster',
            'marketing',
            'petugasSubmit', 'petugasEditor1', 'petugasAuthor1',
            'petugasEditor2', 'petugasReviewer1', 'petugasReviewer2',
            'petugasEditor3', 'petugasAuthor2', 'petugasProduction'
        ])->latest()->first();

        $settings = \App\Services\ComponentSettingService::all();
        $colorOptions = \App\Services\ComponentSettingService::colorOptions();
        $rowColorOptions = \App\Services\ComponentSettingService::rowColorOptions();
        $statuses = \App\Services\ComponentSettingService::statuses();
        $trackingSteps = \App\Services\ComponentSettingService::trackingSteps();

        // Complete Marketing menu/function mapping
        $marketingMenus = [
            [
                'group' => 'Utama',
                'items' => [
                    ['name' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'marketing.dashboard', 'type' => 'GET', 'description' => 'Ringkasan statistik, progress submission terbaru', 'components' => ['submission-status', 'submission-progress']],
                    ['name' => 'Artikel (Submissions)', 'icon' => 'bi-file-earmark-text', 'route' => 'marketing.submissions', 'type' => 'GET', 'description' => 'Daftar semua submission, status & progress', 'components' => ['submission-status', 'submission-progress']],
                    ['name' => 'Buat Artikel Baru', 'icon' => 'bi-plus-circle', 'route' => 'marketing.submissions.create', 'type' => 'GET/POST', 'description' => 'Form input submission baru', 'components' => []],
                    ['name' => 'Detail Submission', 'icon' => 'bi-eye', 'route' => 'marketing.submissions.show', 'type' => 'GET', 'description' => 'Detail submission + tracking proses review + catatan', 'components' => ['tracking-table']],
                    ['name' => 'Monitoring Artikel', 'icon' => 'bi-bar-chart-line', 'route' => 'marketing.submissions.monitoring', 'type' => 'GET', 'description' => 'Monitoring status & progress semua artikel', 'components' => ['submission-status', 'submission-progress']],
                ],
            ],
            [
                'group' => 'Fasttrack',
                'items' => [
                    ['name' => 'Fasttrack', 'icon' => 'bi-lightning-charge', 'route' => 'marketing.fasttrack.index', 'type' => 'GET', 'description' => 'Daftar submission fasttrack', 'components' => []],
                    ['name' => 'Buat Fasttrack Baru', 'icon' => 'bi-plus-circle', 'route' => 'marketing.fasttrack.create', 'type' => 'GET/POST', 'description' => 'Form input fasttrack baru', 'components' => []],
                    ['name' => 'Monitoring Fasttrack', 'icon' => 'bi-bar-chart', 'route' => 'marketing.fasttrack.monitoring', 'type' => 'GET', 'description' => 'Monitoring status fasttrack submissions', 'components' => []],
                    ['name' => 'Detail Fasttrack', 'icon' => 'bi-eye', 'route' => 'marketing.fasttrack.show', 'type' => 'GET', 'description' => 'Detail submission fasttrack', 'components' => []],
                ],
            ],
            [
                'group' => 'Pengelolaan Jurnal',
                'items' => [
                    ['name' => 'Data Jurnal', 'icon' => 'bi-journal-text', 'route' => 'marketing.journals.index', 'type' => 'GET', 'description' => 'List jurnal master + kode slot (read-only)', 'components' => ['slot-link']],
                    ['name' => 'Data Slot', 'icon' => 'bi-calendar3', 'route' => 'marketing.journal-slots.index', 'type' => 'GET', 'description' => 'List slot jurnal + kode slot (read-only)', 'components' => ['slot-link']],
                    ['name' => 'Detail Slot', 'icon' => 'bi-calendar-check', 'route' => 'marketing.journal-slots.show', 'type' => 'GET', 'description' => 'Detail slot + submissions di slot tersebut', 'components' => ['submission-status', 'submission-progress']],
                ],
            ],
            [
                'group' => 'Lainnya',
                'items' => [
                    ['name' => 'Point Saya', 'icon' => 'bi-trophy', 'route' => 'marketing.points', 'type' => 'GET', 'description' => 'Riwayat point marketing', 'components' => []],
                    ['name' => 'Laporan Jurnal', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'marketing.reports.journal-articles', 'type' => 'GET', 'description' => 'Laporan statistik jurnal', 'components' => []],
                    ['name' => 'Profile Saya', 'icon' => 'bi-person-circle', 'route' => 'marketing.profile.edit', 'type' => 'GET/POST', 'description' => 'Edit profil dan ubah password', 'components' => []],
                ],
            ],
        ];

        // Complete PIC menu/function mapping
        $picMenus = [
            [
                'group' => 'Utama',
                'items' => [
                    ['name' => 'Dashboard', 'icon' => 'bi-house-door', 'route' => 'pic.dashboard', 'type' => 'GET', 'description' => 'Dashboard PIC overview', 'components' => []],
                    ['name' => 'Author Dashboard', 'icon' => 'bi-person-workspace', 'route' => 'pic.author.dashboard', 'type' => 'GET', 'description' => 'Dashboard PIC sebagai Author', 'components' => []],
                ],
            ],
            [
                'group' => 'Submissions & Monitoring',
                'items' => [
                    ['name' => 'Data Submissions', 'icon' => 'bi-file-earmark-text', 'route' => 'pic.submissions.index', 'type' => 'GET', 'description' => 'List semua submission yang ditangani', 'components' => []],
                    ['name' => 'Buat Submission', 'icon' => 'bi-plus-circle', 'route' => 'pic.submissions.create', 'type' => 'GET/POST', 'description' => 'Form input submission baru', 'components' => []],
                    ['name' => 'Monitoring & Tugas Saya', 'icon' => 'bi-list-check', 'route' => 'pic.submissions.monitoring', 'type' => 'GET', 'description' => 'Monitoring submission + daftar tugas PIC', 'components' => []],
                    ['name' => 'My Tasks', 'icon' => 'bi-clipboard-check', 'route' => 'pic.my-tasks.index', 'type' => 'GET', 'description' => 'Daftar tugas yang ditugaskan ke PIC', 'components' => []],
                    ['name' => 'Proses Submission', 'icon' => 'bi-gear', 'route' => 'pic.submissions.process', 'type' => 'GET/POST', 'description' => 'Proses/kerjakan submission (submit work, revision)', 'components' => []],
                    ['name' => 'Toggle Validasi', 'icon' => 'bi-check2-square', 'route' => 'pic.submissions.toggle-validation', 'type' => 'POST', 'description' => 'Validasi/toggle status tahap review', 'components' => []],
                    ['name' => 'Update Credential', 'icon' => 'bi-key', 'route' => 'pic.submissions.update-credential', 'type' => 'POST', 'description' => 'Update credential (username/password) submission', 'components' => []],
                    ['name' => 'Update Petugas', 'icon' => 'bi-person-plus', 'route' => 'pic.submissions.update-petugas', 'type' => 'POST', 'description' => 'Assign petugas ke tahap review', 'components' => []],
                ],
            ],
            [
                'group' => 'Pengelolaan Jurnal',
                'items' => [
                    ['name' => 'Data Jurnal (CRUD)', 'icon' => 'bi-journal-text', 'route' => 'pic.journals.index', 'type' => 'GET/POST/PUT/DELETE', 'description' => 'Kelola jurnal master (buat, edit, hapus)', 'components' => []],
                    ['name' => 'Data Slot (CRUD)', 'icon' => 'bi-calendar3', 'route' => 'pic.journal-slots.index', 'type' => 'GET/POST/PUT/DELETE', 'description' => 'Kelola slot jurnal (buat, edit, hapus)', 'components' => ['slot-link']],
                    ['name' => 'Monitoring Slot', 'icon' => 'bi-graph-up', 'route' => 'pic.journal-slots.monitoring', 'type' => 'GET', 'description' => 'Monitoring penggunaan slot jurnal', 'components' => ['slot-link']],
                    ['name' => 'Detail Slot', 'icon' => 'bi-calendar-check', 'route' => 'pic.journal-slots.show', 'type' => 'GET', 'description' => 'Detail slot + semua submissions di slot', 'components' => ['submission-status', 'submission-progress']],
                    ['name' => 'Akreditasi (Read)', 'icon' => 'bi-award', 'route' => 'pic.accreditations.index', 'type' => 'GET', 'description' => 'Lihat daftar akreditasi', 'components' => []],
                ],
            ],
            [
                'group' => 'Fasttrack',
                'items' => [
                    ['name' => 'Data Fasttrack', 'icon' => 'bi-lightning-charge', 'route' => 'pic.fasttrack.index', 'type' => 'GET', 'description' => 'List semua submission fasttrack', 'components' => []],
                    ['name' => 'Buat Fasttrack', 'icon' => 'bi-plus-circle', 'route' => 'pic.fasttrack.create', 'type' => 'GET/POST', 'description' => 'Input submission fasttrack baru', 'components' => []],
                    ['name' => 'Edit Fasttrack', 'icon' => 'bi-pencil-square', 'route' => 'pic.fasttrack.edit', 'type' => 'GET/PUT', 'description' => 'Edit data submission fasttrack', 'components' => []],
                    ['name' => 'Monitoring Fasttrack', 'icon' => 'bi-graph-up', 'route' => 'pic.fasttrack.monitoring', 'type' => 'GET', 'description' => 'Monitoring status fasttrack + validasi', 'components' => []],
                    ['name' => 'Update Assignment FT', 'icon' => 'bi-person-plus', 'route' => 'pic.fasttrack.update-assignment', 'type' => 'POST', 'description' => 'Assign petugas fasttrack', 'components' => []],
                ],
            ],
            [
                'group' => 'Lainnya',
                'items' => [
                    ['name' => 'Daftar Reviewer', 'icon' => 'bi-people', 'route' => 'pic.reviewers.index', 'type' => 'GET', 'description' => 'List reviewer + login as reviewer', 'components' => []],
                    ['name' => 'Point Saya', 'icon' => 'bi-trophy-fill', 'route' => 'pic.points.index', 'type' => 'GET', 'description' => 'Riwayat point PIC', 'components' => []],
                    ['name' => 'Laporan Jurnal', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'pic.reports.journal-articles', 'type' => 'GET', 'description' => 'Laporan statistik jurnal', 'components' => []],
                    ['name' => 'Profile Saya', 'icon' => 'bi-person-circle', 'route' => 'pic.profile.edit', 'type' => 'GET/POST', 'description' => 'Edit profil dan ubah password', 'components' => []],
                ],
            ],
        ];

        return view('admin.component-overview', compact(
            'sampleSubmission',
            'settings',
            'colorOptions',
            'rowColorOptions',
            'statuses',
            'trackingSteps',
            'marketingMenus',
            'picMenus'
        ));
    }

    /**
     * Save component visual settings.
     */
    public function saveComponentSettings(Request $request)
    {
        \App\Services\ComponentSettingService::save($request->except('_token'));
        
        return redirect()->route('admin.component-overview')
            ->with('success', 'Pengaturan komponen berhasil disimpan! Perubahan langsung berlaku di semua halaman.');
    }

    /**
     * Reset component settings to defaults.
     */
    public function resetComponentSettings()
    {
        \App\Services\ComponentSettingService::resetToDefaults();
        
        return redirect()->route('admin.component-overview')
            ->with('success', 'Semua pengaturan komponen telah dikembalikan ke default.');
    }

    /**
     * Feature Management - Admin page for feature toggles, limits, roles, system info.
     */
    public function featureManagement()
    {
        $featureSettings = \App\Services\FeatureSettingService::all();
        $groupedFeatures = \App\Services\FeatureSettingService::groupedFeatures();
        $limitMeta = \App\Services\FeatureSettingService::limitMeta();
        $roleDefinitions = \App\Services\FeatureSettingService::roleDefinitions();
        $capabilityDefs = \App\Services\FeatureSettingService::capabilityDefinitions();
        $capabilityOptions = \App\Services\FeatureSettingService::capabilityOptions();
        $systemInfo = \App\Services\FeatureSettingService::systemInfo();
        $changelogs = \App\Services\FeatureSettingService::changelogs();

        return view('admin.feature-management', compact(
            'featureSettings',
            'groupedFeatures',
            'limitMeta',
            'roleDefinitions',
            'capabilityDefs',
            'capabilityOptions',
            'systemInfo',
            'changelogs'
        ));
    }

    /**
     * Save feature settings.
     */
    public function saveFeatureSettings(Request $request)
    {
        $data = $request->except('_token');

        // Convert checkboxes: present = '1', absent = '0'
        $featureMeta = \App\Services\FeatureSettingService::featureMeta();
        foreach ($featureMeta as $key => $_) {
            $data[$key] = $request->has($key) ? '1' : '0';
        }
        // Maintenance mode checkbox
        $data['maintenance_mode'] = $request->has('maintenance_mode') ? '1' : '0';

        \App\Services\FeatureSettingService::save($data);
        
        return redirect()->route('admin.feature-management')
            ->with('success', 'Pengaturan fitur berhasil disimpan!');
    }

    /**
     * Reset feature settings to defaults.
     */
    public function resetFeatureSettings()
    {
        \App\Services\FeatureSettingService::resetToDefaults();
        
        return redirect()->route('admin.feature-management')
            ->with('success', 'Semua pengaturan fitur telah dikembalikan ke default.');
    }
}

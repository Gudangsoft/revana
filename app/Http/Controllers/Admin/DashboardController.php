<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BirthdayWish;
use App\Models\JournalMaster;
use App\Models\Submission;
use App\Models\User;
use App\Models\ReviewRequest;
use App\Models\Pic;
use App\Models\Marketing;
use Illuminate\Support\Facades\Cache;
use App\Exports\CompletedReviewsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantKey = app()->bound('tenant') ? app('tenant')->subdomain : 'master';

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
        $regularSubmissions   = Submission::where(function ($q) {
            $q->where('process_type', 'normal')->orWhereNull('process_type');
        })->count();
        $fasttrackSubmissions = Submission::where('process_type', 'fasttrack')->count();

        // BKD & JAFA stats
        $bkdStats = [
            'total'     => Submission::where('program_type', 'bkd')->count(),
            'pending'   => Submission::where('program_type', 'bkd')->where('status', 'SUBMITTED')->count(),
            'proses'    => Submission::where('program_type', 'bkd')->whereNotIn('status', ['SUBMITTED','PUBLISHED','REJECTED'])->count(),
            'published' => Submission::where('program_type', 'bkd')->where('status', 'PUBLISHED')->count(),
            'rejected'  => Submission::where('program_type', 'bkd')->where('status', 'REJECTED')->count(),
        ];
        $jafaStats = [
            'total'     => Submission::where('program_type', 'jafa')->count(),
            'pending'   => Submission::where('program_type', 'jafa')->where('status', 'SUBMITTED')->count(),
            'proses'    => Submission::where('program_type', 'jafa')->whereNotIn('status', ['SUBMITTED','PUBLISHED','REJECTED'])->count(),
            'published' => Submission::where('program_type', 'jafa')->where('status', 'PUBLISHED')->count(),
            'rejected'  => Submission::where('program_type', 'jafa')->where('status', 'REJECTED')->count(),
        ];

        // Journal statistics
        $journalsByAccreditation = JournalMaster::selectRaw('accreditation, COUNT(*) as count')
            ->groupBy('accreditation')
            ->orderBy('count', 'desc')
            ->get();

        // Recent submissions — newest first, include relations
        $recentSubmissions = Submission::with(['journalSlot.journalMaster', 'marketing', 'petugasSubmit'])
            ->orderBy('tanggal_submit', 'desc')
            ->orderBy('id', 'desc')
            ->take(15)
            ->get();

        // Completed submissions (approved) — newest approved first
        $completedReviews = Submission::with(['journalSlot.journalMaster', 'marketing'])
            ->where('status', 'PUBLISHED')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $totalCompletedReviews = $approvedSubmissions;
        
        // Monthly statistics
        $monthlySubmissions = Submission::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Monthly breakdown per status for chart (cache 5 menit per tahun)
        $chartData = Cache::remember("dashboard.monthlyStats.{$tenantKey}." . date('Y'), 300, function () {
            $months = range(1, 12);
            $totals    = array_fill_keys($months, 0);
            $published = array_fill_keys($months, 0);
            $rejected  = array_fill_keys($months, 0);

            foreach (Submission::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))->groupBy('month')->get() as $row) {
                $totals[(int)$row->month] = (int)$row->count;
            }
            foreach (Submission::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))->where('status', 'PUBLISHED')
                ->groupBy('month')->get() as $row) {
                $published[(int)$row->month] = (int)$row->count;
            }
            foreach (Submission::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))->where('status', 'REJECTED')
                ->groupBy('month')->get() as $row) {
                $rejected[(int)$row->month] = (int)$row->count;
            }
            return compact('totals', 'published', 'rejected');
        });

        $chartLabels    = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $chartTotals    = array_values($chartData['totals']);
        $chartPublished = array_values($chartData['published']);
        $chartRejected  = array_values($chartData['rejected']);

        // PIC Point Rankings - Top 10 PIC dengan point tertinggi (cache 5 menit)
        try {
            $topPics = Cache::remember("rankings.topPics.{$tenantKey}", 300, fn () =>
                Pic::where('is_active', true)->orderBy('total_points', 'desc')->take(10)->get()
            );
        } catch (\Throwable) {
            $topPics = collect();
        }

        // Marketing Point Rankings - Top 10 Marketing dengan point tertinggi (cache 5 menit)
        try {
            $topMarketings = Cache::remember("rankings.topMarketings.{$tenantKey}", 300, fn () =>
                Marketing::where('is_active', true)->orderBy('total_points', 'desc')->take(10)->get()
            );
        } catch (\Throwable) {
            $topMarketings = collect();
        }

        // Analytics: top 5 reviewer by total_points (cache 5 menit)
        try {
            $topReviewers = Cache::remember("analytics.topReviewers.{$tenantKey}", 300, fn () =>
                User::where('role', 'reviewer')
                    ->where('total_points', '>', 0)
                    ->orderBy('total_points', 'desc')
                    ->take(5)
                    ->get(['id', 'name', 'total_points'])
            );
        } catch (\Throwable) {
            $topReviewers = collect();
        }

        // Analytics: rata-rata hari penyelesaian submission (PUBLISHED)
        try {
            $avgCompletionDays = Cache::remember("analytics.avgCompletion.{$tenantKey}", 300, fn () =>
                (int) round(
                    Submission::where('status', 'PUBLISHED')
                        ->whereNotNull('tanggal_submit')
                        ->selectRaw('AVG(DATEDIFF(updated_at, tanggal_submit)) as avg_days')
                        ->value('avg_days') ?? 0
                )
            );
        } catch (\Throwable) {
            $avgCompletionDays = 0;
        }

        // Birthday widget
        [$todayBirthdays, $myWishes] = $this->todayBirthdayData('admin', auth()->id());

        // Tahun-tahun yang benar-benar punya data submission — dipakai isi dropdown
        // filter tahun di widget "Monitoring Tren Submission" (submissionTrend()).
        $submissionYears = Cache::remember("dashboard.submissionYears.{$tenantKey}", 300, fn () =>
            Submission::selectRaw('DISTINCT YEAR(created_at) as y')
                ->orderByDesc('y')->pluck('y')->toArray()
        );
        if (empty($submissionYears)) {
            $submissionYears = [(int) date('Y')];
        }

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
            'monthlySubmissions',
            'topPics',
            'topMarketings',
            'chartLabels',
            'chartTotals',
            'chartPublished',
            'chartRejected',
            'bkdStats',
            'jafaStats',
            'topReviewers',
            'avgCompletionDays',
            'todayBirthdays',
            'myWishes',
            'submissionYears'
        ) + ['trendCategoryOptions' => self::$trendCategoryOptions]);
    }

    /** Label kategori dipakai bareng widget dashboard & filter submissionTrend(). */
    public static array $trendCategoryOptions = [
        'semua'     => 'Semua',
        'normal'    => 'Normal',
        'fasttrack' => 'Fasttrack',
        'bkd'       => 'BKD',
        'jafa'      => 'JAFA',
    ];

    /**
     * Ekspresi SQL conditional-SUM per kategori — SATU sumber kebenaran dipakai
     * submissionTrend() supaya definisinya konsisten dengan "Regular/Fasttrack"
     * dan "BKD/JAFA" yang sudah dipakai stat card dashboard (index() di atas:
     * $regularSubmissions, dst). Dihitung SEKALIGUS utk semua kategori dalam
     * satu query (bukan query terpisah per kategori) supaya tooltip chart bisa
     * menampilkan rincian semua kategori tanpa peduli kategori mana yang
     * sedang dipilih di dropdown.
     */
    private const TREND_CATEGORY_SQL = [
        'normal'    => "SUM(CASE WHEN (process_type = 'normal' OR process_type IS NULL) THEN 1 ELSE 0 END)",
        'fasttrack' => "SUM(CASE WHEN process_type = 'fasttrack' THEN 1 ELSE 0 END)",
        'bkd'       => "SUM(CASE WHEN program_type = 'bkd' THEN 1 ELSE 0 END)",
        'jafa'      => "SUM(CASE WHEN program_type = 'jafa' THEN 1 ELSE 0 END)",
    ];

    /**
     * Widget "Monitoring Tren Submission" di dashboard — bar chart total
     * submission sesuai kategori terpilih (Semua/Normal/Fasttrack/BKD/JAFA),
     * dgn filter granularitas per tahun/bulan/hari. Response JUGA menyertakan
     * `breakdown` (rincian semua kategori per titik data) supaya tooltip bisa
     * menampilkan "Normal: X, Fasttrack: Y, BKD: Z, JAFA: W" sekaligus, apa pun
     * kategori yang sedang aktif di dropdown. Dipanggil via fetch() dari
     * dashboard.
     */
    public function submissionTrend(Request $request)
    {
        $period = in_array($request->get('period'), ['year', 'month', 'day'], true)
            ? $request->get('period') : 'month';
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        $kategori = array_key_exists($request->get('kategori'), self::$trendCategoryOptions)
            ? $request->get('kategori') : 'semua';

        $extraSelect = implode(', ', array_map(
            fn ($key, $expr) => "{$expr} as {$key}",
            array_keys(self::TREND_CATEGORY_SQL), self::TREND_CATEGORY_SQL
        ));

        if ($period === 'year') {
            $bucketExpr = 'YEAR(created_at)';
            $rows = Submission::selectRaw("{$bucketExpr} as bucket, COUNT(*) as total, {$extraSelect}")
                ->groupBy('bucket')->orderBy('bucket')->get()->keyBy('bucket');
            $bucketKeys = $rows->keys()->all();
            $labels = array_map('strval', $bucketKeys);
        } elseif ($period === 'day') {
            $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
            $bucketKeys = range(1, $daysInMonth);
            $labels = array_map('strval', $bucketKeys);
            $rows = Submission::selectRaw("DAY(created_at) as bucket, COUNT(*) as total, {$extraSelect}")
                ->whereYear('created_at', $year)->whereMonth('created_at', $month)
                ->groupBy('bucket')->get()->keyBy('bucket');
        } else {
            $bucketKeys = range(1, 12);
            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $rows = Submission::selectRaw("MONTH(created_at) as bucket, COUNT(*) as total, {$extraSelect}")
                ->whereYear('created_at', $year)
                ->groupBy('bucket')->get()->keyBy('bucket');
        }

        $series = ['semua' => [], 'normal' => [], 'fasttrack' => [], 'bkd' => [], 'jafa' => []];
        foreach ($bucketKeys as $key) {
            $row = $rows->get($key);
            $series['semua'][]     = (int) ($row->total ?? 0);
            $series['normal'][]    = (int) ($row->normal ?? 0);
            $series['fasttrack'][] = (int) ($row->fasttrack ?? 0);
            $series['bkd'][]       = (int) ($row->bkd ?? 0);
            $series['jafa'][]      = (int) ($row->jafa ?? 0);
        }

        $data = $series[$kategori];

        return response()->json([
            'labels' => $labels,
            'data'   => $data,
            'total'  => array_sum($data),
            'kategori_label' => self::$trendCategoryOptions[$kategori],
            'breakdown' => [
                'Normal'    => $series['normal'],
                'Fasttrack' => $series['fasttrack'],
                'BKD'       => $series['bkd'],
                'JAFA'      => $series['jafa'],
            ],
        ]);
    }

    /**
     * Point Rankings Page - Menampilkan peringkat point PIC dan Marketing
     */
    public function pointRankings()
    {
        // PIC Point Rankings - Semua PIC diurutkan berdasarkan point
        $picRankings = Pic::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get()
            ->map(function ($pic, $index) {
                $pic->rank = $index + 1;
                return $pic;
            });

        // Marketing Point Rankings - Semua Marketing diurutkan berdasarkan point
        $marketingRankings = Marketing::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get()
            ->map(function ($marketing, $index) {
                $marketing->rank = $index + 1;
                return $marketing;
            });

        // Statistics
        $totalPicPoints = Pic::where('is_active', true)->sum('total_points');
        $totalMarketingPoints = Marketing::where('is_active', true)->sum('total_points');
        $activePicCount = Pic::where('is_active', true)->count();
        $activeMarketingCount = Marketing::where('is_active', true)->count();

        return view('admin.point-rankings', compact(
            'picRankings',
            'marketingRankings',
            'totalPicPoints',
            'totalMarketingPoints',
            'activePicCount',
            'activeMarketingCount'
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
        // Check scheduled maintenance
        \App\Services\FeatureSettingService::checkScheduledMaintenance();

        $featureSettings = \App\Services\FeatureSettingService::all();
        $groupedFeatures = \App\Services\FeatureSettingService::groupedFeatures();
        $limitMeta = \App\Services\FeatureSettingService::limitMeta();
        $roleDefinitions = \App\Services\FeatureSettingService::roleDefinitions();
        $capabilityDefs = \App\Services\FeatureSettingService::capabilityDefinitions();
        $capabilityOptions = \App\Services\FeatureSettingService::capabilityOptions();
        $systemInfo = \App\Services\FeatureSettingService::systemInfo();
        $changelogs = \App\Services\FeatureSettingService::changelogs();
        $auditLogs = \App\Services\FeatureSettingService::auditLogs(30);
        $envOverrides = \App\Services\FeatureSettingService::envOverrides();

        return view('admin.feature-management', compact(
            'featureSettings',
            'groupedFeatures',
            'limitMeta',
            'roleDefinitions',
            'capabilityDefs',
            'capabilityOptions',
            'systemInfo',
            'changelogs',
            'auditLogs',
            'envOverrides'
        ));
    }

    /**
     * Save feature settings with notification for critical changes.
     */
    public function saveFeatureSettings(Request $request)
    {
        $data = $request->except('_token');
        $oldSettings = \App\Services\FeatureSettingService::all();

        // Convert checkboxes: present = '1', absent = '0'
        $featureMeta = \App\Services\FeatureSettingService::featureMeta();
        foreach ($featureMeta as $key => $_) {
            $data[$key] = $request->has($key) ? '1' : '0';
        }
        // Maintenance mode checkbox
        $data['maintenance_mode'] = $request->has('maintenance_mode') ? '1' : '0';

        \App\Services\FeatureSettingService::save($data);

        // Send notification for critical changes
        $this->notifyCriticalChanges($oldSettings, $data);

        return redirect()->route('admin.feature-management')
            ->with('success', 'Pengaturan fitur berhasil disimpan!');
    }

    /**
     * Reset feature settings to defaults.
     */
    public function resetFeatureSettings()
    {
        \App\Services\FeatureSettingService::resetToDefaults();

        // Notify admins
        $this->sendSettingNotification('reset');

        return redirect()->route('admin.feature-management')
            ->with('success', 'Semua pengaturan fitur telah dikembalikan ke default.');
    }

    /**
     * Export settings as JSON download.
     */
    public function exportFeatureSettings()
    {
        $json = \App\Services\FeatureSettingService::exportAsJson();
        $filename = 'sipera-settings-' . date('Y-m-d_His') . '.json';

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import settings from uploaded JSON file.
     */
    public function importFeatureSettings(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json,txt|max:1024',
        ]);

        try {
            $json = file_get_contents($request->file('settings_file')->getRealPath());
            $changes = \App\Services\FeatureSettingService::importFromJson($json);

            $this->sendSettingNotification('import', $changes);

            return redirect()->route('admin.feature-management')
                ->with('success', 'Berhasil import ' . count($changes) . ' pengaturan dari file JSON.');
        } catch (\Exception $e) {
            return redirect()->route('admin.feature-management')
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Notify admins of critical setting changes.
     */
    private function notifyCriticalChanges(array $oldSettings, array $newData): void
    {
        try {
            $adminName = auth()->user()->name ?? 'Admin';

            // Maintenance mode changed?
            $oldMaint = $oldSettings['maintenance_mode'] ?? '0';
            $newMaint = $newData['maintenance_mode'] ?? '0';
            if ($oldMaint !== $newMaint) {
                $action = $newMaint === '1' ? 'maintenance_on' : 'maintenance_off';
                $this->sendSettingNotification($action);
            }

            // Role capability changes?
            $roleChanges = [];
            foreach ($newData as $key => $value) {
                if (str_starts_with($key, 'role_') && isset($oldSettings[$key]) && $oldSettings[$key] !== $value) {
                    $roleChanges[$key] = ['old' => $oldSettings[$key], 'new' => $value];
                }
            }
            if (!empty($roleChanges)) {
                $this->sendSettingNotification('role_change', $roleChanges);
            }
        } catch (\Exception $e) {
            // Don't fail the save if notification fails
        }
    }

    // ── Birthday ──────────────────────────────────────────────────────────────

    public function storeWish(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:pic,marketing',
            'recipient_id'   => 'required|integer',
            'message'        => 'required|string|max:200',
        ]);

        $recipient = $request->recipient_type === 'pic'
            ? Pic::find($request->recipient_id)
            : Marketing::find($request->recipient_id);

        if (!$recipient) {
            return back()->with('error', 'Penerima tidak ditemukan.');
        }

        BirthdayWish::updateOrCreate(
            [
                'sender_type'    => 'admin',
                'sender_id'      => auth()->id(),
                'recipient_type' => $request->recipient_type,
                'recipient_id'   => $request->recipient_id,
                'wish_year'      => now()->year,
            ],
            [
                'sender_name'    => auth()->user()->name ?? 'Admin',
                'recipient_name' => $recipient->name,
                'message'        => $request->message,
            ]
        );

        return back()->with('wish_sent', 'Ucapan untuk ' . $recipient->name . ' berhasil dikirim! 🎉');
    }

    private function todayBirthdayData(string $senderType, int $senderId): array
    {
        try {
            $month = now()->month;
            $day   = now()->day;

            $pics = Pic::whereNotNull('tanggal_lahir')
                ->whereMonth('tanggal_lahir', $month)
                ->whereDay('tanggal_lahir', $day)
                ->where('is_active', true)
                ->get()
                ->map(fn($p) => (object)['id' => $p->id, 'name' => $p->name, 'type' => 'pic', 'umur' => $p->umur]);

            $mktgs = Marketing::whereNotNull('tanggal_lahir')
                ->whereMonth('tanggal_lahir', $month)
                ->whereDay('tanggal_lahir', $day)
                ->where('is_active', true)
                ->get()
                ->map(fn($m) => (object)['id' => $m->id, 'name' => $m->name, 'type' => 'marketing', 'umur' => $m->umur]);

            $todayBirthdays = $pics->merge($mktgs);

            $myWishes = BirthdayWish::where('sender_type', $senderType)
                ->where('sender_id', $senderId)
                ->where('wish_year', now()->year)
                ->get()
                ->map(fn($w) => $w->recipient_type . '-' . $w->recipient_id)
                ->toArray();

            return [$todayBirthdays, $myWishes];
        } catch (\Throwable) {
            return [collect(), []];
        }
    }

    /**
     * Send notification to all admin users.
     */
    private function sendSettingNotification(string $action, array $changes = []): void
    {
        try {
            if (!\App\Services\FeatureSettingService::isEnabled('email_notifications')) {
                return;
            }

            $adminName = auth()->user()->name ?? 'Admin';
            $admins = \App\Models\User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\SettingChangedNotification($action, $adminName, $changes));
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the flow
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Models\Submission;
use App\Exports\PicsExport;
use App\Exports\TeamPerformanceExport;
use App\Exports\AllTeamPerformanceExport;
use App\Imports\PicsImport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PicController extends Controller
{
    public function index(Request $request)
    {
        $query = Pic::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }
        $pics = $query->orderBy('name')->paginate(request()->input('per_page', 20));

        // Ranking data - Top 10 PIC dengan point tertinggi
        $topPics = Pic::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->take(10)
            ->get();

        return view('admin.pics.index', compact('pics', 'topPics'));
    }

    public function create()
    {
        return view('admin.pics.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        
        // Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        Pic::create($validated);

        return redirect()->route('admin.pics.index')
            ->with('success', 'PIC berhasil ditambahkan');
    }

    public function edit(Pic $pic)
    {
        return view('admin.pics.edit', compact('pic'));
    }

    public function update(Request $request, Pic $pic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        
        // Hash password if provided, otherwise remove from update
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $pic->update($validated);

        return redirect()->route('admin.pics.index')
            ->with('success', 'PIC berhasil diupdate');
    }

    public function destroy(Pic $pic)
    {
        $pic->delete();

        return redirect()->route('admin.pics.index')
            ->with('success', 'PIC berhasil dihapus');
    }

    public function export()
    {
        $filename = 'pics_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new PicsExport, $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new PicsImport;
            Excel::import($import, $request->file('file'));

            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();
            $skipped = $import->getSkippedCount();
            
            $message = "Import berhasil! {$created} PIC baru ditambahkan, {$updated} PIC diupdate.";
            if ($skipped > 0) {
                $message .= " {$skipped} baris dilewati karena data tidak valid (email tidak valid atau nama kosong).";
            }
            
            return redirect()->route('admin.pics.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.pics.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function array(): array
            {
                return [
                    ['John Doe', 'john_doe', 'john@example.com', '081234567890', 'Aktif'],
                    ['Jane Smith', 'jane_smith', 'jane@example.com', '089876543210', 'Nonaktif'],
                ];
            }
            
            public function headings(): array
            {
                return ['Nama', 'Username', 'Email', 'Telepon', 'Status'];
            }
        }, 'template_pics.xlsx');
    }

    /**
     * Login as a PIC (Admin impersonation)
     */
    public function loginAs(Pic $pic)
    {
        if (!$pic->is_active) {
            return redirect()->route('admin.pics.index')
                ->with('error', 'PIC tidak aktif, tidak dapat login sebagai PIC ini.');
        }

        $adminId = Auth::id();

        // Clear any existing PIC session first to prevent stale pic_id
        Auth::guard('pic')->logout();

        // Store admin ID for return-to-admin
        session(['admin_impersonating' => $adminId]);

        // Login as the selected PIC
        Auth::guard('pic')->login($pic);

        return redirect()->route('pic.dashboard')
            ->with('success', 'Sedang melihat sebagai: ' . $pic->name);
    }

    /**
     * Return from PIC impersonation back to admin
     */
    public function returnToAdmin()
    {
        Auth::guard('pic')->logout();
        session()->forget('admin_impersonating');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Kembali ke akun admin.');
    }

    /**
     * Show PIC activity report
     */
    public function activityReport(Request $request)
    {
        // Get all PICs
        $query = Pic::query();
        
        // Filter by PIC
        if ($request->filled('pic_id')) {
            $query->where('id', $request->pic_id);
        }
        
        // Only show active PICs by default
        if (!$request->filled('show_inactive')) {
            $query->where('is_active', true);
        }
        
        $pics = $query->orderBy('total_points', 'desc')->get();
        
        // Calculate filtered points for each PIC
        $pics->each(function($pic) use ($request) {
            $pointQuery = PicPointHistory::where('pic_id', $pic->id);
            
            if ($request->filled('tanggal_dari')) {
                $pointQuery->whereDate('created_at', '>=', $request->tanggal_dari);
            }
            if ($request->filled('tanggal_sampai')) {
                $pointQuery->whereDate('created_at', '<=', $request->tanggal_sampai);
            }
            
            $pic->filtered_points = $pointQuery->sum('points_earned');
            $pic->filtered_tasks = $pointQuery->count();
            
            // Get breakdown by step
            $pic->step_breakdown = PicPointHistory::where('pic_id', $pic->id)
                ->when($request->filled('tanggal_dari'), function($q) use ($request) {
                    $q->whereDate('created_at', '>=', $request->tanggal_dari);
                })
                ->when($request->filled('tanggal_sampai'), function($q) use ($request) {
                    $q->whereDate('created_at', '<=', $request->tanggal_sampai);
                })
                ->select('step', DB::raw('COUNT(*) as count'), DB::raw('SUM(points_earned) as total'))
                ->groupBy('step')
                ->get();
        });
        
        // Overall statistics
        $stats = [
            'total_pics' => $pics->count(),
            'active_pics' => Pic::where('is_active', true)->count(),
            'total_points_given' => $pics->sum('filtered_points'),
            'total_tasks_completed' => $pics->sum('filtered_tasks'),
        ];
        
        // Get all PICs for filter dropdown
        $allPics = Pic::orderBy('name')->get();
        
        return view('admin.pics.activity-report', compact('pics', 'stats', 'allPics'));
    }

    /**
     * Reset individual PIC password to default
     */
    public function resetPassword(Pic $pic)
    {
        $defaultPassword = 'pic123';
        $pic->password = bcrypt($defaultPassword);
        $pic->save();

        return redirect()->route('admin.pics.index')
            ->with('success', "Password untuk {$pic->name} telah direset ke default: {$defaultPassword}");
    }

    /**
     * Reset all PICs password to default
     */
    public function resetAllPasswords()
    {
        $defaultPassword = bcrypt('pic123');
        
        // Use single query for better performance
        $count = DB::table('pics')->update([
            'password' => $defaultPassword,
            'updated_at' => now()
        ]);
        
        return redirect()->route('admin.pics.index')
            ->with('success', "Berhasil! Password {$count} PIC telah direset ke default: pic123");
    }

    /**
     * Report - Tim Terbanyak Submit
     */
    public function teamSubmitReport(Request $request)
    {
        $query = Submission::query();
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        
        // Get PIC submissions with count
        $picSubmits = $query->clone()
            ->select('petugas_submit_id', DB::raw('COUNT(*) as total_submit'))
            ->whereNotNull('petugas_submit_id')
            ->groupBy('petugas_submit_id')
            ->orderByDesc('total_submit')
            ->get();
        
        // Add rank and get PIC names
        $picSubmits = $picSubmits->map(function ($item, $index) {
            $pic = Pic::find($item->petugas_submit_id);
            $item->rank = $index + 1;
            $item->pic_name = $pic ? $pic->name : 'Unknown';
            $item->pic = $pic;
            return $item;
        });
        
        // Statistics
        $stats = [
            'total_submissions' => $query->count(),
            'total_pic_submit' => $picSubmits->count(),
            'top_submitter' => $picSubmits->first(),
        ];
        
        $generatedAt = now()->format('d M Y H:i');
        $filterInfo = $this->getFilterInfo($request);
        
        // Export PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.team-submit-pdf', compact('picSubmits', 'stats', 'generatedAt', 'filterInfo'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('Laporan_Tim_Submit_Terbanyak_' . now()->format('Y-m-d') . '.pdf');
        }
        
        return view('admin.reports.team-submit', compact('picSubmits', 'stats'));
    }

    /**
     * Report - Tim Terbanyak Reviewer
     */
    public function teamReviewerReport(Request $request)
    {
        $reviewerStats = collect();
        
        // Get all PICs who have reviewer tasks
        $query1 = Submission::query();
        $query2 = Submission::query();
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query1->whereDate('reviewer1_validated_at', '>=', $request->tanggal_dari);
            $query2->whereDate('reviewer2_validated_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query1->whereDate('reviewer1_validated_at', '<=', $request->tanggal_sampai);
            $query2->whereDate('reviewer2_validated_at', '<=', $request->tanggal_sampai);
        }
        
        // Reviewer 1 counts (validated)
        $reviewer1Counts = $query1->clone()
            ->select('petugas_reviewer1_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('petugas_reviewer1_id')
            ->where('reviewer1_valid', true)
            ->groupBy('petugas_reviewer1_id')
            ->get()
            ->pluck('count', 'petugas_reviewer1_id');
        
        // Reviewer 2 counts (validated)
        $reviewer2Counts = $query2->clone()
            ->select('petugas_reviewer2_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('petugas_reviewer2_id')
            ->where('reviewer2_valid', true)
            ->groupBy('petugas_reviewer2_id')
            ->get()
            ->pluck('count', 'petugas_reviewer2_id');
        
        // Merge all reviewer PICs
        $allPicIds = $reviewer1Counts->keys()->merge($reviewer2Counts->keys())->unique();
        
        foreach ($allPicIds as $picId) {
            $pic = Pic::find($picId);
            if ($pic) {
                $reviewerStats->push((object)[
                    'pic_id' => $picId,
                    'pic_name' => $pic->name,
                    'pic' => $pic,
                    'reviewer1_count' => $reviewer1Counts->get($picId, 0),
                    'reviewer2_count' => $reviewer2Counts->get($picId, 0),
                    'total_review' => ($reviewer1Counts->get($picId, 0) + $reviewer2Counts->get($picId, 0)),
                ]);
            }
        }
        
        // Sort by total review desc and add rank
        $reviewerStats = $reviewerStats->sortByDesc('total_review')->values();
        $reviewerStats = $reviewerStats->map(function ($item, $index) {
            $item->rank = $index + 1;
            return $item;
        });
        
        // Statistics
        $stats = [
            'total_reviews' => $reviewerStats->sum('total_review'),
            'total_reviewers' => $reviewerStats->count(),
            'top_reviewer' => $reviewerStats->first(),
        ];
        
        $generatedAt = now()->format('d M Y H:i');
        $filterInfo = $this->getFilterInfo($request);
        
        // Export PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.team-reviewer-pdf', compact('reviewerStats', 'stats', 'generatedAt', 'filterInfo'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('Laporan_Tim_Reviewer_Terbanyak_' . now()->format('Y-m-d') . '.pdf');
        }
        
        return view('admin.reports.team-reviewer', compact('reviewerStats', 'stats'));
    }

    /**
     * Report - Tim Terbanyak Marketing
     */
    public function teamMarketingReport(Request $request)
    {
        $query = Submission::query();
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        
        // Get Marketing submissions with count
        $marketingSubmits = $query->clone()
            ->select('marketing_id', DB::raw('COUNT(*) as total_submit'))
            ->whereNotNull('marketing_id')
            ->groupBy('marketing_id')
            ->orderByDesc('total_submit')
            ->get();
        
        // Add rank and get Marketing names
        $marketingSubmits = $marketingSubmits->map(function ($item, $index) {
            $marketing = \App\Models\Marketing::find($item->marketing_id);
            $item->rank = $index + 1;
            $item->marketing_name = $marketing ? $marketing->name : 'Unknown';
            $item->marketing = $marketing;
            return $item;
        });
        
        // Statistics
        $stats = [
            'total_submissions' => $query->count(),
            'total_marketing' => $marketingSubmits->count(),
            'top_marketing' => $marketingSubmits->first(),
        ];
        
        $generatedAt = now()->format('d M Y H:i');
        $filterInfo = $this->getFilterInfo($request);
        
        // Export PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.team-marketing-pdf', compact('marketingSubmits', 'stats', 'generatedAt', 'filterInfo'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('Laporan_Tim_Marketing_Terbanyak_' . now()->format('Y-m-d') . '.pdf');
        }
        
        return view('admin.reports.team-marketing', compact('marketingSubmits', 'stats'));
    }

    /**
     * Report - Tim Terbanyak Editor 1
     */
    public function teamEditor1Report(Request $request)
    {
        $query = Submission::query();
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('editor1_validated_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('editor1_validated_at', '<=', $request->tanggal_sampai);
        }
        
        // Get PIC Editor 1 with count (validated only)
        $picEditor1s = $query->clone()
            ->select('petugas_editor1_id', DB::raw('COUNT(*) as total_task'))
            ->whereNotNull('petugas_editor1_id')
            ->where('editor1_valid', true)
            ->groupBy('petugas_editor1_id')
            ->orderByDesc('total_task')
            ->get();
        
        // Add rank and get PIC names
        $picEditor1s = $picEditor1s->map(function ($item, $index) {
            $pic = Pic::find($item->petugas_editor1_id);
            $item->rank = $index + 1;
            $item->pic_name = $pic ? $pic->name : 'Unknown';
            $item->pic = $pic;
            return $item;
        });
        
        // Statistics
        $stats = [
            'total_tasks' => $picEditor1s->sum('total_task'),
            'total_pic' => $picEditor1s->count(),
            'top_pic' => $picEditor1s->first(),
        ];
        
        $generatedAt = now()->format('d M Y H:i');
        $filterInfo = $this->getFilterInfo($request);
        
        // Export PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.team-editor1-pdf', compact('picEditor1s', 'stats', 'generatedAt', 'filterInfo'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('Laporan_Tim_Editor1_Terbanyak_' . now()->format('Y-m-d') . '.pdf');
        }
        
        return view('admin.reports.team-editor1', compact('picEditor1s', 'stats'));
    }

    /**
     * Report - Tim Terbanyak Author 1
     */
    public function teamAuthor1Report(Request $request)
    {
        $query = Submission::query();
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('author1_validated_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('author1_validated_at', '<=', $request->tanggal_sampai);
        }
        
        // Get PIC Author 1 with count (validated only)
        $picAuthor1s = $query->clone()
            ->select('petugas_author1_id', DB::raw('COUNT(*) as total_task'))
            ->whereNotNull('petugas_author1_id')
            ->where('author1_valid', true)
            ->groupBy('petugas_author1_id')
            ->orderByDesc('total_task')
            ->get();
        
        // Add rank and get PIC names
        $picAuthor1s = $picAuthor1s->map(function ($item, $index) {
            $pic = Pic::find($item->petugas_author1_id);
            $item->rank = $index + 1;
            $item->pic_name = $pic ? $pic->name : 'Unknown';
            $item->pic = $pic;
            return $item;
        });
        
        // Statistics
        $stats = [
            'total_tasks' => $picAuthor1s->sum('total_task'),
            'total_pic' => $picAuthor1s->count(),
            'top_pic' => $picAuthor1s->first(),
        ];
        
        $generatedAt = now()->format('d M Y H:i');
        $filterInfo = $this->getFilterInfo($request);
        
        // Export PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.team-author1-pdf', compact('picAuthor1s', 'stats', 'generatedAt', 'filterInfo'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('Laporan_Tim_Author1_Terbanyak_' . now()->format('Y-m-d') . '.pdf');
        }
        
        return view('admin.reports.team-author1', compact('picAuthor1s', 'stats'));
    }

    /**
     * Report - Tim Terbanyak Production
     */
    public function teamProductionReport(Request $request)
    {
        $query = Submission::query();
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('production_validated_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('production_validated_at', '<=', $request->tanggal_sampai);
        }
        
        // Get PIC Production with count (validated only)
        $picProductions = $query->clone()
            ->select('petugas_production_id', DB::raw('COUNT(*) as total_task'))
            ->whereNotNull('petugas_production_id')
            ->where('production_valid', true)
            ->groupBy('petugas_production_id')
            ->orderByDesc('total_task')
            ->get();
        
        // Add rank and get PIC names
        $picProductions = $picProductions->map(function ($item, $index) {
            $pic = Pic::find($item->petugas_production_id);
            $item->rank = $index + 1;
            $item->pic_name = $pic ? $pic->name : 'Unknown';
            $item->pic = $pic;
            return $item;
        });
        
        // Statistics
        $stats = [
            'total_tasks' => $picProductions->sum('total_task'),
            'total_pic' => $picProductions->count(),
            'top_pic' => $picProductions->first(),
        ];
        
        $generatedAt = now()->format('d M Y H:i');
        $filterInfo = $this->getFilterInfo($request);
        
        // Export PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.team-production-pdf', compact('picProductions', 'stats', 'generatedAt', 'filterInfo'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('Laporan_Tim_Production_Terbanyak_' . now()->format('Y-m-d') . '.pdf');
        }
        
        return view('admin.reports.team-production', compact('picProductions', 'stats'));
    }

    /**
     * Unified Team Performance Report - All workflow steps with Normal/Fasttrack filter
     */
    public function teamPerformanceReport(Request $request)
    {
        $step = $request->get('step', 'submit');
        $processType = $request->get('process_type', 'all'); // all, normal, fasttrack
        
        // Define step configurations
        $stepConfigs = [
            'submit' => [
                'title' => 'Submit',
                'field' => 'petugas_submit_id',
                'date_field' => 'created_at',
                'valid_field' => null,
                'color' => 'success',
                'icon' => 'send-fill',
            ],
            'editor1' => [
                'title' => 'Editor 1',
                'field' => 'petugas_editor1_id',
                'date_field' => 'editor1_validated_at',
                'valid_field' => 'editor1_valid',
                'color' => 'primary',
                'icon' => 'pencil-square',
            ],
            'author1' => [
                'title' => 'Author 1',
                'field' => 'petugas_author1_id',
                'date_field' => 'author1_validated_at',
                'valid_field' => 'author1_valid',
                'color' => 'warning',
                'icon' => 'person-fill',
            ],
            'editor2' => [
                'title' => 'Editor 2',
                'field' => 'petugas_editor2_id',
                'date_field' => 'editor2_validated_at',
                'valid_field' => 'editor2_valid',
                'color' => 'info',
                'icon' => 'pencil-square',
            ],
            'reviewer1' => [
                'title' => 'Reviewer 1',
                'field' => 'petugas_reviewer1_id',
                'date_field' => 'reviewer1_validated_at',
                'valid_field' => 'reviewer1_valid',
                'color' => 'secondary',
                'icon' => 'person-check-fill',
            ],
            'reviewer2' => [
                'title' => 'Reviewer 2',
                'field' => 'petugas_reviewer2_id',
                'date_field' => 'reviewer2_validated_at',
                'valid_field' => 'reviewer2_valid',
                'color' => 'dark',
                'icon' => 'person-check-fill',
            ],
            'editor3' => [
                'title' => 'Editor 3',
                'field' => 'petugas_editor3_id',
                'date_field' => 'editor3_validated_at',
                'valid_field' => 'editor3_valid',
                'color' => 'primary',
                'icon' => 'pencil-square',
            ],
            'author2' => [
                'title' => 'Author 2',
                'field' => 'petugas_author2_id',
                'date_field' => 'author2_validated_at',
                'valid_field' => 'author2_valid',
                'color' => 'warning',
                'icon' => 'person-fill',
            ],
            'production' => [
                'title' => 'Production',
                'field' => 'petugas_production_id',
                'date_field' => 'production_validated_at',
                'valid_field' => 'production_valid',
                'color' => 'danger',
                'icon' => 'box-seam-fill',
            ],
            'marketing' => [
                'title' => 'Marketing',
                'field' => 'marketing_id',
                'date_field' => 'created_at',
                'valid_field' => null,
                'color' => 'danger',
                'icon' => 'megaphone-fill',
                'is_marketing' => true,
            ],
        ];
        
        $config = $stepConfigs[$step] ?? $stepConfigs['submit'];
        
        // Build query
        $query = Submission::query();
        
        // Filter process type
        if ($processType === 'normal') {
            $query->where(function($q) {
                $q->where('process_type', 'normal')->orWhereNull('process_type');
            });
        } elseif ($processType === 'fasttrack') {
            $query->where('process_type', 'fasttrack');
        }
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate($config['date_field'], '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate($config['date_field'], '<=', $request->tanggal_sampai);
        }
        
        // Get data with count
        $dataQuery = $query->clone()
            ->select(
                $config['field'], 
                DB::raw('COUNT(*) as total_task'),
                DB::raw('SUM(CASE WHEN status = "PUBLISHED" THEN 1 ELSE 0 END) as completed_task')
            )
            ->whereNotNull($config['field']);
        
        // Add valid filter if applicable
        if ($config['valid_field']) {
            $dataQuery->where($config['valid_field'], true);
        }
        
        $rankings = $dataQuery
            ->groupBy($config['field'])
            ->orderByDesc('total_task')
            ->get();
        
        // Add rank and get PIC/Marketing names
        $isMarketing = $step === 'marketing';
        $rankings = $rankings->map(function ($item, $index) use ($config, $isMarketing) {
            if ($isMarketing) {
                $model = \App\Models\Marketing::find($item->{$config['field']});
                $item->rank = $index + 1;
                $item->pic_name = $model ? $model->name : 'Unknown';
                $item->pic = $model;
            } else {
                $pic = Pic::find($item->{$config['field']});
                $item->rank = $index + 1;
                $item->pic_name = $pic ? $pic->name : 'Unknown';
                $item->pic = $pic;
            }
            return $item;
        });
        
        // Get totals by process type for stats
        $totalAll = Submission::whereNotNull($config['field'])
            ->when($config['valid_field'], fn($q) => $q->where($config['valid_field'], true))
            ->count();
        $totalNormal = Submission::whereNotNull($config['field'])
            ->where(fn($q) => $q->where('process_type', 'normal')->orWhereNull('process_type'))
            ->when($config['valid_field'], fn($q) => $q->where($config['valid_field'], true))
            ->count();
        $totalFasttrack = Submission::whereNotNull($config['field'])
            ->where('process_type', 'fasttrack')
            ->when($config['valid_field'], fn($q) => $q->where($config['valid_field'], true))
            ->count();
        
        // Statistics
        $stats = [
            'total_tasks' => $rankings->sum('total_task'),
            'total_pic' => $rankings->count(),
            'top_pic' => $rankings->first(),
            'total_all' => $totalAll,
            'total_normal' => $totalNormal,
            'total_fasttrack' => $totalFasttrack,
        ];
        
        $generatedAt = now()->format('d M Y H:i');
        $filterInfo = $this->getFilterInfoExtended($request, $step, $processType, $stepConfigs);
        
        // Export PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.team-performance-pdf', compact(
                'rankings', 'stats', 'generatedAt', 'filterInfo', 'config', 'step', 'processType', 'stepConfigs'
            ))->setPaper('a4', 'portrait');
            
            $filename = 'Laporan_Tim_' . $config['title'] . '_' . ucfirst($processType) . '_' . now()->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }
        
        // Export Excel - Single Step
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'Laporan_Tim_' . $config['title'] . '_' . ucfirst($processType) . '_' . now()->format('Y-m-d') . '.xlsx';
            return Excel::download(
                new TeamPerformanceExport($step, $processType, $request->tanggal_dari, $request->tanggal_sampai),
                $filename
            );
        }
        
        // Export Excel - All Steps (Rekap Seluruh Laporan)
        if ($request->has('export') && $request->export === 'excel_all') {
            $filename = 'Rekap_Seluruh_Laporan_Tim_' . ucfirst($processType) . '_' . now()->format('Y-m-d') . '.xlsx';
            return Excel::download(
                new AllTeamPerformanceExport($processType, $request->tanggal_dari, $request->tanggal_sampai),
                $filename
            );
        }
        
        return view('admin.reports.team-performance', compact(
            'rankings', 'stats', 'config', 'step', 'processType', 'stepConfigs'
        ));
    }

    /**
     * Marketing Performance Report with Normal/Fasttrack filter
     */
    public function teamMarketingPerformance(Request $request)
    {
        $processType = $request->get('process_type', 'all');
        
        $query = Submission::query();
        
        // Filter process type
        if ($processType === 'normal') {
            $query->where(function($q) {
                $q->where('process_type', 'normal')->orWhereNull('process_type');
            });
        } elseif ($processType === 'fasttrack') {
            $query->where('process_type', 'fasttrack');
        }
        
        // Filter tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        
        // Get Marketing submissions with count
        $marketingRankings = $query->clone()
            ->select(
                'marketing_id', 
                DB::raw('COUNT(*) as total_task'),
                DB::raw('SUM(CASE WHEN status = "PUBLISHED" THEN 1 ELSE 0 END) as completed_task')
            )
            ->whereNotNull('marketing_id')
            ->groupBy('marketing_id')
            ->orderByDesc('total_task')
            ->get();
        
        // Add rank and get Marketing names
        $marketingRankings = $marketingRankings->map(function ($item, $index) {
            $marketing = \App\Models\Marketing::find($item->marketing_id);
            $item->rank = $index + 1;
            $item->name = $marketing ? $marketing->name : 'Unknown';
            $item->model = $marketing;
            return $item;
        });
        
        // Get totals by process type
        $totalAll = Submission::whereNotNull('marketing_id')->count();
        $totalNormal = Submission::whereNotNull('marketing_id')
            ->where(fn($q) => $q->where('process_type', 'normal')->orWhereNull('process_type'))
            ->count();
        $totalFasttrack = Submission::whereNotNull('marketing_id')
            ->where('process_type', 'fasttrack')
            ->count();
        
        // Statistics
        $stats = [
            'total_tasks' => $marketingRankings->sum('total_task'),
            'total_marketing' => $marketingRankings->count(),
            'top_marketing' => $marketingRankings->first(),
            'total_all' => $totalAll,
            'total_normal' => $totalNormal,
            'total_fasttrack' => $totalFasttrack,
        ];
        
        $generatedAt = now()->format('d M Y H:i');
        $filterInfo = $this->getFilterInfoMarketing($request, $processType);
        
        // Export PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.team-marketing-performance-pdf', compact(
                'marketingRankings', 'stats', 'generatedAt', 'filterInfo', 'processType'
            ))->setPaper('a4', 'portrait');
            
            $filename = 'Laporan_Tim_Marketing_' . ucfirst($processType) . '_' . now()->format('Y-m-d') . '.pdf';
            return $pdf->download($filename);
        }
        
        // Export Excel
        if ($request->has('export') && $request->export === 'excel') {
            $filename = 'Laporan_Tim_Marketing_' . ucfirst($processType) . '_' . now()->format('Y-m-d') . '.xlsx';
            return Excel::download(
                new TeamPerformanceExport('marketing', $processType, $request->tanggal_dari, $request->tanggal_sampai),
                $filename
            );
        }
        
        // Export Excel - All Steps (Rekap Seluruh Laporan)
        if ($request->has('export') && $request->export === 'excel_all') {
            $filename = 'Rekap_Seluruh_Laporan_Tim_' . ucfirst($processType) . '_' . now()->format('Y-m-d') . '.xlsx';
            return Excel::download(
                new AllTeamPerformanceExport($processType, $request->tanggal_dari, $request->tanggal_sampai),
                $filename
            );
        }
        
        return view('admin.reports.team-marketing-performance', compact(
            'marketingRankings', 'stats', 'processType'
        ));
    }

    /**
     * Get filter information for PDF reports (extended)
     */
    private function getFilterInfoExtended(Request $request, $step, $processType, $stepConfigs)
    {
        $info = [];
        $info[] = 'Step: ' . ($stepConfigs[$step]['title'] ?? $step);
        $info[] = 'Jalur: ' . ($processType === 'all' ? 'Semua' : ($processType === 'normal' ? 'Normal' : 'Fasttrack'));
        
        if ($request->filled('tanggal_dari')) {
            $info[] = 'Dari: ' . date('d M Y', strtotime($request->tanggal_dari));
        }
        if ($request->filled('tanggal_sampai')) {
            $info[] = 'Sampai: ' . date('d M Y', strtotime($request->tanggal_sampai));
        }
        return implode(' | ', $info);
    }

    /**
     * Get filter information for Marketing PDF reports
     */
    private function getFilterInfoMarketing(Request $request, $processType)
    {
        $info = [];
        $info[] = 'Jalur: ' . ($processType === 'all' ? 'Semua' : ($processType === 'normal' ? 'Normal' : 'Fasttrack'));
        
        if ($request->filled('tanggal_dari')) {
            $info[] = 'Dari: ' . date('d M Y', strtotime($request->tanggal_dari));
        }
        if ($request->filled('tanggal_sampai')) {
            $info[] = 'Sampai: ' . date('d M Y', strtotime($request->tanggal_sampai));
        }
        return implode(' | ', $info);
    }

    /**
     * Get filter information for PDF reports
     */
    private function getFilterInfo(Request $request)
    {
        $info = [];
        if ($request->filled('tanggal_dari')) {
            $info[] = 'Dari: ' . date('d M Y', strtotime($request->tanggal_dari));
        }
        if ($request->filled('tanggal_sampai')) {
            $info[] = 'Sampai: ' . date('d M Y', strtotime($request->tanggal_sampai));
        }
        return $info ? implode(' | ', $info) : 'Semua periode';
    }
}

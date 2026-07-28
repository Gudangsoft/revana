<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use App\Exports\MarketingPointHistoryExport;
use App\Exports\MarketingLeaderboardExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class MarketingPointReportController extends Controller
{
    /**
     * Display Marketing points leaderboard/report
     */
    public function index(Request $request)
    {
        // Urutkan berdasarkan total_points (kolom yang sama yang ditampilkan di tabel) —
        // sebelumnya diurutkan berdasarkan submissions_count, yang bisa beda dari
        // total_points kalau rate poin per submission pernah berubah, membuat leaderboard
        // tampak salah urutan dibanding angka yang ditampilkan.
        $query = Marketing::where('is_active', true)
            ->withCount('submissions')
            ->orderByDesc('total_points');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $marketings = $query->paginate(request()->input('per_page', 20));

        $totalMarketings = Marketing::where('is_active', true)->count();
        $totalSubmissions = \App\Models\Submission::whereNotNull('marketing_id')->count();
        $totalPoints = (float) MarketingPointHistory::sum('points_earned');

        // Top performer this month
        $month = now()->month;
        $year  = now()->year;
        $topPerformerThisMonth = Marketing::where('is_active', true)
            ->whereHas('pointHistories', fn($q) => $q->whereMonth('created_at', $month)->whereYear('created_at', $year))
            ->get()
            ->map(function ($m) use ($month, $year) {
                $m->points_this_month = $m->pointHistories()
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year)
                    ->sum('points_earned');
                return $m;
            })
            ->sortByDesc('points_this_month')
            ->first();

        return view('admin.marketing-points.index', compact(
            'marketings',
            'totalMarketings',
            'totalPoints',
            'totalSubmissions',
            'topPerformerThisMonth'
        ));
    }

    /**
     * Show detail points for a specific Marketing
     */
    public function show(Request $request, Marketing $marketing)
    {
        $query = $marketing->pointHistories()
            ->with('submission.journalSlot.journalMaster');

        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }

        // Filter by process type (normal/fasttrack)
        if ($request->filled('process_type') && $request->process_type !== 'all') {
            $processType = $request->process_type;
            $query->whereHas('submission', function($q) use ($processType) {
                if ($processType === 'normal') {
                    $q->where(function($qq) {
                        $qq->where('process_type', 'normal')->orWhereNull('process_type');
                    });
                } else {
                    $q->where('process_type', $processType);
                }
            });
        }

        // Rekap akreditasi dari seluruh data terfilter (tanpa pagination)
        $allFiltered = (clone $query)->get();
        $recapByAccreditation = $allFiltered
            ->filter(fn($h) => $h->submission?->journalSlot?->journalMaster)
            ->groupBy(fn($h) => $h->submission->journalSlot->journalMaster->accreditation ?? 'Lainnya')
            ->map(fn($group) => [
                'count'  => $group->count(),
                'points' => $group->sum('points_earned'),
            ])
            ->sortKeys();
        $recapTotal = [
            'count'  => $allFiltered->count(),
            'points' => $allFiltered->sum('points_earned'),
        ];

        $pointHistories = (clone $query)->latest()->paginate(request()->input('per_page', 20));

        // Stats keseluruhan (tidak terpengaruh filter)
        $stats = [
            'total_points'      => $marketing->total_points,
            'total_submissions' => $marketing->pointHistories()->count(),
            'points_this_month' => $marketing->pointHistories()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('points_earned'),
        ];

        return view('admin.marketing-points.show', compact(
            'marketing',
            'pointHistories',
            'stats',
            'recapByAccreditation',
            'recapTotal'
        ));
    }

    /**
     * Export point history to Excel
     */
    public function exportExcel(Request $request, Marketing $marketing)
    {
        $tanggalDari = $request->tanggal_dari;
        $tanggalSampai = $request->tanggal_sampai;
        $processType = $request->process_type;
        
        $filename = 'point-history-' . str_replace(' ', '-', strtolower($marketing->name)) . '-' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(
            new MarketingPointHistoryExport($marketing, $tanggalDari, $tanggalSampai, $processType),
            $filename
        );
    }

    /**
     * Export leaderboard to Excel
     */
    public function exportLeaderboard(Request $request)
    {
        $search = $request->filled('search') ? $request->search : null;
        $filename = 'leaderboard-marketing-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new MarketingLeaderboardExport($search), $filename);
    }

    /**
     * Bulk sync: backfill missing marketing_point_histories + recalculate totals.
     * Called from TaskPointSettingController after saving settings.
     * Returns [$created, $synced].
     *
     * PENTING: versi sebelumnya juga menimpa ulang `points_earned` pada baris yang SUDAH
     * ADA setiap kali fungsi ini terpanggil (dipicu oleh admin menyimpan setting poin
     * TUGAS APAPUN, bukan cuma marketing) — itu menghancurkan riwayat poin yang sudah
     * benar milik ribuan submission lama begitu rate poin submit berubah. Sekarang HANYA
     * mengisi baris yang belum ada; baris yang sudah ada tidak pernah disentuh lagi.
     */
    public static function runBulkSync(): array
    {
        $submitPoints = (float) (\App\Models\TaskPointSetting::getMarketingPoints('submit') ?? 1);

        // Bulk INSERT missing history records for all submissions without a record
        $created = \DB::affectingStatement("
            INSERT INTO marketing_point_histories (marketing_id, submission_id, points_earned, description, created_at, updated_at)
            SELECT s.marketing_id, s.id, ?,
                   CONCAT('Sinkronisasi: ', COALESCE(s.kode_submit,''), ' - ', COALESCE(SUBSTR(s.judul_artikel, 1, 200),'')),
                   COALESCE(s.created_at, NOW()), COALESCE(s.updated_at, NOW())
            FROM submissions s
            WHERE s.marketing_id IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM marketing_point_histories mph
                  WHERE mph.marketing_id = s.marketing_id AND mph.submission_id = s.id
              )
        ", [$submitPoints]);

        // Recalculate total_points = SUM of points_earned per marketing
        $synced = \DB::affectingStatement('
            UPDATE marketings m
            SET m.total_points = (
                SELECT COALESCE(SUM(mph.points_earned), 0)
                FROM marketing_point_histories mph
                WHERE mph.marketing_id = m.id
            )
        ');

        return [$created, $synced];
    }

    /**
     * Sync all marketing points — backfill riwayat yang hilang lalu hitung ulang
     * total_points dari SUM riwayat (bukan lagi COUNT submission, karena rate poin
     * per submission bisa berubah dari waktu ke waktu, lihat TaskPointSetting).
     */
    public function syncAllPoints()
    {
        $submitPoints = (float) (\App\Models\TaskPointSetting::getMarketingPoints('submit') ?? 1);
        $marketings = Marketing::all();
        $synced = 0;
        $created = 0;

        foreach ($marketings as $marketing) {
            // Create missing point history records (pakai rate yang berlaku saat ini)
            $submissions = \App\Models\Submission::where('marketing_id', $marketing->id)
                ->whereDoesntHave('marketingPointHistory')
                ->get();

            foreach ($submissions as $submission) {
                MarketingPointHistory::create([
                    'marketing_id' => $marketing->id,
                    'submission_id' => $submission->id,
                    'points_earned' => $submitPoints,
                    'description' => mb_substr("Sinkronisasi: {$submission->kode_submit} - {$submission->judul_artikel}", 0, 255),
                ]);
                $created++;
            }

            $actualPoints = MarketingPointHistory::where('marketing_id', $marketing->id)->sum('points_earned');
            $oldTotal = $marketing->total_points ?? 0;

            if (round((float) $oldTotal, 4) !== round((float) $actualPoints, 4)) {
                $marketing->update(['total_points' => $actualPoints]);
                $synced++;
            }
        }

        return redirect()->back()
            ->with('success', "Sinkronisasi selesai! {$synced} marketing point diperbarui, {$created} riwayat point baru dibuat.");
    }

    /**
     * Adjust points manually
     */
    public function adjustPoints(Request $request, Marketing $marketing)
    {
        $validated = $request->validate([
            'points' => 'required|numeric',
            'reason' => 'required|string|max:255',
        ]);

        MarketingPointHistory::create([
            'marketing_id' => $marketing->id,
            'submission_id' => null,
            'points_earned' => $validated['points'],
            'description' => 'Penyesuaian manual: ' . $validated['reason'],
        ]);

        // Recalculate total from actual sum to avoid drift
        $actual = MarketingPointHistory::where('marketing_id', $marketing->id)->sum('points_earned');
        $marketing->update(['total_points' => max(0, $actual)]);

        return redirect()->back()
            ->with('success', 'Poin berhasil disesuaikan');
    }
}

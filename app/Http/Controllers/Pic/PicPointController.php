<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\PicPointHistory;
use App\Models\TaskPointSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PicPointController extends Controller
{
    /**
     * Sync point data for logged-in PIC and redirect back with fresh data
     */
    public function syncMyPoints()
    {
        $pic = Auth::guard('pic')->user();
        $picId = $pic->id;
        $resetAt = $pic->points_reset_at;
        $backfilled = 0;

        // --- BACKFILL: step submit ---
        // Award point for every submission where this PIC is petugas_submit but has no history.
        // Tanggal riwayat (created_at) diisi dari submission->created_at, BUKAN waktu sync
        // ini berjalan — supaya tanggal penyelesaian tugas tidak ikut berubah ke tanggal
        // sync setiap kali PIC menekan tombol "Sinkronkan Poin Saya".
        $submitSubmissions = \App\Models\Submission::where('petugas_submit_id', $picId)->get();
        foreach ($submitSubmissions as $submission) {
            $occurredAt = $submission->created_at;
            // Jangan backfill submission yang lebih tua dari kapan poin PIC ini terakhir
            // direset — kalau tidak, riwayat yang sengaja dihapus lewat "Reset Semua
            // Point" akan "hidup lagi" (insiden 29 Juli 2026, lihat
            // docs/tests/log-update-2026-07-29.md #4).
            if ($resetAt && $occurredAt && $occurredAt->lt($resetAt)) {
                continue;
            }
            $history = PicPointHistory::awardPoints(
                $picId, $submission->id, 'submit',
                "Submit artikel: {$submission->kode_submit} - {$submission->judul_artikel}",
                $occurredAt
            );
            if ($history) $backfilled++;
        }

        // --- BACKFILL: workflow steps (only if step is validated / done) ---
        $workflowSteps = [
            ['field' => 'petugas_editor1_id',   'valid' => 'editor1_valid',   'step' => 'editor1',   'validated_at' => 'editor1_validated_at'],
            ['field' => 'petugas_author1_id',    'valid' => 'author1_valid',   'step' => 'author1',   'validated_at' => 'author1_validated_at'],
            ['field' => 'petugas_editor2_id',    'valid' => 'editor2_valid',   'step' => 'editor2',   'validated_at' => 'editor2_validated_at'],
            ['field' => 'petugas_reviewer1_id',  'valid' => 'reviewer1_valid', 'step' => 'reviewer1', 'validated_at' => 'reviewer1_validated_at'],
            ['field' => 'petugas_reviewer2_id',  'valid' => 'reviewer2_valid', 'step' => 'reviewer2', 'validated_at' => 'reviewer2_validated_at'],
            ['field' => 'petugas_editor3_id',    'valid' => 'editor3_valid',   'step' => 'editor3',   'validated_at' => 'editor3_validated_at'],
            ['field' => 'petugas_author2_id',    'valid' => 'author2_valid',   'step' => 'author2',   'validated_at' => 'author2_validated_at'],
            ['field' => 'petugas_production_id', 'valid' => 'production_valid','step' => 'production', 'validated_at' => 'production_validated_at'],
        ];

        foreach ($workflowSteps as $ws) {
            $submissions = \App\Models\Submission::where($ws['field'], $picId)
                ->where($ws['valid'], true)
                ->get();
            foreach ($submissions as $submission) {
                $occurredAt = $submission->{$ws['validated_at']} ?? $submission->updated_at;
                if ($resetAt && $occurredAt && $occurredAt->lt($resetAt)) {
                    continue;
                }
                $history = PicPointHistory::awardPoints(
                    $picId, $submission->id, $ws['step'],
                    "Menyelesaikan tugas {$ws['step']} untuk artikel: {$submission->kode_submit}",
                    $occurredAt
                );
                if ($history) $backfilled++;
            }
        }

        // Recalculate total_points from all history records
        $actualPoints = PicPointHistory::where('pic_id', $picId)->sum('points_earned');
        $pic->update(['total_points' => $actualPoints]);

        $msg = 'Data point berhasil disinkronkan. Total point terkini: ' . number_format($actualPoints);
        if ($backfilled > 0) {
            $msg .= " ({$backfilled} riwayat baru ditemukan dan ditambahkan)";
        }

        // Jika dipanggil dari modal logout, logout setelah sync
        if (request('redirect') === 'logout') {
            Auth::guard('pic')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('pic.login')
                ->with('success', $msg . ' Anda telah logout.');
        }

        return redirect()->route('pic.points.index')->with('success', $msg);
    }

    /**
     * Display points dashboard for logged in PIC
     */
    public function index(Request $request)
    {
        $pic = Auth::guard('pic')->user();
        
        // Get point histories with pagination
        $query = $pic->pointHistories()->with('submission');

        // Filter cepat (hari ini/minggu ini/bulan ini/tahun ini) — kalau dipilih, ini
        // yang dipakai; kalau tidak, jatuh ke filter tanggal_dari/tanggal_sampai manual.
        if ($request->filled('period')) {
            match ($request->period) {
                'today' => $query->whereDate('created_at', today()),
                'week'  => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                'year'  => $query->whereYear('created_at', now()->year),
                default => null,
            };
        } else {
            if ($request->filled('tanggal_dari')) {
                $query->whereDate('created_at', '>=', $request->tanggal_dari);
            }
            if ($request->filled('tanggal_sampai')) {
                $query->whereDate('created_at', '<=', $request->tanggal_sampai);
            }
        }

        // Filter by step
        if ($request->filled('step')) {
            $query->where('step', $request->step);
        }

        // Total tugas & total point untuk hasil filter saat ini (bukan keseluruhan) —
        // dihitung dari query yang sama sebelum paginate, supaya tetap akurat walau
        // hasil filter lebih dari 1 halaman.
        $filteredTotals = (clone $query)->selectRaw('COUNT(*) as total_tasks, COALESCE(SUM(points_earned), 0) as total_points')->first();

        $pointHistories = $query->latest()->paginate(request()->input('per_page', 20));
        
        // Statistics - calculate real-time from point histories
        $totalPoints = $pic->pointHistories()->sum('points_earned');
        
        $pointsToday = $pic->pointHistories()
            ->whereDate('created_at', today())
            ->sum('points_earned');
            
        $pointsThisMonth = $pic->pointHistories()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('points_earned');
            
        $totalTasks = $pic->pointHistories()->count();
        
        // Sync total_points in database
        $pic->update(['total_points' => $totalPoints]);
        
        $stats = [
            'total_points' => $totalPoints,
            'points_today' => $pointsToday,
            'points_this_month' => $pointsThisMonth,
            'total_tasks' => $totalTasks,
        ];
        
        // Monthly breakdown for chart
        $monthlyPoints = PicPointHistory::where('pic_id', $pic->id)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(points_earned) as total')
            ->groupBy('year', 'month')
            ->orderByRaw('year DESC, month DESC')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();
        
        // Points by step breakdown
        $pointsByStep = PicPointHistory::where('pic_id', $pic->id)
            ->selectRaw('step, SUM(points_earned) as total, COUNT(*) as count')
            ->groupBy('step')
            ->orderBy('total', 'desc')
            ->get();
        
        $stepConfig = TaskPointSetting::getPicPointConfig();
        
        return view('pic.points.index', compact(
            'pic',
            'pointHistories',
            'stats',
            'monthlyPoints',
            'pointsByStep',
            'stepConfig',
            'filteredTotals'
        ));
    }

    /**
     * Display point rankings for PIC and Marketing
     */
    public function rankings()
    {
        $currentPic = Auth::guard('pic')->user();

        // PIC Point Rankings - Semua PIC aktif diurutkan berdasarkan point
        $picRankings = \App\Models\Pic::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get()
            ->map(function ($pic, $index) {
                $pic->rank = $index + 1;
                return $pic;
            });

        // Marketing Point Rankings - Semua Marketing aktif diurutkan berdasarkan point
        $marketingRankings = \App\Models\Marketing::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get()
            ->map(function ($marketing, $index) {
                $marketing->rank = $index + 1;
                return $marketing;
            });

        // Statistics
        $totalPicPoints = \App\Models\Pic::where('is_active', true)->sum('total_points');
        $totalMarketingPoints = \App\Models\Marketing::where('is_active', true)->sum('total_points');
        $activePicCount = \App\Models\Pic::where('is_active', true)->count();
        $activeMarketingCount = \App\Models\Marketing::where('is_active', true)->count();

        // Current PIC rank
        $currentPicRank = $picRankings->where('id', $currentPic->id)->first()->rank ?? null;

        // Additional rank info
        $pointsToNextRank = 0;
        $nextRankPic = null;
        $topPercentage = 0;
        
        if ($currentPicRank && $currentPicRank > 1) {
            $nextRankPic = $picRankings->where('rank', $currentPicRank - 1)->first();
            if ($nextRankPic) {
                $pointsToNextRank = ($nextRankPic->total_points ?? 0) - ($currentPic->total_points ?? 0);
            }
        }
        
        if ($activePicCount > 0 && $currentPicRank) {
            $topPercentage = round(($currentPicRank / $activePicCount) * 100, 1);
        }

        return view('pic.points.rankings', compact(
            'currentPic',
            'currentPicRank',
            'picRankings',
            'marketingRankings',
            'totalPicPoints',
            'totalMarketingPoints',
            'activePicCount',
            'activeMarketingCount',
            'pointsToNextRank',
            'nextRankPic',
            'topPercentage'
        ));
    }
}

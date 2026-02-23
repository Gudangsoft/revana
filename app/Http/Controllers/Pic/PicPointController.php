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
        $backfilled = 0;

        // --- BACKFILL: step submit ---
        // Award point for every submission where this PIC is petugas_submit but has no history
        $submitSubmissions = \App\Models\Submission::where('petugas_submit_id', $picId)->get();
        foreach ($submitSubmissions as $submission) {
            $history = PicPointHistory::awardPoints(
                $picId, $submission->id, 'submit',
                "Submit artikel: {$submission->kode_submit} - {$submission->judul_artikel}"
            );
            if ($history) $backfilled++;
        }

        // --- BACKFILL: workflow steps (only if step is validated / done) ---
        $workflowSteps = [
            ['field' => 'petugas_editor1_id',   'valid' => 'editor1_valid',   'step' => 'editor1'],
            ['field' => 'petugas_author1_id',    'valid' => 'author1_valid',   'step' => 'author1'],
            ['field' => 'petugas_editor2_id',    'valid' => 'editor2_valid',   'step' => 'editor2'],
            ['field' => 'petugas_reviewer1_id',  'valid' => 'reviewer1_valid', 'step' => 'reviewer1'],
            ['field' => 'petugas_reviewer2_id',  'valid' => 'reviewer2_valid', 'step' => 'reviewer2'],
            ['field' => 'petugas_editor3_id',    'valid' => 'editor3_valid',   'step' => 'editor3'],
            ['field' => 'petugas_author2_id',    'valid' => 'author2_valid',   'step' => 'author2'],
            ['field' => 'petugas_production_id', 'valid' => 'production_valid','step' => 'production'],
        ];

        foreach ($workflowSteps as $ws) {
            $submissions = \App\Models\Submission::where($ws['field'], $picId)
                ->where($ws['valid'], true)
                ->get();
            foreach ($submissions as $submission) {
                $history = PicPointHistory::awardPoints(
                    $picId, $submission->id, $ws['step'],
                    "Menyelesaikan tugas {$ws['step']} untuk artikel: {$submission->kode_submit}"
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
        
        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('created_at', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('created_at', '<=', $request->tanggal_sampai);
        }
        
        // Filter by step
        if ($request->filled('step')) {
            $query->where('step', $request->step);
        }
        
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
            'stepConfig'
        ));
    }
}

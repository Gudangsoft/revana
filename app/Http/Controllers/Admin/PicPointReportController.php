<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Models\TaskPointSetting;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PicPointsExport;
use App\Exports\PicPointHistoryExport;

class PicPointReportController extends Controller
{
    /**
     * Display PIC points leaderboard/report
     */
    public function index(Request $request)
    {
        $query = Pic::where('is_active', true)
            ->orderBy('total_points', 'desc');
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $pics = $query->paginate(request()->input('per_page', 20));
        
        // Get overall statistics
        $totalPics = Pic::where('is_active', true)->count();
        $totalPoints = Pic::where('is_active', true)->sum('total_points');
        $totalTasks = PicPointHistory::count();
        
        // Top performer this month
        $topPerformerThisMonth = Pic::where('is_active', true)
            ->whereHas('pointHistories', function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            })
            ->withSum(['pointHistories as points_this_month' => function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            }], 'points_earned')
            ->orderByDesc('points_this_month')
            ->first();
        
        // Points distribution by step
        $pointsByStep = \DB::table('pic_point_histories')
            ->selectRaw('step, SUM(points_earned) as total, COUNT(*) as count')
            ->groupBy('step')
            ->orderByRaw('SUM(points_earned) desc')
            ->get();
        
        $stepConfig = TaskPointSetting::getPicPointConfig();
        
        return view('admin.pic-points.index', compact(
            'pics',
            'totalPics',
            'totalPoints',
            'totalTasks',
            'topPerformerThisMonth',
            'pointsByStep',
            'stepConfig'
        ));
    }

    /**
     * Show detail points for a specific PIC
     */
    public function show(Request $request, Pic $pic)
    {
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
        
        // Stats
        $stats = [
            'total_points' => $pic->total_points ?? 0,
            'total_tasks' => $pic->pointHistories()->count(),
            'points_today' => $pic->pointHistories()
                ->whereDate('created_at', now()->toDateString())
                ->sum('points_earned'),
            'points_this_month' => $pic->pointHistories()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('points_earned'),
        ];
        
        // Points by step
        $pointsByStep = \DB::table('pic_point_histories')
            ->where('pic_id', $pic->id)
            ->selectRaw('step, SUM(points_earned) as total, COUNT(*) as count')
            ->groupBy('step')
            ->orderByRaw('SUM(points_earned) desc')
            ->get();
        
        $stepConfig = TaskPointSetting::getPicPointConfig();
        
        return view('admin.pic-points.show', compact(
            'pic',
            'pointHistories',
            'stats',
            'pointsByStep',
            'stepConfig'
        ));
    }

    /**
     * Manually adjust points for a PIC
     */
    public function adjustPoints(Request $request, Pic $pic)
    {
        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $adjustment = $validated['adjustment'];
        $reason = $validated['reason'];

        // Create history entry
        PicPointHistory::create([
            'pic_id' => $pic->id,
            'submission_id' => null,
            'step' => 'adjustment',
            'points_earned' => $adjustment,
            'description' => "Penyesuaian manual: {$reason}",
        ]);

        // Update total points
        $pic->increment('total_points', $adjustment);

        $action = $adjustment > 0 ? 'ditambahkan' : 'dikurangi';
        $absPoints = abs($adjustment);

        return back()->with('success', "{$absPoints} point berhasil {$action} untuk {$pic->name}");
    }

    /**
     * Sync all PIC points from point history (comprehensive sync)
     */
    public function syncAllPoints()
    {
        $pics = Pic::all();
        $synced    = 0;
        $unchanged = 0;

        foreach ($pics as $pic) {
            // 1. Recalculate total_points from actual point history records
            $actualPoints = PicPointHistory::where('pic_id', $pic->id)->sum('points_earned');
            $oldTotal     = $pic->total_points ?? 0;

            if ($actualPoints != $oldTotal) {
                $pic->update(['total_points' => $actualPoints]);
                $synced++;
            } else {
                $unchanged++;
            }
        }

        // 2. Remove orphan point histories (histories whose pic no longer exists)
        $validPicIds = Pic::pluck('id');
        $orphanCount = PicPointHistory::whereNotIn('pic_id', $validPicIds)->count();
        if ($orphanCount > 0) {
            PicPointHistory::whereNotIn('pic_id', $validPicIds)->delete();
        }

        return redirect()->route('admin.pic-points.index')
            ->with('success', "Sinkronisasi selesai! {$synced} PIC diperbarui, {$unchanged} sudah sesuai" . ($orphanCount > 0 ? ", {$orphanCount} riwayat orphan dihapus" : "." ));
    }

    /**
     * Export points report
     */
    public function export(Request $request)
    {
        $filename = 'laporan-point-pic-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new PicPointsExport($request), $filename);
    }

    /**
     * Export point history for a specific PIC
     */
    public function exportShow(Request $request, Pic $pic)
    {
        $filename = 'point-history-' . str_replace(' ', '-', strtolower($pic->name)) . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new PicPointHistoryExport(
                $pic,
                $request->tanggal_dari,
                $request->tanggal_sampai,
                $request->step
            ),
            $filename
        );
    }
}

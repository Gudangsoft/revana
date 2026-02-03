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
        
        $pointHistories = $query->latest()->paginate(20);
        
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

<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\Reward;
use App\Models\PointHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $pendingTasks = ReviewAssignment::where('reviewer_id', $user->id)
            ->where('status', 'PENDING')
            ->count();

        $activeTasks = ReviewAssignment::where('reviewer_id', $user->id)
            ->whereIn('status', ['ACCEPTED', 'ON_PROGRESS', 'REVISION'])
            ->count();

        $completedTasks = ReviewAssignment::where('reviewer_id', $user->id)
            ->where('status', 'APPROVED')
            ->count();

        $recentAssignments = ReviewAssignment::where('reviewer_id', $user->id)
            ->with('journal')
            ->latest()
            ->take(10)
            ->get();

        $availableRewards = Reward::where('is_active', true)
            ->where('points_required', '<=', $user->available_points)
            ->get();

        // Chart Data: Reviews per month (last 6 months)
        $reviewsPerMonth = ReviewAssignment::where('reviewer_id', $user->id)
            ->where('status', 'APPROVED')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Prepare chart labels and data
        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $chartLabels[] = $monthName;
            
            $count = $reviewsPerMonth->first(function($item) use ($date) {
                return $item->month == $date->month && $item->year == $date->year;
            });
            
            $chartData[] = $count ? $count->count : 0;
        }

        // Points History Chart (last 6 months)
        $pointsHistory = PointHistory::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(CASE WHEN points > 0 THEN points ELSE 0 END) as earned'),
                DB::raw('SUM(CASE WHEN points < 0 THEN ABS(points) ELSE 0 END) as spent')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $pointsEarned = [];
        $pointsSpent = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            $history = $pointsHistory->first(function($item) use ($date) {
                return $item->month == $date->month && $item->year == $date->year;
            });
            
            $pointsEarned[] = $history ? $history->earned : 0;
            $pointsSpent[] = $history ? $history->spent : 0;
        }

        // Status Distribution
        $statusDistribution = ReviewAssignment::where('reviewer_id', $user->id)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return view('reviewer.dashboard', compact(
            'user',
            'pendingTasks',
            'activeTasks',
            'completedTasks',
            'recentAssignments',
            'availableRewards',
            'chartLabels',
            'chartData',
            'pointsEarned',
            'pointsSpent',
            'statusDistribution'
        ));
    }
}

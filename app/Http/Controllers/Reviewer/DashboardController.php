<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\Reward;
use Illuminate\Http\Request;

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

        return view('reviewer.dashboard', compact(
            'user',
            'pendingTasks',
            'activeTasks',
            'completedTasks',
            'recentAssignments',
            'availableRewards'
        ));
    }
}

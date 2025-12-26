<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        // Get reviewers with their statistics
        $reviewers = User::where('role', 'reviewer')
            ->withCount([
                'reviewAssignments as total_reviews' => function($q) {
                    $q->where('status', 'APPROVED');
                },
                'reviewAssignments as pending_reviews' => function($q) {
                    $q->whereIn('status', ['PENDING', 'ACCEPTED', 'SUBMITTED']);
                }
            ])
            ->withSum('pointHistories as total_points_earned', 'points')
            ->with(['rewardRedemptions' => function($q) {
                $q->where('status', 'COMPLETED')
                  ->with('reward:id,name,tier');
            }])
            ->get()
            ->map(function($reviewer) {
                // Calculate redemption stats
                $completedRedemptions = $reviewer->rewardRedemptions;
                
                $reviewer->total_redemptions = $completedRedemptions->count();
                $reviewer->total_points_spent = $completedRedemptions->sum('points_used');
                $reviewer->total_points_earned = $reviewer->total_points_earned ?? 0;
                $reviewer->current_points = $reviewer->available_points ?? 0;
                
                // Count rewards by tier
                $reviewer->platinum_count = $completedRedemptions->where('reward.tier', 'Platinum')->count();
                $reviewer->gold_count = $completedRedemptions->where('reward.tier', 'Gold')->count();
                $reviewer->silver_count = $completedRedemptions->where('reward.tier', 'Silver')->count();
                $reviewer->bronze_count = $completedRedemptions->where('reward.tier', 'Bronze')->count();
                
                // Calculate tier score for ranking
                $reviewer->tier_score = 
                    ($reviewer->platinum_count * 1000) + 
                    ($reviewer->gold_count * 100) + 
                    ($reviewer->silver_count * 10) + 
                    ($reviewer->bronze_count * 1);
                
                return $reviewer;
            })
            ->sortByDesc('tier_score')
            ->values();

        // Assign ranks
        $rank = 1;
        $reviewers = $reviewers->map(function($reviewer) use (&$rank) {
            $reviewer->rank = $rank++;
            return $reviewer;
        });

        return view('admin.leaderboard.index', compact('reviewers'));
    }
}

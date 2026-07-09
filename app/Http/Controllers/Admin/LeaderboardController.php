<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        $tenantKey = app()->bound('tenant') ? app('tenant')->subdomain : 'master';
        $reviewers = Cache::remember("leaderboard.reviewers.{$tenantKey}", 300, function () {
            return $this->buildLeaderboard();
        });

        return view('admin.leaderboard.index', compact('reviewers'));
    }

    private function buildLeaderboard()
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
            // Hitung poin dari riwayat transaksi (point_histories) sebagai sumber kebenaran,
            // dipisah per type — sebelumnya di-sum tanpa filter type sehingga poin EARNED dan
            // REDEEMED (keduanya disimpan sebagai angka positif) ikut terjumlah bersama,
            // membuat "total poin" tampil lebih besar dari yang sebenarnya untuk reviewer
            // yang pernah menukar reward.
            ->withSum(['pointHistories as total_points_earned' => function ($q) {
                $q->where('type', 'EARNED');
            }], 'points')
            ->withSum(['pointHistories as total_points_redeemed' => function ($q) {
                $q->where('type', 'REDEEMED');
            }], 'points')
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
                // Poin saat ini dihitung langsung dari riwayat transaksi (bukan dari kolom
                // users.available_points yang cuma cache) — supaya selalu sinkron walau
                // kolom cache-nya pernah tidak ter-update dengan benar.
                $reviewer->current_points = $reviewer->total_points_earned - ($reviewer->total_points_redeemed ?? 0);
                
                // Count rewards by tier - using pluck to get nested reward tier
                $tiers = $completedRedemptions->pluck('reward.tier');
                $reviewer->platinum_count = $tiers->filter(fn($tier) => $tier === 'Platinum')->count();
                $reviewer->gold_count = $tiers->filter(fn($tier) => $tier === 'Gold')->count();
                $reviewer->silver_count = $tiers->filter(fn($tier) => $tier === 'Silver')->count();
                $reviewer->bronze_count = $tiers->filter(fn($tier) => $tier === 'Bronze')->count();
                
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

        return $reviewers;
    }
}

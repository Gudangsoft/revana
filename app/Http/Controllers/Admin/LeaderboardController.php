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

    /** Kondisi "user ini ditugaskan di assignment ini" — cek SEMUA slot reviewer (utama + 2-5), bukan cuma reviewer_id */
    private function anyReviewerSlotMatchesUser($query)
    {
        return $query->where(function ($q) {
            $q->whereColumn('review_assignments.reviewer_id', 'users.id')
              ->orWhereColumn('review_assignments.reviewer_2_id', 'users.id')
              ->orWhereColumn('review_assignments.reviewer_3_id', 'users.id')
              ->orWhereColumn('review_assignments.reviewer_4_id', 'users.id')
              ->orWhereColumn('review_assignments.reviewer_5_id', 'users.id');
        });
    }

    private function buildLeaderboard()
    {
        // Get reviewers with their statistics
        // NB: total_reviews/pending_reviews sebelumnya cuma hitung assignment di mana user
        // jadi reviewer UTAMA (reviewer_id) — reviewer pendamping (reviewer_2..5_id) tidak
        // pernah terhitung sama sekali, padahal hampir semua assignment punya reviewer
        // pendamping. Sekarang dihitung lewat subquery yang cek kelima slot reviewer.
        $reviewers = User::where('role', 'reviewer')
            ->select('users.*')
            ->selectSub(function ($q) {
                $this->anyReviewerSlotMatchesUser(
                    $q->from('review_assignments')->where('status', 'APPROVED')
                )->selectRaw('count(*)');
            }, 'total_reviews')
            ->selectSub(function ($q) {
                $this->anyReviewerSlotMatchesUser(
                    $q->from('review_assignments')->whereIn('status', ['PENDING', 'ACCEPTED', 'SUBMITTED'])
                )->selectRaw('count(*)');
            }, 'pending_reviews')
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

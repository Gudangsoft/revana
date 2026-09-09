<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Query & ranking leaderboard reviewer — dipakai BERSAMA oleh
 * Admin\LeaderboardController (/admin/leaderboard) dan
 * Reviewer\LeaderboardController (/reviewer/leaderboard).
 *
 * Riwayat (9 Sept 2026): kedua controller di atas tadinya punya salinan query
 * sendiri-sendiri yang perlahan MENYIMPANG. Versi admin sempat diperbaiki:
 * - `total_reviews`/`pending_reviews` sebelumnya cuma hitung assignment di mana
 *   user jadi reviewer UTAMA (`reviewer_id`) — padahal satu assignment bisa
 *   punya sampai 5 reviewer sekaligus (`reviewer_id` s/d `reviewer_5_id`),
 *   jadi review sebagai reviewer pendamping tidak pernah terhitung.
 * - Poin sebelumnya di-sum dari `point_histories` TANPA pisah `type` — padahal
 *   baris `type='REDEEMED'` juga disimpan sebagai angka POSITIF (bukan
 *   negatif), jadi ikut terjumlah sebagai "earned" dan menampilkan total poin
 *   lebih besar dari yang sebenarnya untuk reviewer yang pernah tukar reward.
 * - Ranking sebelumnya berdasar `tier_score` (dari reward yang sudah ditukar)
 *   — SELALU 0 untuk reviewer yang belum pernah redeem reward apa pun,
 *   sehingga urutan rank jadi acak sesuai urutan baris DB, bukan performa
 *   review nyata.
 *
 * ...tapi perbaikan itu cuma diterapkan di controller admin — versi reviewer
 * dibiarkan pakai logika lama yang salah (dilaporkan user 9 Sept 2026:
 * "/reviewer/leaderboard perbaiki agar sesuai dengan data real"). Disatukan
 * di sini supaya kedua halaman selalu konsisten & tidak bisa menyimpang lagi.
 */
class ReviewerLeaderboardService
{
    /** Kondisi "user ini ditugaskan di assignment ini" — cek SEMUA slot reviewer (utama + 2-5) */
    private function anyReviewerSlotMatchesUser(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereColumn('review_assignments.reviewer_id', 'users.id')
              ->orWhereColumn('review_assignments.reviewer_2_id', 'users.id')
              ->orWhereColumn('review_assignments.reviewer_3_id', 'users.id')
              ->orWhereColumn('review_assignments.reviewer_4_id', 'users.id')
              ->orWhereColumn('review_assignments.reviewer_5_id', 'users.id');
        });
    }

    public function build(): Collection
    {
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
            ->withSum(['pointHistories as total_points_earned' => function ($q) {
                $q->where('type', 'EARNED');
            }], 'points')
            ->withSum(['pointHistories as total_points_redeemed' => function ($q) {
                $q->where('type', 'REDEEMED');
            }], 'points')
            ->with(['rewardRedemptions' => function ($q) {
                $q->where('status', 'COMPLETED')
                  ->with('reward:id,name,tier');
            }])
            ->get()
            ->map(function ($reviewer) {
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
                $reviewer->platinum_count = $tiers->filter(fn ($tier) => $tier === 'Platinum')->count();
                $reviewer->gold_count = $tiers->filter(fn ($tier) => $tier === 'Gold')->count();
                $reviewer->silver_count = $tiers->filter(fn ($tier) => $tier === 'Silver')->count();
                $reviewer->bronze_count = $tiers->filter(fn ($tier) => $tier === 'Bronze')->count();

                // Tier score dari reward masih dihitung untuk ditampilkan sebagai badge
                // Platinum/Gold/Silver/Bronze di tabel, tapi bukan lagi dasar ranking (lihat
                // sortByDesc di bawah) — ranking langsung berdasarkan poin, karena tier
                // score selalu 0 untuk reviewer yang belum pernah redeem reward, sehingga
                // urutan rank jadi acak dan tidak mencerminkan poin sama sekali.
                $reviewer->tier_score =
                    ($reviewer->platinum_count * 1000) +
                    ($reviewer->gold_count * 100) +
                    ($reviewer->silver_count * 10) +
                    ($reviewer->bronze_count * 1);

                return $reviewer;
            })
            ->sortByDesc('current_points')
            ->values();

        // Assign ranks
        $rank = 1;
        return $reviewers->map(function ($reviewer) use (&$rank) {
            $reviewer->rank = $rank++;
            return $reviewer;
        });
    }

    /**
     * Sama seperti build(), tapi di-cache 5 menit (key ber-tenant, konvensi yang
     * sama dipakai widget ranking lain di sistem ini — lihat App\Support\RankingCache)
     * supaya kedua halaman (admin & reviewer) tidak menjalankan query yang sama
     * berulang-ulang di request terpisah — siapa pun yang buka duluan akan
     * mengisi cache utk yang lain juga, karena datanya memang identik.
     */
    public function get(): Collection
    {
        $tenantKey = app()->bound('tenant') ? app('tenant')->subdomain : 'master';

        return Cache::remember("leaderboard.reviewers.{$tenantKey}", 300, function () {
            return $this->build();
        });
    }
}

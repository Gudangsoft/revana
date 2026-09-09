<?php

namespace Tests\Feature;

use App\Models\PointHistory;
use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\ReviewAssignment;
use App\Models\User;
use App\Services\ReviewerLeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi 9 Sept 2026: "/reviewer/leaderboard perbaiki agar sesuai dengan data
 * real" — LeaderboardController versi reviewer punya 3 bug data-akurasi yang
 * SUDAH diperbaiki di Admin\LeaderboardController tapi tidak pernah dibawa ke
 * versi reviewer (kedua controller ternyata punya salinan query sendiri yang
 * menyimpang):
 *
 * 1. total_reviews cuma hitung assignment di mana user jadi reviewer_id
 *    (reviewer UTAMA) — review sebagai reviewer_2_id..reviewer_5_id (reviewer
 *    pendamping) tidak pernah terhitung, padahal satu assignment bisa punya
 *    sampai 5 reviewer.
 * 2. Poin di-sum dari point_histories TANPA pisah type EARNED/REDEEMED —
 *    padahal baris REDEEMED juga disimpan sebagai angka POSITIF, jadi ikut
 *    kejumlah sebagai "earned" untuk reviewer yang pernah tukar reward.
 * 3. Ranking berdasar tier_score (dari reward yang sudah ditukar) — SELALU 0
 *    kalau belum ada reviewer yang redeem apa pun, jadi urutan rank jadi
 *    acak/sesuai urutan baris DB, bukan performa review nyata.
 *
 * Diperbaiki dengan menyatukan logika ke ReviewerLeaderboardService, dipakai
 * BERSAMA oleh Admin\LeaderboardController & Reviewer\LeaderboardController,
 * supaya kedua halaman selalu konsisten.
 */
class ReviewerLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeReviewer(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Reviewer Test ' . uniqid(),
            'email' => 'reviewer-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'reviewer',
        ], $overrides));
    }

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    private function makeAssignment(array $overrides = []): ReviewAssignment
    {
        $assigner = $this->makeAdmin();
        // reviewer_id NOT NULL di skema — default ke reviewer dummy kalau test
        // sengaja cuma mau isi salah satu slot reviewer_2..5_id.
        $defaultReviewer = $this->makeReviewer();

        return ReviewAssignment::create(array_merge([
            'article_title' => 'Judul Artikel Test ' . uniqid(),
            'article_number' => 'ART-' . uniqid(),
            'submit_link' => 'https://example.test/artikel-' . uniqid(),
            'status' => 'APPROVED',
            'approved_at' => now(),
            'reviewer_id' => $defaultReviewer->id,
            'assigned_by' => $assigner->id,
        ], $overrides));
    }

    /**
     * Bug #1: review sebagai reviewer PENDAMPING (reviewer_2_id) sebelumnya
     * tidak pernah terhitung di total_reviews.
     */
    public function test_total_reviews_counts_assignments_across_all_five_reviewer_slots(): void
    {
        $reviewer = $this->makeReviewer();

        // 2 review sebagai reviewer utama (reviewer_id)
        $this->makeAssignment(['reviewer_id' => $reviewer->id]);
        $this->makeAssignment(['reviewer_id' => $reviewer->id]);
        // 1 review sebagai reviewer_2, 1 sebagai reviewer_3, 1 sebagai reviewer_5
        $this->makeAssignment(['reviewer_2_id' => $reviewer->id]);
        $this->makeAssignment(['reviewer_3_id' => $reviewer->id]);
        $this->makeAssignment(['reviewer_5_id' => $reviewer->id]);
        // Review milik reviewer LAIN — tidak boleh ikut terhitung
        $other = $this->makeReviewer();
        $this->makeAssignment(['reviewer_id' => $other->id]);

        $leaderboard = app(ReviewerLeaderboardService::class)->build();
        $entry = $leaderboard->firstWhere('id', $reviewer->id);

        $this->assertNotNull($entry);
        $this->assertEquals(5, $entry->total_reviews,
            'Review sebagai reviewer pendamping (slot 2-5) harus ikut terhitung, bukan cuma reviewer utama');
    }

    /** Assignment dengan status non-APPROVED tidak boleh ikut terhitung sebagai total_reviews. */
    public function test_total_reviews_only_counts_approved_assignments(): void
    {
        $reviewer = $this->makeReviewer();
        $this->makeAssignment(['reviewer_id' => $reviewer->id, 'status' => 'APPROVED']);
        $this->makeAssignment(['reviewer_2_id' => $reviewer->id, 'status' => 'ON_PROGRESS']);
        $this->makeAssignment(['reviewer_3_id' => $reviewer->id, 'status' => 'REJECTED']);

        $leaderboard = app(ReviewerLeaderboardService::class)->build();
        $entry = $leaderboard->firstWhere('id', $reviewer->id);

        $this->assertEquals(1, $entry->total_reviews);
    }

    /**
     * Bug #2: poin REDEEMED disimpan sebagai angka POSITIF di point_histories
     * — harus DIKURANGKAN dari total earned, bukan ikut dijumlah sebagai poin.
     */
    public function test_current_points_subtracts_redeemed_from_earned(): void
    {
        $reviewer = $this->makeReviewer();

        PointHistory::create([
            'user_id' => $reviewer->id, 'points' => 100, 'type' => 'EARNED',
            'description' => 'Review disetujui',
        ]);
        PointHistory::create([
            'user_id' => $reviewer->id, 'points' => 60, 'type' => 'EARNED',
            'description' => 'Review disetujui lagi',
        ]);
        // Redeemed tersimpan sebagai angka POSITIF (bukan -30).
        PointHistory::create([
            'user_id' => $reviewer->id, 'points' => 30, 'type' => 'REDEEMED',
            'description' => 'Tukar reward',
        ]);

        $leaderboard = app(ReviewerLeaderboardService::class)->build();
        $entry = $leaderboard->firstWhere('id', $reviewer->id);

        $this->assertEquals(160, $entry->total_points_earned, 'total_points_earned harus cuma dari baris type=EARNED');
        $this->assertEquals(130, $entry->current_points, 'current_points harus 160 (earned) - 30 (redeemed) = 130, bukan 190 (earned+redeemed tergabung)');
    }

    public function test_current_points_is_zero_when_no_point_history_exists(): void
    {
        $reviewer = $this->makeReviewer();

        $leaderboard = app(ReviewerLeaderboardService::class)->build();
        $entry = $leaderboard->firstWhere('id', $reviewer->id);

        $this->assertEquals(0, $entry->total_points_earned);
        $this->assertEquals(0, $entry->current_points);
    }

    /**
     * Bug #3: ranking harus berdasarkan poin (current_points), BUKAN
     * tier_score dari reward yang sudah ditukar — reviewer dengan poin lebih
     * tinggi harus di atas, walau reward yang sudah ditukar lebih sedikit.
     */
    public function test_ranking_is_based_on_points_not_reward_tier_count(): void
    {
        $lowPointsButManyRewards = $this->makeReviewer(['name' => 'Banyak Reward Sedikit Poin']);
        $highPointsNoRewards = $this->makeReviewer(['name' => 'Banyak Poin Tanpa Reward']);

        // Reviewer A: poin sedikit (50), tapi sudah tukar 1 reward Bronze.
        PointHistory::create([
            'user_id' => $lowPointsButManyRewards->id, 'points' => 50, 'type' => 'EARNED',
            'description' => 'Review disetujui',
        ]);
        $bronzeReward = Reward::create([
            'name' => 'Voucher Kecil', 'description' => 'test', 'type' => 'voucher',
            'tier' => 'Bronze', 'points_required' => 10, 'is_active' => true,
        ]);
        RewardRedemption::create([
            'user_id' => $lowPointsButManyRewards->id, 'reward_id' => $bronzeReward->id,
            'points_used' => 10, 'status' => 'COMPLETED',
        ]);

        // Reviewer B: poin jauh lebih tinggi (500), belum pernah tukar reward sama sekali.
        PointHistory::create([
            'user_id' => $highPointsNoRewards->id, 'points' => 500, 'type' => 'EARNED',
            'description' => 'Review disetujui',
        ]);

        $leaderboard = app(ReviewerLeaderboardService::class)->build();

        $rankHighPoints = $leaderboard->firstWhere('id', $highPointsNoRewards->id)->rank;
        $rankLowPoints = $leaderboard->firstWhere('id', $lowPointsButManyRewards->id)->rank;

        $this->assertLessThan($rankLowPoints, $rankHighPoints,
            'Reviewer dengan poin lebih tinggi harus rank lebih baik (angka lebih kecil), walau reward yang sudah ditukar lebih sedikit');
        $this->assertEquals(1, $rankHighPoints);
    }

    public function test_reviewer_leaderboard_page_renders_with_correct_rank_and_points(): void
    {
        $reviewer = $this->makeReviewer(['name' => 'Reviewer Utama']);
        PointHistory::create([
            'user_id' => $reviewer->id, 'points' => 200, 'type' => 'EARNED',
            'description' => 'Review disetujui',
        ]);
        $this->makeAssignment(['reviewer_id' => $reviewer->id]);
        $this->makeAssignment(['reviewer_2_id' => $reviewer->id]);

        $this->actingAs($reviewer);
        $response = $this->get(route('reviewer.leaderboard.index'));

        $response->assertOk();
        $response->assertSee('Reviewer Utama');
        $response->assertSee('Rank #1', false);
        $response->assertSee('200', false); // poin
        $response->assertSee('Diurutkan berdasarkan poin tertinggi');
    }

    public function test_admin_and_reviewer_leaderboard_pages_show_consistent_data(): void
    {
        $admin = $this->makeAdmin();
        $reviewer = $this->makeReviewer(['name' => 'Reviewer Konsisten']);
        PointHistory::create([
            'user_id' => $reviewer->id, 'points' => 350, 'type' => 'EARNED',
            'description' => 'Review disetujui',
        ]);
        $this->makeAssignment(['reviewer_3_id' => $reviewer->id]);

        // Halaman reviewer.
        $this->actingAs($reviewer);
        $reviewerResponse = $this->get(route('reviewer.leaderboard.index'));
        $reviewerResponse->assertOk();
        $reviewerResponse->assertSee('350', false);
        $reviewerResponse->assertSee('Reviewer Konsisten');

        // Halaman admin — harus menunjukkan angka yang SAMA (data dari service yang sama).
        $this->actingAs($admin);
        $adminResponse = $this->get(route('admin.leaderboard.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('350', false);
        $adminResponse->assertSee('Reviewer Konsisten');
    }
}

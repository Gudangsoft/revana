<?php

namespace Tests\Feature\Points;

use App\Models\MarketingPointHistory;
use App\Models\PicPointHistory;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 1 konsolidasi 29 Juli 2026 (lihat docs/tests/log-update-2026-07-29.md #7):
 * `PicPointHistory::awardPoints()`/`revokePoints()` dan `MarketingPointHistory::
 * awardPoints()` sekarang delegate tipis ke `PointsService`. Test di sini:
 * (1) membuktikan delegate & service menghasilkan hasil IDENTIK (parity — supaya
 * kalau suatu saat ada yang lupa sinkron, test ini langsung merah), dan
 * (2) menguji `revokeFromMarketing()` yang BARU (sebelumnya tidak ada method
 * setara ini sama sekali di sisi Marketing, salah satu penyebab
 * `SubmissionController::destroy()` cuma mencabut poin Marketing dan diam-diam
 * membiarkan poin PIC "bocor").
 */
class PointsServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    // --- Parity: delegate model lama vs PointsService baru ---

    public function test_award_to_pic_and_legacy_delegate_produce_identical_result(): void
    {
        $picA = $this->makePic();
        $picB = $this->makePic();
        $submissionA = $this->makeSubmission(['petugas_submit_id' => $picA->id]);
        $submissionB = $this->makeSubmission(['petugas_submit_id' => $picB->id]);
        $occurredAt = now()->subDays(2);

        $viaService = PointsService::awardToPic($picA->id, $submissionA->id, 'submit', 'Test', $occurredAt);
        $viaDelegate = PicPointHistory::awardPoints($picB->id, $submissionB->id, 'submit', 'Test', $occurredAt);

        $this->assertNotNull($viaService);
        $this->assertNotNull($viaDelegate);
        $this->assertEquals($viaService->points_earned, $viaDelegate->points_earned);
        $this->assertEquals($viaService->created_at->format('Y-m-d H:i:s'), $viaDelegate->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals($picA->fresh()->total_points, $picB->fresh()->total_points);
    }

    public function test_award_to_marketing_and_legacy_delegate_produce_identical_result(): void
    {
        $mktA = $this->makeMarketing();
        $mktB = $this->makeMarketing();
        $submissionA = $this->makeSubmission(['marketing_id' => $mktA->id]);
        $submissionB = $this->makeSubmission(['marketing_id' => $mktB->id]);
        $occurredAt = now()->subDays(2);

        $viaService = PointsService::awardToMarketing($mktA->id, $submissionA->id, 'Test', $occurredAt);
        $viaDelegate = MarketingPointHistory::awardPoints($mktB->id, $submissionB->id, 'Test', $occurredAt);

        $this->assertNotNull($viaService);
        $this->assertNotNull($viaDelegate);
        $this->assertEquals($viaService->points_earned, $viaDelegate->points_earned);
        $this->assertEquals($mktA->fresh()->total_points, $mktB->fresh()->total_points);
    }

    public function test_revoke_from_pic_and_legacy_delegate_behave_identically(): void
    {
        $picA = $this->makePic();
        $picB = $this->makePic();
        $submissionA = $this->makeSubmission(['petugas_submit_id' => $picA->id]);
        $submissionB = $this->makeSubmission(['petugas_submit_id' => $picB->id]);
        PointsService::awardToPic($picA->id, $submissionA->id, 'submit');
        PointsService::awardToPic($picB->id, $submissionB->id, 'submit');

        $resultService = PointsService::revokeFromPic($picA->id, $submissionA->id, 'submit');
        $resultDelegate = PicPointHistory::revokePoints($picB->id, $submissionB->id, 'submit');

        $this->assertTrue($resultService);
        $this->assertTrue($resultDelegate);
        $this->assertEquals(0, $picA->fresh()->total_points);
        $this->assertEquals(0, $picB->fresh()->total_points);
    }

    // --- awardToPic / awardToMarketing: perilaku inti (idempotensi, backdate) langsung lewat service ---

    public function test_award_to_pic_is_idempotent(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();

        $first = PointsService::awardToPic($pic->id, $submission->id, 'submit', 'First');
        $second = PointsService::awardToPic($pic->id, $submission->id, 'submit', 'Second');

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertEquals(1, PicPointHistory::where('pic_id', $pic->id)->where('submission_id', $submission->id)->count());
    }

    public function test_award_to_marketing_is_idempotent(): void
    {
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission();

        $first = PointsService::awardToMarketing($marketing->id, $submission->id, 'First');
        $second = PointsService::awardToMarketing($marketing->id, $submission->id, 'Second');

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertEquals(1, MarketingPointHistory::where('marketing_id', $marketing->id)->where('submission_id', $submission->id)->count());
    }

    public function test_award_to_pic_backdates_created_at_to_occurred_at(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();
        $occurredAt = now()->subDays(5);

        $history = PointsService::awardToPic($pic->id, $submission->id, 'editor1', 'Validasi', $occurredAt);

        $this->assertEquals($occurredAt->format('Y-m-d H:i:s'), $history->created_at->format('Y-m-d H:i:s'));
    }

    // --- revokeFromMarketing(): BARU, belum pernah ada test karena method-nya belum pernah ada ---

    public function test_revoke_from_marketing_deletes_history_row_and_decrements_total(): void
    {
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);
        PointsService::awardToMarketing($marketing->id, $submission->id, 'Test');
        $this->assertGreaterThan(0, $marketing->fresh()->total_points);

        $result = PointsService::revokeFromMarketing($marketing->id, $submission->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('marketing_point_histories', [
            'marketing_id' => $marketing->id,
            'submission_id' => $submission->id,
        ]);
        $this->assertEquals(0, $marketing->fresh()->total_points);
    }

    public function test_revoke_from_marketing_returns_false_when_nothing_to_revoke(): void
    {
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission();

        $result = PointsService::revokeFromMarketing($marketing->id, $submission->id);

        $this->assertFalse($result);
    }

    public function test_revoke_from_marketing_never_takes_total_points_below_zero(): void
    {
        $marketing = $this->makeMarketing(['total_points' => 0]);
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);
        // Baris riwayat dibuat langsung (bukan lewat awardToMarketing) supaya total_points
        // TIDAK ikut ter-increment — mensimulasikan data yang sudah kadung tidak sinkron.
        MarketingPointHistory::create([
            'marketing_id' => $marketing->id,
            'submission_id' => $submission->id,
            'points_earned' => 5,
            'description' => 'Test drift',
        ]);

        PointsService::revokeFromMarketing($marketing->id, $submission->id);

        $this->assertEquals(0, $marketing->fresh()->total_points);
    }

    public function test_revoke_from_marketing_only_affects_the_targeted_submission(): void
    {
        $marketing = $this->makeMarketing();
        $submissionKeep = $this->makeSubmission(['marketing_id' => $marketing->id]);
        $submissionRevoke = $this->makeSubmission(['marketing_id' => $marketing->id]);
        PointsService::awardToMarketing($marketing->id, $submissionKeep->id, 'Keep');
        PointsService::awardToMarketing($marketing->id, $submissionRevoke->id, 'Revoke');
        $totalBefore = $marketing->fresh()->total_points;

        PointsService::revokeFromMarketing($marketing->id, $submissionRevoke->id);

        $this->assertDatabaseHas('marketing_point_histories', [
            'marketing_id' => $marketing->id,
            'submission_id' => $submissionKeep->id,
        ]);
        $this->assertLessThan($totalBefore, $marketing->fresh()->total_points);
        $this->assertGreaterThan(0, $marketing->fresh()->total_points);
    }
}

<?php

namespace Tests\Feature\Points;

use App\Models\MarketingPointHistory;
use App\Models\TaskPointSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk insiden poin Marketing 27-28 Juli 2026: total_points sempat dihitung
 * dari COUNT(submissions) alih-alih SUM(points_earned) riwayat — cuma benar kalau
 * rate poin per submission tidak pernah berubah. Begitu rate berubah (mis. dari 1
 * ke 0,5), COUNT tidak lagi cocok dengan SUM riwayat yang sebenarnya pernah
 * diberikan, dan sinkronisasi berbasis COUNT diam-diam menurunkan poin yang benar.
 */
class MarketingPointHistoryAwardTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    public function test_award_points_is_idempotent(): void
    {
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission();

        $first = MarketingPointHistory::awardPoints($marketing->id, $submission->id, 'Test award');
        $second = MarketingPointHistory::awardPoints($marketing->id, $submission->id, 'Percobaan kedua');

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertEquals(1, MarketingPointHistory::where('marketing_id', $marketing->id)
            ->where('submission_id', $submission->id)->count());
    }

    public function test_database_rejects_duplicate_marketing_submission_combination(): void
    {
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission();

        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submission->id,
            'points_earned' => 1, 'description' => 'Baris pertama',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submission->id,
            'points_earned' => 1, 'description' => 'Percobaan kembar',
        ]);
    }

    /**
     * Test inti insiden 27 Juli: getActualPoints() HARUS memakai SUM riwayat, BUKAN
     * COUNT submission. Skenario: rate berubah dari waktu ke waktu (persis kasus
     * nyata Risqi/Wandi) — 2 submission diberi poin di rate LAMA (10/submission,
     * total seharusnya 20), lalu rate saat ini diubah ke 0,5. Kalau formula salah
     * pakai COUNT, hasilnya akan 2 (jumlah submission) alih-alih 20 (total yang
     * sebenarnya pernah diberikan) — persis bug yang menyebabkan insiden.
     */
    public function test_get_actual_points_uses_sum_of_history_not_submission_count(): void
    {
        $marketing = $this->makeMarketing();
        // marketing_id di-set eksplisit di kedua submission — supaya formula berbasis
        // COUNT(submissions WHERE marketing_id=...) SAMA-SAMA menghitung 2 submission
        // ini (bukan 0), dan perbedaan hasil murni dari COUNT (2) vs SUM (20).
        $submissionA = $this->makeSubmission(['marketing_id' => $marketing->id]);
        $submissionB = $this->makeSubmission(['marketing_id' => $marketing->id]);

        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submissionA->id,
            'points_earned' => 10, 'description' => 'Diberikan saat rate masih 10',
        ]);
        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submissionB->id,
            'points_earned' => 10, 'description' => 'Diberikan saat rate masih 10',
        ]);

        // Rate SEKARANG berbeda dari rate historis di atas — kalau formula pakai
        // COUNT submission, ini akan membuat getActualPoints() salah menghitung.
        TaskPointSetting::updateOrCreate(
            ['user_type' => 'marketing', 'task_key' => 'submit'],
            ['task_label' => 'Submit', 'points' => 0.5, 'is_active' => true]
        );

        $actual = $marketing->getActualPoints();

        $this->assertEquals(20, $actual, 'getActualPoints() harus SUM riwayat (20), bukan COUNT submission (2) atau rate baru x count');
    }

    public function test_sync_points_does_not_deflate_total_when_rate_changes(): void
    {
        $marketing = $this->makeMarketing(['total_points' => 999]); // nilai lama yang salah, akan dikoreksi
        $submission = $this->makeSubmission();

        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submission->id,
            'points_earned' => 10, 'description' => 'Rate lama',
        ]);

        $marketing->syncPoints();

        $this->assertEquals(10, $marketing->fresh()->total_points);
    }
}

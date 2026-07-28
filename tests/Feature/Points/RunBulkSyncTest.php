<?php

namespace Tests\Feature\Points;

use App\Http\Controllers\Admin\MarketingPointReportController;
use App\Http\Controllers\Admin\PicPointReportController;
use App\Models\MarketingPointHistory;
use App\Models\PicPointHistory;
use App\Models\TaskPointSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Jaminan inti runBulkSync() (PIC & Marketing): sinkronisasi HANYA boleh mengisi
 * riwayat yang benar-benar belum ada (backfill), dan TIDAK PERNAH menimpa ulang
 * points_earned pada baris yang sudah ada, walau rate di TaskPointSetting berubah
 * setelahnya. Versi lama pernah menimpa ulang semua baris setiap kali admin
 * menyimpan setting poin apapun — itu yang menyebabkan insiden penurunan ~70% total
 * poin PIC. Regresi ini mengunci perilaku "backfill-only, never rewrite".
 */
class RunBulkSyncTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    public function test_pic_bulk_sync_never_rewrites_existing_history_when_rate_changes(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'petugas_editor1_id' => $pic->id,
            'editor1_valid' => true,
            'editor1_validated_at' => now(),
        ]);

        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'editor1',
            'points_earned' => 10, 'description' => 'Diberikan saat rate lama',
        ]);

        TaskPointSetting::updateOrCreate(
            ['user_type' => 'pic', 'task_key' => 'editor1'],
            ['task_label' => 'Editor 1', 'points' => 0.1, 'is_active' => true]
        );

        PicPointReportController::runBulkSync();

        $this->assertEquals(10, PicPointHistory::where('pic_id', $pic->id)
            ->where('submission_id', $submission->id)->where('step', 'editor1')->value('points_earned'),
            'runBulkSync() tidak boleh menimpa ulang points_earned baris yang sudah ada');
    }

    public function test_pic_bulk_sync_backfills_missing_history_using_current_rate(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'petugas_editor1_id' => $pic->id,
            'editor1_valid' => true,
            'editor1_validated_at' => now(),
        ]);

        TaskPointSetting::updateOrCreate(
            ['user_type' => 'pic', 'task_key' => 'editor1'],
            ['task_label' => 'Editor 1', 'points' => 0.2, 'is_active' => true]
        );

        $this->assertEquals(0, PicPointHistory::where('submission_id', $submission->id)->count());

        [$backfilled] = PicPointReportController::runBulkSync();

        $this->assertGreaterThan(0, $backfilled);
        $this->assertEquals(0.2, PicPointHistory::where('pic_id', $pic->id)
            ->where('submission_id', $submission->id)->where('step', 'editor1')->value('points_earned'));
    }

    public function test_pic_bulk_sync_does_not_duplicate_existing_row_on_repeated_calls(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'petugas_editor1_id' => $pic->id,
            'editor1_valid' => true,
            'editor1_validated_at' => now(),
        ]);

        PicPointReportController::runBulkSync();
        PicPointReportController::runBulkSync();

        $this->assertEquals(1, PicPointHistory::where('pic_id', $pic->id)
            ->where('submission_id', $submission->id)->where('step', 'editor1')->count());
    }

    public function test_marketing_bulk_sync_never_rewrites_existing_history_when_rate_changes(): void
    {
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);

        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submission->id,
            'points_earned' => 10, 'description' => 'Diberikan saat rate lama',
        ]);

        TaskPointSetting::updateOrCreate(
            ['user_type' => 'marketing', 'task_key' => 'submit'],
            ['task_label' => 'Submit', 'points' => 0.5, 'is_active' => true]
        );

        MarketingPointReportController::runBulkSync();

        $this->assertEquals(10, MarketingPointHistory::where('marketing_id', $marketing->id)
            ->where('submission_id', $submission->id)->value('points_earned'),
            'runBulkSync() tidak boleh menimpa ulang points_earned baris yang sudah ada');
    }

    public function test_marketing_bulk_sync_backfills_missing_history_using_current_rate(): void
    {
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);

        TaskPointSetting::updateOrCreate(
            ['user_type' => 'marketing', 'task_key' => 'submit'],
            ['task_label' => 'Submit', 'points' => 0.5, 'is_active' => true]
        );

        [$created] = MarketingPointReportController::runBulkSync();

        $this->assertGreaterThan(0, $created);
        $this->assertEquals(0.5, MarketingPointHistory::where('marketing_id', $marketing->id)
            ->where('submission_id', $submission->id)->value('points_earned'));
    }

    public function test_marketing_bulk_sync_recalculates_total_points_as_sum_not_count(): void
    {
        $marketing = $this->makeMarketing(['total_points' => 999]);
        $submissionA = $this->makeSubmission(['marketing_id' => $marketing->id]);
        $submissionB = $this->makeSubmission(['marketing_id' => $marketing->id]);

        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submissionA->id,
            'points_earned' => 10, 'description' => 'Rate lama',
        ]);
        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submissionB->id,
            'points_earned' => 10, 'description' => 'Rate lama',
        ]);

        MarketingPointReportController::runBulkSync();

        $this->assertEquals(20, $marketing->fresh()->total_points);
    }
}

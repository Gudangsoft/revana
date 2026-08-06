<?php

namespace Tests\Feature\Points;

use App\Http\Controllers\Admin\MarketingPointReportController;
use App\Http\Controllers\Admin\PicPointReportController;
use App\Models\MarketingPointHistory;
use App\Models\PicPointHistory;
use App\Models\TaskPointSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    /**
     * Regresi 31 Juli 2026: ketika editor1_valid=1 tapi editor1_validated_at NULL
     * (data lama/celah lain), runBulkSync() dulu pakai COALESCE(validated_at, NOW())
     * — jatuh ke NOW() = momen sync ini kebetulan dijalankan, BUKAN tanggal asli.
     * Ditemukan lewat pola nyata: gerombolan puluhan-ratusan submission dgn
     * production_validated_at identik sampai ke detik di production, tersebar di
     * banyak tanggal (satu gerombolan = satu kali runBulkSync() jalan). Diperbaiki:
     * fallback ke submissions.created_at (tanggal submission dibuat) — bukan
     * sempurna, tapi jauh lebih masuk akal & idempotent (tidak berubah tiap re-sync).
     */
    public function test_pic_bulk_sync_falls_back_to_submission_created_at_when_validated_at_missing(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'petugas_editor1_id' => $pic->id,
            'editor1_valid' => true,
            'editor1_validated_at' => null,
        ]);
        DB::table('submissions')->where('id', $submission->id)
            ->update(['created_at' => '2026-03-12 10:41:38']);

        PicPointReportController::runBulkSync();

        $this->assertEquals(
            '2026-03-12 10:41:38',
            PicPointHistory::where('pic_id', $pic->id)
                ->where('submission_id', $submission->id)->where('step', 'editor1')->value('created_at'),
            'Fallback validated_at kosong harus pakai submissions.created_at, bukan NOW()'
        );
        $this->assertEquals(
            '2026-03-12 10:41:38',
            DB::table('submissions')->where('id', $submission->id)->value('editor1_validated_at'),
            'Backfill validated_at yang NULL juga harus konsisten dgn fallback yang sama'
        );
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

    /**
     * Regresi langsung untuk pertanyaan user (28 Juli 2026): "ketika klik sinkron
     * harusnya tidak merubah tanggal kerja". Mengunci lewat runBulkSync() (dipakai
     * TaskPointSettingController & syncAllAndLogout() — tombol backfill manual di
     * /admin/sync sendiri sudah dihapus total di section #38): baris yang tanggalnya
     * SUDAH BENAR harus tetap sama persis setelah sinkron — bukan ikut tertimpa ke
     * tanggal sinkron dijalankan.
     */
    public function test_run_bulk_sync_does_not_change_created_at_of_already_correct_history(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'petugas_editor1_id' => $pic->id,
            'editor1_valid' => true,
            'editor1_validated_at' => '2026-07-20 09:00:00',
        ]);

        // Insert langsung via query builder (bukan Eloquent create()) supaya created_at
        // benar-benar ter-set ke tanggal yang sudah cocok dengan validated_at — created_at
        // bukan kolom fillable di PicPointHistory, jadi create() akan mengabaikannya.
        $historyId = DB::table('pic_point_histories')->insertGetId([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'editor1',
            'points_earned' => 1, 'description' => 'sudah benar',
            'created_at' => '2026-07-20 09:00:00', 'updated_at' => '2026-07-20 09:00:00',
        ]);

        PicPointReportController::runBulkSync();

        $this->assertEquals(
            '2026-07-20 09:00:00',
            DB::table('pic_point_histories')->where('id', $historyId)->value('created_at'),
            'runBulkSync() tidak boleh mengubah tanggal riwayat yang sudah benar'
        );
    }

    /**
     * Pasangan test di atas: kalau tanggalnya SUDAH TELANJUR SALAH (mis. dari insiden
     * sebelum fix section #21), runBulkSync() harus MEMPERBAIKI ke tanggal validasi
     * asli — bukan membiarkannya salah, dan bukan pula menimpanya ke tanggal sinkron
     * dijalankan.
     */
    public function test_run_bulk_sync_repairs_mismatched_created_at_to_true_validated_at(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'petugas_editor1_id' => $pic->id,
            'editor1_valid' => true,
            'editor1_validated_at' => '2026-07-21 10:00:00',
        ]);

        $historyId = DB::table('pic_point_histories')->insertGetId([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'editor1',
            'points_earned' => 1, 'description' => 'telanjur salah tanggal',
            'created_at' => '2026-07-25 15:00:00', 'updated_at' => '2026-07-25 15:00:00',
        ]);

        PicPointReportController::runBulkSync();

        $this->assertEquals(
            '2026-07-21 10:00:00',
            DB::table('pic_point_histories')->where('id', $historyId)->value('created_at'),
            'runBulkSync() harus memperbaiki tanggal yang salah ke tanggal validasi asli, bukan ke tanggal sinkron dijalankan'
        );
    }
}

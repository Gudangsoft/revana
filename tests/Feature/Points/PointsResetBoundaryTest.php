<?php

namespace Tests\Feature\Points;

use App\Http\Controllers\Admin\MarketingPointReportController;
use App\Http\Controllers\Admin\PicPointReportController;
use App\Models\PicPointHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regresi insiden 29 Juli 2026 (docs/tests/log-update-2026-07-29.md #4): admin
 * menjalankan "Reset Semua Point" untuk PIC & Marketing supaya poin mulai dihitung
 * ulang dari 0, tapi backfill (`runBulkSync()`, `PicPointController::syncMyPoints()`)
 * tidak tahu kapan reset terjadi — jadi membangun ulang SEMUA riwayat lama yang
 * submission-nya masih ada di database, seolah "belum tercatat". ~112.000 baris poin
 * yang sengaja dihapus "hidup lagi".
 *
 * Perbaikan: kolom `points_reset_at` dicatat oleh `resetAllPoints()`, dan semua jalur
 * backfill sekarang mengabaikan submission yang lebih tua dari tanggal itu untuk
 * PIC/marketing yang bersangkutan.
 */
class PointsResetBoundaryTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($admin);

        return $admin;
    }

    public function test_reset_all_points_records_points_reset_at_for_pic_and_marketing(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic();
        $marketing = $this->makeMarketing();

        $this->assertNull($pic->fresh()->points_reset_at);
        $this->assertNull($marketing->fresh()->points_reset_at);

        $this->post(route('admin.pic-points.reset-all'), ['konfirmasi' => 'RESET']);
        $this->post(route('admin.marketing-points.reset-all'), ['konfirmasi' => 'RESET']);

        $this->assertNotNull($pic->fresh()->points_reset_at);
        $this->assertNotNull($marketing->fresh()->points_reset_at);
    }

    public function test_pic_bulk_sync_does_not_resurrect_submissions_older_than_reset(): void
    {
        $pic = $this->makePic([
            'total_points' => 0,
            'points_reset_at' => now(),
        ]);

        // Submission ASLI dari sebelum reset — di production, ini tetap ada di tabel
        // submissions walau riwayat poinnya sudah dihapus reset.
        $oldSubmission = $this->makeSubmission(['petugas_submit_id' => $pic->id]);
        DB::table('submissions')->where('id', $oldSubmission->id)
            ->update(['created_at' => now()->subDays(30)]);

        [$backfilled] = PicPointReportController::runBulkSync();

        $this->assertSame(0, DB::table('pic_point_histories')->where('pic_id', $pic->id)->count());
        $this->assertEquals(0, $pic->fresh()->total_points);
    }

    public function test_pic_bulk_sync_still_backfills_submissions_created_after_reset(): void
    {
        $resetAt = now()->subHours(2);
        $pic = $this->makePic([
            'total_points' => 0,
            'points_reset_at' => $resetAt,
        ]);

        // Submission BARU, dibuat setelah reset — aktivitas asli periode baru, HARUS
        // tetap ter-backfill kalau belum tercatat karena sebab lain (mis. bug terpisah).
        $newSubmission = $this->makeSubmission(['petugas_submit_id' => $pic->id]);
        DB::table('submissions')->where('id', $newSubmission->id)
            ->update(['created_at' => $resetAt->copy()->addHour()]);

        [$backfilled] = PicPointReportController::runBulkSync();

        // NB: runBulkSync() sendiri tidak menghitung ulang total_points (itu tanggung
        // jawab pemanggilnya, TaskPointSettingController::syncTotals()) — cukup pastikan
        // baris riwayatnya benar-benar dibuat.
        $this->assertGreaterThan(0, $backfilled);
        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id,
            'submission_id' => $newSubmission->id,
            'step' => 'submit',
        ]);
    }

    public function test_pic_bulk_sync_backfills_normally_when_never_reset(): void
    {
        $pic = $this->makePic(['total_points' => 0, 'points_reset_at' => null]);
        $oldSubmission = $this->makeSubmission(['petugas_submit_id' => $pic->id]);
        DB::table('submissions')->where('id', $oldSubmission->id)
            ->update(['created_at' => now()->subDays(200)]);

        PicPointReportController::runBulkSync();

        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id,
            'submission_id' => $oldSubmission->id,
            'step' => 'submit',
        ]);
    }

    public function test_marketing_bulk_sync_does_not_resurrect_submissions_older_than_reset(): void
    {
        $marketing = $this->makeMarketing([
            'total_points' => 0,
            'points_reset_at' => now(),
        ]);
        $oldSubmission = $this->makeSubmission(['marketing_id' => $marketing->id]);
        DB::table('submissions')->where('id', $oldSubmission->id)
            ->update(['created_at' => now()->subDays(30)]);

        MarketingPointReportController::runBulkSync();

        $this->assertSame(0, DB::table('marketing_point_histories')->where('marketing_id', $marketing->id)->count());
        $this->assertEquals(0, $marketing->fresh()->total_points);
    }

    public function test_marketing_bulk_sync_still_backfills_submissions_created_after_reset(): void
    {
        $resetAt = now()->subHours(2);
        $marketing = $this->makeMarketing(['total_points' => 0, 'points_reset_at' => $resetAt]);
        $newSubmission = $this->makeSubmission(['marketing_id' => $marketing->id]);
        DB::table('submissions')->where('id', $newSubmission->id)
            ->update(['created_at' => $resetAt->copy()->addHour()]);

        MarketingPointReportController::runBulkSync();

        $this->assertDatabaseHas('marketing_point_histories', [
            'marketing_id' => $marketing->id,
            'submission_id' => $newSubmission->id,
        ]);
    }

    public function test_pic_sync_my_points_does_not_resurrect_submissions_older_than_reset(): void
    {
        $pic = $this->makePic(['total_points' => 0, 'points_reset_at' => now()]);
        $oldSubmission = $this->makeSubmission(['petugas_submit_id' => $pic->id]);
        DB::table('submissions')->where('id', $oldSubmission->id)
            ->update(['created_at' => now()->subDays(30)]);

        $this->actingAs($pic, 'pic')
            ->post(route('pic.points.sync'))
            ->assertRedirect();

        $this->assertSame(0, PicPointHistory::where('pic_id', $pic->id)->count());
        $this->assertEquals(0, $pic->fresh()->total_points);
    }

    public function test_pic_sync_my_points_still_backfills_submissions_after_reset(): void
    {
        $resetAt = now()->subHours(2);
        $pic = $this->makePic(['total_points' => 0, 'points_reset_at' => $resetAt]);
        $newSubmission = $this->makeSubmission(['petugas_submit_id' => $pic->id]);
        DB::table('submissions')->where('id', $newSubmission->id)
            ->update(['created_at' => $resetAt->copy()->addHour()]);

        $this->actingAs($pic, 'pic')
            ->post(route('pic.points.sync'))
            ->assertRedirect();

        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id,
            'submission_id' => $newSubmission->id,
            'step' => 'submit',
        ]);
    }
}

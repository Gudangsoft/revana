<?php

namespace Tests\Feature\Points;

use App\Models\PicPointHistory;
use App\Models\User;
use App\Support\PointsAutoSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * PointsAutoSync — jaring pengaman TANPA cron (section #40): cron scheduler
 * server tidak pernah terdeteksi aktif (dicek langsung oleh user di /admin/sync),
 * jadi points:auto-sync yang terjadwal tidak pernah benar-benar jalan otomatis.
 * Solusinya: AdminMiddleware (dipakai di SEMUA route admin) memicu sinkronisasi
 * yang sama lewat runIfDue() — cukup ada admin yang membuka halaman apa pun,
 * tidak perlu setel apa pun di panel hosting.
 */
class PointsAutoSyncTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Test Admin', 'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $this->actingAs($admin);
        return $admin;
    }

    public function test_run_if_due_syncs_when_never_run_before(): void
    {
        $pic = $this->makePic(['total_points' => 0]);
        $submission = $this->makeSubmission();
        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
            'points_earned' => 0.25, 'description' => 'test',
        ]);

        PointsAutoSync::runIfDue();

        $this->assertEquals(0.25, $pic->fresh()->total_points);
    }

    public function test_run_if_due_is_throttled_within_15_minutes(): void
    {
        $pic = $this->makePic(['total_points' => 0]);
        $submission = $this->makeSubmission();
        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
            'points_earned' => 0.25, 'description' => 'test',
        ]);

        PointsAutoSync::runIfDue();
        $this->assertEquals(0.25, $pic->fresh()->total_points);

        // Riwayat baru muncul TAPI belum 15 menit sejak terakhir jalan — runIfDue()
        // harus diam, tidak ikut mengoreksi yang ini sampai throttle habis.
        // refresh() dulu supaya objek di memori sinkron dengan DB (0.25, hasil raw
        // SQL update di dalam run()) — tanpa ini, update(0) di bawah dianggap
        // Eloquent "tidak ada perubahan" (0 == 0 in-memory) dan diam-diam tidak
        // benar-benar mengirim query UPDATE, walau tetap mengembalikan true.
        $pic->refresh()->update(['total_points' => 0]);
        PointsAutoSync::runIfDue();

        $this->assertEquals(0, $pic->fresh()->total_points,
            'runIfDue() harus diam kalau belum 15 menit sejak terakhir jalan');
    }

    public function test_run_if_due_does_not_resurrect_old_data_after_reset(): void
    {
        $pic = $this->makePic(['total_points' => 0]);
        // Submission tervalidasi tanpa riwayat sama sekali (persis kondisi setelah
        // "Reset Semua Point") — runIfDue() TIDAK BOLEH membuat riwayat baru.
        $this->makeSubmission([
            'petugas_editor1_id' => $pic->id,
            'editor1_valid' => true,
            'editor1_validated_at' => now()->subMonths(2),
        ]);

        PointsAutoSync::runIfDue();

        $this->assertEquals(0, PicPointHistory::where('pic_id', $pic->id)->count());
        $this->assertEquals(0, $pic->fresh()->total_points);
    }

    /**
     * Test integrasi ujung-ke-ujung: admin membuka halaman admin APA SAJA (bukan
     * /admin/sync) — AdminMiddleware harus otomatis memicu sinkronisasi lewat
     * terminate(), tanpa admin perlu tahu atau melakukan apa pun.
     */
    public function test_visiting_any_admin_page_triggers_sync_via_middleware(): void
    {
        $this->actingAsAdmin();

        $pic = $this->makePic(['total_points' => 0]);
        $submission = $this->makeSubmission();
        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
            'points_earned' => 0.25, 'description' => 'test',
        ]);

        $this->assertNull(Cache::get('points.auto_sync.last_run_at'));

        // Halaman admin biasa, BUKAN /admin/sync — buktikan trigger-nya global,
        // bukan cuma di halaman sinkronisasi itu sendiri.
        $this->get(route('admin.pic-points.index'))->assertOk();

        $this->assertEquals(0.25, $pic->fresh()->total_points,
            'AdminMiddleware harus memicu PointsAutoSync lewat terminate() di halaman admin manapun');
        $this->assertNotNull(Cache::get('points.auto_sync.last_run_at'));
    }
}

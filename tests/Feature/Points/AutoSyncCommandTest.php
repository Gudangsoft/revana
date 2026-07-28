<?php

namespace Tests\Feature\Points;

use App\Models\MarketingPointHistory;
use App\Models\PicPointHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Command `points:auto-sync` — jaring pengaman berkala (lewat scheduler, tiap 15
 * menit) untuk kasus seperti "Eko Siswanto PIC": riwayat poin benar tapi
 * total_points tidak ikut ter-update (data lama yang sudah kadung desync SEBELUM
 * perbaikan atomik di awardPoints()). Perbaikan atomik mencegah desync BARU;
 * command ini membetulkan data yang SUDAH desync, otomatis tanpa perlu klik manual.
 *
 * PENTING: command ini HANYA boleh recompute total_points dari riwayat yang SUDAH
 * ADA — TIDAK BOLEH membuat riwayat baru dari submission lama (itu tugas backfill
 * manual di /admin/sync). Versi awal salah memakai runFullSync() yang juga
 * membackfill, sehingga begitu admin reset semua poin ke 0, auto-sync tiap 15 menit
 * langsung membangun ulang semua data lama — persis kebalikan dari yang diinginkan.
 */
class AutoSyncCommandTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    public function test_auto_sync_command_fixes_pic_with_correct_history_but_wrong_total(): void
    {
        // Persis kasus Eko Siswanto: riwayat benar (+0.25), total_points salah (0).
        $pic = $this->makePic(['total_points' => 0]);
        $submission = $this->makeSubmission();
        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
            'points_earned' => 0.25, 'description' => 'Submit artikel',
        ]);

        $this->artisan('points:auto-sync')->assertSuccessful();

        $this->assertEquals(0.25, $pic->fresh()->total_points);
    }

    public function test_auto_sync_command_fixes_marketing_with_correct_history_but_wrong_total(): void
    {
        $marketing = $this->makeMarketing(['total_points' => 0]);
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);
        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submission->id,
            'points_earned' => 0.5, 'description' => 'Submit artikel',
        ]);

        $this->artisan('points:auto-sync')->assertSuccessful();

        $this->assertEquals(0.5, $marketing->fresh()->total_points);
    }

    public function test_auto_sync_command_is_quiet_when_everything_already_synced(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();
        PicPointHistory::awardPoints($pic->id, $submission->id, 'submit', 'test');

        $this->artisan('points:auto-sync')
            ->expectsOutputToContain('sudah sinkron')
            ->assertSuccessful();
    }

    /**
     * Test PALING PENTING: simulasi persis kondisi setelah admin klik "Reset Semua
     * Point" — submission tervalidasi ada, TAPI riwayat poinnya sudah dihapus
     * (submission_id-nya sekarang "yatim", tidak ada di pic_point_histories sama
     * sekali). Auto-sync TIDAK BOLEH membuat riwayat baru untuk submission ini —
     * total_points harus tetap 0, persis seperti yang diharapkan setelah reset.
     */
    public function test_auto_sync_command_does_not_resurrect_old_data_after_reset(): void
    {
        $pic = $this->makePic(['total_points' => 0]);
        // Submission dengan step editor1 sudah tervalidasi (persis data lama sebelum
        // reset) — TAPI sengaja TIDAK ada baris di pic_point_histories untuk ini,
        // mensimulasikan kondisi setelah PicPointHistory::truncate().
        $this->makeSubmission([
            'petugas_editor1_id' => $pic->id,
            'editor1_valid' => true,
            'editor1_validated_at' => now()->subMonths(2),
        ]);

        $this->artisan('points:auto-sync')->assertSuccessful();

        $this->assertEquals(0, \App\Models\PicPointHistory::where('pic_id', $pic->id)->count(),
            'Auto-sync tidak boleh membuat riwayat baru dari submission lama setelah reset');
        $this->assertEquals(0, $pic->fresh()->total_points,
            'total_points harus tetap 0 setelah reset, bukan dibangun ulang dari submission lama');
    }
}

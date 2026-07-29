<?php

namespace Tests\Feature\Points;

use App\Models\PicPointHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Audit 29 Juli 2026 (lanjutan diskusi setelah insiden reset — lihat
 * docs/tests/log-update-2026-07-29.md #4-#6): user minta seluruh halaman terkait poin
 * PIC/Marketing konsisten 2 desimal (supaya tidak ada lagi kejadian angka tampil
 * dibulatkan/salah seperti kasus Risqi 26,5 -> "27"), dan tombol "Hitung Ulang Semua
 * Point PIC" yang menimpa ulang riwayat lama ke rate saat ini (kelas bug yang sama
 * dengan insiden "poin anjlok" 28 Juli) dihapus total dari sistem.
 */
class PointsDisplayAuditTest extends TestCase
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

    public function test_recalculate_all_points_route_no_longer_exists(): void
    {
        $this->assertFalse(Route::has('admin.pic-points.recalculate-all'));
    }

    public function test_pic_points_index_does_not_offer_recalculate_all_button(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.pic-points.index'));

        $response->assertOk();
        $response->assertDontSee('Hitung Ulang');
        $response->assertDontSee('recalculateAllPointsModal', false);
    }

    public function test_task_point_settings_page_does_not_reference_removed_sync_ulang_poin_feature(): void
    {
        // Regresi: setelah tombol "Hitung Ulang Semua Point PIC" dihapus, teks info di
        // halaman /admin/task-point-settings masih menuding fitur "Sync Ulang Poin" yang
        // tidak ada lagi (menyesatkan admin yang mengikuti link-nya).
        $this->actingAsAdmin();

        $response = $this->get(route('admin.task-point-settings.index'));

        $response->assertOk();
        $response->assertDontSee('Sync Ulang Poin');
    }

    public function test_pic_points_index_shows_fractional_total_without_rounding(): void
    {
        $this->actingAsAdmin();
        $this->makePic(['total_points' => 12.55, 'is_active' => true]);

        $response = $this->get(route('admin.pic-points.index'));

        $response->assertOk();
        $response->assertSee('12.55');
    }

    public function test_marketing_points_index_shows_fractional_total_without_rounding(): void
    {
        $this->actingAsAdmin();
        $this->makeMarketing(['total_points' => 12.55, 'is_active' => true]);

        $response = $this->get(route('admin.marketing-points.index'));

        $response->assertOk();
        $response->assertSee('12.55');
    }

    public function test_pic_activity_report_shows_fractional_points_without_rounding(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic(['total_points' => 0, 'is_active' => true]);
        $submission = $this->makeSubmission(['petugas_submit_id' => $pic->id]);
        PicPointHistory::create([
            'pic_id' => $pic->id,
            'submission_id' => $submission->id,
            'step' => 'submit',
            'points_earned' => 6.25,
            'description' => 'Test',
        ]);

        $response = $this->get(route('admin.pics.activity-report'));

        $response->assertOk();
        $response->assertSee('6.25');
    }

    public function test_laporan_kinerja_shows_fractional_points_without_rounding(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic(['total_points' => 0, 'is_active' => true]);
        $submission = $this->makeSubmission(['petugas_submit_id' => $pic->id]);
        PicPointHistory::create([
            'pic_id' => $pic->id,
            'submission_id' => $submission->id,
            'step' => 'submit',
            'points_earned' => 6.25,
            'description' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('admin.laporan-kinerja.index'));

        $response->assertOk();
        $response->assertSee('6.25');
    }

    public function test_laporan_kinerja_pic_name_links_to_detail_page(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic(['total_points' => 0, 'is_active' => true]);
        $submission = $this->makeSubmission(['petugas_submit_id' => $pic->id]);
        PicPointHistory::create([
            'pic_id' => $pic->id,
            'submission_id' => $submission->id,
            'step' => 'submit',
            'points_earned' => 1,
            'description' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('admin.laporan-kinerja.index'));

        $response->assertOk();
        $response->assertSee(route('admin.pic-points.show', $pic->id), false);
    }
}

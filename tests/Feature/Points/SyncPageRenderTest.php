<?php

namespace Tests\Feature\Points;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Riwayat fitur: 7 tombol sinkronisasi point yang tersebar sempat dikonsolidasi jadi
 * 1 tombol di /admin/sync (section #22) — lalu tombol backfill manual itu SENDIRI
 * dihapus total (section #38) karena berisiko membangun ulang data lama yang sudah
 * sengaja direset admin. Sinkronisasi total_points sekarang murni lewat auto-sync
 * otomatis (scheduler, hanya recompute dari riwayat yang SUDAH ADA, tidak backfill).
 */
class SyncPageRenderTest extends TestCase
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

    public function test_sync_page_no_longer_has_manual_point_sync_button(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.sync.index'));

        $response->assertOk();
        $response->assertDontSee('Sinkronisasi Point (PIC & Marketing)', false);
    }

    public function test_manual_point_sync_routes_no_longer_exist(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.sync.points'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.sync.all'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.pic-points.sync-all'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.marketing-points.sync-all'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.sync-and-logout'),
            '"Sinkronkan & Logout" di modal logout admin juga memicu backfill penuh — dihapus total');
    }

    /**
     * Modal "Sebelum Logout" (dengan pilihan "Sinkronkan & Logout") dihapus total —
     * itu jalur LAIN yang juga memicu backfill penuh (runBulkSync()) setiap kali
     * dipakai, kemungkinan turut menyebabkan data lama "muncul lagi" setelah reset.
     * Admin sekarang logout langsung tanpa modal, sama seperti PIC/Marketing/Reviewer.
     */
    public function test_admin_page_renders_without_sync_and_logout_modal(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.sync.index'));

        $response->assertOk();
        $response->assertDontSee('id="logoutModal"', false);
        $response->assertDontSee('Sinkronkan & Logout', false);
        $response->assertDontSee('Sebelum Logout', false);
    }

    public function test_pic_points_page_renders_without_removed_button(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.pic-points.index'));

        $response->assertOk();
        $response->assertDontSee('id="syncPointForm"', false);
    }

    public function test_marketing_points_page_renders_without_removed_button(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.marketing-points.index'));

        $response->assertOk();
    }

    public function test_team_performance_report_renders_without_sync_link(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.team-performance', ['step' => 'pic']));

        $response->assertOk();
        $response->assertDontSee('Sinkronisasi Point', false);
    }

    public function test_team_marketing_performance_report_renders_without_sync_link(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.team-marketing-performance'));

        $response->assertOk();
        $response->assertDontSee('Sinkronisasi Point', false);
    }

    /**
     * Indikator "kapan auto-sync otomatis terakhir jalan" — dipakai admin untuk
     * memastikan cron scheduler server aktif tanpa perlu buka log/SSH.
     */
    public function test_sync_page_shows_warning_when_auto_sync_never_ran(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.sync.index'));

        $response->assertOk();
        $response->assertSee('belum pernah terdeteksi berjalan');
    }

    public function test_sync_page_shows_last_run_time_after_auto_sync_command_runs(): void
    {
        $this->actingAsAdmin();
        $this->artisan('points:auto-sync')->assertSuccessful();

        $response = $this->get(route('admin.sync.index'));

        $response->assertOk();
        $response->assertSee('menit yang lalu');
        $response->assertDontSee('belum pernah terdeteksi berjalan');
    }
}

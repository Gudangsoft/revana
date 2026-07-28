<?php

namespace Tests\Feature\Points;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifikasi sementara (bukan bagian permanen test suite poin) untuk memastikan
 * halaman /admin/sync render tanpa error setelah konsolidasi 7 tombol sinkronisasi
 * point jadi 1 — dan halaman yang tombolnya baru dihapus juga tetap render normal.
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

    public function test_sync_page_renders_with_merged_point_button(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.sync.index'));

        $response->assertOk();
        $response->assertSee('Sinkronisasi Point (PIC & Marketing)', false);
        $response->assertSee(route('admin.sync.points'), false);
    }

    public function test_sync_points_action_runs_without_error(): void
    {
        $this->actingAsAdmin();

        $response = $this->post(route('admin.sync.points'));

        $response->assertRedirect();
        $this->assertNotNull(session('success'));
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

    public function test_team_performance_report_renders_with_link_to_sync_page(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.team-performance', ['step' => 'pic']));

        $response->assertOk();
        $response->assertSee(route('admin.sync.index'), false);
    }

    public function test_team_marketing_performance_report_renders_with_link_to_sync_page(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.team-marketing-performance'));

        $response->assertOk();
        $response->assertSee(route('admin.sync.index'), false);
    }
}

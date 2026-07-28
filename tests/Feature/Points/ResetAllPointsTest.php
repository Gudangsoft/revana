<?php

namespace Tests\Feature\Points;

use App\Models\MarketingPointHistory;
use App\Models\PicPointHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Reset Semua Point" (PIC & Marketing) — dipakai user untuk benar-benar mengosongkan
 * poin ke 0 dan MEMBIARKANNYA (bukan dibangun ulang lewat sync), lewat tombol yang
 * sudah ada di /admin/pic-points dan yang baru dibuat untuk /admin/marketing-points.
 * Wajib ketik "RESET" untuk konfirmasi — mengunci supaya tidak ke-trigger tanpa sengaja.
 */
class ResetAllPointsTest extends TestCase
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

    public function test_pic_reset_requires_typed_confirmation(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic(['total_points' => 5]);
        $submission = $this->makeSubmission();
        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'editor1',
            'points_earned' => 5, 'description' => 'test',
        ]);

        $response = $this->post(route('admin.pic-points.reset-all'), ['konfirmasi' => 'salah']);

        $response->assertSessionHasErrors('konfirmasi');
        $this->assertEquals(1, PicPointHistory::count(), 'Tanpa konfirmasi yang benar, riwayat tidak boleh terhapus');
        $this->assertEquals(5, $pic->fresh()->total_points);
    }

    public function test_pic_reset_truncates_history_and_zeroes_totals(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic(['total_points' => 5]);
        $submission = $this->makeSubmission();
        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'editor1',
            'points_earned' => 5, 'description' => 'test',
        ]);

        $response = $this->post(route('admin.pic-points.reset-all'), ['konfirmasi' => 'RESET']);

        $response->assertRedirect(route('admin.pic-points.index'));
        $this->assertEquals(0, PicPointHistory::count());
        $this->assertEquals(0, $pic->fresh()->total_points);
    }

    public function test_marketing_reset_requires_typed_confirmation(): void
    {
        $this->actingAsAdmin();
        $marketing = $this->makeMarketing(['total_points' => 10]);
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);
        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submission->id,
            'points_earned' => 10, 'description' => 'test',
        ]);

        $response = $this->post(route('admin.marketing-points.reset-all'), ['konfirmasi' => 'salah']);

        $response->assertSessionHasErrors('konfirmasi');
        $this->assertEquals(1, MarketingPointHistory::count());
        $this->assertEquals(10, $marketing->fresh()->total_points);
    }

    public function test_marketing_reset_truncates_history_and_zeroes_totals(): void
    {
        $this->actingAsAdmin();
        $marketing = $this->makeMarketing(['total_points' => 10]);
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);
        MarketingPointHistory::create([
            'marketing_id' => $marketing->id, 'submission_id' => $submission->id,
            'points_earned' => 10, 'description' => 'test',
        ]);

        $response = $this->post(route('admin.marketing-points.reset-all'), ['konfirmasi' => 'RESET']);

        $response->assertRedirect(route('admin.marketing-points.index'));
        $this->assertEquals(0, MarketingPointHistory::count());
        $this->assertEquals(0, $marketing->fresh()->total_points);
    }

    public function test_pic_points_page_shows_reset_button_and_marketing_points_page_too(): void
    {
        $this->actingAsAdmin();

        $picResponse = $this->get(route('admin.pic-points.index'));
        $picResponse->assertOk();
        $picResponse->assertSee('Reset Semua Point');

        $mktResponse = $this->get(route('admin.marketing-points.index'));
        $mktResponse->assertOk();
        $mktResponse->assertSee('Reset Semua Point');
    }
}

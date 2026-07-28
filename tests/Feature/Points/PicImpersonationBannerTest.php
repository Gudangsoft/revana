<?php

namespace Tests\Feature\Points;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Saat admin "login as" seorang PIC (impersonasi), harus ada tombol "Kembali ke
 * Admin" yang langsung terlihat di halaman PIC manapun — sebelumnya cuma ada badge
 * pasif di navbar (aksi sebenarnya tersembunyi di dalam dropdown profil), berbeda
 * dari layout Marketing yang sudah punya banner penuh + tombol langsung terlihat.
 */
class PicImpersonationBannerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    public function test_return_to_admin_banner_shows_when_impersonating(): void
    {
        $pic = $this->makePic();
        $this->actingAs($pic, 'pic');
        session(['admin_impersonating' => 1]);

        $response = $this->get(route('pic.points.index'));

        $response->assertOk();
        $response->assertSee('Mode Admin');
        $response->assertSee('Kembali ke Admin');
        $response->assertSee(route('admin.pics.return-to-admin'), false);
    }

    public function test_return_to_admin_banner_hidden_for_normal_pic_session(): void
    {
        $pic = $this->makePic();
        $this->actingAs($pic, 'pic');

        $response = $this->get(route('pic.points.index'));

        $response->assertOk();
        $response->assertDontSee('Kembali ke Admin');
    }
}

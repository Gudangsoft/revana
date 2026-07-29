<?php

namespace Tests\Feature\Points;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi 29 Juli 2026: user melaporkan halaman "Detail Point Marketing"
 * (/admin/marketing-points/{id}) untuk marketing "Risqi" menampilkan Total Point "27"
 * padahal seharusnya 0,5 x 53 submission = 26,5. Root cause: BUKAN data yang salah —
 * `number_format($stats['total_points'])` di blade dipanggil TANPA parameter desimal,
 * jadi PHP membulatkan 26.5 -> "27" (round half away from zero). Data aslinya (26.5)
 * sudah benar; ini murni bug tampilan, sama persis dengan kelas bug yang sudah diperbaiki
 * di halaman PIC/marketing-facing pada docs/tests/log-update-2026-07-28.md #13 — tapi
 * halaman detail ADMIN (bukan halaman milik PIC/marketing sendiri) ternyata tidak ikut
 * disisir saat itu.
 */
class AdminDetailPageDecimalDisplayTest extends TestCase
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

    public function test_marketing_detail_page_shows_fractional_total_point_without_rounding(): void
    {
        $this->actingAsAdmin();
        $marketing = $this->makeMarketing(['total_points' => 26.5]);

        // 53 baris @ 0.5 supaya "Submission" (COUNT, integer) dan "Total Point" (SUM,
        // pecahan) sama-sama cocok dengan skenario nyata yang dilaporkan user.
        for ($i = 0; $i < 53; $i++) {
            $submission = $this->makeSubmission();
            \App\Models\MarketingPointHistory::create([
                'marketing_id' => $marketing->id,
                'submission_id' => $submission->id,
                'points_earned' => 0.5,
                'description' => 'Test submission',
            ]);
        }

        $response = $this->get(route('admin.marketing-points.show', $marketing));

        $response->assertOk();
        $response->assertSee('26.50');
        $response->assertDontSee('>27<', false);
    }

    public function test_pic_detail_page_shows_fractional_total_point_without_rounding(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic(['total_points' => 26.5]);

        $response = $this->get(route('admin.pic-points.show', $pic));

        $response->assertOk();
        $response->assertSee('26.50');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur baru 9 Sept 2026: dashboard reviewer sekarang juga menampilkan
 * padanan Rupiah dari Total Points & Available Points (sebelumnya cuma
 * angka poin polos, padahal nilai tukar poin-ke-Rupiah (`point_value`,
 * dikelola admin di /admin/point-settings) sudah dipakai di halaman
 * reviewer lain — tasks/index.blade.php & rewards/index.blade.php — jadi
 * dashboard mengikuti konvensi tampilan yang sama: "≈ Rp {angka}".
 */
class ReviewerDashboardPointsRupiahTest extends TestCase
{
    use RefreshDatabase;

    private function makeReviewer(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Reviewer Test ' . uniqid(),
            'email' => 'reviewer-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'reviewer',
            'total_points' => 0,
            'available_points' => 0,
            'completed_reviews' => 0,
        ], $overrides));
    }

    public function test_dashboard_shows_rupiah_equivalent_for_total_and_available_points(): void
    {
        Setting::set('point_value', 1500);

        $reviewer = $this->makeReviewer([
            'total_points' => 40,
            'available_points' => 25,
        ]);
        $this->actingAs($reviewer);

        $response = $this->get(route('reviewer.dashboard'));

        $response->assertOk();
        // 40 * 1500 = 60.000, 25 * 1500 = 37.500
        $response->assertSee('Rp 60.000', false);
        $response->assertSee('Rp 37.500', false);
    }

    public function test_dashboard_uses_default_point_value_when_setting_not_configured(): void
    {
        // Tidak set Setting::set('point_value', ...) sama sekali — harus
        // fallback ke default 1000, sama seperti konvensi di reviewer/tasks
        // & reviewer/rewards.
        $reviewer = $this->makeReviewer([
            'total_points' => 10,
            'available_points' => 10,
        ]);
        $this->actingAs($reviewer);

        $response = $this->get(route('reviewer.dashboard'));

        $response->assertOk();
        // 10 * 1000 (default) = 10.000
        $response->assertSee('Rp 10.000', false);
    }

    public function test_dashboard_shows_zero_rupiah_when_reviewer_has_no_points(): void
    {
        Setting::set('point_value', 2000);

        $reviewer = $this->makeReviewer([
            'total_points' => 0,
            'available_points' => 0,
        ]);
        $this->actingAs($reviewer);

        $response = $this->get(route('reviewer.dashboard'));

        $response->assertOk();
        $response->assertSee('Rp 0', false);
    }
}

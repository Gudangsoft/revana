<?php

namespace Tests\Feature\Points;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi: filter Bulan+Tahun di /admin/laporan-kinerja sempat diartikan sebagai
 * periode cutoff 26-25 (section #13) — tapi ini membingungkan karena data di
 * hari-hari terakhir bulan berjalan jadi tidak muncul sampai "bulan berikutnya"
 * dipilih. Direvert ke kalender biasa (section #36) atas permintaan user. Test ini
 * mengunci supaya tidak balik ke cutoff tanpa sengaja di masa depan.
 */
class LaporanKinerjaPeriodTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Test Admin', 'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $this->actingAs($admin);
        return $admin;
    }

    public function test_bulan_filter_covers_full_calendar_month_not_cutoff_26_25(): void
    {
        $this->actingAsAdmin();

        $controller = new \App\Http\Controllers\Admin\LaporanKinerjaController();
        $refl = new \ReflectionMethod($controller, 'resolvePeriod');
        $refl->setAccessible(true);

        $request = new \Illuminate\Http\Request();
        $request->merge(['bulan' => 7, 'tahun' => 2026]);
        [$periodStart, $periodEnd, $namaBulan, $isRange] = $refl->invoke($controller, $request);

        $this->assertEquals('2026-07-01 00:00:00', $periodStart->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-07-31 23:59:59', $periodEnd->format('Y-m-d H:i:s'));
        $this->assertEquals('Juli 2026', $namaBulan);
        $this->assertFalse($isRange);
    }

    public function test_data_on_28th_of_month_is_included_when_filtering_that_month(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.laporan-kinerja.index', ['bulan' => 7, 'tahun' => 2026]));

        $response->assertOk();
        $response->assertSee('Juli 2026');
    }
}

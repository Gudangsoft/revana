<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 19 Agustus 2026: widget "Monitoring Tren Submission" di dashboard admin
 * — grafik total submission (bukan dipecah status) dengan filter granularitas
 * per tahun/bulan/hari, dimuat via AJAX ke admin.dashboard.submission-trend.
 */
class SubmissionTrendTest extends TestCase
{
    use RefreshDatabase;

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

    private function makeSubmissionAt(string $createdAt): Submission
    {
        $user = User::create([
            'name' => 'Creator', 'email' => 'creator-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $journal = JournalMaster::create([
            'kode_jurnal' => 'JRN-' . uniqid(), 'nama_jurnal' => 'Jurnal Test',
            'publisher' => 'Penerbit Test', 'link_jurnal' => 'https://example.test/jurnal',
            'created_by' => $user->id, 'is_active' => true,
        ]);
        $slot = JournalSlot::create([
            'kode_slot' => 'SLOT-' . uniqid(), 'journal_master_id' => $journal->id,
            'volume' => '1', 'nomor' => '1', 'bulan' => 'Januari', 'tahun' => 2026,
            'jumlah_slot' => 100, 'created_by' => $user->id,
        ]);
        $submission = Submission::create([
            'kode_submit' => 'SUB-' . uniqid(), 'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(), 'judul_artikel' => 'Judul Test',
            'created_by' => $user->id, 'status' => 'SUBMITTED',
        ]);
        $submission->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

        return $submission;
    }

    public function test_month_period_returns_12_labels_with_correct_counts(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2026-03-05 10:00:00');
        $this->makeSubmissionAt('2026-03-15 10:00:00');
        $this->makeSubmissionAt('2026-07-01 10:00:00');
        $this->makeSubmissionAt('2025-03-05 10:00:00'); // tahun lain, tidak boleh ikut terhitung

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'month', 'year' => 2026]));

        $response->assertOk();
        $json = $response->json();
        $this->assertCount(12, $json['labels']);
        $this->assertEquals('Mar', $json['labels'][2]);
        $this->assertEquals(2, $json['data'][2]); // Maret = index 2
        $this->assertEquals(1, $json['data'][6]); // Juli = index 6
        $this->assertEquals(3, $json['total']);
    }

    public function test_day_period_returns_days_in_month_with_correct_counts(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2026-02-10 08:00:00');
        $this->makeSubmissionAt('2026-02-10 09:00:00');
        $this->makeSubmissionAt('2026-02-28 08:00:00');

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'day', 'year' => 2026, 'month' => 2]));

        $response->assertOk();
        $json = $response->json();
        $this->assertCount(28, $json['labels']); // Februari 2026 bukan tahun kabisat
        $this->assertEquals(2, $json['data'][9]);  // tanggal 10 -> index 9
        $this->assertEquals(1, $json['data'][27]); // tanggal 28 -> index 27
        $this->assertEquals(3, $json['total']);
    }

    public function test_year_period_groups_across_all_years(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2025-01-01 00:00:00');
        $this->makeSubmissionAt('2026-01-01 00:00:00');
        $this->makeSubmissionAt('2026-06-01 00:00:00');

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'year']));

        $response->assertOk();
        $json = $response->json();
        $this->assertEquals(['2025', '2026'], $json['labels']);
        $this->assertEquals([1, 2], $json['data']);
        $this->assertEquals(3, $json['total']);
    }

    public function test_invalid_period_falls_back_to_month(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'bogus', 'year' => 2026]));

        $response->assertOk();
        $this->assertCount(12, $response->json('labels'));
    }

    public function test_dashboard_page_renders_the_new_widget(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Monitoring Tren Submission');
        $response->assertSee('trendPeriodSelect', false);
    }
}

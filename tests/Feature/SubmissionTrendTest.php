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

    private function makeSubmissionAt(string $createdAt, array $overrides = []): Submission
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
        $submission = Submission::create(array_merge([
            'kode_submit' => 'SUB-' . uniqid(), 'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(), 'judul_artikel' => 'Judul Test',
            'created_by' => $user->id, 'status' => 'SUBMITTED',
        ], $overrides));
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

    /**
     * "Normal" cuma menyaring process_type (normal/null), TIDAK peduli
     * program_type — persis konvensi $regularSubmissions yang sudah dipakai di
     * stat card dashboard (index()). Jadi submission BKD yang process_type-nya
     * masih normal/null tetap ikut terhitung "Normal" DAN "BKD" sekaligus
     * (kategori sengaja tidak eksklusif, sesuai data nyata: kombinasi
     * "normal+bkd" memang ada di database).
     */
    public function test_kategori_filter_normal_excludes_only_fasttrack(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2026-04-01 00:00:00'); // normal/regular (default)
        $this->makeSubmissionAt('2026-04-02 00:00:00', ['process_type' => 'fasttrack']);
        $this->makeSubmissionAt('2026-04-03 00:00:00', ['program_type' => 'bkd']); // process_type tetap null -> ikut "Normal"

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'month', 'year' => 2026, 'kategori' => 'normal']));

        $response->assertOk();
        $json = $response->json();
        $this->assertEquals(2, $json['data'][3]); // April = index 3
        $this->assertEquals(2, $json['total']);
        $this->assertEquals('Normal', $json['kategori_label']);
    }

    public function test_kategori_filter_fasttrack_only_counts_fasttrack(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2026-04-01 00:00:00');
        $this->makeSubmissionAt('2026-04-02 00:00:00', ['process_type' => 'fasttrack']);
        $this->makeSubmissionAt('2026-04-03 00:00:00', ['process_type' => 'fasttrack']);

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'month', 'year' => 2026, 'kategori' => 'fasttrack']));

        $response->assertOk();
        $this->assertEquals(2, $response->json('total'));
    }

    public function test_kategori_filter_bkd_only_counts_bkd(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2026-04-01 00:00:00');
        $this->makeSubmissionAt('2026-04-02 00:00:00', ['program_type' => 'bkd']);
        $this->makeSubmissionAt('2026-04-03 00:00:00', ['program_type' => 'jafa']);

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'month', 'year' => 2026, 'kategori' => 'bkd']));

        $response->assertOk();
        $this->assertEquals(1, $response->json('total'));
    }

    public function test_kategori_semua_includes_everything(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2026-04-01 00:00:00');
        $this->makeSubmissionAt('2026-04-02 00:00:00', ['process_type' => 'fasttrack']);
        $this->makeSubmissionAt('2026-04-03 00:00:00', ['program_type' => 'bkd']);
        $this->makeSubmissionAt('2026-04-04 00:00:00', ['program_type' => 'jafa']);

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'month', 'year' => 2026, 'kategori' => 'semua']));

        $response->assertOk();
        $this->assertEquals(4, $response->json('total'));
    }

    public function test_invalid_kategori_falls_back_to_semua(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2026-04-01 00:00:00', ['process_type' => 'fasttrack']);

        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'month', 'year' => 2026, 'kategori' => 'bogus']));

        $response->assertOk();
        $this->assertEquals(1, $response->json('total'));
        $this->assertEquals('Semua', $response->json('kategori_label'));
    }

    /**
     * Fitur 19 Agustus 2026 (revisi tooltip): user minta saat hover di chart,
     * tampil rincian Normal/Fasttrack/BKD/JAFA sekaligus — bukan cuma total
     * kategori yang lagi dipilih. `breakdown` harus selalu berisi rincian
     * lengkap semua kategori, TERLEPAS dari `kategori` yang dikirim di request.
     */
    public function test_response_always_includes_full_category_breakdown(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmissionAt('2026-05-01 00:00:00');
        $this->makeSubmissionAt('2026-05-02 00:00:00', ['process_type' => 'fasttrack']);
        $this->makeSubmissionAt('2026-05-03 00:00:00', ['program_type' => 'bkd']);
        $this->makeSubmissionAt('2026-05-04 00:00:00', ['program_type' => 'jafa']);

        // Sengaja minta kategori 'fasttrack' — breakdown TETAP harus lengkap 4 kategori.
        $response = $this->getJson(route('admin.dashboard.submission-trend', ['period' => 'month', 'year' => 2026, 'kategori' => 'fasttrack']));

        $response->assertOk();
        $breakdown = $response->json('breakdown');
        $this->assertArrayHasKey('Normal', $breakdown);
        $this->assertArrayHasKey('Fasttrack', $breakdown);
        $this->assertArrayHasKey('BKD', $breakdown);
        $this->assertArrayHasKey('JAFA', $breakdown);
        // Mei = index 4 — #1 (normal murni), #3 (bkd), #4 (jafa) semuanya process_type null -> ikut "Normal"
        $this->assertEquals(3, $breakdown['Normal'][4]);
        $this->assertEquals(1, $breakdown['Fasttrack'][4]);
        $this->assertEquals(1, $breakdown['BKD'][4]);
        $this->assertEquals(1, $breakdown['JAFA'][4]);
        // Data chart yg tampil tetap sesuai kategori yg diminta (fasttrack), bukan breakdown-nya.
        $this->assertEquals(1, $response->json('data')[4]);
    }

    public function test_dashboard_page_renders_the_new_widget(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Monitoring Tren Submission');
        $response->assertSee('trendPeriodSelect', false);
        $response->assertSee('trendKategoriSelect', false);
        $response->assertSee('Fasttrack');
        $response->assertSee('BKD');
        $response->assertSee('JAFA');
    }
}

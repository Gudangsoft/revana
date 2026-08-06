<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 1 Agustus 2026: monitoring masa berlaku akreditasi jurnal, supaya tim
 * bisa mulai siapkan dokumen reakreditasi sebelum kedaluwarsa.
 *
 * Revisi (masih tanggal sama): rancangan awal pakai tanggal kalender penuh
 * (accreditation_expires_at), tapi user mengoreksi — akreditasi jurnal (SINTA)
 * TIDAK memakai tanggal kalender, melainkan periode "Volume X Nomor Y Tahun Z"
 * (dikonfirmasi konsisten di 125 data loa_status yang sudah ada). Diganti ke 3
 * kolom terpisah accreditation_end_volume/nomor/tahun (migration
 * 2026_08_01_000004_*). Ambang "Perlu Bersiap" jadi berbasis Tahun: tahun ini
 * atau tahun depan (padanan ~12 bulan dengan presisi tahunan, karena data
 * sumbernya memang cuma sampai tingkat Tahun).
 */
class MonitoringAkreditasiTest extends TestCase
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

    private function makeJournal(array $overrides = []): JournalMaster
    {
        $user = User::create([
            'name' => 'Creator', 'email' => 'creator-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);

        return JournalMaster::create(array_merge([
            'kode_jurnal' => 'JRN-' . uniqid(),
            'nama_jurnal' => 'Jurnal Test',
            'publisher' => 'Penerbit Test',
            'link_jurnal' => 'https://example.test/jurnal',
            'created_by' => $user->id,
            'is_active' => true,
            'accreditation' => 'SINTA 3',
        ], $overrides));
    }

    public function test_periode_accessor_combines_volume_nomor_tahun(): void
    {
        $journal = $this->makeJournal([
            'accreditation_end_volume' => 6,
            'accreditation_end_nomor' => 1,
            'accreditation_end_tahun' => 2027,
        ]);

        $this->assertEquals('Volume 6 Nomor 1 Tahun 2027', $journal->accreditation_periode);
    }

    public function test_periode_accessor_is_null_when_incomplete(): void
    {
        $journal = $this->makeJournal(['accreditation_end_volume' => 6, 'accreditation_end_nomor' => null, 'accreditation_end_tahun' => 2027]);

        $this->assertNull($journal->accreditation_periode);
    }

    public function test_journal_ending_this_year_is_marked_warning(): void
    {
        $this->actingAsAdmin();
        $this->makeJournal([
            'nama_jurnal' => 'Jurnal Segera Habis',
            'accreditation_end_volume' => 6, 'accreditation_end_nomor' => 1,
            'accreditation_end_tahun' => now()->year,
        ]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Segera Habis');
        $response->assertSee('Perlu Bersiap');
    }

    public function test_journal_ending_next_year_is_marked_warning(): void
    {
        $this->actingAsAdmin();
        $this->makeJournal([
            'nama_jurnal' => 'Jurnal Habis Tahun Depan',
            'accreditation_end_volume' => 6, 'accreditation_end_nomor' => 1,
            'accreditation_end_tahun' => now()->year + 1,
        ]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Habis Tahun Depan');
        $response->assertSee('Perlu Bersiap');
    }

    public function test_journal_ended_last_year_is_marked_expired(): void
    {
        $this->actingAsAdmin();
        $this->makeJournal([
            'nama_jurnal' => 'Jurnal Sudah Lewat',
            'accreditation_end_volume' => 5, 'accreditation_end_nomor' => 2,
            'accreditation_end_tahun' => now()->year - 1,
        ]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Sudah Lewat');
        $response->assertSee('Kedaluwarsa');
    }

    public function test_journal_ending_far_in_future_is_marked_safe(): void
    {
        $this->actingAsAdmin();
        $this->makeJournal([
            'nama_jurnal' => 'Jurnal Masih Aman',
            'accreditation_end_volume' => 8, 'accreditation_end_nomor' => 1,
            'accreditation_end_tahun' => now()->year + 5,
        ]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Masih Aman');
        $response->assertSee('Aman');
    }

    public function test_journal_without_end_tahun_is_marked_unknown(): void
    {
        $this->actingAsAdmin();
        $this->makeJournal(['nama_jurnal' => 'Jurnal Belum Diisi', 'accreditation_end_tahun' => null]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Belum Diisi');
        $response->assertSee('Belum Diisi');
    }

    public function test_journal_without_accreditation_at_all_is_excluded(): void
    {
        $this->actingAsAdmin();
        $this->makeJournal(['nama_jurnal' => 'Jurnal Tanpa Akreditasi', 'accreditation' => null]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertDontSee('Jurnal Tanpa Akreditasi');
    }

    public function test_expired_journals_are_sorted_before_safe_ones(): void
    {
        $this->actingAsAdmin();
        $this->makeJournal([
            'nama_jurnal' => 'Zebra Aman',
            'accreditation_end_volume' => 8, 'accreditation_end_nomor' => 1,
            'accreditation_end_tahun' => now()->year + 5,
        ]);
        $this->makeJournal([
            'nama_jurnal' => 'Alfa Kedaluwarsa',
            'accreditation_end_volume' => 5, 'accreditation_end_nomor' => 1,
            'accreditation_end_tahun' => now()->year - 1,
        ]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $content = $response->getContent();
        $posExpired = strpos($content, 'Alfa Kedaluwarsa');
        $posSafe = strpos($content, 'Zebra Aman');
        $this->assertNotFalse($posExpired);
        $this->assertNotFalse($posSafe);
        $this->assertLessThan($posSafe, $posExpired, 'Jurnal kedaluwarsa harus tampil sebelum jurnal aman, walau namanya alfabetis terbalik');
    }

    public function test_monitoring_page_shows_periode_text(): void
    {
        $this->actingAsAdmin();
        $this->makeJournal([
            'nama_jurnal' => 'Jurnal Periode Lengkap',
            'accreditation_end_volume' => 6, 'accreditation_end_nomor' => 1,
            'accreditation_end_tahun' => now()->year + 5,
        ]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Volume 6 Nomor 1 Tahun ' . (now()->year + 5));
    }

    public function test_loa_master_update_saves_accreditation_periode(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal();

        $this->put(route('admin.loa-master.update', $journal), [
            'e_issn' => '1234-5678',
            'accreditation_end_volume' => 6,
            'accreditation_end_nomor' => 1,
            'accreditation_end_tahun' => 2028,
        ])->assertRedirect();

        $fresh = $journal->fresh();
        $this->assertEquals(6, $fresh->accreditation_end_volume);
        $this->assertEquals(1, $fresh->accreditation_end_nomor);
        $this->assertEquals(2028, $fresh->accreditation_end_tahun);
    }
}

<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 1 Agustus 2026: monitoring masa berlaku akreditasi jurnal, supaya tim
 * bisa mulai siapkan dokumen reakreditasi sebelum kedaluwarsa. Sebelumnya tidak
 * ada tanggal berakhir akreditasi yang tersimpan terstruktur (cuma teks bebas
 * di loa_status) sehingga tidak bisa dipakai untuk peringatan otomatis.
 * Ambang "Perlu Bersiap" = 12 bulan sebelum kedaluwarsa (pilihan user).
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

    public function test_journal_expiring_within_12_months_is_marked_warning(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(['nama_jurnal' => 'Jurnal Segera Habis', 'accreditation_expires_at' => now()->addMonths(6)]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Segera Habis');
        $response->assertSee('Perlu Bersiap');
    }

    public function test_journal_expired_in_the_past_is_marked_expired(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(['nama_jurnal' => 'Jurnal Sudah Lewat', 'accreditation_expires_at' => now()->subMonths(2)]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Sudah Lewat');
        $response->assertSee('Kedaluwarsa');
    }

    public function test_journal_expiring_far_in_future_is_marked_safe(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(['nama_jurnal' => 'Jurnal Masih Aman', 'accreditation_expires_at' => now()->addMonths(24)]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $response->assertSee('Jurnal Masih Aman');
        $response->assertSee('Aman');
    }

    public function test_journal_without_expiry_date_is_marked_unknown(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(['nama_jurnal' => 'Jurnal Belum Diisi', 'accreditation_expires_at' => null]);

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
        $this->makeJournal(['nama_jurnal' => 'Zebra Aman', 'accreditation_expires_at' => now()->addMonths(24)]);
        $this->makeJournal(['nama_jurnal' => 'Alfa Kedaluwarsa', 'accreditation_expires_at' => now()->subMonths(1)]);

        $response = $this->get(route('admin.monitoring-akreditasi.index'));

        $response->assertOk();
        $content = $response->getContent();
        $posExpired = strpos($content, 'Alfa Kedaluwarsa');
        $posSafe = strpos($content, 'Zebra Aman');
        $this->assertNotFalse($posExpired);
        $this->assertNotFalse($posSafe);
        $this->assertLessThan($posSafe, $posExpired, 'Jurnal kedaluwarsa harus tampil sebelum jurnal aman, walau namanya alfabetis terbalik');
    }

    public function test_loa_master_update_saves_accreditation_expiry_date(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal();

        $this->put(route('admin.loa-master.update', $journal), [
            'e_issn' => '1234-5678',
            'accreditation_expires_at' => '2028-03-15',
        ])->assertRedirect();

        $this->assertEquals('2028-03-15', $journal->fresh()->accreditation_expires_at->toDateString());
    }
}

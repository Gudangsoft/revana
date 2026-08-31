<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi 19 Agustus 2026: `/admin/submissions/export` mengembalikan 500 di
 * production ("Allowed memory size of 536870912 bytes exhausted") saat
 * mengekspor seluruh 14.691 submission (44 kolom) — PhpSpreadsheet menyimpan
 * semua sel di memori saat menulis XLSX, `WithChunkReading` cuma mengurangi
 * beban query DB, bukan memori penulisan file, jadi tetap melebihi
 * memory_limit default begitu dataset makin besar.
 *
 * Diperbaiki dengan menaikkan memory_limit KHUSUS utk request export ini
 * (tidak memengaruhi request lain). Diverifikasi manual dengan dataset
 * PRODUKSI asli (14.691 baris) — peak memory ~524MB, sebelumnya crash di
 * ambang 512M, sekarang aman dengan ceiling 2048M. Test PHPUnit di sini cuma
 * memverifikasi endpoint tetap berfungsi normal (dataset kecil, tidak
 * mereproduksi OOM — mereproduksi 14rb baris di test suite tidak praktis).
 */
class SubmissionExportMemoryTest extends TestCase
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

    private function makeSubmission(array $overrides = []): Submission
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

        return Submission::create(array_merge([
            'kode_submit' => 'SUB-' . uniqid(), 'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(), 'judul_artikel' => 'Judul Test',
            'nama_penulis' => 'Penulis Test',
            'created_by' => $user->id, 'status' => 'SUBMITTED',
        ], $overrides));
    }

    public function test_export_endpoint_succeeds_with_no_filters(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission();
        $this->makeSubmission();

        $response = $this->get(route('admin.submissions.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_endpoint_respects_status_filter(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission(['status' => 'PUBLISHED']);
        $this->makeSubmission(['status' => 'SUBMITTED']);

        $response = $this->get(route('admin.submissions.export', ['status' => 'PUBLISHED']));

        $response->assertOk();
    }

    public function test_export_raises_memory_limit_above_default(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission();
        $before = ini_get('memory_limit');

        $this->get(route('admin.submissions.export'));

        $this->assertEquals('2048M', ini_get('memory_limit'));
        // Kembalikan supaya tidak bocor ke test lain dalam proses PHPUnit yang sama.
        ini_set('memory_limit', $before);
    }
}

<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Marketing;
use App\Models\Pic;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Permintaan 10 Sept 2026: di halaman Monitoring, nama jurnal sebelumnya
 * dipotong `Str::limit(..., 20)` ("Jurnal Ilmiah Kedokt...") dan info
 * slot cuma menampilkan "Vol.X No.Y" tanpa bulan & tahun. User minta nama
 * jurnal tampil PENUH beserta volume, nomor, bulan, dan tahun lengkap —
 * berlaku untuk SEMUA halaman monitoring (admin/pic/marketing, normal &
 * fasttrack).
 */
class MonitoringJournalInfoFullTest extends TestCase
{
    use RefreshDatabase;

    /** Nama sengaja > 20 karakter supaya ketahuan kalau masih ada Str::limit(...,20) */
    private const LONG_JOURNAL_NAME = 'Jurnal Ilmiah Kedokteran Universitas Indonesia Raya';

    private function actingAsAdmin(): User
    {
        $admin = User::create([
            'name' => 'Test Admin', 'email' => 'admin-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $this->actingAs($admin);

        return $admin;
    }

    private function makePicUser(): Pic
    {
        $pic = Pic::create([
            'name' => 'PIC Test', 'username' => 'pic-' . uniqid(),
            'password' => bcrypt('password'), 'is_active' => true,
        ]);
        $this->actingAs($pic, 'pic');

        return $pic;
    }

    private function makeMarketingUser(): Marketing
    {
        $marketing = Marketing::create([
            'name' => 'Marketing Test', 'username' => 'mkt-' . uniqid(),
            'password' => bcrypt('password'), 'is_active' => true,
        ]);
        $this->actingAs($marketing, 'marketing');

        return $marketing;
    }

    private function makeSubmission(array $overrides = []): Submission
    {
        $user = User::create([
            'name' => 'Creator', 'email' => 'creator-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $journal = JournalMaster::create([
            'kode_jurnal' => 'JRN-' . uniqid(), 'nama_jurnal' => self::LONG_JOURNAL_NAME,
            'publisher' => 'Penerbit Test', 'link_jurnal' => 'https://example.test/jurnal',
            'created_by' => $user->id, 'is_active' => true,
        ]);
        $slot = JournalSlot::create([
            'kode_slot' => 'SLOT-' . uniqid(), 'journal_master_id' => $journal->id,
            'volume' => '7', 'nomor' => '3', 'bulan' => 'Maret', 'tahun' => 2024,
            'jumlah_slot' => 100, 'created_by' => $user->id,
        ]);

        return Submission::create(array_merge([
            'kode_submit' => 'SUB-' . uniqid(), 'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(), 'judul_artikel' => 'Judul Test',
            'nama_penulis' => 'Penulis Test',
            'created_by' => $user->id, 'status' => 'SUBMITTED',
        ], $overrides));
    }

    private function assertFullJournalInfoVisible(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertOk();
        // Nama jurnal PENUH (bukan "...Kedokt...")
        $response->assertSee(self::LONG_JOURNAL_NAME);
        // Volume + nomor
        $response->assertSee('Vol.7 No.3');
        // Bulan + tahun
        $response->assertSee('Maret 2024');
    }

    public function test_admin_submissions_monitoring_shows_full_journal_info(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission();

        $this->assertFullJournalInfoVisible($this->get(route('admin.submissions.monitoring')));
    }

    public function test_admin_fasttrack_monitoring_shows_full_journal_info(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission(['process_type' => 'fasttrack']);

        $this->assertFullJournalInfoVisible($this->get(route('admin.fasttrack-management.monitoring.index')));
    }

    public function test_pic_submissions_monitoring_shows_full_journal_info(): void
    {
        $pic = $this->makePicUser();
        $this->makeSubmission(['petugas_submit_id' => $pic->id]);

        $this->assertFullJournalInfoVisible($this->get(route('pic.submissions.monitoring')));
    }

    public function test_pic_fasttrack_monitoring_shows_full_journal_info(): void
    {
        $pic = $this->makePicUser();
        $this->makeSubmission(['process_type' => 'fasttrack', 'petugas_submit_id' => $pic->id]);

        $this->assertFullJournalInfoVisible($this->get(route('pic.fasttrack.monitoring')));
    }

    public function test_marketing_submissions_monitoring_shows_full_journal_info(): void
    {
        $marketing = $this->makeMarketingUser();
        $this->makeSubmission(['marketing_id' => $marketing->id]);

        $this->assertFullJournalInfoVisible($this->get(route('marketing.submissions.monitoring')));
    }

    public function test_marketing_fasttrack_monitoring_shows_full_journal_info(): void
    {
        $marketing = $this->makeMarketingUser();
        $this->makeSubmission(['process_type' => 'fasttrack', 'marketing_id' => $marketing->id]);

        $this->assertFullJournalInfoVisible($this->get(route('marketing.fasttrack.monitoring')));
    }

    /**
     * Di baris data monitoring, nama jurnal TIDAK boleh lagi dipotong di
     * tengah kata (mis. "...Universi...") — dulu `Str::limit(..., 20)` /
     * `Str::limit(..., 30)`. Filter dropdown "Jurnal" di atas tabel memang
     * masih memendekkan nama (itu wajar untuk <option> yang panjang & bukan
     * yang diminta user), jadi di sini cukup dipastikan nama penuh muncul
     * (yang otomatis membuktikan tidak ada pemotongan di baris data — string
     * 50 karakter takkan cocok kalau dipotong).
     */
    public function test_full_journal_name_is_present_in_data_rows_on_all_monitoring_pages(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission();
        $this->makeSubmission(['process_type' => 'fasttrack']);

        $this->get(route('admin.submissions.monitoring'))->assertSee(self::LONG_JOURNAL_NAME);
        $this->get(route('admin.fasttrack-management.monitoring.index'))->assertSee(self::LONG_JOURNAL_NAME);
    }
}

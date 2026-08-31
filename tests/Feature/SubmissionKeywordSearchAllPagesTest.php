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
 * Fitur 19 Agustus 2026 (lanjutan section #5): user minta pencarian keyword
 * bebas diterapkan ke SEMUA halaman Data Submit & Monitoring, bukan cuma yang
 * sudah dikerjakan (admin.submissions.index/monitoring). Diterapkan ke 8
 * endpoint tambahan yang sebelumnya masing-masing pakai pencarian sendiri
 * (kebanyakan cuma cocok di 3 field: kode_submit/judul_artikel/nama_penulis,
 * beberapa malah tidak ada input pencarian sama sekali di view-nya) — semua
 * diseragamkan pakai Submission::scopeSearch() yang sama (6 field: nama
 * penulis, ID artikel, judul, kode submit, no HP, nama jurnal).
 */
class SubmissionKeywordSearchAllPagesTest extends TestCase
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

    // ── Admin: Fasttrack Management ──────────────────────────────────────

    public function test_admin_fasttrack_submissions_page_filters_by_keyword(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'process_type' => 'fasttrack']);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'process_type' => 'fasttrack']);

        $response = $this->get(route('admin.fasttrack-management.submissions.index', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_admin_fasttrack_monitoring_page_filters_by_keyword(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'process_type' => 'fasttrack']);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'process_type' => 'fasttrack']);

        $response = $this->get(route('admin.fasttrack-management.monitoring.index', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    // ── PIC ───────────────────────────────────────────────────────────────

    public function test_pic_submissions_index_filters_by_keyword(): void
    {
        $this->makePicUser();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi']);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini']);

        $response = $this->get(route('pic.submissions.index', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_pic_submissions_monitoring_filters_by_keyword(): void
    {
        $pic = $this->makePicUser();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'petugas_submit_id' => $pic->id]);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'petugas_submit_id' => $pic->id]);

        $response = $this->get(route('pic.submissions.monitoring', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_pic_fasttrack_index_filters_by_keyword(): void
    {
        $this->makePicUser();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'process_type' => 'fasttrack']);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'process_type' => 'fasttrack']);

        $response = $this->get(route('pic.fasttrack.index', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_pic_fasttrack_monitoring_filters_by_keyword(): void
    {
        $pic = $this->makePicUser();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'process_type' => 'fasttrack', 'petugas_submit_id' => $pic->id]);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'process_type' => 'fasttrack', 'petugas_submit_id' => $pic->id]);

        $response = $this->get(route('pic.fasttrack.monitoring', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    // ── Marketing ─────────────────────────────────────────────────────────

    public function test_marketing_submissions_filters_by_keyword(): void
    {
        $marketing = $this->makeMarketingUser();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'marketing_id' => $marketing->id]);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'marketing_id' => $marketing->id]);

        $response = $this->get(route('marketing.submissions', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_marketing_submissions_monitoring_filters_by_keyword(): void
    {
        $marketing = $this->makeMarketingUser();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'marketing_id' => $marketing->id]);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'marketing_id' => $marketing->id]);

        $response = $this->get(route('marketing.submissions.monitoring', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_marketing_fasttrack_index_filters_by_keyword(): void
    {
        $marketing = $this->makeMarketingUser();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'process_type' => 'fasttrack', 'marketing_id' => $marketing->id]);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'process_type' => 'fasttrack', 'marketing_id' => $marketing->id]);

        $response = $this->get(route('marketing.fasttrack.index', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_marketing_fasttrack_monitoring_filters_by_keyword(): void
    {
        $marketing = $this->makeMarketingUser();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi', 'process_type' => 'fasttrack', 'marketing_id' => $marketing->id]);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini', 'process_type' => 'fasttrack', 'marketing_id' => $marketing->id]);

        $response = $this->get(route('marketing.fasttrack.monitoring', ['search' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }
}

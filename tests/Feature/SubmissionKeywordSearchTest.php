<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 19 Agustus 2026: user minta sistem pencarian dengan "keyword diketik
 * apapun itu" ditambahkan ke halaman Data Submit (admin.submissions.index) dan
 * Monitoring (admin.submissions.monitoring) — sebelumnya kedua halaman itu cuma
 * bisa filter tanggal/status/nama jurnal, tidak ada cara mencari langsung
 * berdasarkan nama penulis/ID artikel/judul/kode submit/no HP tanpa pindah ke
 * halaman pencarian global terpisah. Field cakupan pencarian dibuat sama
 * persis dengan pencarian global navbar (SearchController) lewat
 * Submission::scopeSearch() — satu sumber kebenaran dipakai bersama.
 */
class SubmissionKeywordSearchTest extends TestCase
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

    private function makeSubmission(array $overrides = []): Submission
    {
        $user = User::create([
            'name' => 'Creator', 'email' => 'creator-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $journal = JournalMaster::create(array_merge([
            'kode_jurnal' => 'JRN-' . uniqid(), 'nama_jurnal' => 'Jurnal Test',
            'publisher' => 'Penerbit Test', 'link_jurnal' => 'https://example.test/jurnal',
            'created_by' => $user->id, 'is_active' => true,
        ], $overrides['journal'] ?? []));
        $slot = JournalSlot::create([
            'kode_slot' => 'SLOT-' . uniqid(), 'journal_master_id' => $journal->id,
            'volume' => '1', 'nomor' => '1', 'bulan' => 'Januari', 'tahun' => 2026,
            'jumlah_slot' => 100, 'created_by' => $user->id,
        ]);
        unset($overrides['journal']);

        return Submission::create(array_merge([
            'kode_submit' => 'SUB-' . uniqid(), 'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(), 'judul_artikel' => 'Judul Test',
            'nama_penulis' => 'Penulis Test',
            'created_by' => $user->id, 'status' => 'SUBMITTED',
        ], $overrides));
    }

    public function test_scope_search_matches_nama_penulis(): void
    {
        $this->makeSubmission(['nama_penulis' => 'Budi Santoso']);
        $this->makeSubmission(['nama_penulis' => 'Citra Lestari']);

        $results = Submission::search('Budi')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Budi Santoso', $results->first()->nama_penulis);
    }

    public function test_scope_search_matches_kode_submit(): void
    {
        $this->makeSubmission(['kode_submit' => 'SUBUNIK123']);
        $this->makeSubmission(['kode_submit' => 'SUBLAIN456']);

        $results = Submission::search('UNIK')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('SUBUNIK123', $results->first()->kode_submit);
    }

    public function test_scope_search_matches_judul_artikel(): void
    {
        $this->makeSubmission(['judul_artikel' => 'Analisis Kualitas Pendidikan']);
        $this->makeSubmission(['judul_artikel' => 'Strategi Pemasaran Digital']);

        $results = Submission::search('Pendidikan')->get();

        $this->assertCount(1, $results);
    }

    public function test_scope_search_matches_nama_jurnal_via_relation(): void
    {
        $this->makeSubmission(['journal' => ['nama_jurnal' => 'Jurnal Kedokteran Nusantara']]);
        $this->makeSubmission(['journal' => ['nama_jurnal' => 'Jurnal Ekonomi Bisnis']]);

        $results = Submission::search('Kedokteran')->get();

        $this->assertCount(1, $results);
    }

    public function test_scope_search_returns_all_when_keyword_empty(): void
    {
        $this->makeSubmission();
        $this->makeSubmission();

        $this->assertCount(2, Submission::search('')->get());
        $this->assertCount(2, Submission::search(null)->get());
    }

    public function test_data_submit_page_filters_by_keyword(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi']);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini']);

        $response = $this->get(route('admin.submissions.index', ['keyword' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_monitoring_page_filters_by_keyword(): void
    {
        $this->actingAsAdmin();
        $this->makeSubmission(['nama_penulis' => 'Ahmad Fauzi']);
        $this->makeSubmission(['nama_penulis' => 'Dewi Anggraini']);

        $response = $this->get(route('admin.submissions.monitoring', ['keyword' => 'Ahmad']));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Dewi Anggraini');
    }

    public function test_data_submit_page_shows_keyword_input_field(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.submissions.index'));

        $response->assertOk();
        $response->assertSee('name="keyword"', false);
    }

    public function test_monitoring_page_shows_keyword_input_field(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.submissions.monitoring'));

        $response->assertOk();
        $response->assertSee('name="keyword"', false);
    }
}

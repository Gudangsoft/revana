<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 1 Agustus 2026: toggle opsional "Tampilkan Tanda Tangan & Nama Editor"
 * di menu Master LOA (loa_show_signature, default true). User minta TTD & nama
 * editor di LOA bisa ditampilkan atau disembunyikan sesuai pilihan admin, tanpa
 * menghapus data editor_name/editor_signature_path yang sudah tersimpan.
 */
class LoaShowSignatureTest extends TestCase
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
            'editor_name' => 'Dr. Budi Santoso, M.Kom.',
            'editor_signature_path' => 'journals/signatures/fake-sign.png',
        ], $overrides));
    }

    private function makeSubmissionFor(JournalMaster $journal): Submission
    {
        $user = User::create([
            'name' => 'Creator2', 'email' => 'creator2-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $slot = JournalSlot::create([
            'kode_slot' => 'SLOT-' . uniqid(),
            'journal_master_id' => $journal->id,
            'volume' => '1', 'nomor' => '1', 'bulan' => 'Januari', 'tahun' => 2026,
            'jumlah_slot' => 100, 'created_by' => $user->id,
        ]);

        return Submission::create([
            'kode_submit' => 'SUB-' . uniqid(),
            'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(),
            'judul_artikel' => 'Judul Artikel Test',
            'created_by' => $user->id,
            'status' => 'SUBMITTED',
            'kode_loa' => 'LOA-' . uniqid(),
        ]);
    }

    public function test_loa_master_update_saves_show_signature_true_when_checkbox_checked(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(['loa_show_signature' => false]);

        $this->put(route('admin.loa-master.update', $journal), [
            'e_issn' => '1234-5678',
            'loa_show_signature' => '1',
        ])->assertRedirect();

        $this->assertTrue($journal->fresh()->loa_show_signature);
    }

    public function test_loa_master_update_saves_show_signature_false_when_checkbox_unchecked(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(['loa_show_signature' => true]);

        // Checkbox tidak dikirim sama sekali = unchecked, persis perilaku HTML form asli.
        $this->put(route('admin.loa-master.update', $journal), [
            'e_issn' => '1234-5678',
        ])->assertRedirect();

        $this->assertFalse($journal->fresh()->loa_show_signature);
    }

    public function test_loa_document_shows_signature_and_editor_name_by_default(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(); // loa_show_signature default true
        $submission = $this->makeSubmissionFor($journal);

        $response = $this->get(route('admin.submissions.loa', $submission));

        $response->assertOk();
        $response->assertSee('Dr. Budi Santoso, M.Kom.');
        $response->assertSee('fake-sign.png', false);
    }

    public function test_loa_document_hides_signature_and_editor_name_when_toggle_off(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(['loa_show_signature' => false]);
        $submission = $this->makeSubmissionFor($journal);

        $response = $this->get(route('admin.submissions.loa', $submission));

        $response->assertOk();
        $response->assertDontSee('Dr. Budi Santoso, M.Kom.');
        $response->assertDontSee('fake-sign.png', false);
    }

    public function test_loa_master_edit_page_has_editor_name_and_signature_inputs(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal();

        $response = $this->get(route('admin.loa-master.edit', $journal));

        $response->assertOk();
        $response->assertSee('name="editor_name"', false);
        $response->assertSee('name="editor_signature"', false);
        $response->assertSee($journal->editor_name);
    }

    public function test_loa_document_still_shows_other_journal_fields_when_signature_hidden(): void
    {
        $this->actingAsAdmin();
        $journal = $this->makeJournal(['loa_show_signature' => false]);
        $submission = $this->makeSubmissionFor($journal);

        $response = $this->get(route('admin.submissions.loa', $submission));

        $response->assertOk();
        $response->assertSee('Jurnal Test');
    }
}

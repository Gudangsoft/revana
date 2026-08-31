<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 19 Agustus 2026: dropdown "Tipe Proses" (Normal/Fasttrack) ditambahkan
 * ke halaman edit submission (admin.submissions.edit/update), terpisah dari
 * dropdown "Program" (BKD/JAFA) yang sudah ada. Mapping ke process_type —
 * field YANG SAMA dipakai dashboard, PicController, dan menu Fasttrack di
 * seluruh sistem, supaya toggle di sini benar-benar memindahkan submission
 * masuk/keluar semua laporan/listing Fasttrack yang sudah ada.
 */
class SubmissionProcessTypeEditTest extends TestCase
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

    private function baseUpdatePayload(Submission $submission): array
    {
        return [
            'journal_slot_id' => $submission->journal_slot_id,
            'id_artikel' => $submission->id_artikel,
            'judul_artikel' => $submission->judul_artikel,
            'nama_penulis' => $submission->nama_penulis,
        ];
    }

    public function test_can_mark_existing_submission_as_fasttrack_via_edit_form(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission();

        $response = $this->put(route('admin.submissions.update', $submission), array_merge(
            $this->baseUpdatePayload($submission),
            ['process_type' => 'fasttrack']
        ));

        $response->assertRedirect();
        $this->assertEquals('fasttrack', $submission->fresh()->process_type);
        $this->assertTrue($submission->fresh()->isFasttrack());
    }

    public function test_can_revert_fasttrack_submission_back_to_normal(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission(['process_type' => 'fasttrack']);

        $response = $this->put(route('admin.submissions.update', $submission), array_merge(
            $this->baseUpdatePayload($submission),
            ['process_type' => 'normal']
        ));

        $response->assertRedirect();
        $this->assertTrue($submission->fresh()->isNormal());
    }

    public function test_changing_process_type_does_not_alter_kode_submit_prefix(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission(['kode_submit' => 'SUB999']);

        $this->put(route('admin.submissions.update', $submission), array_merge(
            $this->baseUpdatePayload($submission),
            ['process_type' => 'fasttrack']
        ));

        $this->assertEquals('SUB999', $submission->fresh()->kode_submit);
    }

    public function test_process_type_is_independent_from_program_type(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission(['program_type' => 'bkd']);

        $this->put(route('admin.submissions.update', $submission), array_merge(
            $this->baseUpdatePayload($submission),
            ['program_type' => 'bkd', 'process_type' => 'fasttrack']
        ));

        $fresh = $submission->fresh();
        $this->assertEquals('bkd', $fresh->program_type);
        $this->assertEquals('fasttrack', $fresh->process_type);
    }

    public function test_invalid_process_type_value_is_rejected(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission();

        $response = $this->put(route('admin.submissions.update', $submission), array_merge(
            $this->baseUpdatePayload($submission),
            ['process_type' => 'bukan-nilai-valid']
        ));

        $response->assertSessionHasErrors('process_type');
    }

    public function test_edit_page_shows_tipe_proses_dropdown(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmission();

        $response = $this->get(route('admin.submissions.edit', $submission));

        $response->assertOk();
        $response->assertSee('Tipe Proses');
        $response->assertSee('name="process_type"', false);
    }
}

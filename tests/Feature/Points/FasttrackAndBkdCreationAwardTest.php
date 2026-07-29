<?php

namespace Tests\Feature\Points;

use App\Models\PicPointHistory;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi lanjutan dari insiden 29 Juli 2026 (lihat ProductionViaLinkPublishAwardTest).
 * Audit menyeluruh menemukan pola bug yang sama (production ter-assign & valid sejak
 * pembuatan, tapi poin production tidak pernah diberikan) di 2 tempat lain, plus 1 celah
 * yang memungkinkan submission fasttrack dibuat admin tanpa PIC sama sekali:
 *
 * 1. Pic\JournalManagementController::fasttrackStore() — fasttrack yang PIC buat sendiri
 *    dengan link publish langsung diisi saat pembuatan.
 * 2. Pic\JournalManagementController::submissionsStore() — submission BKD dengan link
 *    publish langsung diisi saat pembuatan (auto-publish, skip proses review).
 * 3. Admin\SubmissionController::fasttrackStore() — field "PIC Submit" tadinya opsional,
 *    submission bisa dibuat tanpa PIC sama sekali (tidak ada yang dapat poin/tercatat).
 * 4. Admin\SubmissionController::fasttrackUpdate() — mengisi PIC Submit yang tadinya
 *    kosong lewat form edit tidak pernah memberi poin retroaktif.
 */
class FasttrackAndBkdCreationAwardTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

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

    public function test_pic_fasttrack_store_with_immediate_publish_awards_both_submit_and_production(): void
    {
        $pic = $this->makePic();
        $slot = $this->makeJournalSlot();

        $this->actingAs($pic, 'pic')
            ->post(route('pic.fasttrack.store'), [
                'journal_slot_id' => $slot->id,
                'id_artikel' => 'ART-' . uniqid(),
                'judul_artikel' => 'Judul Fasttrack Test',
                'nama_penulis' => 'Penulis Test',
                'link_publish' => 'https://example.test/published',
            ])
            ->assertRedirect();

        $submission = Submission::where('process_type', 'fasttrack')->latest('id')->first();

        $this->assertEquals($pic->id, $submission->petugas_production_id);
        $this->assertTrue((bool) $submission->production_valid);
        $this->assertNotNull($submission->production_validated_at);

        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
        ]);
        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'production',
        ]);
    }

    public function test_pic_bkd_submission_with_immediate_publish_awards_both_submit_and_production(): void
    {
        $pic = $this->makePic();
        $slot = $this->makeJournalSlot();

        $this->actingAs($pic, 'pic')
            ->post(route('pic.submissions.store'), [
                'journal_slot_id' => $slot->id,
                'id_artikel' => 'ART-' . uniqid(),
                'judul_artikel' => 'Judul BKD Test',
                'nama_penulis' => 'Penulis Test',
                'program_type' => 'bkd',
                'link_publish' => 'https://example.test/published-bkd',
            ])
            ->assertRedirect();

        $submission = Submission::where('program_type', 'bkd')->latest('id')->first();

        $this->assertEquals('PUBLISHED', $submission->status);
        $this->assertEquals($pic->id, $submission->petugas_production_id);
        $this->assertTrue((bool) $submission->production_valid);
        $this->assertNotNull($submission->production_validated_at);

        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
        ]);
        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'production',
        ]);
    }

    public function test_admin_fasttrack_store_requires_pic_submit(): void
    {
        $this->actingAsAdmin();
        $slot = $this->makeJournalSlot();

        $this->post(route('admin.fasttrack.store'), [
            'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(),
            'judul_artikel' => 'Judul Fasttrack Tanpa PIC',
            'nama_penulis' => 'Penulis Test',
            // petugas_submit_id sengaja tidak diisi
        ])->assertSessionHasErrors('petugas_submit_id');

        $this->assertDatabaseMissing('submissions', ['judul_artikel' => 'Judul Fasttrack Tanpa PIC']);
    }

    public function test_admin_fasttrack_update_awards_points_when_filling_previously_empty_pic_submit(): void
    {
        $admin = $this->actingAsAdmin();
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'process_type' => 'fasttrack',
            'status' => 'SUBMITTED',
            'petugas_submit_id' => null,
        ]);

        $this->put(route('admin.fasttrack.update', $submission), [
            'journal_slot_id' => $submission->journal_slot_id,
            'judul_artikel' => $submission->judul_artikel,
            'nama_penulis' => 'Penulis Test',
            'petugas_submit_id' => $pic->id,
        ])->assertRedirect();

        $submission->refresh();
        $this->assertEquals($pic->id, $submission->petugas_submit_id);

        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
        ]);
    }

    public function test_admin_fasttrack_update_does_not_duplicate_points_when_pic_submit_unchanged(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'process_type' => 'fasttrack',
            'status' => 'SUBMITTED',
            'petugas_submit_id' => $pic->id,
        ]);
        PicPointHistory::awardPoints($pic->id, $submission->id, 'submit', 'Submit awal');

        $this->put(route('admin.fasttrack.update', $submission), [
            'journal_slot_id' => $submission->journal_slot_id,
            'judul_artikel' => 'Judul Diperbarui',
            'nama_penulis' => 'Penulis Test',
            'petugas_submit_id' => $pic->id,
        ])->assertRedirect();

        $this->assertEquals(
            1,
            PicPointHistory::where('pic_id', $pic->id)
                ->where('submission_id', $submission->id)
                ->where('step', 'submit')
                ->count()
        );
    }
}

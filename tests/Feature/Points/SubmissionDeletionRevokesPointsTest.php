<?php

namespace Tests\Feature\Points;

use App\Models\MarketingPointHistory;
use App\Models\PicPointHistory;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 3 konsolidasi poin 29 Juli 2026 (lihat docs/tests/log-update-2026-07-29.md #9):
 * `SubmissionController::destroy()` sebelumnya cuma mencabut poin Marketing (manual,
 * hapus baris langsung di controller) dan TIDAK PERNAH menyentuh poin PIC sama sekali
 * — poin PIC "bocor" (tetap ada) walau submission-nya sudah dihapus.
 * `fasttrackDestroy()` malah tidak mencabut poin sama sekali (PIC maupun Marketing).
 * Keduanya sekarang memanggil `PointsService::revokeAllForSubmission()` sebelum hapus.
 */
class SubmissionDeletionRevokesPointsTest extends TestCase
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

    // --- PointsService::revokeAllForSubmission() langsung ---

    public function test_revoke_all_for_submission_revokes_points_from_multiple_pics_across_steps(): void
    {
        $picSubmit = $this->makePic();
        $picEditor1 = $this->makePic();
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);

        PointsService::awardToPic($picSubmit->id, $submission->id, 'submit');
        PointsService::awardToPic($picEditor1->id, $submission->id, 'editor1');
        PointsService::awardToMarketing($marketing->id, $submission->id);

        $result = PointsService::revokeAllForSubmission($submission->id);

        $this->assertEquals(2, $result['pic']);
        $this->assertEquals(1, $result['marketing']);
        $this->assertEquals(0, $picSubmit->fresh()->total_points);
        $this->assertEquals(0, $picEditor1->fresh()->total_points);
        $this->assertEquals(0, $marketing->fresh()->total_points);
        $this->assertSame(0, PicPointHistory::where('submission_id', $submission->id)->count());
        $this->assertSame(0, MarketingPointHistory::where('submission_id', $submission->id)->count());
    }

    public function test_revoke_all_for_submission_on_submission_with_no_points_does_not_error(): void
    {
        $submission = $this->makeSubmission();

        $result = PointsService::revokeAllForSubmission($submission->id);

        $this->assertEquals(['pic' => 0, 'marketing' => 0], $result);
    }

    public function test_revoke_all_for_submission_does_not_affect_other_submissions(): void
    {
        $pic = $this->makePic();
        $marketing = $this->makeMarketing();
        $submissionToDelete = $this->makeSubmission(['marketing_id' => $marketing->id]);
        $submissionToKeep = $this->makeSubmission(['marketing_id' => $marketing->id]);
        PointsService::awardToPic($pic->id, $submissionToDelete->id, 'submit');
        PointsService::awardToPic($pic->id, $submissionToKeep->id, 'submit');
        PointsService::awardToMarketing($marketing->id, $submissionToDelete->id);
        PointsService::awardToMarketing($marketing->id, $submissionToKeep->id);

        PointsService::revokeAllForSubmission($submissionToDelete->id);

        $this->assertDatabaseHas('pic_point_histories', ['submission_id' => $submissionToKeep->id]);
        $this->assertDatabaseHas('marketing_point_histories', ['submission_id' => $submissionToKeep->id]);
        $this->assertGreaterThan(0, $pic->fresh()->total_points);
        $this->assertGreaterThan(0, $marketing->fresh()->total_points);
    }

    public function test_revoke_all_for_submission_is_safe_to_call_twice(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();
        PointsService::awardToPic($pic->id, $submission->id, 'submit');

        $first = PointsService::revokeAllForSubmission($submission->id);
        $second = PointsService::revokeAllForSubmission($submission->id);

        $this->assertEquals(1, $first['pic']);
        $this->assertEquals(['pic' => 0, 'marketing' => 0], $second);
        $this->assertEquals(0, $pic->fresh()->total_points);
    }

    // --- HTTP-level: destroy() ---

    public function test_destroy_revokes_pic_and_marketing_points_before_deleting(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic();
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission(['marketing_id' => $marketing->id]);
        PointsService::awardToPic($pic->id, $submission->id, 'submit');
        PointsService::awardToMarketing($marketing->id, $submission->id);

        $this->delete(route('admin.submissions.destroy', $submission))->assertRedirect();

        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        $this->assertEquals(0, $pic->fresh()->total_points);
        $this->assertEquals(0, $marketing->fresh()->total_points);
        $this->assertSame(0, PicPointHistory::where('pic_id', $pic->id)->count());
    }

    // --- HTTP-level: fasttrackDestroy() — sebelumnya TIDAK mencabut poin sama sekali ---

    public function test_fasttrack_destroy_revokes_pic_and_marketing_points_before_deleting(): void
    {
        $this->actingAsAdmin();
        $pic = $this->makePic();
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission([
            'process_type' => 'fasttrack',
            'marketing_id' => $marketing->id,
        ]);
        PointsService::awardToPic($pic->id, $submission->id, 'submit');
        PointsService::awardToMarketing($marketing->id, $submission->id);

        $this->delete(route('admin.fasttrack.destroy', $submission))->assertRedirect();

        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        $this->assertEquals(0, $pic->fresh()->total_points);
        $this->assertEquals(0, $marketing->fresh()->total_points);
    }
}

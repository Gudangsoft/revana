<?php

namespace Tests\Feature;

use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fitur baru 9 Sept 2026: admin bisa submit "Surat Tugas" (file upload atau
 * link eksternal) untuk sebuah review assignment lewat halaman
 * admin.assignments.show, dan reviewer yang ditugaskan bisa mengunduhnya dari
 * halaman tugas mereka (reviewer.tasks.show) — sebelumnya kolom
 * `assignment_letter_link` sudah ada di skema tapi tidak pernah bisa diisi
 * lewat UI manapun (selalu di-set null saat assignment dibuat).
 */
class ReviewAssignmentLetterTest extends TestCase
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

    private function makeReviewer(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Reviewer Test ' . uniqid(),
            'email' => 'reviewer-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'reviewer',
        ], $overrides));
    }

    private function makeAssignment(array $overrides = []): ReviewAssignment
    {
        $assigner = $this->makeReviewer(['role' => 'admin']);
        $defaultReviewer = $this->makeReviewer();

        return ReviewAssignment::create(array_merge([
            'article_title' => 'Judul Artikel Test',
            'article_number' => 'ART-' . uniqid(),
            'submit_link' => 'https://example.test/artikel-' . uniqid(),
            'status' => 'PENDING',
            'reviewer_id' => $defaultReviewer->id,
            'assigned_by' => $assigner->id,
        ], $overrides));
    }

    public function test_admin_can_upload_letter_file(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $assignment = $this->makeAssignment();

        $file = UploadedFile::fake()->create('surat-tugas.pdf', 500, 'application/pdf');

        $response = $this->post(route('admin.assignments.upload-letter', $assignment), [
            'assignment_letter_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $assignment->refresh();
        $this->assertNotNull($assignment->assignment_letter_file);
        Storage::disk('public')->assertExists($assignment->assignment_letter_file);
    }

    public function test_admin_can_set_letter_link_without_file(): void
    {
        $this->actingAsAdmin();
        $assignment = $this->makeAssignment();

        $response = $this->post(route('admin.assignments.upload-letter', $assignment), [
            'assignment_letter_link' => 'https://drive.example.test/surat-tugas-123',
        ]);

        $response->assertRedirect();
        $assignment->refresh();
        $this->assertSame('https://drive.example.test/surat-tugas-123', $assignment->assignment_letter_link);
        $this->assertNull($assignment->assignment_letter_file);
    }

    public function test_upload_letter_rejects_when_neither_file_nor_link_given(): void
    {
        $this->actingAsAdmin();
        $assignment = $this->makeAssignment();

        $response = $this->post(route('admin.assignments.upload-letter', $assignment), []);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $assignment->refresh();
        $this->assertNull($assignment->assignment_letter_link);
        $this->assertNull($assignment->assignment_letter_file);
    }

    public function test_upload_letter_rejects_invalid_link_format(): void
    {
        $this->actingAsAdmin();
        $assignment = $this->makeAssignment();

        $response = $this->post(route('admin.assignments.upload-letter', $assignment), [
            'assignment_letter_link' => 'bukan-url-valid',
        ]);

        $response->assertSessionHasErrors('assignment_letter_link');
    }

    public function test_admin_show_page_displays_submit_button_when_letter_not_yet_set(): void
    {
        $this->actingAsAdmin();
        $assignment = $this->makeAssignment();

        $response = $this->get(route('admin.assignments.show', $assignment));

        $response->assertOk();
        $response->assertSee('Submit Surat Tugas');
        $response->assertSee('Surat tugas belum diunggah');
    }

    public function test_admin_show_page_displays_download_and_link_buttons_when_letter_is_set(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();
        $assignment = $this->makeAssignment([
            'assignment_letter_file' => 'assignments/letters/dummy.pdf',
            'assignment_letter_link' => 'https://drive.example.test/surat',
        ]);

        $response = $this->get(route('admin.assignments.show', $assignment));

        $response->assertOk();
        $response->assertSee('Ganti Surat Tugas');
        $response->assertSee('Download File');
        $response->assertSee('Buka Link');
    }

    public function test_reviewer_task_page_shows_download_button_when_letter_file_is_set(): void
    {
        $reviewer = $this->makeReviewer();
        $assignment = $this->makeAssignment([
            'reviewer_id' => $reviewer->id,
            'assignment_letter_file' => 'assignments/letters/dummy.pdf',
        ]);
        $this->actingAs($reviewer);

        $response = $this->get(route('reviewer.tasks.show', $assignment));

        $response->assertOk();
        $response->assertSee('Unduh Surat Tugas');
    }

    public function test_reviewer_task_page_shows_link_button_when_only_letter_link_is_set(): void
    {
        $reviewer = $this->makeReviewer();
        $assignment = $this->makeAssignment([
            'reviewer_id' => $reviewer->id,
            'assignment_letter_link' => 'https://drive.example.test/surat',
        ]);
        $this->actingAs($reviewer);

        $response = $this->get(route('reviewer.tasks.show', $assignment));

        $response->assertOk();
        $response->assertSee('Buka Surat Tugas');
        $response->assertSee('https://drive.example.test/surat', false);
    }

    public function test_reviewer_task_page_shows_not_yet_uploaded_message_when_letter_absent(): void
    {
        $reviewer = $this->makeReviewer();
        $assignment = $this->makeAssignment(['reviewer_id' => $reviewer->id]);
        $this->actingAs($reviewer);

        $response = $this->get(route('reviewer.tasks.show', $assignment));

        $response->assertOk();
        $response->assertSee('Surat tugas belum diunggah admin');
    }

    /**
     * Reviewer di slot manapun (2-5, bukan cuma reviewer utama) harus tetap
     * bisa lihat surat tugas yang sama — konsisten dengan perbaikan
     * leaderboard sebelumnya yang menegaskan assignment bisa punya 5 reviewer.
     */
    public function test_reviewer_2_can_also_see_the_same_letter(): void
    {
        $reviewer2 = $this->makeReviewer();
        $assignment = $this->makeAssignment([
            'reviewer_2_id' => $reviewer2->id,
            'assignment_letter_link' => 'https://drive.example.test/surat',
        ]);
        $this->actingAs($reviewer2);

        $response = $this->get(route('reviewer.tasks.show', $assignment));

        $response->assertOk();
        $response->assertSee('Buka Surat Tugas');
    }
}

<?php

namespace Tests\Feature\Points;

use App\Models\PicPointHistory;
use App\Models\TaskPointSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk insiden poin PIC 28 Juli 2026: pengecekan "sudah ada atau belum"
 * di awardPoints() tidak atomik, menyebabkan baris kembar kalau 2 permintaan datang
 * hampir bersamaan (klik ganda, retry jaringan). Diperbaiki dengan UNIQUE index
 * (pic_id, submission_id, step) + penanganan exception yang aman.
 */
class PicPointHistoryAwardTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    public function test_award_points_creates_a_history_row_using_configured_rate(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();

        TaskPointSetting::updateOrCreate(
            ['user_type' => 'pic', 'task_key' => 'submit'],
            ['task_label' => 'Submit', 'points' => 0.25, 'is_active' => true]
        );

        $history = PicPointHistory::awardPoints($pic->id, $submission->id, 'submit', 'Test award');

        $this->assertNotNull($history);
        $this->assertEquals(0.25, $history->points_earned);
        $this->assertEquals(0.25, $pic->fresh()->total_points);
    }

    public function test_award_points_is_idempotent_via_application_level_check(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();

        $first = PicPointHistory::awardPoints($pic->id, $submission->id, 'submit', 'First');
        $second = PicPointHistory::awardPoints($pic->id, $submission->id, 'submit', 'Second attempt');

        $this->assertNotNull($first);
        $this->assertNull($second, 'Panggilan kedua untuk kombinasi pic+submission+step yang sama harus null');
        $this->assertEquals(1, PicPointHistory::where('pic_id', $pic->id)
            ->where('submission_id', $submission->id)
            ->where('step', 'submit')
            ->count());
    }

    /**
     * Test inti insiden 28 Juli: buktikan database MENOLAK baris kembar untuk
     * kombinasi (pic_id, submission_id, step) yang sama lewat UNIQUE constraint —
     * ini jaring pengaman terakhir kalau pengecekan aplikasi di awardPoints() kalah
     * cepat oleh race condition (2 request bersamaan, keduanya lolos pengecekan
     * sebelum salah satu sempat tersimpan — persis pola 3 baris kembar yang
     * dilaporkan user, semua dalam detik yang sama).
     */
    public function test_database_rejects_duplicate_pic_submission_step_combination(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();

        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
            'points_earned' => 1, 'description' => 'Baris pertama',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->expectExceptionMessageMatches('/pic_point_histories_unique_award/');

        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
            'points_earned' => 1, 'description' => 'Percobaan kembar',
        ]);
    }

    /**
     * Buktikan awardPoints() sendiri tidak pernah crash kalau baris untuk kombinasi
     * yang sama SUDAH ADA saat dipanggil (skenario paling umum: request kedua
     * datang setelah request pertama selesai tersimpan) — mengembalikan null
     * dengan mulus lewat pengecekan aplikasi, tidak pernah sampai ke exception.
     */
    public function test_award_points_returns_null_without_throwing_when_row_already_exists(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();

        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => $submission->id, 'step' => 'submit',
            'points_earned' => 1, 'description' => 'Sudah ada duluan',
        ]);

        $result = PicPointHistory::awardPoints($pic->id, $submission->id, 'submit', 'Percobaan kedua');

        $this->assertNull($result);
    }

    public function test_manual_adjustment_rows_are_not_constrained_by_unique_index(): void
    {
        $pic = $this->makePic();

        // submission_id NULL (penyesuaian manual) — harus bisa dibuat berkali-kali
        // untuk PIC yang sama tanpa ditolak unique constraint.
        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => null, 'step' => 'adjustment',
            'points_earned' => 10, 'description' => 'Bonus 1',
        ]);
        PicPointHistory::create([
            'pic_id' => $pic->id, 'submission_id' => null, 'step' => 'adjustment',
            'points_earned' => 5, 'description' => 'Bonus 2',
        ]);

        $this->assertEquals(2, PicPointHistory::where('pic_id', $pic->id)->where('step', 'adjustment')->count());
    }

    public function test_get_points_for_step_uses_active_task_point_setting_over_fallback(): void
    {
        TaskPointSetting::updateOrCreate(
            ['user_type' => 'pic', 'task_key' => 'editor1'],
            ['task_label' => 'Editor 1', 'points' => 0.1, 'is_active' => true]
        );

        $this->assertEquals(0.1, PicPointHistory::getPointsForStep('editor1'));
    }

    public function test_get_points_for_step_falls_back_to_default_when_no_active_setting(): void
    {
        // Migration awal men-seed baris 'production' aktif — nonaktifkan dulu di sini
        // supaya benar-benar menguji jalur fallback ke POINT_CONFIG, bukan kebetulan
        // cocok dengan nilai seed (keduanya sama-sama 1).
        TaskPointSetting::where('user_type', 'pic')->where('task_key', 'production')->delete();

        $this->assertEquals(1, PicPointHistory::getPointsForStep('production'));
    }

    /**
     * Regresi untuk bug "tanggal kerja berubah" (28 Juli 2026): 3 tempat yang
     * mengaward poin secara langsung/real-time (JournalManagementController,
     * SubmissionController::validateStep, SubmissionController::toggleValidField)
     * dulu tidak mengirim $occurredAt ke awardPoints() — kalau baris poin baru
     * berhasil tersimpan belakangan (race/retry), tanggalnya jadi "sekarang", bukan
     * tanggal validasi sebenarnya. Test ini mengunci kontrak: kalau $occurredAt
     * dikirim, created_at HARUS mengikutinya, bukan waktu saat baris dibuat.
     */
    public function test_award_points_backdates_created_at_to_provided_occurred_at(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission();
        $occurredAt = now()->subDays(3);

        $history = PicPointHistory::awardPoints($pic->id, $submission->id, 'editor1', 'Validasi Editor 1', $occurredAt);

        $this->assertEquals($occurredAt->format('Y-m-d H:i:s'), $history->created_at->format('Y-m-d H:i:s'));
    }
}

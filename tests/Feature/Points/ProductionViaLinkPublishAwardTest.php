<?php

namespace Tests\Feature\Points;

use App\Models\MarketingPointHistory;
use App\Models\PicPointHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk insiden 29 Juli 2026: PIC yang mengisi "Link Publish" untuk submission
 * fasttrack yang BELUM punya petugas_production_id (mengisi lewat text field, bukan
 * klik tombol centang validasi) auto-assigned jadi petugas production & production_valid
 * ikut ter-set true lewat JournalManagementController::updateCredential() — tapi jalur ini
 * tidak pernah memanggil awardPoints() sama sekali (beda dari toggleValidation() yang
 * sudah benar). Akibatnya PIC tsb tidak dapat poin DAN tidak muncul di Laporan Kinerja
 * sama sekali (production_validated_at ikut tidak pernah ter-set, sehingga query laporan
 * yang di-filter per tanggal tidak pernah menghitungnya).
 */
class ProductionViaLinkPublishAwardTest extends TestCase
{
    use RefreshDatabase;
    use CreatesPointTestFixtures;

    public function test_filling_link_publish_on_unassigned_submission_awards_pic_points(): void
    {
        $pic = $this->makePic();
        $marketing = $this->makeMarketing();
        $submission = $this->makeSubmission([
            'process_type' => 'fasttrack',
            'status' => 'PRODUCTION_PROCESS',
            'marketing_id' => $marketing->id,
            'petugas_production_id' => null,
            'production_valid' => false,
        ]);

        $this->actingAs($pic, 'pic')
            ->post(route('pic.fasttrack.update-credential'), [
                'submission_id' => $submission->id,
                'field' => 'link_publish',
                'value' => 'https://example.test/published-article',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $submission->refresh();

        $this->assertEquals($pic->id, $submission->petugas_production_id);
        $this->assertTrue((bool) $submission->production_valid);
        $this->assertNotNull($submission->production_validated_at);

        $this->assertDatabaseHas('pic_point_histories', [
            'pic_id' => $pic->id,
            'submission_id' => $submission->id,
            'step' => 'production',
        ]);

        $this->assertDatabaseHas('marketing_point_histories', [
            'marketing_id' => $marketing->id,
            'submission_id' => $submission->id,
        ]);

        $pic->refresh();
        $marketing->refresh();

        $this->assertEquals(
            PicPointHistory::where('pic_id', $pic->id)->sum('points_earned'),
            (float) $pic->total_points
        );
        $this->assertEquals(
            MarketingPointHistory::where('marketing_id', $marketing->id)->sum('points_earned'),
            (float) $marketing->total_points
        );
    }

    public function test_unvalidating_and_refilling_link_publish_does_not_duplicate_points(): void
    {
        $pic = $this->makePic();
        $submission = $this->makeSubmission([
            'process_type' => 'fasttrack',
            'status' => 'PRODUCTION_PROCESS',
            'petugas_production_id' => null,
            'production_valid' => false,
        ]);

        $this->actingAs($pic, 'pic')
            ->post(route('pic.fasttrack.update-credential'), [
                'submission_id' => $submission->id,
                'field' => 'link_publish',
                'value' => 'https://example.test/published-article',
            ])
            ->assertOk();

        // Link publish terkunci selama production_valid true — admin/PIC harus matikan
        // validasi dulu (tombol centang) sebelum bisa edit link lagi.
        $this->actingAs($pic, 'pic')
            ->post(route('pic.fasttrack.toggle-validation'), [
                'submission_id' => $submission->id,
                'field' => 'production_valid',
                'value' => false,
            ])
            ->assertOk();

        // Isi ulang link publish (mis. perbaiki typo) — tidak boleh dobel poin, karena
        // PicPointHistory::awardPoints() sudah idempoten per (pic_id, submission_id, step).
        $this->actingAs($pic, 'pic')
            ->post(route('pic.fasttrack.update-credential'), [
                'submission_id' => $submission->id,
                'field' => 'link_publish',
                'value' => 'https://example.test/published-article-fixed',
            ])
            ->assertOk();

        $this->assertEquals(
            1,
            PicPointHistory::where('pic_id', $pic->id)
                ->where('submission_id', $submission->id)
                ->where('step', 'production')
                ->count()
        );
    }
}

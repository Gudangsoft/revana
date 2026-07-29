<?php

namespace Tests\Feature;

use App\Http\Controllers\Reviewer\CertificateController;
use App\Models\Certificate;
use App\Models\ReviewAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur baru 30 Juli 2026: QR code verifikasi di sertifikat reviewer, dituju ke
 * halaman publik (tidak perlu login) yang menampilkan status keaslian review.
 * Dirender pakai GD murni (bukan lewat simplesoftwareio/simple-qrcode langsung)
 * karena server tidak punya ekstensi imagick yang dibutuhkan paket itu untuk
 * output PNG — lihat docblock CertificateController::renderQrPng().
 */
class ReviewerCertificateVerifyTest extends TestCase
{
    use RefreshDatabase;

    private function makeReviewer(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Reviewer Test ' . uniqid(),
            'email' => 'reviewer-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role' => 'reviewer',
        ], $overrides));
    }

    private function makeApprovedAssignment(array $overrides = []): ReviewAssignment
    {
        $assigner = $this->makeReviewer(['role' => 'admin']);
        // reviewer_id NOT NULL di skema — default ke reviewer dummy kalau test
        // sengaja cuma mau isi reviewer_2_id (skenario "reviewer 2").
        $defaultReviewer = $this->makeReviewer();

        return ReviewAssignment::create(array_merge([
            'article_title' => 'Judul Artikel Test',
            'article_number' => 'ART-' . uniqid(),
            'submit_link' => 'https://example.test/artikel-' . uniqid(),
            'status' => 'APPROVED',
            'approved_at' => now(),
            'reviewer_id' => $defaultReviewer->id,
            'assigned_by' => $assigner->id,
        ], $overrides));
    }

    public function test_verify_page_shows_valid_details_for_reviewer_1_of_approved_assignment(): void
    {
        $reviewer = $this->makeReviewer(['name' => 'Dr. Test Reviewer']);
        $assignment = $this->makeApprovedAssignment(['reviewer_id' => $reviewer->id]);

        $response = $this->get(route('reviewer-certificate.verify', [
            'assignment' => $assignment->id,
            'reviewerId' => $reviewer->id,
        ]));

        $response->assertOk();
        $response->assertSee('Terverifikasi');
        $response->assertSee('Dr. Test Reviewer');
        $response->assertSee('REVIEWER 1');
        $response->assertSee('Judul Artikel Test');
    }

    public function test_verify_page_shows_valid_details_for_reviewer_2_of_approved_assignment(): void
    {
        $reviewer2 = $this->makeReviewer(['name' => 'Dr. Reviewer Dua']);
        $assignment = $this->makeApprovedAssignment(['reviewer_2_id' => $reviewer2->id]);

        $response = $this->get(route('reviewer-certificate.verify', [
            'assignment' => $assignment->id,
            'reviewerId' => $reviewer2->id,
        ]));

        $response->assertOk();
        $response->assertSee('Terverifikasi');
        $response->assertSee('REVIEWER 2');
    }

    public function test_verify_page_shows_invalid_for_reviewer_not_part_of_assignment(): void
    {
        $realReviewer = $this->makeReviewer();
        $unrelatedUser = $this->makeReviewer();
        $assignment = $this->makeApprovedAssignment(['reviewer_id' => $realReviewer->id]);

        $response = $this->get(route('reviewer-certificate.verify', [
            'assignment' => $assignment->id,
            'reviewerId' => $unrelatedUser->id,
        ]));

        $response->assertOk();
        $response->assertSee('Tidak Ditemukan');
    }

    public function test_verify_page_shows_invalid_for_non_approved_assignment(): void
    {
        $reviewer = $this->makeReviewer();
        $assignment = $this->makeApprovedAssignment([
            'reviewer_id' => $reviewer->id,
            'status' => 'SUBMITTED',
        ]);

        $response = $this->get(route('reviewer-certificate.verify', [
            'assignment' => $assignment->id,
            'reviewerId' => $reviewer->id,
        ]));

        $response->assertOk();
        $response->assertSee('Tidak Ditemukan');
    }

    public function test_verify_page_shows_configured_logo_when_set(): void
    {
        \App\Models\Setting::set('logo', 'settings/test-logo.png');
        $reviewer = $this->makeReviewer();
        $assignment = $this->makeApprovedAssignment(['reviewer_id' => $reviewer->id]);

        $response = $this->get(route('reviewer-certificate.verify', [
            'assignment' => $assignment->id,
            'reviewerId' => $reviewer->id,
        ]));

        $response->assertOk();
        $response->assertSee('storage/settings/test-logo.png', false);
    }

    public function test_verify_page_falls_back_to_app_name_text_when_no_logo_configured(): void
    {
        \App\Models\Setting::set('logo', '');
        \App\Models\Setting::set('app_name', 'SIPERA');
        $reviewer = $this->makeReviewer();
        $assignment = $this->makeApprovedAssignment(['reviewer_id' => $reviewer->id]);

        $response = $this->get(route('reviewer-certificate.verify', [
            'assignment' => $assignment->id,
            'reviewerId' => $reviewer->id,
        ]));

        $response->assertOk();
        $response->assertSee('class="text-logo"', false);
        $response->assertSee('SIPERA');
    }

    public function test_verify_page_shows_invalid_for_nonexistent_assignment(): void
    {
        $response = $this->get(route('reviewer-certificate.verify', [
            'assignment' => 999999,
            'reviewerId' => 999999,
        ]));

        $response->assertOk();
        $response->assertSee('Tidak Ditemukan');
    }

    /**
     * Regresi 30 Juli 2026: judul artikel panjang meluber keluar dari border kiri
     * & kanan sertifikat (dilaporkan user via screenshot) karena wrapping lama
     * (wordwrap 100 karakter) tidak memperhitungkan lebar piksel sesungguhnya di
     * ukuran font 60 — bisa jadi lebih lebar dari kanvas sertifikat sendiri.
     * wrapTextByWidth() sekarang mengukur lebar piksel asli (imagettfbbox) dan
     * MENJAMIN setiap baris tidak pernah melebihi batas yang ditentukan.
     */
    public function test_wrap_text_by_width_never_exceeds_max_width_for_long_title(): void
    {
        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'wrapTextByWidth');
        $method->setAccessible(true);

        $font = file_exists(public_path('fonts/arial-bold.ttf'))
            ? public_path('fonts/arial-bold.ttf')
            : public_path('fonts/arial.ttf');

        // Judul persis yang dilaporkan overflow.
        $title = 'Perkembangan Budaya Islam Kontemporer dan Inovasi Produksi Konten Keagamaan di Ranah Digital';
        $maxWidth = 2455; // 70% dari lebar kanvas 3508px, sama seperti di generateCertificate()

        $lines = $method->invoke($controller, $title, $font, 60, $maxWidth);

        $this->assertGreaterThan(1, count($lines), 'Judul sepanjang ini harus terbagi lebih dari 1 baris');

        foreach ($lines as $line) {
            $bbox = imagettfbbox(60, 0, $font, $line);
            $lineWidth = abs($bbox[4] - $bbox[0]);
            $this->assertLessThanOrEqual($maxWidth, $lineWidth,
                "Baris \"{$line}\" selebar {$lineWidth}px, melebihi batas {$maxWidth}px");
        }

        // Rekonstruksi ulang harus tetap mengandung semua kata asli (tidak ada
        // kata yang hilang/dipenggal saat dibungkus).
        $this->assertEquals(
            preg_replace('/\s+/', ' ', $title),
            implode(' ', $lines)
        );
    }

    public function test_wrap_text_by_width_keeps_short_title_on_a_single_line(): void
    {
        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'wrapTextByWidth');
        $method->setAccessible(true);

        $font = file_exists(public_path('fonts/arial-bold.ttf'))
            ? public_path('fonts/arial-bold.ttf')
            : public_path('fonts/arial.ttf');

        $lines = $method->invoke($controller, 'Judul Pendek', $font, 60, 2455);

        $this->assertCount(1, $lines);
        $this->assertEquals('Judul Pendek', $lines[0]);
    }

    public function test_render_qr_png_produces_a_valid_decodable_png(): void
    {
        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'renderQrPng');
        $method->setAccessible(true);

        $png = $method->invoke($controller, 'https://portal.apji.org/verify/sertifikat-reviewer/1/1');

        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);
        $decoded = @imagecreatefromstring($png);
        $this->assertNotFalse($decoded, 'PNG hasil renderQrPng() harus bisa didekode ulang oleh GD');
    }

    /**
     * Uji end-to-end generateCertificate() dengan background dummy (file template
     * aktif sesungguhnya tidak ada di lokal) — membuktikan seluruh pipeline
     * termasuk overlay QR baru berjalan tanpa error dan menghasilkan file gambar.
     */
    public function test_generate_certificate_completes_with_qr_overlay_using_dummy_background(): void
    {
        $reviewer = $this->makeReviewer();
        $assignment = $this->makeApprovedAssignment(['reviewer_id' => $reviewer->id]);

        $certificate = Certificate::create([
            'name' => 'Template Test',
            'file_path' => 'certificates/test-' . uniqid() . '.jpg',
            'is_active' => true,
        ]);

        $dummyPath = storage_path('app/public/' . $certificate->file_path);
        @mkdir(dirname($dummyPath), 0755, true);
        $im = imagecreatetruecolor(1200, 850);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
        imagejpeg($im, $dummyPath, 80);
        imagedestroy($im);

        $this->actingAs($reviewer);

        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'generateCertificate');
        $method->setAccessible(true);
        $result = $method->invoke($controller, $assignment, true);

        $this->assertNotFalse($result);
        $fullPath = public_path($result);
        $this->assertFileExists($fullPath);
        $this->assertGreaterThan(0, filesize($fullPath));

        @unlink($fullPath);
        @unlink($dummyPath);
    }
}

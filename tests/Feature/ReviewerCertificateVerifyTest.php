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

    /**
     * Regresi 30 Juli 2026 (lanjutan): nama reviewer sebelumnya dirender 1 baris
     * tanpa pengaman lebar sama sekali (beda dari judul artikel yang sudah
     * diperbaiki) — nama panjang dengan banyak gelar akademik berisiko meluber
     * keluar border persis seperti kasus judul artikel. Sekarang pakai
     * wrapTextByWidth() yang sama, di ukuran font nama sesungguhnya (80).
     */
    public function test_wrap_text_by_width_never_exceeds_max_width_for_long_reviewer_name(): void
    {
        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'wrapTextByWidth');
        $method->setAccessible(true);

        $font = file_exists(public_path('fonts/arial-bold.ttf'))
            ? public_path('fonts/arial-bold.ttf')
            : public_path('fonts/arial.ttf');

        $name = strtoupper('Prof. Dr. H. Muhammad Abdurrahman Wahyu Kusuma Wardhana, S.Pd., M.Pd., Ph.D.');
        $maxWidth = 2455; // 70% dari lebar kanvas 3508px, sama seperti di generateCertificate()

        $lines = $method->invoke($controller, $name, $font, 80, $maxWidth);

        $this->assertGreaterThan(1, count($lines), 'Nama sepanjang ini harus terbagi lebih dari 1 baris');

        foreach ($lines as $line) {
            $bbox = imagettfbbox(80, 0, $font, $line);
            $lineWidth = abs($bbox[4] - $bbox[0]);
            $this->assertLessThanOrEqual($maxWidth, $lineWidth,
                "Baris \"{$line}\" selebar {$lineWidth}px, melebihi batas {$maxWidth}px");
        }
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

    /**
     * Regresi 9 Sept 2026: nama reviewer & judul artikel tumpang tindih dengan
     * paragraf tetap di template sertifikat (dilaporkan user via screenshot) —
     * posisi Y sebelumnya tetap (fixed: 1120 & 1500) tidak peduli berapa
     * banyak baris yang dihasilkan wrapTextByWidth(), jadi kalau nama/judul
     * menghasilkan lebih banyak baris dari yang diperkirakan, teks meluber ke
     * zona paragraf tetangga. wrapTextWithAutoShrink() menambahkan pengaman:
     * kalau baris > $maxLines, font dikecilkan bertahap sampai muat atau
     * sampai $minFontSize tercapai.
     */
    private function font(): string
    {
        return file_exists(public_path('fonts/arial-bold.ttf'))
            ? public_path('fonts/arial-bold.ttf')
            : public_path('fonts/arial.ttf');
    }

    public function test_wrap_text_with_auto_shrink_keeps_original_font_size_when_lines_already_fit(): void
    {
        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'wrapTextWithAutoShrink');
        $method->setAccessible(true);

        [$lines, $fontSize] = $method->invoke($controller, 'Dr. Test Reviewer', $this->font(), 80, 50, 2455, 2);

        $this->assertSame(80, $fontSize, 'Teks pendek tidak perlu mengecilkan font sama sekali');
        $this->assertCount(1, $lines);
    }

    public function test_wrap_text_with_auto_shrink_reduces_font_size_for_reviewer_name_with_many_gelar(): void
    {
        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'wrapTextWithAutoShrink');
        $method->setAccessible(true);

        // Nama + gelar akademik sangat panjang: 3 baris di font 80 (>maxLines
        // 2 yang aman untuk zona nama), harus mengecil sampai muat 2 baris.
        $name = strtoupper('Prof. Dr. H. Muhammad Abdurrahman Wahyu Kusuma Wardhana Al Faruqi Nasution, S.Pd., M.Pd., M.Hum., Ph.D.');
        $maxWidth = (int) (3508 * 0.70);

        [$lines, $fontSize] = $method->invoke($controller, $name, $this->font(), 80, 50, $maxWidth, 2);

        $this->assertLessThan(80, $fontSize, 'Font seharusnya dikecilkan karena nama menghasilkan >2 baris di ukuran asli');
        $this->assertCount(2, $lines, 'Setelah dikecilkan, nama harus muat dalam 2 baris (batas aman zona nama)');

        // Tidak ada kata yang hilang/dipenggal selama proses shrink+rewrap.
        $this->assertEquals(
            preg_replace('/\s+/', ' ', $name),
            implode(' ', $lines)
        );
    }

    public function test_wrap_text_with_auto_shrink_reduces_font_size_for_very_long_article_title(): void
    {
        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'wrapTextWithAutoShrink');
        $method->setAccessible(true);

        // Judul sangat panjang: 6 baris di font 60 (>maxLines 4 yang aman
        // untuk zona judul), harus mengecil sampai muat 4 baris.
        $title = 'Analisis Komprehensif Dampak Transformasi Digital Terhadap Perkembangan Budaya '
            . 'Islam Kontemporer Inovasi Produksi Konten Keagamaan Ranah Digital Serta '
            . 'Implikasinya Bagi Generasi Muda Di Era Globalisasi Modern Saat Ini Dan '
            . 'Tantangan Depan Bagi Masyarakat';
        $maxWidth = (int) (3508 * 0.70);

        [$lines, $fontSize] = $method->invoke($controller, $title, $this->font(), 60, 40, $maxWidth, 4);

        $this->assertLessThan(60, $fontSize, 'Font seharusnya dikecilkan karena judul menghasilkan >4 baris di ukuran asli');
        $this->assertLessThanOrEqual(4, count($lines), 'Setelah dikecilkan, judul harus muat dalam 4 baris (batas aman zona judul)');
        $this->assertGreaterThanOrEqual(40, $fontSize, 'Font tidak boleh dikecilkan melewati batas minimum');
    }

    public function test_wrap_text_with_auto_shrink_stops_at_min_font_size_even_if_still_too_many_lines(): void
    {
        $controller = new CertificateController();
        $method = new \ReflectionMethod($controller, 'wrapTextWithAutoShrink');
        $method->setAccessible(true);

        $name = strtoupper('Prof. Dr. H. Muhammad Abdurrahman Wahyu Kusuma Wardhana Al Faruqi Nasution, S.Pd., M.Pd., M.Hum., Ph.D.');
        $maxWidth = (int) (3508 * 0.70);

        // maxLines=1 sengaja mustahil dicapai teks ini walau font dikecilkan
        // habis-habisan — pastikan fungsi berhenti di $minFontSize (tidak
        // infinite loop, tidak mengecil sampai tak terbaca) walau constraint
        // belum sepenuhnya terpenuhi.
        [$lines, $fontSize] = $method->invoke($controller, $name, $this->font(), 80, 50, $maxWidth, 1);

        $this->assertSame(50, $fontSize, 'Harus berhenti tepat di minFontSize, tidak lebih kecil lagi');
        $this->assertGreaterThan(1, count($lines), 'Constraint maxLines=1 memang tidak realistis dicapai teks ini, best-effort saja');
    }

    /**
     * Uji integrasi: generateCertificate() dengan nama & judul yang SANGAT
     * panjang (skenario yang dulu memicu tumpang tindih) tetap selesai tanpa
     * error dan menghasilkan file — membuktikan wrapTextWithAutoShrink() +
     * perhitungan Y ber-center di generateCertificate() bekerja end-to-end,
     * bukan cuma di unit function-nya saja.
     */
    public function test_generate_certificate_completes_with_very_long_name_and_title_using_dummy_background(): void
    {
        $reviewer = $this->makeReviewer([
            'name' => 'Prof. Dr. H. Muhammad Abdurrahman Wahyu Kusuma Wardhana Al Faruqi Nasution, S.Pd., M.Pd., M.Hum., Ph.D.',
        ]);
        $assignment = $this->makeApprovedAssignment([
            'reviewer_id' => $reviewer->id,
            'article_title' => 'Analisis Komprehensif Dampak Transformasi Digital Terhadap Perkembangan Budaya '
                . 'Islam Kontemporer Inovasi Produksi Konten Keagamaan Ranah Digital Serta '
                . 'Implikasinya Bagi Generasi Muda Di Era Globalisasi Modern Saat Ini Dan '
                . 'Tantangan Depan Bagi Masyarakat',
        ]);

        $certificate = Certificate::create([
            'name' => 'Template Test',
            'file_path' => 'certificates/test-' . uniqid() . '.jpg',
            'is_active' => true,
        ]);

        $dummyPath = storage_path('app/public/' . $certificate->file_path);
        @mkdir(dirname($dummyPath), 0755, true);
        $im = imagecreatetruecolor(2560, 1811);
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

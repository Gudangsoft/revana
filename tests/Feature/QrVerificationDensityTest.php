<?php

namespace Tests\Feature;

use App\Models\JournalMaster;
use App\Models\JournalSlot;
use App\Models\Submission;
use App\Models\User;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi 19 Agustus 2026: QR "Scan untuk verifikasi" di Kwitansi/Invoice
 * meng-encode SELURUH parameter pembayaran (termasuk `keterangan` = judul
 * artikel penuh) ke dalam URL. Untuk artikel jurnal akademik berjudul panjang
 * (rutin 200+ karakter), ini menghasilkan QR Version 19 (93x93 modul) — pada
 * ukuran render 80x80px cuma 0.86 piksel/modul, jauh di bawah ambang minimum
 * kamera HP mana pun bisa scan. QR terlihat "ada" secara visual tapi praktis
 * tidak bisa discan.
 *
 * Diperbaiki: QR sekarang pakai URL pendek terpisah (qrVerifyUrl(), cuma
 * jumlah/metode_bayar/tanggal — TANPA nama_pembayar/keterangan), sementara
 * link "Kirim ke Author" (publicPdfUrl()) TETAP membawa semua parameter
 * seperti semula (tidak ada regresi di fitur share/kirim dokumen). Ukuran
 * render QR juga dinaikkan dari 80 ke 160px sebagai margin aman tambahan.
 */
class QrVerificationDensityTest extends TestCase
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

    private function makeSubmissionWithLongTitle(): Submission
    {
        $user = User::create([
            'name' => 'Creator', 'email' => 'creator-' . uniqid() . '@example.test',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $journal = JournalMaster::create([
            'kode_jurnal' => 'JRN-' . uniqid(),
            'nama_jurnal' => 'Jurnal Test Panjang',
            'publisher' => 'Penerbit Test',
            'link_jurnal' => 'https://example.test/jurnal',
            'created_by' => $user->id,
            'is_active' => true,
        ]);
        $slot = JournalSlot::create([
            'kode_slot' => 'SLOT-' . uniqid(),
            'journal_master_id' => $journal->id,
            'volume' => '1', 'nomor' => '1', 'bulan' => 'Januari', 'tahun' => 2026,
            'jumlah_slot' => 100, 'created_by' => $user->id,
        ]);

        // Judul artikel super panjang — meniru kasus nyata jurnal akademik
        // (lihat log-update: contoh nyata di database mencapai 250+ karakter).
        $longTitle = 'Implementasi Instruksi Presiden Republik Indonesia Nomor 1 Tahun 2025 Tentang '
            . 'Efisiensi Belanja Dalam Pelaksanaan Anggaran Pendapatan Dan Belanja Negara (APBN) Dan '
            . 'Anggaran Pendapatan Dan Belanja Daerah (APBD) Dan Dampaknya Terhadap Program Prioritas '
            . 'Di Dinas Pendidikan Dan Kebudayaan Provinsi Nusa Tenggara Timur';

        return Submission::create([
            'kode_submit' => 'SUB-' . uniqid(),
            'journal_slot_id' => $slot->id,
            'id_artikel' => 'ART-' . uniqid(),
            'judul_artikel' => $longTitle,
            'nama_penulis' => 'Penulis Test Dengan Nama Cukup Panjang, Rekan Penulis Kedua',
            'created_by' => $user->id,
            'status' => 'SUBMITTED',
        ]);
    }

    /** Modul QR pada size 160px harus tetap >= 2.5 px/modul supaya realistis discan kamera HP. */
    private function assertQrIsScannable(string $url): void
    {
        $qr = Encoder::encode($url, ErrorCorrectionLevel::L());
        $matrixWidth = $qr->getMatrix()->getWidth();
        $pixelsPerModule = 160 / $matrixWidth;

        $this->assertGreaterThanOrEqual(
            2.5,
            $pixelsPerModule,
            "QR terlalu padat untuk discan: {$matrixWidth}x{$matrixWidth} modul -> {$pixelsPerModule} px/modul (URL " . strlen($url) . ' karakter)'
        );
    }

    public function test_kwitansi_qr_stays_scannable_for_long_article_title(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmissionWithLongTitle();

        $response = $this->get(route('admin.submissions.kwitansi', [
            'submission' => $submission,
            'nama_pembayar' => $submission->nama_penulis,
            'jumlah' => '500000',
        ]));

        $response->assertOk();
        $verifyUrl = $response->viewData('verifyUrl');
        $this->assertNotEmpty($verifyUrl);
        $this->assertQrIsScannable($verifyUrl);
    }

    public function test_kwitansi_share_link_still_carries_full_keterangan_and_nama(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmissionWithLongTitle();

        $response = $this->get(route('admin.submissions.kwitansi', [
            'submission' => $submission,
            'nama_pembayar' => $submission->nama_penulis,
            'jumlah' => '500000',
        ]));

        $response->assertOk();
        $publicPdfUrl = $response->viewData('publicPdfUrl');
        $this->assertStringContainsString(rawurlencode($submission->nama_penulis), $publicPdfUrl);
        $this->assertStringContainsString('keterangan=', $publicPdfUrl);
    }

    public function test_kwitansi_qr_short_url_still_produces_valid_pdf_when_fetched(): void
    {
        $submission = $this->makeSubmissionWithLongTitle();

        // Persis apa yang terjadi kalau QR benar-benar discan: fetch URL pendek
        // TANPA nama_pembayar/keterangan.
        $response = $this->get(route('kwitansi.public.pdf', [
            'kode_submit' => $submission->kode_submit,
            'jumlah' => '500000',
            'metode_bayar' => 'Transfer Bank',
            'tanggal' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_invoice_qr_stays_scannable_for_long_article_title(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmissionWithLongTitle();

        $response = $this->get(route('admin.submissions.invoice', [
            'submission' => $submission,
            'nama_pembayar' => $submission->nama_penulis,
            'jumlah' => '500000',
            'bank_name' => 'Bank Central Asia yang Namanya Cukup Panjang',
            'bank_account_holder' => 'PT Penerbit Jurnal Nasional Bersama',
        ]));

        $response->assertOk();
        $verifyUrl = $response->viewData('verifyUrl');
        $this->assertNotEmpty($verifyUrl);
        $this->assertQrIsScannable($verifyUrl);
    }

    public function test_invoice_share_link_still_carries_full_params(): void
    {
        $this->actingAsAdmin();
        $submission = $this->makeSubmissionWithLongTitle();

        $response = $this->get(route('admin.submissions.invoice', [
            'submission' => $submission,
            'nama_pembayar' => $submission->nama_penulis,
            'jumlah' => '500000',
        ]));

        $response->assertOk();
        $publicPdfUrl = $response->viewData('publicPdfUrl');
        $this->assertStringContainsString(rawurlencode($submission->nama_penulis), $publicPdfUrl);
    }
}

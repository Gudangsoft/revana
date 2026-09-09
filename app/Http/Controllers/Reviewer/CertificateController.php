<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ReviewAssignment;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class CertificateController extends Controller
{
    /**
     * Halaman verifikasi publik (tidak perlu login) — dituju oleh QR code yang
     * dicetak di sertifikat. Siapa pun yang scan bisa mengecek review-nya benar
     * asli & sudah disetujui, tanpa perlu akses ke sistem.
     */
    public function verify(int $assignmentId, int $reviewerId)
    {
        // Logo SIPERA (dikonfigurasi admin lewat Setting, sama seperti dipakai di
        // sidebar layouts/app.blade.php) — dikirim ke SEMUA hasil (valid maupun
        // tidak) supaya halaman publik ini tetap ada identitas resminya.
        $brand = [
            'logoUrl' => \App\Models\Setting::get('logo') ? asset('storage/' . \App\Models\Setting::get('logo')) : null,
            'appName' => \App\Models\Setting::get('app_name', 'SIPERA'),
        ];

        $assignment = ReviewAssignment::find($assignmentId);

        if (!$assignment || $assignment->status !== 'APPROVED') {
            return view('reviewer.certificates.verify', $brand + ['valid' => false]);
        }

        if ($assignment->reviewer_id != $reviewerId && $assignment->reviewer_2_id != $reviewerId) {
            return view('reviewer.certificates.verify', $brand + ['valid' => false]);
        }

        $reviewer = User::find($reviewerId);
        if (!$reviewer) {
            return view('reviewer.certificates.verify', $brand + ['valid' => false]);
        }

        $position = ($assignment->reviewer_id == $reviewerId) ? 'REVIEWER 1' : 'REVIEWER 2';

        // Pencocokan sama seperti generateCertificate() — lihat catatan di sana.
        $sourceSubmission = \App\Models\Submission::where('link_artikel', $assignment->submit_link)
            ->with('journalSlot.journalMaster')
            ->first();

        return view('reviewer.certificates.verify', $brand + [
            'valid' => true,
            'reviewerName' => $reviewer->name,
            'articleTitle' => $assignment->article_title,
            'articleNumber' => $assignment->article_number,
            'position' => $position,
            'approvedAt' => $assignment->approved_at,
            'namaJurnal' => $sourceSubmission?->journalSlot?->journalMaster?->nama_jurnal ?? '-',
            'namaPublisher' => $sourceSubmission?->journalSlot?->journalMaster?->publisher ?? '-',
            'nomorSurat' => $sourceSubmission ? ($sourceSubmission->kode_loa ?: $sourceSubmission->kode_submit) : '-',
        ]);
    }

    /**
     * Render QR code jadi PNG mentah pakai GD murni (bukan lewat paket
     * simplesoftwareio/simple-qrcode langsung) — server ini TIDAK punya ekstensi
     * imagick terpasang, dan format('png') paket itu HANYA didukung lewat backend
     * Imagick (dicek langsung: format('svg')/('eps') jalan, format('png') melempar
     * "You need to install the imagick extension"). SVG tidak bisa langsung
     * ditempel ke gambar sertifikat (JPEG raster) lewat Intervention Image (driver
     * GD tidak bisa decode SVG). Solusinya: ambil matrix QR mentah langsung dari
     * bacon/bacon-qr-code (dependency simple-qrcode, sudah terpasang) lalu gambar
     * sendiri modul-per-modul pakai GD — sama sekali tidak butuh imagick.
     */
    private function renderQrPng(string $content, int $moduleScale = 8, int $quietZoneModules = 2): string
    {
        $qrCode = Encoder::encode($content, ErrorCorrectionLevel::M());
        $matrix = $qrCode->getMatrix();
        $moduleCount = $matrix->getWidth();

        $size = ($moduleCount + $quietZoneModules * 2) * $moduleScale;
        $im = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $white);

        for ($y = 0; $y < $moduleCount; $y++) {
            for ($x = 0; $x < $moduleCount; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $px = ($x + $quietZoneModules) * $moduleScale;
                    $py = ($y + $quietZoneModules) * $moduleScale;
                    imagefilledrectangle($im, $px, $py, $px + $moduleScale - 1, $py + $moduleScale - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        return $png;
    }

    /**
     * Bungkus teks jadi beberapa baris supaya lebar hasil render TIDAK PERNAH
     * melebihi $maxWidthPx, diukur pakai lebar piksel SESUNGGUHNYA (imagettfbbox,
     * fungsi bawaan GD) — bukan tebak-tebakan jumlah karakter. Kata per kata
     * ditambahkan ke baris berjalan; begitu menambah 1 kata lagi akan melebihi
     * batas, baris ditutup dan kata itu jadi awal baris berikutnya.
     *
     * Catatan: kalau ada SATU kata yang sendirian sudah lebih lebar dari
     * $maxWidthPx (jarang terjadi untuk judul artikel normal), kata itu tetap
     * dibiarkan apa adanya di barisnya sendiri (tidak dipenggal di tengah kata).
     */
    private function wrapTextByWidth(string $text, string $fontFile, int $fontSize, int $maxWidthPx): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $candidate = $currentLine === '' ? $word : $currentLine . ' ' . $word;
            $bbox = imagettfbbox($fontSize, 0, $fontFile, $candidate);
            $candidateWidth = abs($bbox[4] - $bbox[0]);

            if ($candidateWidth > $maxWidthPx && $currentLine !== '') {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $candidate;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines ?: [$text];
    }

    /**
     * Sama seperti wrapTextByWidth(), tapi dengan pengaman jumlah baris: kalau
     * hasil bungkus masih melebihi $maxLines (artinya teks akan meluber keluar
     * zona vertikal yang tersedia di template), ukuran font DIKECILKAN
     * bertahap (langkah 5px) lalu dibungkus ulang, sampai muat dalam
     * $maxLines baris atau sampai $minFontSize tercapai (mana yang duluan).
     *
     * Ditambahkan 9 Sept 2026 sebagai perbaikan permanen dari bug nama
     * reviewer & judul artikel yang saling tumpang tindih di sertifikat —
     * sebelumnya posisi Y tetap (fixed) tidak peduli berapa baris teks yang
     * dihasilkan, jadi nama/judul yang panjang (banyak baris) akan menabrak
     * teks tetap lain di template. Dikombinasikan dengan perhitungan Y
     * ber-center di generateCertificate(), ini mencegah kasus yang sama
     * terulang untuk nama/judul yang lebih panjang lagi di masa depan.
     *
     * @return array{0: array<string>, 1: int} [$lines, $fontSizeAkhir]
     */
    private function wrapTextWithAutoShrink(string $text, string $fontFile, int $fontSize, int $minFontSize, int $maxWidthPx, int $maxLines): array
    {
        $lines = $this->wrapTextByWidth($text, $fontFile, $fontSize, $maxWidthPx);

        while (count($lines) > $maxLines && $fontSize > $minFontSize) {
            $fontSize -= 5;
            $lines = $this->wrapTextByWidth($text, $fontFile, $fontSize, $maxWidthPx);
        }

        return [$lines, $fontSize];
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get all approved assignments for this reviewer
        $assignments = ReviewAssignment::where(function($query) use ($user) {
                $query->where('reviewer_id', $user->id)
                      ->orWhere('reviewer_2_id', $user->id);
            })
            ->where('status', 'APPROVED')
            ->with(['reviewer', 'reviewer2'])
            ->latest()
            ->get();
        
        // Get active certificate templates
        $templates = Certificate::where('is_active', true)->get();
        
        // Get app settings
        $appSettings = [
            'app_name' => \App\Models\Setting::get('app_name', 'APJI Review System'),
        ];
        
        return view('reviewer.certificates.index', compact('assignments', 'templates', 'appSettings'));
    }

    public function view(ReviewAssignment $assignment)
    {
        // Cek apakah user adalah reviewer dari assignment ini
        if ($assignment->reviewer_id != auth()->id() && $assignment->reviewer_2_id != auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Cek apakah review sudah approved
        if ($assignment->status !== 'APPROVED') {
            return back()->with('error', 'Sertifikat hanya tersedia untuk review yang sudah disetujui');
        }

        // Generate certificate preview
        $certificatePath = $this->generateCertificate($assignment, true);
        
        if (!$certificatePath) {
            return back()->with('error', 'Gagal generate preview sertifikat');
        }

        return view('reviewer.certificates.view', compact('assignment', 'certificatePath'));
    }

    public function download(ReviewAssignment $assignment)
    {
        // Cek apakah user adalah reviewer dari assignment ini
        if ($assignment->reviewer_id != auth()->id() && $assignment->reviewer_2_id != auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Cek apakah review sudah approved
        if ($assignment->status !== 'APPROVED') {
            return back()->with('error', 'Sertifikat hanya tersedia untuk review yang sudah disetujui');
        }

        // Generate certificate
        $tempPath = $this->generateCertificate($assignment, false);
        
        if (!$tempPath) {
            return back()->with('error', 'Gagal generate sertifikat');
        }

        $reviewer = auth()->user();
        $filename = 'Sertifikat_' . str_replace(' ', '_', $reviewer->name) . '_' . $assignment->article_number . '.jpg';
        
        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    private function generateCertificate(ReviewAssignment $assignment, $forPreview = false)
    {
        // Get active certificate template
        $template = Certificate::where('is_active', true)->first();
        
        if (!$template) {
            return false;
        }

        $templatePath = storage_path('app/public/' . $template->file_path);
        
        if (!file_exists($templatePath)) {
            return false;
        }

        $reviewer = auth()->user();
        
        // Create image manager
        $manager = new ImageManager(new Driver());
        
        // Load template image
        $image = $manager->read($templatePath);
        
        // Get image dimensions
        $width = $image->width();
        $height = $image->height();
        
        // Prepare text data
        $year = $assignment->approved_at->format('Y');
        $date = $assignment->approved_at->format('d');
        $month = $assignment->approved_at->locale('id')->translatedFormat('F');
        $reviewerName = strtoupper($reviewer->name);
        $articleTitle = $assignment->article_title;
        $articleNumber = $assignment->article_number;

        // ReviewAssignment tidak menyimpan link balik ke jurnal aslinya (journal_id
        // selalu null, lihat ReviewAssignmentController::store()) — jadi nomor surat
        // (kode LOA), nama jurnal, dan nama publisher dicari lewat pencocokan
        // submit_link == submissions.link_artikel (dikonfirmasi cocok 100% untuk
        // semua assignment approved yang ada).
        $sourceSubmission = \App\Models\Submission::where('link_artikel', $assignment->submit_link)
            ->with('journalSlot.journalMaster')
            ->first();
        $nomorSurat    = $sourceSubmission ? ($sourceSubmission->kode_loa ?: $sourceSubmission->kode_submit) : '-';
        $namaJurnal    = $sourceSubmission?->journalSlot?->journalMaster?->nama_jurnal ?? '-';
        $namaPublisher = $sourceSubmission?->journalSlot?->journalMaster?->publisher ?? '-';
        
        // Get reviewer position
        $position = ($assignment->reviewer_id == auth()->id()) ? 'REVIEWER 1' : 'REVIEWER 2';

        // Font paths
        $fontBold = public_path('fonts/arial-bold.ttf');
        $fontRegular = public_path('fonts/arial.ttf');
        
        // Fallback to arial.ttf if bold not found
        if (!file_exists($fontBold)) {
            $fontBold = $fontRegular;
        }
        
        // Template positions (untuk template 2560x1811px atau proporsional)
        // Sesuaikan dengan desain template terbaru
        
        // Lebar aman untuk teks yang di-center (judul artikel — nama reviewer
        // punya lebar sendiri, $nameMaxWidthRatio, lihat di bawah).
        //
        // Perbaikan 9 Sept 2026 (lanjutan ke-4): dengan rasio lama 70%, judul
        // nyata yang dilaporkan ("PENGARUH CITRA MEREK...") harus dikecilkan
        // sampai font 30 supaya muat 2 baris — user menilai hasilnya jadi
        // KURANG BESAR / kurang terbaca. Rasio dinaikkan ke 88% (masih sisa
        // margin ±6% kiri-kanan utk border emas) supaya judul yang sama muat
        // 2 baris di font 40 (naik dari 30) tanpa perlu font sekecil itu.
        $maxTitleWidthRatio = 0.88;

        // --- Perbaikan 9 Sept 2026: layout tumpang tindih -----------------------
        // Sebelumnya $yNamePosition (1120) & $yArticlePosition (1500) adalah
        // angka TETAP yang ditebak tanpa bisa render ke file template AKTIF
        // (waktu itu file-nya tidak tersedia). Setelah user mengirim contoh
        // sertifikat asli beresolusi penuh (2560x1811), posisi tetap itu
        // ternyata jauh lebih rendah dari zona kosong sesungguhnya di template
        // (paragraf "Sertifikat ini diberikan kepada :" / "in Recognition of
        // Contribution..." dan "Sebagai bentuk penghargaan..." / "Thank you
        // your contribution...") — akibatnya nama reviewer & judul artikel
        // tercetak menabrak paragraf tetap tersebut.
        //
        // Diukur langsung dari contoh sertifikat asli itu (nilai dalam rasio
        // terhadap tinggi kanvas, supaya tetap benar walau template diganti
        // dengan resolusi lain yang proporsinya sama):
        // - Zona nama   : Y 599-838  dari tinggi 1811px (antara paragraf pembuka
        //                 dan "in Recognition of Contribution...")
        // - Zona judul  : Y 887-1231 dari tinggi 1811px (antara "Sebagai bentuk
        //                 penghargaan..." dan "Thank you your contribution...")
        //
        // Posisi Y sekarang dihitung supaya teks SELALU DI-CENTER VERTIKAL di
        // dalam zona tsb, berapa pun jumlah barisnya — bukan mulai dari titik
        // tetap seperti sebelumnya. Ditambah wrapTextWithAutoShrink(): kalau
        // nama/judul sangat panjang sampai baris yang dihasilkan tidak lagi
        // muat dengan aman di zona (>1 baris nama, >2 baris judul — lihat
        // update 9 Sept 2026 lanjutan di bawah), font DIKECILKAN bertahap
        // secara otomatis supaya tidak meluber ke luar zona.

        // Reviewer Name (center, di zona setelah "Sertifikat ini diberikan kepada :")
        //
        // Perbaikan 9 Sept 2026 (lanjutan, setelah dicek user pada sertifikat asli):
        // font 80 masih terlalu besar dibanding proporsi teks tetap di
        // template (nama jadi jauh lebih dominan dari sekitarnya) — diturunkan
        // ke 60.
        //
        // Perbaikan 9 Sept 2026 (lanjutan lagi): user minta NAMA dibuat 1
        // baris — nama nyata yang dilaporkan ("MARTINA ROSMAULINA MARBUN,
        // S.PD., M.HUM") lebarnya di font 60 ternyata 1828px, cuma sedikit
        // melebihi batas lebar 70% ($maxTitleWidthRatio, dipakai bersama utk
        // judul artikel) = 1792px, jadi kepotong ke 2 baris padahal harusnya
        // pas 1 baris. Nama dikasih lebar sendiri yang lebih lega
        // ($nameMaxWidthRatio 82% — masih sisa margin ±9% di kiri-kanan utk
        // border emas) supaya nama sepanjang ini muat 1 baris TANPA perlu
        // mengecilkan font sama sekali. Batas baris juga diperketat 2→1;
        // untuk nama yang jauh lebih panjang lagi (banyak gelar akademik),
        // wrapTextWithAutoShrink() akan mengecilkan font secara otomatis
        // sampai muat 1 baris.
        $nameMaxWidthRatio = 0.82;
        $nameFontSize = 60;
        $nameMinFontSize = 24;
        [$nameLines, $nameFontSize] = $this->wrapTextWithAutoShrink(
            $reviewerName, $fontBold, $nameFontSize, $nameMinFontSize,
            (int) ($width * $nameMaxWidthRatio), 1
        );
        $nameLineSpacing = (int) round($nameFontSize * 1.15);
        $nameZoneTop = $height * (599 / 1811);
        $nameZoneBottom = $height * (838 / 1811);
        $yNamePosition = ($nameZoneTop + $nameZoneBottom) / 2 - ((count($nameLines) - 1) * $nameLineSpacing) / 2;
        foreach ($nameLines as $nameLine) {
            $image->text($nameLine, $width / 2, $yNamePosition, function($font) use ($fontBold, $nameFontSize) {
                $font->filename($fontBold);
                $font->size($nameFontSize);
                $font->color('#C9A961');
                $font->align('center');
                $font->valign('middle');
            });
            $yNamePosition += $nameLineSpacing;
        }

        // Article Title (center, di zona setelah "Sebagai bentuk penghargaan...")
        //
        // Dibungkus berdasar LEBAR PIKSEL SESUNGGUHNYA (diukur pakai
        // imagettfbbox() — fungsi GD asli, tidak butuh library tambahan) lewat
        // wrapTextByWidth(), supaya baris manapun TIDAK PERNAH melebihi lebar
        // aman yang ditentukan, berapa pun panjang teksnya.
        //
        // Perbaikan 9 Sept 2026 (lanjutan, setelah dicek user pada sertifikat
        // asli): judul artikel nyata ("PENGARUH CITRA MEREK, RELATIONSHIP
        // MARKETING...") ternyata jadi 4 baris di font 60 — meluber ke luar
        // zona sampai menabrak "Thank you your contribution...". Font awal
        // diturunkan ke 50 (sebelumnya juga dikeluhkan terlalu besar), dan
        // batas baris diperketat dari 4 menjadi 2 SESUAI PERMINTAAN USER
        // ("judul dibuat maksimal 2 baris") — wrapTextWithAutoShrink() akan
        // mengecilkan font sampai muat 2 baris (turun sampai $articleMinFontSize
        // untuk judul yang sangat panjang).
        $articleFontSize = 50;
        $articleMinFontSize = 26;
        [$articleLines, $articleFontSize] = $this->wrapTextWithAutoShrink(
            $articleTitle, $fontBold, $articleFontSize, $articleMinFontSize,
            (int) ($width * $maxTitleWidthRatio), 2
        );
        $articleLineSpacing = (int) round($articleFontSize * 1.17);
        $articleZoneTop = $height * (887 / 1811);
        $articleZoneBottom = $height * (1231 / 1811);
        $yArticlePosition = ($articleZoneTop + $articleZoneBottom) / 2 - ((count($articleLines) - 1) * $articleLineSpacing) / 2;
        foreach ($articleLines as $articleLine) {
            $image->text($articleLine, $width / 2, $yArticlePosition, function($font) use ($fontBold, $articleFontSize) {
                $font->filename($fontBold);
                $font->size($articleFontSize);
                $font->color('#C9A961');
                $font->align('center');
                $font->valign('middle');
            });
            $yArticlePosition += $articleLineSpacing;
        }
        
        // Tanggal di center (sesuai kotak merah di bawah)
        $dateText = "$date $month $year";
        $image->text($dateText, $width / 2, $height - 180, function($font) use ($fontBold) {
            $font->filename($fontBold);
            $font->size(55);
            $font->color('#C9A961');
            $font->align('center');
            $font->valign('middle');
        });

        // Nomor Surat (kode LOA), Nama Jurnal, dan Publisher — satu baris kecil di
        // bawah tanggal, sebelum border bawah. CATATAN: posisi Y ini perkiraan
        // berdasarkan template referensi (file template AKTIF tidak tersedia untuk
        // dites render langsung) — cek visual hasil sertifikat asli, geser
        // "$height - 110" kalau ternyata tumpang tindih dengan elemen lain.
        $infoText = "No. Surat: {$nomorSurat}   |   Jurnal: {$namaJurnal}   |   Publisher: {$namaPublisher}";
        $image->text($infoText, $width / 2, $height - 110, function($font) use ($fontRegular) {
            $font->filename($fontRegular);
            $font->size(26);
            $font->color('#8B6914');
            $font->align('center');
            $font->valign('middle');
        });

        // QR Code verifikasi — di-center secara horizontal, di atas tanggal.
        // Siapa pun yang scan bisa memastikan sertifikat ini asli & review-nya
        // benar sudah APPROVED, tanpa perlu login (lihat verify() di atas).
        //
        // Perbaikan 9 Sept 2026 (lanjutan ke-5): sebelumnya QR ditaruh di
        // pojok kiri bawah ($qrX = 150, ukuran 260x260) — sebaris dengan
        // tanggal, bukan di atasnya. Sesuai permintaan user: dipindah ke
        // TENGAH (horizontal) dan diletakkan DI ATAS tanggal, ukurannya juga
        // agak diperkecil (260 → 200).
        //
        // Perbaikan 9 Sept 2026 (lanjutan ke-6): setelah di-center, user
        // laporkan (screenshot) QR jadi MENABRAK paragraf tetap di atasnya
        // ("...standard of academic i[ntegrity]" / "...integritas akademik
        // yang tinggi da[lam]..."). Ternyata $qrY lama ($height - 480 =
        // Y1331 di referensi 1811px) memang tumpang tindih dengan akhir
        // paragraf itu (berakhir ~Y1380) — sebelumnya tidak kelihatan karena
        // QR ada di pojok KIRI sedangkan baris terakhir paragraf yang
        // di-center tidak menjangkau sejauh itu ke kiri; begitu QR
        // di-center, keduanya pas bertabrakan di tengah.
        //
        // Diperbaiki dengan: (1) turunkan $qrY supaya mulai SETELAH akhir
        // paragraf (~Y1380 + margin), (2) kecilkan lagi ukuran QR (200→150)
        // dan rapatkan jarak ke label supaya seluruh blok (QR + label) tetap
        // muat sebelum tanggal mulai (~Y1598) — ruang di antara paragraf &
        // tanggal ini sempit (~218px), jadi ukuran & jarak sengaja dipadatkan
        // supaya ada margin aman di ATAS (ke paragraf) maupun BAWAH (ke
        // tanggal), bukan cuma salah satu sisi.
        $verifyUrl = route('reviewer-certificate.verify', ['assignment' => $assignment->id, 'reviewerId' => $reviewer->id]);
        $qrPng = $this->renderQrPng($verifyUrl);
        $qrSize = 150;
        $qrImage = $manager->read($qrPng)->resize($qrSize, $qrSize);
        $qrX = ($width - $qrSize) / 2;
        $qrY = $height - 410;
        $image->place($qrImage, 'top-left', (int) $qrX, $qrY);

        $image->text('Scan untuk verifikasi', $qrX + ($qrSize / 2), $qrY + $qrSize + 15, function($font) use ($fontRegular) {
            $font->filename($fontRegular);
            $font->size(20);
            $font->color('#8B6914');
            $font->align('center');
            $font->valign('middle');
        });

        // Save file
        if ($forPreview) {
            // Save to public/temp for preview
            $tempFilename = 'preview_certificate_' . time() . '_' . $assignment->id . '.jpg';
            $tempPath = public_path('temp/' . $tempFilename);
            
            // Create temp directory if not exists
            if (!file_exists(public_path('temp'))) {
                mkdir(public_path('temp'), 0755, true);
            }
            
            $image->toJpeg(90)->save($tempPath);
            
            return 'temp/' . $tempFilename;
        } else {
            // Save to storage/temp for download
            $tempFilename = 'certificate_' . time() . '_' . $assignment->id . '.jpg';
            $tempPath = storage_path('app/public/temp/' . $tempFilename);
            
            // Create temp directory if not exists
            if (!file_exists(storage_path('app/public/temp'))) {
                mkdir(storage_path('app/public/temp'), 0755, true);
            }
            
            $image->toJpeg(95)->save($tempPath);
            
            return $tempPath;
        }
    }
}

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
        
        // Lebar aman untuk teks yang di-center (nama reviewer & judul artikel) —
        // 70% dari lebar kanvas, sisakan margin kiri-kanan untuk border emas.
        // CATATAN: sama seperti posisi elemen lain di file ini, ini perkiraan (file
        // template AKTIF tidak tersedia untuk dites render langsung) — cek visual
        // hasil sertifikat asli, sesuaikan kalau masih terlalu lebar/kesempitan.
        $maxTitleWidthRatio = 0.70;

        // Reviewer Name (center, posisi setelah "This certificate is awarded to :")
        //
        // Sama seperti judul artikel di bawah — sebelumnya di-render 1 baris tanpa
        // pengaman lebar sama sekali, jadi nama reviewer yang panjang (mis. dengan
        // banyak gelar akademik) berisiko meluber keluar border persis seperti kasus
        // judul artikel. Dibungkus pakai wrapTextByWidth() yang sama. Ruang vertikal
        // sampai judul artikel mulai (Y=1500) cukup lega (~380px) untuk menampung
        // nama 2 baris tanpa tabrakan, jadi posisi Y judul artikel TIDAK digeser.
        $nameFontSize = 80;
        $nameLines = $this->wrapTextByWidth($reviewerName, $fontBold, $nameFontSize, (int) ($width * $maxTitleWidthRatio));
        $yNamePosition = 1120;
        foreach ($nameLines as $nameLine) {
            $image->text($nameLine, $width / 2, $yNamePosition, function($font) use ($fontBold, $nameFontSize) {
                $font->filename($fontBold);
                $font->size($nameFontSize);
                $font->color('#C9A961');
                $font->align('center');
                $font->valign('middle');
            });
            $yNamePosition += 95; // Line spacing (font lebih besar dari judul, spasi sedikit lebih lebar)
        }
        
        // Article Title (center, posisi setelah "Manuscript Entitled :")
        //
        // DULU dibungkus pakai wordwrap($articleTitle, 100, "\n") — membagi baris
        // berdasar JUMLAH KARAKTER, bukan lebar piksel sesungguhnya saat dirender.
        // Di ukuran font 60 (bold), 100 karakter bisa jadi ~3500-4500px lebar —
        // SAMA ATAU LEBIH LEBAR dari kanvas sertifikat sendiri (3508px)! Itu sebab
        // judul artikel yang panjang meluber keluar dari border kiri & kanan
        // (dilaporkan user, judul "PERKEMBANGAN BUDAYA ISLAM..." terpotong di
        // kedua sisi). Sekarang dibungkus berdasar LEBAR PIKSEL SESUNGGUHNYA
        // (diukur pakai imagettfbbox() — fungsi GD asli, tidak butuh library
        // tambahan) lewat wrapTextByWidth(), supaya baris manapun TIDAK PERNAH
        // melebihi lebar aman yang ditentukan, berapa pun panjang teksnya.
        $articleFontSize = 60;
        $articleLines = $this->wrapTextByWidth($articleTitle, $fontBold, $articleFontSize, (int) ($width * $maxTitleWidthRatio));

        $yArticlePosition = 1500;
        foreach ($articleLines as $articleLine) {
            $image->text($articleLine, $width / 2, $yArticlePosition, function($font) use ($fontBold, $articleFontSize) {
                $font->filename($fontBold);
                $font->size($articleFontSize);
                $font->color('#C9A961');
                $font->align('center');
                $font->valign('middle');
            });
            $yArticlePosition += 70; // Line spacing
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

        // QR Code verifikasi — pojok kiri bawah. Siapa pun yang scan bisa memastikan
        // sertifikat ini asli & review-nya benar sudah APPROVED, tanpa perlu login
        // (lihat verify() di atas). CATATAN posisi: sama seperti info text di atas,
        // koordinat ini perkiraan (file template AKTIF tidak tersedia untuk dites
        // render langsung) — cek visual hasil asli, geser $qrX/$qrY kalau tumpang
        // tindih dengan elemen desain lain.
        $verifyUrl = route('reviewer-certificate.verify', ['assignment' => $assignment->id, 'reviewerId' => $reviewer->id]);
        $qrPng = $this->renderQrPng($verifyUrl);
        $qrImage = $manager->read($qrPng)->resize(260, 260);
        $qrX = 150;
        $qrY = $height - 480;
        $image->place($qrImage, 'top-left', $qrX, $qrY);

        $image->text('Scan untuk verifikasi', $qrX + 130, $qrY + 285, function($font) use ($fontRegular) {
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

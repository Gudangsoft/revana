<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\KwitansiMail;
use App\Models\Accreditation;
use App\Models\Setting;
use App\Models\Submission;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KwitansiController extends Controller
{
    // ── Admin: dari detail submission (butuh login) ─────────────────────
    public function show(Request $request, Submission $submission)
    {
        $data = $this->buildViewData($submission, $this->resolveParams($request));
        $data['sendEmailRoute']    = 'admin.submissions.kwitansi.send-email';
        $data['sendWaRoute']       = 'admin.submissions.kwitansi.send-wa';
        $data['updateContactRoute'] = 'admin.submissions.kwitansi.update-contact';
        $data['publicPdfUrl']      = $this->publicPdfUrl($submission, $data);
        $data['verifyUrl']         = $data['publicPdfUrl'];

        return view('admin.kwitansi.receipt', $data);
    }

    // ── Marketing: dari detail submission marketing ──────────────────────
    public function showMarketing(Request $request, Submission $submission)
    {
        if (!$this->marketingOwnsSubmission($submission)) {
            return redirect()->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke submission ini.');
        }

        $data = $this->buildViewData($submission, $this->resolveParams($request));
        $data['sendEmailRoute']    = 'marketing.submissions.kwitansi.send-email';
        $data['sendWaRoute']       = 'marketing.submissions.kwitansi.send-wa';
        $data['updateContactRoute'] = 'marketing.submissions.kwitansi.update-contact';
        $data['publicPdfUrl']      = $this->publicPdfUrl($submission, $data);
        $data['verifyUrl']         = $data['publicPdfUrl'];

        return view('admin.kwitansi.receipt', $data);
    }

    /** Link PDF publik (tanpa login) untuk dibagikan/disalin ke author — sudah membawa semua parameter pembayaran */
    private function publicPdfUrl(Submission $submission, array $data): string
    {
        return route('kwitansi.public.pdf', array_merge(['kode_submit' => $submission->kode_submit], [
            'nama_pembayar' => $data['namaPembayar'],
            'jumlah'        => $data['jumlah'],
            'keterangan'    => $data['keterangan'],
            'metode_bayar'  => $data['metodeBayar'],
            'tanggal'       => $data['tanggal']->toDateString(),
        ]));
    }

    // ── Public: dipakai Fonnte untuk fetch PDF lampiran WA (tanpa login) ──
    public function publicPdf(Request $request, string $kodeSubmit)
    {
        $submission = Submission::where('kode_submit', $kodeSubmit)->firstOrFail();
        $pdf = $this->generateKwitansiPdf($submission, $this->resolveParams($request));

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Kwitansi-' . $kodeSubmit . '.pdf"',
        ]);
    }

    // ── Admin: kirim kwitansi via email ──────────────────────────────────
    public function sendEmail(Request $request, Submission $submission)
    {
        return $this->dispatchEmail($submission, $this->resolveParams($request), 'admin.submissions.kwitansi');
    }

    // ── Marketing: kirim kwitansi via email ──────────────────────────────
    public function sendMarketingEmail(Request $request, Submission $submission)
    {
        if (!$this->marketingOwnsSubmission($submission)) {
            return redirect()->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke submission ini.');
        }

        return $this->dispatchEmail($submission, $this->resolveParams($request), 'marketing.submissions.kwitansi');
    }

    // ── Admin: kirim kwitansi via WhatsApp (Fonnte) ──────────────────────
    public function sendWa(Request $request, Submission $submission)
    {
        return $this->dispatchWa($submission, $this->resolveParams($request), 'admin.submissions.kwitansi');
    }

    // ── Marketing: kirim kwitansi via WhatsApp (Fonnte) ──────────────────
    public function sendMarketingWa(Request $request, Submission $submission)
    {
        if (!$this->marketingOwnsSubmission($submission)) {
            return redirect()->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke submission ini.');
        }

        return $this->dispatchWa($submission, $this->resolveParams($request), 'marketing.submissions.kwitansi');
    }

    // ── Admin: update kontak (email/HP) author dari modal "Kirim ke Author" ──
    public function updateContact(Request $request, Submission $submission)
    {
        return $this->dispatchUpdateContact($request, $submission, 'admin.submissions.kwitansi');
    }

    // ── Marketing: update kontak (email/HP) author dari modal "Kirim ke Author" ──
    public function updateMarketingContact(Request $request, Submission $submission)
    {
        if (!$this->marketingOwnsSubmission($submission)) {
            return redirect()->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke submission ini.');
        }

        return $this->dispatchUpdateContact($request, $submission, 'marketing.submissions.kwitansi');
    }

    /**
     * Update email_penulis/no_hp_penulis milik Submission (data kontak, BUKAN data
     * kwitansi — kontak ini memang sudah tersimpan di DB sejak awal, terpisah dari field
     * pembayaran kwitansi yang sengaja tidak disimpan). Redirect balik ke kwitansi (bukan
     * ke halaman LOA) sambil membawa lagi semua parameter pembayaran yang sedang dilihat.
     */
    private function dispatchUpdateContact(Request $request, Submission $submission, string $backRoute)
    {
        $validated = $request->validate([
            'email_penulis' => 'nullable|email|max:255',
            'no_hp_penulis' => 'nullable|string|max:20',
        ]);

        $submission->update($validated);

        $params = $this->resolveParams($request);

        return redirect()->route($backRoute, array_merge([$submission], $params))
            ->with('success', 'Kontak author berhasil diperbarui.');
    }

    private function marketingOwnsSubmission(Submission $submission): bool
    {
        $marketing = \Auth::guard('marketing')->user();
        return !$submission->marketing_id || $submission->marketing_id === $marketing->id;
    }

    // ── Internal: kirim email berisi PDF kwitansi (tidak pernah disimpan ke DB) ──
    private function dispatchEmail(Submission $submission, array $params, string $backRoute)
    {
        if (!$submission->email_penulis) {
            return redirect()->route($backRoute, array_merge([$submission], $params) )
                ->with('error', 'Submission ini tidak memiliki email penulis.');
        }

        try {
            Mail::to($submission->email_penulis)->send(new KwitansiMail($submission, $params));
        } catch (\Exception $e) {
            \Log::error('Kwitansi email failed for submission ' . $submission->id . ': ' . $e->getMessage());
            return redirect()->route($backRoute, array_merge([$submission], $params))
                ->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }

        return redirect()->route($backRoute, array_merge([$submission], $params))
            ->with('success', 'Kwitansi berhasil dikirim ke ' . $submission->email_penulis);
    }

    // ── Internal: kirim WA berisi link PDF kwitansi (query string dibawa penuh) ──
    private function dispatchWa(Submission $submission, array $params, string $backRoute)
    {
        if (empty($submission->no_hp_penulis)) {
            return redirect()->route($backRoute, array_merge([$submission], $params))
                ->with('error', 'No HP/WA penulis belum diisi.');
        }

        $fonnte = app(FonnteService::class);
        $waToken = Setting::get('fonnte_api_token_loa') ?: null;

        if (!$waToken && !$fonnte->isConfigured()) {
            return redirect()->route($backRoute, array_merge([$submission], $params))
                ->with('error', 'Fonnte API token belum dikonfigurasi (lihat Setting > SMS Gateway).');
        }

        $journal    = $submission->journalSlot?->journalMaster;
        $jurnalNama = $journal?->nama_jurnal ?? 'Jurnal';
        $kodeSubmit = $submission->kode_submit;

        $viewData  = $this->buildViewData($submission, $params);
        $pdfUrl    = route('kwitansi.public.pdf', array_merge(['kode_submit' => $kodeSubmit], $this->paramsAsQuery($params)));

        $message = "Yth. {$viewData['namaPembayar']},\n\nBerikut kami sampaikan kwitansi pembayaran sejumlah Rp " .
            number_format($viewData['jumlah'], 0, ',', '.') . " untuk *{$jurnalNama}*.\n\n" .
            "Silakan unduh kwitansi melalui tautan berikut:\n{$pdfUrl}\n\nTerima kasih.\n\n_Tim {$jurnalNama}_";

        $result = $fonnte->send(
            target: $submission->no_hp_penulis,
            message: $message,
            options: [
                'countryCode' => '62',
                'url'         => $pdfUrl,
                'filename'    => 'Kwitansi-' . $kodeSubmit . '.pdf',
            ],
            token: $waToken
        );

        if (!$result['success']) {
            \Log::error('Kwitansi WA failed for submission ' . $submission->id . ': ' . ($result['message'] ?? 'unknown'));
            return redirect()->route($backRoute, array_merge([$submission], $params))
                ->with('error', 'Gagal kirim WA: ' . $result['message']);
        }

        return redirect()->route($backRoute, array_merge([$submission], $params))
            ->with('success', 'Kwitansi berhasil dikirim via WhatsApp ke ' . $submission->no_hp_penulis);
    }

    /** Query params publik untuk dilampirkan ke pdfUrl (dipakai Fonnte fetch) — tanpa nilai kosong */
    private function paramsAsQuery(array $params): array
    {
        return array_filter([
            'nama_pembayar' => $params['nama_pembayar'] ?? null,
            'jumlah'        => $params['jumlah'] ?? null,
            'keterangan'    => $params['keterangan'] ?? null,
            'metode_bayar'  => $params['metode_bayar'] ?? null,
            'tanggal'       => $params['tanggal'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Baca field pembayaran dari request (query string ATAU form POST, keduanya
     * dibaca lewat $request->input() supaya jalur GET/tampil dan POST/kirim sama-sama
     * jalan) — TIDAK PERNAH disimpan ke database, cuma dipakai sekali untuk request ini.
     */
    public function resolveParams(Request $request): array
    {
        return [
            'nama_pembayar' => trim((string) $request->input('nama_pembayar', '')),
            'jumlah'        => (string) $request->input('jumlah', '0'),
            'keterangan'    => trim((string) $request->input('keterangan', '')),
            'metode_bayar'  => $request->input('metode_bayar', 'Transfer Bank'),
            'tanggal'       => $request->input('tanggal') ?: now()->toDateString(),
        ];
    }

    /**
     * Data pembayaran (jumlah, keterangan, metode bayar, nama pembayar, tanggal) TIDAK
     * pernah disimpan ke database — semuanya cuma dibaca dari query string/form tiap
     * request (persis seperti override tanggal_loa di LoaController) dan langsung
     * dipakai buat render halaman. Reload/refresh dengan input yang sama akan selalu
     * menghasilkan kwitansi yang identik, tapi tidak ada riwayat yang tersimpan di DB.
     */
    public function buildViewData(Submission $submission, array $params): array
    {
        $submission->load(['journalSlot.journalMaster']);
        $journal = $submission->journalSlot?->journalMaster;

        $namaPembayar = $params['nama_pembayar'] ?: ($submission->nama_penulis ?? '-');
        $jumlah       = (int) preg_replace('/[^0-9]/', '', $params['jumlah'] ?? '0');
        $keterangan   = $params['keterangan'] ?: 'Biaya publikasi artikel "' . ($submission->judul_artikel ?? '-') . '" pada ' . ($journal?->nama_jurnal ?? 'jurnal');
        $metodeBayar  = $params['metode_bayar'] ?? 'Transfer Bank';
        $tanggal      = \Carbon\Carbon::parse($params['tanggal'] ?? now()->toDateString());

        $kodeSingkat     = $journal?->kode_singkat ?: 'SIPERA';
        $kodeSubmit      = $submission->kode_submit ?? '';
        $kodeSubmitLabel = str_ends_with(strtoupper($kodeSubmit), 'SIPERA') ? $kodeSubmit : $kodeSubmit . 'SIPERA';
        $romanMonth      = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$tanggal->month - 1];

        return [
            'submission'       => $submission,
            'journal'          => $journal,
            'nomorKwitansi'    => 'KWT/' . $kodeSubmitLabel . '/' . $romanMonth . '/' . $tanggal->year,
            'namaPembayar'     => $namaPembayar,
            'jumlah'           => $jumlah,
            'jumlahTerbilang'  => $jumlah > 0 ? ucfirst($this->terbilang($jumlah)) . ' rupiah' : '-',
            'keterangan'       => $keterangan,
            'metodeBayar'      => $metodeBayar,
            'tanggal'          => $tanggal,
            'logoUrl'          => $journal?->logo_path ? Storage::url($journal->logo_path) : null,
            // Kwitansi ditandatangani Bendahara, bukan Ketua Dewan Redaksi (editor_name/
            // editor_signature_path dipakai LOA) — pakai field terpisah yang diatur lewat
            // menu "Master Kwitansi", supaya tidak salah pakai tanda tangan LOA di kwitansi.
            'signUrl'          => $journal?->bendahara_signature_path ? Storage::url($journal->bendahara_signature_path) : null,
            'headerImageUrl'   => $journal?->header_image_path ? Storage::url($journal->header_image_path) : null,
            'editorName'       => $journal?->bendahara_name ?? '',
            'editorTitle'      => 'Bendahara',
            'kota'             => $journal?->loa_kota ?? 'Semarang',
            'primaryColor'     => $journal?->primary_color ?? '#1A237E',
            'secondaryColor'   => $journal?->secondary_color ?? '#8B6914',
            'accreditationLogoUrl' => $this->resolveAccreditationLogoUrl($journal),
            'linkSkAkreditasi' => $journal?->link_sk_akreditasi,
        ];
    }

    // ── Render kwitansi jadi PDF (dompdf) — dipakai lampiran email & fetch WA ──
    public function generateKwitansiPdf(Submission $submission, array $params): string
    {
        $data = $this->buildViewData($submission, $params);
        $journal = $data['journal'];
        $data['pdfMode'] = true;

        // dompdf punya enable_remote=false (default), pakai path file lokal untuk gambar
        $data['logoUrl']              = $this->localStoragePath($journal?->logo_path);
        $data['signUrl']              = $this->localStoragePath($journal?->bendahara_signature_path);
        $data['headerImageUrl']       = $this->localStoragePath($journal?->header_image_path);
        $data['accreditationLogoUrl'] = $this->resolveAccreditationLogoLocalPath($journal);

        // QR verifikasi — sama seperti LOA, tapi mengarah ke link PDF publik kwitansi ini
        // (satu-satunya cara "verifikasi" yang masuk akal untuk dokumen yang datanya tidak
        // disimpan di DB: scan ulang akan selalu menghasilkan PDF yang identik).
        $verifyUrl = $this->publicPdfUrl($submission, $data);
        $qrSvg = QrCode::format('svg')->size(80)->margin(0)->generate($verifyUrl);
        $data['qrDataUri'] = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.kwitansi.receipt', $data)
            ->setOption('defaultMediaType', 'print')
            ->setPaper('a4')
            ->output();
    }

    private function localStoragePath(?string $relativePath): ?string
    {
        if (!$relativePath) return null;
        $fullPath = Storage::disk('public')->path($relativePath);
        return is_file($fullPath) ? $fullPath : null;
    }

    private function resolveAccreditationLogoLocalPath($journal): ?string
    {
        if (!$journal) return null;
        $acc = Accreditation::where('name', $journal->accreditation)->first();
        if ($acc && $acc->logo_sinta) {
            return $this->localStoragePath($acc->logo_sinta);
        }
        if ($journal->accreditation_logo_path) {
            return $this->localStoragePath($journal->accreditation_logo_path);
        }
        return null;
    }

    private function resolveAccreditationLogoUrl($journal): ?string
    {
        if (!$journal) return null;
        $acc = Accreditation::where('name', $journal->accreditation)->first();
        if ($acc && $acc->logo_sinta) {
            return asset('storage/' . $acc->logo_sinta);
        }
        if ($journal->accreditation_logo_path) {
            return Storage::url($journal->accreditation_logo_path);
        }
        return null;
    }

    // ── Terbilang: angka ke teks Bahasa Indonesia (rekursif, standar) ──────
    private function terbilang(int $angka): string
    {
        $angka = abs($angka);
        $kata = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
                 'sepuluh', 'sebelas'];

        if ($angka < 12) {
            return $kata[$angka];
        }
        if ($angka < 20) {
            return $this->terbilang($angka - 10) . ' belas';
        }
        if ($angka < 100) {
            $sisa = $angka % 10;
            return trim($this->terbilang((int) ($angka / 10)) . ' puluh ' . ($sisa ? $this->terbilang($sisa) : ''));
        }
        if ($angka < 200) {
            return trim('seratus ' . ($angka - 100 > 0 ? $this->terbilang($angka - 100) : ''));
        }
        if ($angka < 1000) {
            $sisa = $angka % 100;
            return trim($this->terbilang((int) ($angka / 100)) . ' ratus ' . ($sisa ? $this->terbilang($sisa) : ''));
        }
        if ($angka < 2000) {
            $sisa = $angka % 1000;
            return trim('seribu ' . ($sisa ? $this->terbilang($sisa) : ''));
        }
        if ($angka < 1000000) {
            $sisa = $angka % 1000;
            return trim($this->terbilang((int) ($angka / 1000)) . ' ribu ' . ($sisa ? $this->terbilang($sisa) : ''));
        }
        if ($angka < 1000000000) {
            $sisa = $angka % 1000000;
            return trim($this->terbilang((int) ($angka / 1000000)) . ' juta ' . ($sisa ? $this->terbilang($sisa) : ''));
        }

        $sisa = $angka % 1000000000;
        return trim($this->terbilang((int) ($angka / 1000000000)) . ' miliar ' . ($sisa ? $this->terbilang($sisa) : ''));
    }
}

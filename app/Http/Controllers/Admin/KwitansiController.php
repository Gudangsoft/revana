<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accreditation;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KwitansiController extends Controller
{
    // ── Admin: dari detail submission (butuh login) ─────────────────────
    public function show(Request $request, Submission $submission)
    {
        return view('admin.kwitansi.receipt', $this->buildViewData($submission, $request));
    }

    // ── Marketing: dari detail submission marketing ──────────────────────
    public function showMarketing(Request $request, Submission $submission)
    {
        $marketing = \Auth::guard('marketing')->user();
        if ($submission->marketing_id && $submission->marketing_id !== $marketing->id) {
            return redirect()->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke submission ini.');
        }

        return view('admin.kwitansi.receipt', $this->buildViewData($submission, $request));
    }

    /**
     * Data pembayaran (jumlah, keterangan, metode bayar, nama pembayar, tanggal) TIDAK
     * pernah disimpan ke database — semuanya cuma dibaca dari query string tiap request
     * (persis seperti override tanggal_loa di LoaController) dan langsung dipakai buat
     * render halaman. Reload/refresh dengan query string yang sama akan selalu
     * menghasilkan kwitansi yang identik, tapi tidak ada riwayat yang tersimpan di DB.
     */
    private function buildViewData(Submission $submission, Request $request): array
    {
        $submission->load(['journalSlot.journalMaster']);
        $journal = $submission->journalSlot?->journalMaster;

        $namaPembayar = trim((string) $request->query('nama_pembayar', '')) ?: ($submission->nama_penulis ?? '-');
        $jumlah       = (int) preg_replace('/[^0-9]/', '', (string) $request->query('jumlah', '0'));
        $keterangan   = trim((string) $request->query('keterangan', ''))
            ?: 'Biaya publikasi artikel "' . ($submission->judul_artikel ?? '-') . '" pada ' . ($journal?->nama_jurnal ?? 'jurnal');
        $metodeBayar  = $request->query('metode_bayar', 'Transfer Bank');
        $tanggalRaw   = $request->query('tanggal') ?: now()->toDateString();
        $tanggal      = \Carbon\Carbon::parse($tanggalRaw);

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
        ];
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

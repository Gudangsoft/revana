<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accreditation;
use App\Models\Submission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LoaController extends Controller
{
    // ── Public: dibuka penulis via link email atau halaman Request LOA ─────
    public function publicView(string $kodeLoa)
    {
        $submission = Submission::where('kode_loa', $kodeLoa)->firstOrFail();

        return view('admin.loa.receipt', $this->buildViewData($submission, [
            'isAdminView' => false,
        ]));
    }

    // ── Admin: dari detail submission (butuh login) ─────────────────────
    public function show(Submission $submission)
    {
        return view('admin.loa.receipt', $this->buildViewData($submission, [
            'isAdminView' => true,
        ]));
    }

    // ── Admin: simpan metadata LOA (nama, afiliasi, judul, tanggal) ─────
    public function updateMetadata(Request $request, Submission $submission)
    {
        $validated = $request->validate([
            'nama_penulis'        => 'required|string|max:255',
            'affiliation_penulis' => 'nullable|string|max:500',
            'judul_artikel'       => 'required|string|max:1000',
            'tanggal_loa'         => 'nullable|date',
            'email_penulis'       => 'nullable|email|max:255',
            'no_hp_penulis'       => 'nullable|string|max:20',
        ]);

        $submission->update($validated);

        return redirect()
            ->route('admin.submissions.loa', $submission)
            ->with('success', 'Metadata LOA berhasil diperbarui.');
    }

    // ── Marketing: view LOA dengan date picker, tanpa akses admin ───────────
    public function showMarketing(Submission $submission)
    {
        $marketing = \Auth::guard('marketing')->user();
        if ($submission->marketing_id && $submission->marketing_id !== $marketing->id) {
            return redirect()->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke LOA ini.');
        }

        return view('admin.loa.receipt', $this->buildViewData($submission, [
            'isAdminView'     => false,
            'isMarketingView' => true,
            'canEditDate'     => true,
            'backUrl'         => route('marketing.submissions.show', $submission),
        ]));
    }

    // ── Marketing: update metadata LOA ────────────────────────────────────
    public function updateMarketingMetadata(Request $request, Submission $submission)
    {
        $marketing = \Auth::guard('marketing')->user();
        if ($submission->marketing_id && $submission->marketing_id !== $marketing->id) {
            return redirect()->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke LOA ini.');
        }

        $validated = $request->validate([
            'nama_penulis'        => 'required|string|max:255',
            'affiliation_penulis' => 'nullable|string|max:500',
            'judul_artikel'       => 'required|string|max:1000',
            'tanggal_loa'         => 'nullable|date',
            'email_penulis'       => 'nullable|email|max:255',
            'no_hp_penulis'       => 'nullable|string|max:20',
        ]);

        $submission->update($validated);

        return redirect()->route('marketing.submissions.loa', $submission)
            ->with('success', 'Metadata LOA berhasil diperbarui.');
    }

    // ── Request LOA publik ─────────────────────────────────────────────────
    public function requestForm()
    {
        return view('loa.request');
    }

    public function requestSubmit(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'kode_submit' => 'required|string|max:50',
            'tanggal'     => 'nullable|date',
        ], [
            'kode_submit.required' => 'Kode SIPERA wajib diisi.',
        ]);

        $kode = trim(strtoupper($request->kode_submit));

        $submission = Submission::where('kode_submit', $kode)
            ->orWhere('kode_loa', $kode)
            ->first();

        if (!$submission) {
            return back()->withInput()
                ->withErrors(['kode_submit' => 'Kode tidak ditemukan. Pastikan kode SIPERA Anda benar.']);
        }

        $params = [];
        if ($request->tanggal) {
            $params['tanggal'] = $request->tanggal;
        }

        $url = route('loa.public', ['kode_loa' => $submission->kode_loa]);
        if ($params) $url .= '?' . http_build_query($params);

        return redirect($url);
    }

    // ── Data view LOA, dipakai bareng oleh show/publicView/showMarketing/PDF ──
    public function buildViewData(Submission $submission, array $overrides = []): array
    {
        $submission->load(['journalSlot.journalMaster']);
        $journal = $submission->journalSlot?->journalMaster;
        $slot    = $submission->journalSlot;
        $date    = request('tanggal') ?: ($submission->tanggal_loa?->toDateString());
        $kode    = $submission->kode_loa ?: $submission->kode_submit;
        $lang    = $journal?->loa_language ?? 'en';

        return array_merge([
            'submission'             => $submission,
            'journal'                => $journal,
            'slot'                   => $slot,
            'loaNumber'              => $this->loaNumber($submission, $journal, $slot, $date),
            'loaDate'                => $this->loaDate($journal, $date, $lang, $slot),
            'loaDateRaw'             => $this->loaDateRawString($date, $journal, $slot),
            'logoUrl'                => $journal?->logo_path ? Storage::url($journal->logo_path) : null,
            'signUrl'                => $journal?->editor_signature_path ? Storage::url($journal->editor_signature_path) : null,
            'headerImageUrl'         => $journal?->header_image_path ? Storage::url($journal->header_image_path) : null,
            'accreditationLogoUrl'   => $this->resolveAccreditationLogoUrl($journal),
            'linkSkAkreditasi'       => $journal?->link_sk_akreditasi,
            'loaStatus'              => $journal?->loa_status,
            'pIssn'                  => $journal?->p_issn,
            'penerbit'               => $journal?->publisher,
            'verifyUrl'              => route('verify.direct', ['kode_loa' => $kode]),
            'lang'                   => $lang,
        ], $overrides);
    }

    // ── Render LOA jadi PDF (untuk dilampirkan ke email) ─────────────────
    public function generateLoaPdf(Submission $submission): string
    {
        $data = $this->buildViewData($submission, [
            'isAdminView' => false,
            'pdfMode'     => true,
        ]);

        // dompdf punya enable_remote=false (default) sehingga tidak bisa fetch gambar via URL
        // (http/https), termasuk URL ke domain sendiri. Untuk PDF, pakai path file lokal langsung.
        $journal = $data['journal'];
        $data['logoUrl']              = $this->localStoragePath($journal?->logo_path);
        $data['signUrl']              = $this->localStoragePath($journal?->editor_signature_path);
        $data['headerImageUrl']       = $this->localStoragePath($journal?->header_image_path);
        $data['accreditationLogoUrl'] = $this->resolveAccreditationLogoLocalPath($journal);

        $verifyUrl = $data['verifyUrl'];
        $qrSvg     = QrCode::format('svg')->size(80)->margin(0)->generate($verifyUrl);
        $data['qrDataUri'] = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        return Pdf::loadView('admin.loa.receipt', $data)
            ->setOption('defaultMediaType', 'print')
            ->setPaper('a4')
            ->output();
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

    // ── Path file lokal (untuk PDF) dari path relatif di disk 'public' ───
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

    // ── helpers ────────────────────────────────────────────────────────────

    private function loaNumber(Submission $s, $j, $slot, ?string $dateOverride = null): string
    {
        $kode       = $j?->kode_singkat ?: 'SIPERA';
        $id         = $s->id_artikel ?: $s->kode_submit;
        $kodeSubmit = $s->kode_submit ?? '';
        $kodeSubmitLabel = str_ends_with(strtoupper($kodeSubmit), 'SIPERA') ? $kodeSubmit : $kodeSubmit . 'SIPERA';

        if ($dateOverride) {
            $dt    = \Carbon\Carbon::parse($dateOverride);
            $roman = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$dt->month - 1];
            $year  = $dt->year;
        } elseif ($j?->loa_tanggal) {
            $dt    = \Carbon\Carbon::parse($j->loa_tanggal);
            $roman = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$dt->month - 1];
            $year  = $dt->year;
        } else {
            $roman = $this->romanMonth($slot?->bulan);
            $year  = $slot?->tahun ?? now()->year;
        }

        return $id . '/' . $kodeSubmitLabel . '/' . $kode . '/' . $roman . '/' . $year;
    }

    private function loaDateRawString(?string $dateOverride, $journal, $slot): string
    {
        if ($dateOverride) return $dateOverride;
        return now()->toDateString();
    }

    private function loaDate($journal, ?string $dateOverride = null, string $lang = 'en', $slot = null): string
    {
        if ($dateOverride) {
            $dt = \Carbon\Carbon::parse($dateOverride);
        } else {
            $dt = now();
        }
        $kota = $journal?->loa_kota ?? 'Semarang';

        if ($lang === 'id') {
            $months = ['Januari','Februari','Maret','April','Mei','Juni',
                       'Juli','Agustus','September','Oktober','November','Desember'];
            $dateStr = $dt->day . ' ' . $months[$dt->month - 1] . ' ' . $dt->year;
        } else {
            $dateStr = $dt->format('F j, Y');
        }

        return $kota . ', ' . $dateStr;
    }

    private function romanMonth(?string $bulan): string
    {
        $map = [
            'januari'   => 'I',   'februari'  => 'II',   'maret'     => 'III',
            'april'     => 'IV',  'mei'        => 'V',    'juni'      => 'VI',
            'juli'      => 'VII', 'agustus'   => 'VIII', 'september' => 'IX',
            'oktober'   => 'X',   'november'  => 'XI',   'desember'  => 'XII',
        ];
        return $map[strtolower(trim($bulan ?? ''))] ?? (string) now()->month;
    }
}

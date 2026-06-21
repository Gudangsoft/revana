<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Support\Facades\Storage;

class LoaController extends Controller
{
    // ── Public: dibuka penulis via link email atau halaman Request LOA ─────
    public function publicView(string $kodeLoa)
    {
        $submission = Submission::where('kode_loa', $kodeLoa)->firstOrFail();
        $submission->load(['journalSlot.journalMaster']);
        $journal = $submission->journalSlot?->journalMaster;
        $slot    = $submission->journalSlot;
        $date    = request('tanggal');
        $kode    = $submission->kode_loa ?: $submission->kode_submit;
        $lang    = $journal?->loa_language ?? 'en';

        return view('admin.loa.receipt', [
            'submission'      => $submission,
            'journal'         => $journal,
            'slot'            => $slot,
            'loaNumber'       => $this->loaNumber($submission, $journal, $slot),
            'loaDate'         => $this->loaDate($journal, $date, $lang),
            'loaDateRaw'      => $date ?: ($journal?->loa_tanggal ? \Carbon\Carbon::parse($journal->loa_tanggal)->toDateString() : now()->toDateString()),
            'logoUrl'         => $journal?->logo_path ? Storage::url($journal->logo_path) : null,
            'signUrl'         => $journal?->editor_signature_path ? Storage::url($journal->editor_signature_path) : null,
            'headerImageUrl'  => $journal?->header_image_path ? Storage::url($journal->header_image_path) : null,
            'footerImageUrl'         => $journal?->footer_image_path ? Storage::url($journal->footer_image_path) : null,
            'accreditationLogoUrl'   => $journal?->accreditation_logo_path ? Storage::url($journal->accreditation_logo_path) : null,
            'verifyUrl'       => route('verify.direct', ['kode_loa' => $kode]),
            'isAdminView'     => false,
            'lang'            => $lang,
        ]);
    }

    // ── Admin: dari detail submission (butuh login) ─────────────────────
    public function show(Submission $submission)
    {
        $submission->load(['journalSlot.journalMaster']);
        $journal = $submission->journalSlot?->journalMaster;
        $slot    = $submission->journalSlot;
        $date    = request('tanggal');
        $kode    = $submission->kode_loa ?: $submission->kode_submit;
        $lang    = $journal?->loa_language ?? 'en';

        return view('admin.loa.receipt', [
            'submission'      => $submission,
            'journal'         => $journal,
            'slot'            => $slot,
            'loaNumber'       => $this->loaNumber($submission, $journal, $slot),
            'loaDate'         => $this->loaDate($journal, $date, $lang),
            'loaDateRaw'      => $date ?: ($journal?->loa_tanggal ? \Carbon\Carbon::parse($journal->loa_tanggal)->toDateString() : now()->toDateString()),
            'logoUrl'         => $journal?->logo_path ? Storage::url($journal->logo_path) : null,
            'signUrl'         => $journal?->editor_signature_path ? Storage::url($journal->editor_signature_path) : null,
            'headerImageUrl'  => $journal?->header_image_path ? Storage::url($journal->header_image_path) : null,
            'footerImageUrl'         => $journal?->footer_image_path ? Storage::url($journal->footer_image_path) : null,
            'accreditationLogoUrl'   => $journal?->accreditation_logo_path ? Storage::url($journal->accreditation_logo_path) : null,
            'verifyUrl'       => route('verify.direct', ['kode_loa' => $kode]),
            'isAdminView'     => true,
            'lang'            => $lang,
        ]);
    }

    // ── Marketing: view LOA dengan date picker, tanpa akses admin ───────────
    public function showMarketing(Submission $submission)
    {
        $marketing = \Auth::guard('marketing')->user();
        if ($submission->marketing_id && $submission->marketing_id !== $marketing->id) {
            return redirect()->route('marketing.submissions')
                ->with('error', 'Anda tidak memiliki akses ke LOA ini.');
        }

        $submission->load(['journalSlot.journalMaster']);
        $journal = $submission->journalSlot?->journalMaster;
        $slot    = $submission->journalSlot;
        $date    = request('tanggal');
        $kode    = $submission->kode_loa ?: $submission->kode_submit;
        $lang    = $journal?->loa_language ?? 'en';

        return view('admin.loa.receipt', [
            'submission'           => $submission,
            'journal'              => $journal,
            'slot'                 => $slot,
            'loaNumber'            => $this->loaNumber($submission, $journal, $slot),
            'loaDate'              => $this->loaDate($journal, $date, $lang),
            'loaDateRaw'           => $date ?: ($journal?->loa_tanggal ? \Carbon\Carbon::parse($journal->loa_tanggal)->toDateString() : now()->toDateString()),
            'logoUrl'              => $journal?->logo_path ? Storage::url($journal->logo_path) : null,
            'signUrl'              => $journal?->editor_signature_path ? Storage::url($journal->editor_signature_path) : null,
            'headerImageUrl'       => $journal?->header_image_path ? Storage::url($journal->header_image_path) : null,
            'footerImageUrl'       => $journal?->footer_image_path ? Storage::url($journal->footer_image_path) : null,
            'accreditationLogoUrl' => $journal?->accreditation_logo_path ? Storage::url($journal->accreditation_logo_path) : null,
            'verifyUrl'            => route('verify.direct', ['kode_loa' => $kode]),
            'isAdminView'          => false,
            'canEditDate'          => true,
            'backUrl'              => route('marketing.submissions.show', $submission),
            'lang'                 => $lang,
        ]);
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

    // ── helpers ────────────────────────────────────────────────────────────

    private function loaNumber(Submission $s, $j, $slot): string
    {
        $kode       = $j?->kode_singkat ?: 'SIPERA';
        $roman      = $this->romanMonth($slot?->bulan);
        $year       = $slot?->tahun ?? now()->year;
        $id         = $s->id_artikel ?: $s->kode_submit;
        $kodeSubmit = $s->kode_submit ?? '';

        $kodeSubmitLabel = str_ends_with(strtoupper($kodeSubmit), 'SIPERA') ? $kodeSubmit : $kodeSubmit . 'SIPERA';

        return $id . '/' . $kodeSubmitLabel . '/' . $kode . '/' . $roman . '/' . $year;
    }

    private function loaDate($journal, ?string $dateOverride = null, string $lang = 'en'): string
    {
        if ($dateOverride) {
            $dt = \Carbon\Carbon::parse($dateOverride);
        } elseif ($journal?->loa_tanggal) {
            $dt = \Carbon\Carbon::parse($journal->loa_tanggal);
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

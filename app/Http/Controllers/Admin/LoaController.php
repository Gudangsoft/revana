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

        $kode = $submission->kode_loa ?: $submission->kode_submit;
        return view('admin.loa.receipt', [
            'submission' => $submission,
            'journal'    => $journal,
            'slot'       => $slot,
            'loaNumber'  => $this->loaNumber($submission, $journal, $slot),
            'loaDate'    => $this->loaDate($journal, $date),
            'logoUrl'    => $journal?->logo_path ? Storage::url($journal->logo_path) : null,
            'signUrl'    => $journal?->editor_signature_path ? Storage::url($journal->editor_signature_path) : null,
            'verifyUrl'  => route('verify.direct', ['kode_loa' => $kode]),
            'isAdminView'=> false,
        ]);
    }

    // ── Admin: dari detail submission (butuh login) ─────────────────────
    public function show(Submission $submission)
    {
        $submission->load(['journalSlot.journalMaster']);
        $journal = $submission->journalSlot?->journalMaster;
        $slot    = $submission->journalSlot;
        $date    = request('tanggal');

        $kode = $submission->kode_loa ?: $submission->kode_submit;
        return view('admin.loa.receipt', [
            'submission' => $submission,
            'journal'    => $journal,
            'slot'       => $slot,
            'loaNumber'  => $this->loaNumber($submission, $journal, $slot),
            'loaDate'    => $this->loaDate($journal, $date),
            'logoUrl'    => $journal?->logo_path ? Storage::url($journal->logo_path) : null,
            'signUrl'    => $journal?->editor_signature_path ? Storage::url($journal->editor_signature_path) : null,
            'verifyUrl'  => route('verify.direct', ['kode_loa' => $kode]),
            'isAdminView'=> true,
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
        $kode   = $j?->kode_singkat ?: 'SIPERA';
        $roman  = $this->romanMonth($slot?->bulan);
        $year   = $slot?->tahun ?? now()->year;
        $id     = $s->id_artikel ?: $s->kode_submit;

        return $id . '/' . $kode . '/APRKOM/' . $roman . '/' . $year;
    }

    private function loaDate($journal, ?string $dateOverride = null): string
    {
        if ($dateOverride) {
            $dt = \Carbon\Carbon::parse($dateOverride);
        } else {
            $dt = now();
        }
        return ($journal?->loa_kota ?? 'Semarang') . ', ' . $dt->format('F j, Y');
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Support\Facades\Storage;

class LoaController extends Controller
{
    public function show(Submission $submission)
    {
        $submission->load(['journalSlot.journalMaster']);
        $journal = $submission->journalSlot?->journalMaster;
        $slot    = $submission->journalSlot;

        return view('admin.loa.receipt', [
            'submission' => $submission,
            'journal'    => $journal,
            'slot'       => $slot,
            'loaNumber'  => $this->loaNumber($submission, $journal, $slot),
            'loaDate'    => $this->loaDate($journal),
            'logoUrl'    => $journal?->logo_path ? Storage::url($journal->logo_path) : null,
            'signUrl'    => $journal?->editor_signature_path ? Storage::url($journal->editor_signature_path) : null,
        ]);
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

    private function loaDate($journal): string
    {
        $dt = $journal?->loa_tanggal ? \Carbon\Carbon::parse($journal->loa_tanggal) : now();
        return ($journal?->loa_kota ?? 'Semarang') . ', ' . $dt->isoFormat('MMMM D, YYYY');
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

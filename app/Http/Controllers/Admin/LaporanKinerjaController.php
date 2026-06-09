<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanKinerjaExport;
use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Models\Marketing;
use App\Models\PicPointHistory;
use App\Models\MarketingPointHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKinerjaController extends Controller
{
    private const STEPS = [
        'submit'     => 'Submit',
        'editor1'    => 'Editor 1',
        'author1'    => 'Author 1',
        'editor2'    => 'Editor 2',
        'reviewer1'  => 'Reviewer 1',
        'reviewer2'  => 'Reviewer 2',
        'editor3'    => 'Editor 3',
        'author2'    => 'Author 2',
        'production' => 'Production',
        'validator'  => 'Validator',
    ];

    public function index(Request $request)
    {
        $dariTanggal   = $request->input('dari_tanggal');   // Y-m-d, opsional
        $sampaiTanggal = $request->input('sampai_tanggal'); // Y-m-d, opsional
        $bulan         = (int) $request->input('bulan', now()->month);
        $tahun         = (int) $request->input('tahun', now()->year);

        $isRange = $dariTanggal && $sampaiTanggal;

        if ($isRange) {
            $namaBulan = \Carbon\Carbon::parse($dariTanggal)->locale('id')->translatedFormat('d F Y')
                . ' — '
                . \Carbon\Carbon::parse($sampaiTanggal)->locale('id')->translatedFormat('d F Y');
        } else {
            $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)
                ->locale('id')
                ->translatedFormat('F Y');
        }

        // --- Rekap PIC ---
        $pics = Pic::where('is_active', true)->orderBy('name')->get();

        // Step config: query submissions by validated_at (authoritative date)
        // Falls back to pic_point_histories for submit step (no validated_at column)
        $stepCfg = [
            'submit'     => ['petugas' => 'petugas_submit_id',     'valid' => null,               'date' => 'created_at'],
            'editor1'    => ['petugas' => 'petugas_editor1_id',    'valid' => 'editor1_valid',    'date' => 'editor1_validated_at'],
            'author1'    => ['petugas' => 'petugas_author1_id',    'valid' => 'author1_valid',    'date' => 'author1_validated_at'],
            'editor2'    => ['petugas' => 'petugas_editor2_id',    'valid' => 'editor2_valid',    'date' => 'editor2_validated_at'],
            'reviewer1'  => ['petugas' => 'petugas_reviewer1_id',  'valid' => 'reviewer1_valid',  'date' => 'reviewer1_validated_at'],
            'reviewer2'  => ['petugas' => 'petugas_reviewer2_id',  'valid' => 'reviewer2_valid',  'date' => 'reviewer2_validated_at'],
            'editor3'    => ['petugas' => 'petugas_editor3_id',    'valid' => 'editor3_valid',    'date' => 'editor3_validated_at'],
            'author2'    => ['petugas' => 'petugas_author2_id',    'valid' => 'author2_valid',    'date' => 'author2_validated_at'],
            'production' => ['petugas' => 'petugas_production_id', 'valid' => 'production_valid', 'date' => 'production_validated_at'],
            'validator'  => ['petugas' => 'petugas_validator_id',  'valid' => 'validator_valid',  'date' => 'validator_validated_at'],
        ];

        // Build count aggregates from submissions (one query per step, fast with index)
        $submissionCounts = []; // [pic_id][step] = count
        foreach ($stepCfg as $step => $cfg) {
            $q = \DB::table('submissions')->whereNotNull($cfg['petugas']);
            if ($cfg['valid']) {
                $q->where($cfg['valid'], true);
            }
            if ($isRange) {
                $q->whereDate($cfg['date'], '>=', $dariTanggal)
                  ->whereDate($cfg['date'], '<=', $sampaiTanggal);
            } else {
                $q->whereMonth($cfg['date'], $bulan)->whereYear($cfg['date'], $tahun);
            }
            foreach ($q->selectRaw("{$cfg['petugas']} as pic_id, COUNT(*) as cnt")->groupBy($cfg['petugas'])->get() as $row) {
                $submissionCounts[$row->pic_id][$step] = (int) $row->cnt;
            }
        }

        // Point value per step
        $pointValues = [];
        foreach (array_keys(self::STEPS) as $step) {
            $pointValues[$step] = PicPointHistory::getPointsForStep($step);
        }

        // Manual adjustments still from history (date-filtered)
        $adjQuery = PicPointHistory::where('step', 'adjustment');
        if ($isRange) {
            $adjQuery->whereDate('created_at', '>=', $dariTanggal)
                     ->whereDate('created_at', '<=', $sampaiTanggal);
        } else {
            $adjQuery->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
        }
        $adjustments = $adjQuery->selectRaw('pic_id, SUM(points_earned) as total_adj')
                                ->groupBy('pic_id')->get()->keyBy('pic_id');

        $picRekap = $pics->map(function ($pic) use ($submissionCounts, $pointValues, $adjustments) {
            $picCounts  = $submissionCounts[$pic->id] ?? [];
            $stepCounts = [];
            $totalTugas = 0;
            $totalPoin  = 0.0;

            foreach (self::STEPS as $key => $label) {
                $count            = $picCounts[$key] ?? 0;
                $stepCounts[$key] = $count;
                $totalTugas      += $count;
                $totalPoin       += $count * ($pointValues[$key] ?? 0);
            }

            $adj = $adjustments->get($pic->id);
            if ($adj) {
                $totalPoin += (float) $adj->total_adj;
            }

            return [
                'pic'         => $pic,
                'step_counts' => $stepCounts,
                'total_tugas' => $totalTugas,
                'total_poin'  => $totalPoin,
            ];
        })->filter(fn($row) => $row['total_tugas'] > 0)
          ->sortByDesc('total_poin')
          ->values();

        // --- Rekap Marketing --- (single aggregated query)
        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();

        $mktQuery = MarketingPointHistory::selectRaw('marketing_id, COUNT(*) as task_count, SUM(points_earned) as total_points');
        if ($isRange) {
            $mktQuery->whereDate('created_at', '>=', $dariTanggal)
                     ->whereDate('created_at', '<=', $sampaiTanggal);
        } else {
            $mktQuery->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
        }
        $mktAggregates = $mktQuery->groupBy('marketing_id')->get()->keyBy('marketing_id');

        $mktRekap = $marketings->map(function ($mkt) use ($mktAggregates) {
            $row = $mktAggregates->get($mkt->id);
            return [
                'marketing'    => $mkt,
                'total_submit' => $row ? (int) $row->task_count : 0,
                'total_poin'   => $row ? (float) $row->total_points : 0,
            ];
        })->filter(fn($row) => $row['total_submit'] > 0)
          ->sortByDesc('total_submit')
          ->values();

        // --- Summary stats ---
        $totalPicTugas  = $picRekap->sum('total_tugas');
        $totalPicPoin   = $picRekap->sum('total_poin');
        $totalMktSubmit = $mktRekap->sum('total_submit');
        $totalMktPoin   = $mktRekap->sum('total_poin');

        $steps     = self::STEPS;
        $tahunList = range(now()->year, max(now()->year - 4, 2024));

        return view('admin.laporan-kinerja.index', compact(
            'bulan', 'tahun', 'dariTanggal', 'sampaiTanggal', 'isRange', 'namaBulan',
            'picRekap', 'mktRekap', 'steps',
            'totalPicTugas', 'totalPicPoin',
            'totalMktSubmit', 'totalMktPoin',
            'tahunList'
        ));
    }

    public function exportExcel(Request $request)
    {
        [$picRekap, $mktRekap, $steps, $namaBulan] = $this->buildData($request);

        $filename = 'laporan-kinerja-' . $namaBulan . '.xlsx';

        return Excel::download(
            new LaporanKinerjaExport($picRekap, $mktRekap, $steps, $namaBulan),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        [$picRekap, $mktRekap, $steps, $namaBulan,
         $totalPicTugas, $totalPicPoin, $totalMktSubmit, $totalMktPoin] = $this->buildData($request, true);

        $pdf = Pdf::loadView('admin.laporan-kinerja.pdf', compact(
            'picRekap', 'mktRekap', 'steps', 'namaBulan',
            'totalPicTugas', 'totalPicPoin', 'totalMktSubmit', 'totalMktPoin'
        ))->setPaper('a3', 'landscape');

        return $pdf->download('laporan-kinerja-' . $namaBulan . '.pdf');
    }

    private function buildData(Request $request, bool $withTotals = false): array
    {
        $dariTanggal   = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');
        $bulan         = (int) $request->input('bulan', now()->month);
        $tahun         = (int) $request->input('tahun', now()->year);
        $isRange       = $dariTanggal && $sampaiTanggal;

        if ($isRange) {
            $namaBulan = \Carbon\Carbon::parse($dariTanggal)->locale('id')->translatedFormat('d F Y')
                . ' — '
                . \Carbon\Carbon::parse($sampaiTanggal)->locale('id')->translatedFormat('d F Y');
        } else {
            $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)
                ->locale('id')->translatedFormat('F Y');
        }

        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $picQuery = PicPointHistory::query();
        if ($isRange) {
            $picQuery->whereDate('created_at', '>=', $dariTanggal)
                     ->whereDate('created_at', '<=', $sampaiTanggal);
        } else {
            $picQuery->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
        }
        $picHistories = $picQuery->get()->groupBy('pic_id');

        $picRekap = $pics->map(function ($pic) use ($picHistories) {
            $histories = $picHistories->get($pic->id, collect());
            $byStep    = $histories->groupBy('step');
            $stepCounts = [];
            foreach (self::STEPS as $key => $label) {
                $stepCounts[$key] = $byStep->get($key, collect())->count();
            }
            return [
                'pic'         => $pic,
                'step_counts' => $stepCounts,
                'total_tugas' => $histories->count(),
                'total_poin'  => $histories->sum('points_earned'),
            ];
        })->filter(fn($r) => $r['total_tugas'] > 0)->sortByDesc('total_poin')->values();

        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $mktQuery = MarketingPointHistory::query();
        if ($isRange) {
            $mktQuery->whereDate('created_at', '>=', $dariTanggal)
                     ->whereDate('created_at', '<=', $sampaiTanggal);
        } else {
            $mktQuery->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
        }
        $mktHistories = $mktQuery->get()->groupBy('marketing_id');

        $mktRekap = $marketings->map(function ($mkt) use ($mktHistories) {
            $histories = $mktHistories->get($mkt->id, collect());
            return [
                'marketing'    => $mkt,
                'total_submit' => $histories->count(),
                'total_poin'   => $histories->sum('points_earned'),
            ];
        })->filter(fn($r) => $r['total_submit'] > 0)->sortByDesc('total_submit')->values();

        $steps = self::STEPS;

        if ($withTotals) {
            return [
                $picRekap, $mktRekap, $steps, $namaBulan,
                $picRekap->sum('total_tugas'),
                $picRekap->sum('total_poin'),
                $mktRekap->sum('total_submit'),
                $mktRekap->sum('total_poin'),
            ];
        }

        return [$picRekap, $mktRekap, $steps, $namaBulan];
    }
}

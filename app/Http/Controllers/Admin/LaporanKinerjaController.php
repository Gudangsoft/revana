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
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        // --- Rekap PIC ---
        $pics = Pic::where('is_active', true)
            ->orderBy('name')
            ->get();

        $picHistories = PicPointHistory::with('pic')
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->get()
            ->groupBy('pic_id');

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
        })->filter(fn($row) => $row['total_tugas'] > 0)
          ->sortByDesc('total_poin')
          ->values();

        // --- Rekap Marketing ---
        $marketings = Marketing::where('is_active', true)
            ->orderBy('name')
            ->get();

        $mktHistories = MarketingPointHistory::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->get()
            ->groupBy('marketing_id');

        $mktRekap = $marketings->map(function ($mkt) use ($mktHistories) {
            $histories = $mktHistories->get($mkt->id, collect());
            return [
                'marketing'    => $mkt,
                'total_submit' => $histories->count(),
                'total_poin'   => $histories->sum('points_earned'),
            ];
        })->filter(fn($row) => $row['total_submit'] > 0)
          ->sortByDesc('total_submit')
          ->values();

        // --- Summary stats ---
        $totalPicTugas  = $picRekap->sum('total_tugas');
        $totalPicPoin   = $picRekap->sum('total_poin');
        $totalMktSubmit = $mktRekap->sum('total_submit');
        $totalMktPoin   = $mktRekap->sum('total_poin');

        $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)
            ->locale('id')
            ->translatedFormat('F Y');

        $steps = self::STEPS;

        // Build year options (from earliest history or 5 years back)
        $tahunList = range(now()->year, max(now()->year - 4, 2024));

        return view('admin.laporan-kinerja.index', compact(
            'bulan', 'tahun', 'namaBulan',
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
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $pics = Pic::where('is_active', true)->orderBy('name')->get();
        $picHistories = PicPointHistory::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->get()->groupBy('pic_id');

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
        $mktHistories = MarketingPointHistory::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->get()->groupBy('marketing_id');

        $mktRekap = $marketings->map(function ($mkt) use ($mktHistories) {
            $histories = $mktHistories->get($mkt->id, collect());
            return [
                'marketing'    => $mkt,
                'total_submit' => $histories->count(),
                'total_poin'   => $histories->sum('points_earned'),
            ];
        })->filter(fn($r) => $r['total_submit'] > 0)->sortByDesc('total_submit')->values();

        $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)
            ->locale('id')->translatedFormat('F Y');

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

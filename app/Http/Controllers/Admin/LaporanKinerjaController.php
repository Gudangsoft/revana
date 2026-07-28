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

    /**
     * Resolve rentang tanggal efektif dari request.
     *
     * Kalau dari_tanggal/sampai_tanggal diisi manual, pakai itu apa adanya.
     * Kalau tidak (pilih dropdown Bulan+Tahun), periode SATU "bulan" adalah
     * kalender biasa 1 s/d akhir bulan itu (BUKAN cutoff 26-25 — sempat dipakai di
     * section #13, tapi di-revert karena membingungkan: data hari-hari terakhir
     * bulan berjalan jadi tidak muncul sampai periode "bulan berikutnya" dipilih).
     */
    private function resolvePeriod(Request $request): array
    {
        $dariTanggal   = $request->input('dari_tanggal');   // Y-m-d, opsional
        $sampaiTanggal = $request->input('sampai_tanggal'); // Y-m-d, opsional
        $bulan         = (int) $request->input('bulan', now()->month);
        $tahun         = (int) $request->input('tahun', now()->year);

        $isRange = $dariTanggal && $sampaiTanggal;

        if ($isRange) {
            $periodStart = \Carbon\Carbon::parse($dariTanggal)->startOfDay();
            $periodEnd   = \Carbon\Carbon::parse($sampaiTanggal)->endOfDay();
        } else {
            $periodStart = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $periodEnd   = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
        }

        if ($isRange) {
            $namaBulan = $periodStart->locale('id')->translatedFormat('d F Y')
                . ' — '
                . $periodEnd->locale('id')->translatedFormat('d F Y');
        } else {
            $namaBulan = \Carbon\Carbon::create()->month($bulan)->locale('id')->translatedFormat('F') . ' ' . $tahun;
        }

        return [$periodStart, $periodEnd, $namaBulan, $isRange, $bulan, $tahun, $dariTanggal, $sampaiTanggal];
    }

    public function index(Request $request)
    {
        [$periodStart, $periodEnd, $namaBulan, $isRange, $bulan, $tahun, $dariTanggal, $sampaiTanggal] = $this->resolvePeriod($request);

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
            $q->whereDate($cfg['date'], '>=', $periodStart->toDateString())
              ->whereDate($cfg['date'], '<=', $periodEnd->toDateString());
            foreach ($q->selectRaw("{$cfg['petugas']} as pic_id, COUNT(*) as cnt")->groupBy($cfg['petugas'])->get() as $row) {
                $submissionCounts[$row->pic_id][$step] = (int) $row->cnt;
            }
        }

        // Poin PIC per periode: SUM(points_earned) riwayat ASLI (bukan count × rate SAAT
        // INI seperti sebelumnya) — supaya laporan bulan lalu tidak ikut berubah setiap
        // kali admin mengubah rate poin di /admin/task-point-settings. Step 'adjustment'
        // otomatis ikut terhitung karena disaring dari created_at yang sama seperti step lain.
        $picPointQuery = PicPointHistory::query()
            ->whereDate('created_at', '>=', $periodStart->toDateString())
            ->whereDate('created_at', '<=', $periodEnd->toDateString());
        $picPointSums = $picPointQuery->selectRaw('pic_id, SUM(points_earned) as total')
                                      ->groupBy('pic_id')->get()->keyBy('pic_id');

        $picRekap = $pics->map(function ($pic) use ($submissionCounts, $picPointSums) {
            $picCounts  = $submissionCounts[$pic->id] ?? [];
            $stepCounts = [];
            $totalTugas = 0;

            foreach (self::STEPS as $key => $label) {
                $count            = $picCounts[$key] ?? 0;
                $stepCounts[$key] = $count;
                $totalTugas      += $count;
            }

            // $totalPoin = (float) ($picPointSums->get($pic->id)->total ?? 0);
            $totalPoin = number_format((float) ($picPointSums->get($pic->id)->total ?? 0), 2, '.', ',');

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

        $mktQuery = MarketingPointHistory::selectRaw('marketing_id, COUNT(*) as task_count, SUM(points_earned) as total_points')
            ->whereDate('created_at', '>=', $periodStart->toDateString())
            ->whereDate('created_at', '<=', $periodEnd->toDateString());
        $mktAggregates = $mktQuery->groupBy('marketing_id')->get()->keyBy('marketing_id');

        $mktRekap = $marketings->map(function ($mkt) use ($mktAggregates) {
            $row = $mktAggregates->get($mkt->id);
            return [
                'marketing'    => $mkt,
                'total_submit' => $row ? (int) $row->task_count : 0,
                'total_poin'   => $row ? number_format((float) $row->total_points, 2, '.', ',') : 0,
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
        [$periodStart, $periodEnd, $namaBulan] = $this->resolvePeriod($request);

        $pics = Pic::where('is_active', true)->orderBy('name')->get();

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

        $submissionCounts = [];
        foreach ($stepCfg as $step => $cfg) {
            $q = \DB::table('submissions')->whereNotNull($cfg['petugas']);
            if ($cfg['valid']) {
                $q->where($cfg['valid'], true);
            }
            $q->whereDate($cfg['date'], '>=', $periodStart->toDateString())
              ->whereDate($cfg['date'], '<=', $periodEnd->toDateString());
            foreach ($q->selectRaw("{$cfg['petugas']} as pic_id, COUNT(*) as cnt")->groupBy($cfg['petugas'])->get() as $row) {
                $submissionCounts[$row->pic_id][$step] = (int) $row->cnt;
            }
        }

        // Poin PIC per periode: SUM(points_earned) riwayat ASLI (bukan count × rate saat
        // ini), lihat catatan lengkap di method index() di atas.
        $picPointQuery = PicPointHistory::query()
            ->whereDate('created_at', '>=', $periodStart->toDateString())
            ->whereDate('created_at', '<=', $periodEnd->toDateString());
        $picPointSums = $picPointQuery->selectRaw('pic_id, SUM(points_earned) as total')
                                      ->groupBy('pic_id')->get()->keyBy('pic_id');

        $picRekap = $pics->map(function ($pic) use ($submissionCounts, $picPointSums) {
            $picCounts  = $submissionCounts[$pic->id] ?? [];
            $stepCounts = [];
            $totalTugas = 0;
            foreach (self::STEPS as $key => $label) {
                $count            = $picCounts[$key] ?? 0;
                $stepCounts[$key] = $count;
                $totalTugas      += $count;
            }
            $totalPoin = number_format((float) ($picPointSums->get($pic->id)->total ?? 0), 2, '.', ',');
            return [
                'pic'         => $pic,
                'step_counts' => $stepCounts,
                'total_tugas' => $totalTugas,
                'total_poin'  => $totalPoin,
            ];
        })->filter(fn($r) => $r['total_tugas'] > 0)->sortByDesc('total_poin')->values();

        $marketings = Marketing::where('is_active', true)->orderBy('name')->get();
        $mktQuery = MarketingPointHistory::query()
            ->whereDate('created_at', '>=', $periodStart->toDateString())
            ->whereDate('created_at', '<=', $periodEnd->toDateString());
        $mktHistories = $mktQuery->get()->groupBy('marketing_id');

        $mktRekap = $marketings->map(function ($mkt) use ($mktHistories) {
            $histories = $mktHistories->get($mkt->id, collect());
            return [
                'marketing'    => $mkt,
                'total_submit' => $histories->count(),
                'total_poin'   => number_format((float) $histories->sum('points_earned'), 2, '.', ','),
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

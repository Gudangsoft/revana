<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanHarianRekapExport;
use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\LaporanHarianLog;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanHarianController extends Controller
{
    public function index(Request $request)
    {
        $picId             = $request->input('pic_id');
        $dariTanggal       = $request->input('dari_tanggal');
        $sampaiTanggal     = $request->input('sampai_tanggal', now()->toDateString());
        $belumDivalidasi   = $request->boolean('belum_divalidasi');

        if (!$dariTanggal) {
            $dariTanggal = now()->startOfMonth()->toDateString();
        }

        $query = LaporanHarian::with('pic')
            ->select(
                'pic_id',
                'tanggal',
                DB::raw('COUNT(*) as total_kegiatan'),
                DB::raw('ROUND(AVG(capaian_hasil)) as avg_capaian'),
                DB::raw('SUM(CASE WHEN validated_at IS NOT NULL THEN 1 ELSE 0 END) as total_validated'),
                DB::raw('MAX(validated_at) as last_validated_at'),
                DB::raw('MAX(validated_by) as last_validated_by'),
                DB::raw('MAX(catatan_admin) as catatan_admin'),
                DB::raw('GROUP_CONCAT(IFNULL(judul_kegiatan, target_kerja) ORDER BY id SEPARATOR "||") as ringkasan_kegiatan')
            )
            ->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
            ->groupBy('pic_id', 'tanggal')
            ->orderByDesc('tanggal')
            ->orderBy('pic_id');

        if ($picId) {
            $query->where('pic_id', $picId);
        }

        if ($belumDivalidasi) {
            $query->havingRaw('total_validated < total_kegiatan');
        }

        $laporan = $query->paginate(30)->withQueryString();
        $pics    = Pic::where('is_active', true)->orderBy('name')->get();

        // Load validators for grouped rows
        $validatorIds = $laporan->pluck('last_validated_by')->filter()->unique()->values();
        $validators   = \App\Models\User::whereIn('id', $validatorIds)->pluck('name', 'id');

        // Chart: avg capaian per day in selected range
        $chartQuery = LaporanHarian::whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
            ->selectRaw('tanggal, ROUND(AVG(capaian_hasil)) as avg_capaian')
            ->groupBy('tanggal')
            ->orderBy('tanggal');
        if ($picId) {
            $chartQuery->where('pic_id', $picId);
        }
        $chartData = $chartQuery->get();

        return view('admin.laporan-harian.index', compact(
            'laporan', 'pics', 'picId', 'dariTanggal', 'sampaiTanggal', 'belumDivalidasi', 'chartData', 'validators'
        ));
    }

    public function export(Request $request)
    {
        $picId         = $request->input('pic_id');
        $dariTanggal   = $request->input('dari_tanggal', now()->startOfMonth()->toDateString());
        $sampaiTanggal = $request->input('sampai_tanggal', now()->toDateString());

        $query = LaporanHarian::with('pic')
            ->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
            ->orderBy('tanggal')->orderBy('pic_id')->orderBy('id');

        if ($picId) {
            $query->where('pic_id', $picId);
        }

        $entries  = $query->get();
        $filename = 'catatan-kinerja-' . $dariTanggal . '-sd-' . $sampaiTanggal . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($entries) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Tanggal', 'PIC', 'Judul Kegiatan', 'Catatan Kerja', 'Laporan Kinerja', 'Bukti', 'Capaian %', 'Divalidasi', 'Divalidasi Pada', 'Catatan Admin']);
            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->tanggal->toDateString(),
                    $e->pic?->name ?? '-',
                    $e->judul_kegiatan ?? '',
                    $e->target_kerja,
                    $e->laporan_kinerja,
                    $e->bukti_hasil ?? '',
                    $e->capaian_hasil,
                    $e->validated_at ? 'Ya' : 'Tidak',
                    $e->validated_at ? $e->validated_at->format('d/m/Y H:i') : '',
                    $e->catatan_admin ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function rekap(Request $request)
    {
        $bulan  = $request->input('bulan', now()->format('Y-m'));
        $picId  = $request->input('pic_id');

        [$year, $month] = explode('-', $bulan);

        $query = LaporanHarian::with('pic')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->select(
                'pic_id',
                DB::raw('COUNT(*) as total_kegiatan'),
                DB::raw('ROUND(AVG(capaian_hasil)) as avg_capaian'),
                DB::raw('SUM(CASE WHEN validated_at IS NOT NULL THEN 1 ELSE 0 END) as total_validated'),
                DB::raw('COUNT(DISTINCT tanggal) as total_hari')
            )
            ->groupBy('pic_id')
            ->orderByDesc('avg_capaian');

        if ($picId) {
            $query->where('pic_id', $picId);
        }

        $rekap = $query->get();
        $pics  = Pic::where('is_active', true)->orderBy('name')->get();

        // Chart: daily avg capaian in this month
        $chartQuery = LaporanHarian::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->selectRaw('tanggal, ROUND(AVG(capaian_hasil)) as avg_capaian')
            ->groupBy('tanggal')
            ->orderBy('tanggal');
        if ($picId) {
            $chartQuery->where('pic_id', $picId);
        }
        $chartData = $chartQuery->get();

        return view('admin.laporan-harian.rekap', compact('rekap', 'pics', 'bulan', 'picId', 'chartData'));
    }

    public function exportRekap(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $picId = $request->input('pic_id');

        [$year, $month] = explode('-', $bulan);

        $query = LaporanHarian::with('pic')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->select(
                'pic_id',
                DB::raw('COUNT(*) as total_kegiatan'),
                DB::raw('ROUND(AVG(capaian_hasil)) as avg_capaian'),
                DB::raw('SUM(CASE WHEN validated_at IS NOT NULL THEN 1 ELSE 0 END) as total_validated'),
                DB::raw('COUNT(DISTINCT tanggal) as total_hari')
            )
            ->groupBy('pic_id')
            ->orderByDesc('avg_capaian');

        if ($picId) {
            $query->where('pic_id', $picId);
        }

        $rekap      = $query->get();
        $bulanLabel = \Carbon\Carbon::parse($bulan)->locale('id')->translatedFormat('F Y');
        $filename   = 'rekap-kinerja-harian-' . $bulan . '.xlsx';

        return Excel::download(new LaporanHarianRekapExport($rekap, $bulanLabel), $filename);
    }

    public function show($picId, $tanggal)
    {
        $pic      = Pic::findOrFail($picId);
        $entries  = LaporanHarian::with('validator')->where('pic_id', $picId)->where('tanggal', $tanggal)->orderBy('id')->get();
        $logs     = LaporanHarianLog::whereIn('laporan_harian_id', $entries->pluck('id'))
                        ->orderByDesc('created_at')->get();

        $allValidated  = $entries->isNotEmpty() && $entries->every(fn($e) => $e->validated_at);
        $someValidated = $entries->some(fn($e) => $e->validated_at);
        $catatanAdmin  = $entries->first()?->catatan_admin;

        return view('admin.laporan-harian.show', compact(
            'pic', 'entries', 'logs', 'tanggal',
            'allValidated', 'someValidated', 'catatanAdmin'
        ));
    }

    public function setValidasi(Request $request, $picId, $tanggal)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:2000',
        ]);

        $adminUser  = auth()->user();
        $entries    = LaporanHarian::where('pic_id', $picId)->where('tanggal', $tanggal)->get();
        $newCatatan = $request->catatan_admin;
        $oldCatatan = $entries->first()?->catatan_admin;

        foreach ($entries as $entry) {
            $entry->update(['catatan_admin' => $newCatatan]);
        }

        if ($entries->isNotEmpty() && (string) $oldCatatan !== (string) $newCatatan) {
            LaporanHarianLog::record(
                $entries->first(), 'admin', $adminUser->id, $adminUser->name, 'catatan',
                ['catatan_admin' => ['old' => $oldCatatan, 'new' => $newCatatan]]
            );
        }

        return redirect()->route('admin.laporan-harian.show', [$picId, $tanggal])
            ->with('success', 'Catatan berhasil disimpan.');
    }

    public function setValidasiEntry(Request $request, LaporanHarian $laporanHarian)
    {
        $request->validate(['catatan_admin' => 'nullable|string|max:2000']);

        $adminUser  = auth()->user();
        $oldCatatan = $laporanHarian->catatan_admin;
        $newCatatan = $request->catatan_admin;

        if ($request->action === 'validate') {
            $laporanHarian->update([
                'validated_at'  => now(),
                'validated_by'  => $adminUser->id,
                'catatan_admin' => $newCatatan,
            ]);
            $changes = [];
            if ((string) $oldCatatan !== (string) $newCatatan) {
                $changes['catatan_admin'] = ['old' => $oldCatatan, 'new' => $newCatatan];
            }
            LaporanHarianLog::record($laporanHarian, 'admin', $adminUser->id, $adminUser->name, 'validated', $changes);
            $message = 'Kegiatan berhasil divalidasi.';
        } elseif ($request->action === 'save_catatan') {
            $laporanHarian->update(['catatan_admin' => $newCatatan]);
            if ((string) $oldCatatan !== (string) $newCatatan) {
                LaporanHarianLog::record($laporanHarian, 'admin', $adminUser->id, $adminUser->name, 'catatan',
                    ['catatan_admin' => ['old' => $oldCatatan, 'new' => $newCatatan]]
                );
            }
            $message = 'Catatan berhasil disimpan.';
        } else {
            $laporanHarian->update([
                'validated_at'  => null,
                'validated_by'  => null,
                'catatan_admin' => $newCatatan,
            ]);
            LaporanHarianLog::record($laporanHarian, 'admin', $adminUser->id, $adminUser->name, 'unvalidated');
            $message = 'Validasi kegiatan dibatalkan.';
        }

        return redirect()->route('admin.laporan-harian.show', [$laporanHarian->pic_id, $laporanHarian->tanggal->toDateString()])
            ->with('success', $message);
    }
}

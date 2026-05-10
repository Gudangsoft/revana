<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\LaporanHarianLog;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanHarianController extends Controller
{
    public function index(Request $request)
    {
        $picId         = $request->input('pic_id');
        $dariTanggal   = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal', now()->toDateString());

        if (!$dariTanggal) {
            $dariTanggal = now()->startOfMonth()->toDateString();
        }

        // Group by pic_id + tanggal — 1 baris per PIC per hari
        $query = LaporanHarian::with('pic')
            ->select(
                'pic_id',
                'tanggal',
                DB::raw('COUNT(*) as total_kegiatan'),
                DB::raw('ROUND(AVG(capaian_hasil)) as avg_capaian'),
                DB::raw('SUM(CASE WHEN validated_at IS NOT NULL THEN 1 ELSE 0 END) as total_validated'),
                DB::raw('MAX(validated_at) as last_validated_at'),
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

        $laporan = $query->paginate(30)->withQueryString();
        $pics    = Pic::where('is_active', true)->orderBy('name')->get();

        return view('admin.laporan-harian.index', compact('laporan', 'pics', 'picId', 'dariTanggal', 'sampaiTanggal'));
    }

    public function show($picId, $tanggal)
    {
        $pic      = Pic::findOrFail($picId);
        $entries  = LaporanHarian::where('pic_id', $picId)->where('tanggal', $tanggal)->orderBy('id')->get();
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

        // Hanya simpan catatan, tidak ubah status validasi
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

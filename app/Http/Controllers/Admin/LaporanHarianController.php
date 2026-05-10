<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\Pic;
use Illuminate\Http\Request;

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

        $query = LaporanHarian::with(['pic', 'validator'])
            ->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
            ->orderByDesc('tanggal')
            ->orderBy('pic_id');

        if ($picId) {
            $query->where('pic_id', $picId);
        }

        $laporan = $query->paginate(30)->withQueryString();
        $pics    = Pic::where('is_active', true)->orderBy('name')->get();

        return view('admin.laporan-harian.index', compact('laporan', 'pics', 'picId', 'dariTanggal', 'sampaiTanggal'));
    }

    public function show(LaporanHarian $laporanHarian)
    {
        $laporanHarian->load(['pic', 'validator']);
        return view('admin.laporan-harian.show', compact('laporanHarian'));
    }

    public function setValidasi(Request $request, LaporanHarian $laporanHarian)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:2000',
            'action'        => 'required|in:validate,unvalidate',
        ]);

        if ($request->action === 'validate') {
            $laporanHarian->update([
                'validated_at'  => now(),
                'validated_by'  => auth()->id(),
                'catatan_admin' => $request->catatan_admin,
            ]);
            $message = 'Catatan kinerja berhasil divalidasi.';
        } else {
            $laporanHarian->update([
                'validated_at'  => null,
                'validated_by'  => null,
                'catatan_admin' => $request->catatan_admin,
            ]);
            $message = 'Validasi dibatalkan.';
        }

        return redirect()->route('admin.laporan-harian.show', $laporanHarian)
            ->with('success', $message);
    }
}

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

        $query = LaporanHarian::with('pic')
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
}

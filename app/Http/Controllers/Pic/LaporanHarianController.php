<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use Illuminate\Http\Request;

class LaporanHarianController extends Controller
{
    public function index()
    {
        $picId   = auth()->guard('pic')->id();
        $today   = now()->toDateString();
        $laporan = LaporanHarian::where('pic_id', $picId)->orderByDesc('tanggal')->paginate(15);
        $todayLaporan = LaporanHarian::where('pic_id', $picId)->where('tanggal', $today)->first();

        return view('pic.laporan-harian.index', compact('laporan', 'todayLaporan', 'today'));
    }

    public function store(Request $request)
    {
        $picId = auth()->guard('pic')->id();

        $validated = $request->validate([
            'tanggal'         => 'required|date',
            'target_kerja'    => 'required|string|max:2000',
            'laporan_kinerja' => 'required|string|max:2000',
            'bukti_hasil'     => 'nullable|url|max:1000',
            'capaian_hasil'   => 'required|integer|min:0|max:100',
        ]);

        // Hanya boleh mengisi/edit catatan untuk hari ini
        if ($validated['tanggal'] !== now()->toDateString()) {
            return redirect()->route('pic.laporan-harian.index')
                ->with('error', 'Catatan hanya bisa diisi atau diedit pada hari yang sama.');
        }

        $validated['pic_id'] = $picId;

        LaporanHarian::updateOrCreate(
            ['pic_id' => $picId, 'tanggal' => $validated['tanggal']],
            $validated
        );

        return redirect()->route('pic.laporan-harian.index')
            ->with('success', 'Catatan kinerja harian berhasil disimpan!');
    }
}

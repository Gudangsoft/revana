<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\LaporanHarianLog;
use Illuminate\Http\Request;

class LaporanHarianController extends Controller
{
    public function index()
    {
        $picId        = auth()->guard('pic')->id();
        $today        = now()->toDateString();
        $todayEntries = LaporanHarian::where('pic_id', $picId)->where('tanggal', $today)->orderBy('id')->get();
        $laporan      = LaporanHarian::where('pic_id', $picId)->orderByDesc('tanggal')->orderBy('id')->paginate(20);

        return view('pic.laporan-harian.index', compact('laporan', 'todayEntries', 'today'));
    }

    public function store(Request $request)
    {
        $picUser = auth()->guard('pic')->user();
        $picId   = $picUser->id;

        $validated = $request->validate([
            'tanggal'          => 'required|date',
            'judul_kegiatan'   => 'nullable|string|max:300',
            'target_kerja'     => 'required|string|max:2000',
            'laporan_kinerja'  => 'required|string|max:2000',
            'bukti_hasil'      => 'nullable|url|max:1000',
            'capaian_hasil'    => 'required|integer|min:0|max:100',
        ]);

        if ($validated['tanggal'] !== now()->toDateString()) {
            return redirect()->route('pic.laporan-harian.index')
                ->with('error', 'Catatan hanya bisa diisi pada hari yang sama.');
        }

        $validated['pic_id'] = $picId;

        $laporan = LaporanHarian::create($validated);

        LaporanHarianLog::record($laporan, 'pic', $picId, $picUser->name, 'created');

        return redirect()->route('pic.laporan-harian.index')
            ->with('success', 'Catatan kegiatan berhasil ditambahkan!');
    }

    public function edit(LaporanHarian $laporanHarian)
    {
        $picId = auth()->guard('pic')->id();

        // Hanya bisa edit milik sendiri dan hanya hari ini
        if ($laporanHarian->pic_id !== $picId) {
            abort(403);
        }
        if ($laporanHarian->tanggal->toDateString() !== now()->toDateString()) {
            return redirect()->route('pic.laporan-harian.index')
                ->with('error', 'Catatan hanya bisa diedit pada hari yang sama.');
        }

        return view('pic.laporan-harian.edit', compact('laporanHarian'));
    }

    public function update(Request $request, LaporanHarian $laporanHarian)
    {
        $picUser = auth()->guard('pic')->user();
        $picId   = $picUser->id;

        if ($laporanHarian->pic_id !== $picId) {
            abort(403);
        }
        if ($laporanHarian->tanggal->toDateString() !== now()->toDateString()) {
            return redirect()->route('pic.laporan-harian.index')
                ->with('error', 'Catatan hanya bisa diedit pada hari yang sama.');
        }

        $validated = $request->validate([
            'judul_kegiatan'  => 'nullable|string|max:300',
            'target_kerja'    => 'required|string|max:2000',
            'laporan_kinerja' => 'required|string|max:2000',
            'bukti_hasil'     => 'nullable|url|max:1000',
            'capaian_hasil'   => 'required|integer|min:0|max:100',
        ]);

        $trackFields = ['judul_kegiatan', 'target_kerja', 'laporan_kinerja', 'bukti_hasil', 'capaian_hasil'];
        $changes = [];
        foreach ($trackFields as $field) {
            $old = $laporanHarian->{$field};
            $new = $validated[$field] ?? null;
            if ((string) $old !== (string) $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        $laporanHarian->update($validated);

        if (!empty($changes)) {
            LaporanHarianLog::record($laporanHarian, 'pic', $picId, $picUser->name, 'updated', $changes);
        }

        return redirect()->route('pic.laporan-harian.index')
            ->with('success', 'Catatan kegiatan berhasil diperbarui!');
    }

    public function destroy(LaporanHarian $laporanHarian)
    {
        $picId = auth()->guard('pic')->id();

        if ($laporanHarian->pic_id !== $picId) {
            abort(403);
        }
        if ($laporanHarian->tanggal->toDateString() !== now()->toDateString()) {
            return redirect()->route('pic.laporan-harian.index')
                ->with('error', 'Catatan hanya bisa dihapus pada hari yang sama.');
        }

        $laporanHarian->delete();

        return redirect()->route('pic.laporan-harian.index')
            ->with('success', 'Catatan kegiatan berhasil dihapus.');
    }
}

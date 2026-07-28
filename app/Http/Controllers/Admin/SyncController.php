<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalSlot;
use App\Models\Marketing;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SyncController extends Controller
{
    /**
     * Himpun statistik sinkronisasi untuk semua modul.
     */
    private function gatherStats(): array
    {
        // --- Slot Jurnal ---
        $slots = JournalSlot::withCount([
            'submissions as actual_used' => fn($q) => $q->where('status', '!=', 'REJECTED'),
        ])->get();

        $slotOutOfSync = $slots->filter(fn($s) => $s->slot_terpakai !== $s->actual_used)->count();
        $totalSlots    = $slots->count();

        // --- Marketing Points --- (SUM riwayat, bukan COUNT submission — rate poin per
        // submission bisa berubah dari waktu ke waktu, lihat TaskPointSetting)
        $marketingActuals = \App\Models\MarketingPointHistory::selectRaw('marketing_id, SUM(points_earned) as total')
            ->groupBy('marketing_id')
            ->pluck('total', 'marketing_id');

        $marketings = Marketing::select('id', 'total_points')->get()->map(function ($m) use ($marketingActuals) {
            $m->actual_points = (float) ($marketingActuals[$m->id] ?? 0);
            return $m;
        });
        $marketingOutOfSync = $marketings->filter(fn($m) => round((float) $m->total_points, 4) !== round((float) $m->actual_points, 4))->count();
        $totalMarketings    = $marketings->count();

        // --- PIC Points --- (single aggregated query instead of N per-PIC queries)
        $picActuals = PicPointHistory::selectRaw('pic_id, SUM(points_earned) as total')
            ->groupBy('pic_id')
            ->pluck('total', 'pic_id');

        $pics = Pic::select('id', 'total_points')->get()->map(function ($pic) use ($picActuals) {
            $pic->actual_points = (float) ($picActuals[$pic->id] ?? 0);
            return $pic;
        });
        $picOutOfSync = $pics->filter(fn($p) => round((float) $p->total_points, 4) !== round((float) $p->actual_points, 4))->count();
        $totalPics    = $pics->count();

        return [
            'slots' => [
                'total'       => $totalSlots,
                'out_of_sync' => $slotOutOfSync,
                'synced'      => $totalSlots - $slotOutOfSync,
            ],
            'marketing' => [
                'total'       => $totalMarketings,
                'out_of_sync' => $marketingOutOfSync,
                'synced'      => $totalMarketings - $marketingOutOfSync,
            ],
            'pic' => [
                'total'       => $totalPics,
                'out_of_sync' => $picOutOfSync,
                'synced'      => $totalPics - $picOutOfSync,
            ],
        ];
    }

    /**
     * Tampilkan halaman sinkronisasi.
     */
    public function index()
    {
        $stats = $this->gatherStats();
        $totalOutOfSync = $stats['slots']['out_of_sync']
            + $stats['marketing']['out_of_sync']
            + $stats['pic']['out_of_sync'];

        return view('admin.sync.index', compact('stats', 'totalOutOfSync'));
    }

    /**
     * Sinkronisasi hanya slot jurnal.
     */
    public function syncSlots()
    {
        $count = JournalSlot::recalculateAll();
        self::clearSyncCache();
        return back()->with('success', "✅ Sinkronisasi slot berhasil. {$count} slot jurnal telah diperbarui berdasarkan data submission aktual.");
    }

    /**
     * Sinkronisasi point PIC & Marketing sekaligus — satu-satunya tombol sinkronisasi
     * point di admin panel (konsolidasi dari 7 tombol yang sebelumnya tersebar di
     * /admin/pic-points, /admin/marketing-points, /admin/reports/team-performance, dan
     * halaman ini sendiri — semuanya melakukan hal serupa dengan tingkat kelengkapan
     * yang tidak konsisten). Memakai logika PALING lengkap yang sudah ada: backfill
     * riwayat hilang, perbaiki tanggal yang tidak cocok dengan tanggal validasi asli
     * (lihat insiden 28 Juli 2026), hitung ulang total_points, dan hapus riwayat orphan.
     */
    public function syncPoints()
    {
        [$picBackfilled, $picRepaired, $picSynced, $picOrphans] = PicPointReportController::runFullSync();
        [$mktCreated, $mktSynced] = MarketingPointReportController::runFullSync();

        self::clearSyncCache();

        $msg = "✅ Sinkronisasi point selesai. PIC: {$picBackfilled} riwayat baru, {$picRepaired} tanggal dikoreksi, {$picSynced} PIC diperbarui";
        if ($picOrphans > 0) {
            $msg .= ", {$picOrphans} riwayat orphan dihapus";
        }
        $msg .= ". Marketing: {$mktCreated} riwayat baru, {$mktSynced} marketing diperbarui.";

        return back()->with('success', $msg);
    }

    /**
     * Sinkronisasi semua data sekaligus (slot + point PIC & Marketing, versi lengkap).
     */
    public function syncAll()
    {
        $slotCount = JournalSlot::recalculateAll();
        [$picBackfilled, $picRepaired, $picSynced, $picOrphans] = PicPointReportController::runFullSync();
        [$mktCreated, $mktSynced] = MarketingPointReportController::runFullSync();

        self::clearSyncCache();

        $msg = "✅ Sinkronisasi semua data berhasil! {$slotCount} slot jurnal diperbarui. "
            . "PIC: {$picBackfilled} riwayat baru, {$picRepaired} tanggal dikoreksi, {$picSynced} PIC diperbarui";
        if ($picOrphans > 0) {
            $msg .= ", {$picOrphans} riwayat orphan dihapus";
        }
        $msg .= ". Marketing: {$mktCreated} riwayat baru, {$mktSynced} marketing diperbarui.";

        return back()->with('success', $msg);
    }

    /**
     * Cek apakah ada data yang tidak sinkron (digunakan sidebar — hasil di-cache 5 menit).
     */
    public static function countOutOfSync(): int
    {
        return Cache::remember('sync.out_of_sync_count', 300, function () {
            // Slots — single withCount query
            $slotOutOfSync = JournalSlot::withCount([
                'submissions as actual_used' => fn($q) => $q->where('status', '!=', 'REJECTED'),
            ])->get()->filter(fn($s) => $s->slot_terpakai !== $s->actual_used)->count();

            // Marketing — single aggregated query, SUM riwayat (bukan COUNT submission)
            $marketingActuals = \App\Models\MarketingPointHistory::selectRaw('marketing_id, SUM(points_earned) as total')
                ->groupBy('marketing_id')
                ->pluck('total', 'marketing_id');

            $marketingOutOfSync = Marketing::select('id', 'total_points')->get()->filter(function ($m) use ($marketingActuals) {
                $actual = (float) ($marketingActuals[$m->id] ?? 0);
                return round((float) $m->total_points, 4) !== round($actual, 4);
            })->count();

            // PIC — single aggregated query instead of N per-PIC queries
            $picActuals = PicPointHistory::selectRaw('pic_id, SUM(points_earned) as total')
                ->groupBy('pic_id')
                ->pluck('total', 'pic_id');

            $picOutOfSync = Pic::select('id', 'total_points')->get()->filter(function ($pic) use ($picActuals) {
                $actual = (float) ($picActuals[$pic->id] ?? 0);
                return round((float) $pic->total_points, 4) !== round($actual, 4);
            })->count();

            return $slotOutOfSync + $marketingOutOfSync + $picOutOfSync;
        });
    }

    /** Bersihkan cache sinkronisasi. */
    private static function clearSyncCache(): void
    {
        Cache::forget('sync.out_of_sync_count');
    }
}

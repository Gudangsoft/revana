<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalSlot;
use App\Models\Marketing;
use App\Models\Pic;
use App\Models\PicPointHistory;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // --- Marketing Points ---
        $marketings         = Marketing::withCount([
            'submissions as actual_points' => fn($q) => $q->where('status', '!=', 'REJECTED'),
        ])->get();
        // Use != (loose) — total_points is cast as float, actual_points is int from withCount
        $marketingOutOfSync = $marketings->filter(fn($m) => (int) round($m->total_points) !== (int) $m->actual_points)->count();
        $totalMarketings    = $marketings->count();

        // --- PIC Points ---
        $pics = Pic::all()->map(function ($pic) {
            $actual = (float) PicPointHistory::where('pic_id', $pic->id)->sum('points_earned');
            $pic->actual_points = $actual;
            return $pic;
        });
        // round() handles float imprecision; compare as float with 2 decimal precision
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
        return back()->with('success', "✅ Sinkronisasi slot berhasil. {$count} slot jurnal telah diperbarui berdasarkan data submission aktual.");
    }

    /**
     * Sinkronisasi total_points semua marketing.
     */
    public function syncMarketingPoints()
    {
        $marketings = Marketing::all();
        $updated    = 0;

        foreach ($marketings as $marketing) {
            $actual = $marketing->submissions()->where('status', '!=', 'REJECTED')->count();
            // Use loose comparison — total_points is float, $actual is int
            if ((int) round($marketing->total_points) !== $actual) {
                $marketing->update(['total_points' => $actual]);
                $updated++;
            }
        }

        return back()->with('success', "✅ Sinkronisasi point marketing berhasil. {$updated} dari {$marketings->count()} marketing diperbarui.");
    }

    /**
     * Sinkronisasi total_points semua PIC dari riwayat point mereka.
     */
    public function syncPicPoints()
    {
        $pics    = Pic::all();
        $updated = 0;

        foreach ($pics as $pic) {
            $actual = (float) PicPointHistory::where('pic_id', $pic->id)->sum('points_earned');
            // round() to 4 decimal places avoids float imprecision false positives
            if (round((float) $pic->total_points, 4) !== round($actual, 4)) {
                $pic->update(['total_points' => $actual]);
                $updated++;
            }
        }

        return back()->with('success', "✅ Sinkronisasi point PIC berhasil. {$updated} dari {$pics->count()} PIC diperbarui.");
    }

    /**
     * Sinkronisasi semua data sekaligus.
     */
    public function syncAll()
    {
        DB::transaction(function () {
            // Slots
            JournalSlot::recalculateAll();

            // Marketing points — 1 point per non-rejected submission
            foreach (Marketing::all() as $m) {
                $actual = $m->submissions()->where('status', '!=', 'REJECTED')->count();
                $m->update(['total_points' => $actual]);
            }

            // PIC points — rebuild total_points from pic_point_histories sum.
            // This also corrects any double-counted values from historical bugs.
            foreach (Pic::all() as $pic) {
                $actual = (float) PicPointHistory::where('pic_id', $pic->id)->sum('points_earned');
                $pic->update(['total_points' => $actual]);
            }
        });

        return back()->with('success', '✅ Sinkronisasi semua data berhasil! Slot jurnal, point marketing, dan point PIC telah diperbarui.');
    }

    /**
     * Cek apakah ada data yang tidak sinkron (digunakan oleh login hook).
     * Mengembalikan jumlah total item yang tidak sinkron.
     */
    public static function countOutOfSync(): int
    {
        $slotOutOfSync = JournalSlot::withCount([
            'submissions as actual_used' => fn($q) => $q->where('status', '!=', 'REJECTED'),
        ])->get()->filter(fn($s) => $s->slot_terpakai !== $s->actual_used)->count();

        $marketingOutOfSync = Marketing::withCount([
            'submissions as actual_points' => fn($q) => $q->where('status', '!=', 'REJECTED'),
        ])->get()->filter(fn($m) => (int) round($m->total_points) !== (int) $m->actual_points)->count();

        $picOutOfSync = Pic::all()->filter(function ($pic) {
            $actual = (float) PicPointHistory::where('pic_id', $pic->id)->sum('points_earned');
            return round((float) $pic->total_points, 4) !== round($actual, 4);
        })->count();

        return $slotOutOfSync + $marketingOutOfSync + $picOutOfSync;
    }
}

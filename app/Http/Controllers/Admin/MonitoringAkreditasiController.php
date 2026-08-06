<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use Carbon\Carbon;

class MonitoringAkreditasiController extends Controller
{
    /** Berapa bulan sebelum kedaluwarsa jurnal mulai ditandai "Perlu Bersiap". */
    private const WARNING_MONTHS_AHEAD = 12;

    // ── Monitoring masa berlaku akreditasi semua jurnal — supaya tim bisa mulai
    //    siapkan dokumen reakreditasi jauh-jauh hari sebelum kedaluwarsa ──────────
    public function index()
    {
        $today     = Carbon::today();
        $threshold = $today->copy()->addMonths(self::WARNING_MONTHS_AHEAD);

        $journals = JournalMaster::where('is_active', true)
            ->whereNotNull('accreditation')
            ->where('accreditation', '!=', '')
            ->orderBy('nama_jurnal')
            ->get()
            ->map(function ($journal) use ($today, $threshold) {
                $expiresAt = $journal->accreditation_expires_at;

                if (!$expiresAt) {
                    $status = 'unknown';
                    $monthsLeft = null;
                } elseif ($expiresAt->lt($today)) {
                    $status = 'expired';
                    $monthsLeft = $today->diffInMonths($expiresAt) * -1;
                } elseif ($expiresAt->lte($threshold)) {
                    $status = 'warning';
                    $monthsLeft = $today->diffInMonths($expiresAt);
                } else {
                    $status = 'safe';
                    $monthsLeft = $today->diffInMonths($expiresAt);
                }

                return [
                    'journal'    => $journal,
                    'expiresAt'  => $expiresAt,
                    'monthsLeft' => $monthsLeft,
                    'status'     => $status,
                ];
            });

        // Urutan prioritas: kedaluwarsa dulu, lalu perlu bersiap (paling dekat
        // duluan), lalu belum diisi, baru yang aman. Sort key numerik tunggal
        // (bukan array) supaya perbandingannya jelas, tidak bergantung pada
        // perilaku perbandingan array PHP.
        $statusOrder = ['expired' => 0, 'warning' => 1, 'unknown' => 2, 'safe' => 3];
        $journals = $journals->sortBy(function ($row) use ($statusOrder) {
            $timestamp = $row['expiresAt']?->timestamp ?? PHP_INT_MAX;
            return $statusOrder[$row['status']] * 10_000_000_000 + $timestamp;
        })->values();

        $stats = [
            'expired' => $journals->where('status', 'expired')->count(),
            'warning' => $journals->where('status', 'warning')->count(),
            'unknown' => $journals->where('status', 'unknown')->count(),
            'safe'    => $journals->where('status', 'safe')->count(),
        ];

        return view('admin.monitoring-akreditasi.index', compact('journals', 'stats'));
    }
}

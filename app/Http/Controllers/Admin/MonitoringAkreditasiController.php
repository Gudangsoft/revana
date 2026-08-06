<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalMaster;
use Carbon\Carbon;

class MonitoringAkreditasiController extends Controller
{
    /**
     * Ambang "Perlu Bersiap" dalam tahun, bukan bulan — data akreditasi jurnal
     * cuma tercatat sampai tingkat Tahun (mengikuti format periode "Volume X
     * Nomor Y Tahun Z" yang sesuai SK, lihat migration 2026_08_01_000004_*),
     * jadi presisi bulan tidak tersedia dan tidak boleh dipura-purakan ada.
     * Nilai 1 = jurnal ditandai "Perlu Bersiap" begitu tahun sekarang sudah masuk
     * tahun akhir periode ATAU satu tahun sebelumnya (setara ambang ~12 bulan
     * yang diminta user, dengan presisi tahunan).
     */
    private const WARNING_YEARS_AHEAD = 1;

    // ── Monitoring masa berlaku akreditasi semua jurnal — supaya tim bisa mulai
    //    siapkan dokumen reakreditasi jauh-jauh hari sebelum kedaluwarsa ──────────
    public function index()
    {
        $currentYear = Carbon::today()->year;

        $journals = JournalMaster::where('is_active', true)
            ->whereNotNull('accreditation')
            ->where('accreditation', '!=', '')
            ->orderBy('nama_jurnal')
            ->get()
            ->map(function ($journal) use ($currentYear) {
                $tahun = $journal->accreditation_end_tahun;

                if (!$tahun) {
                    $status = 'unknown';
                } elseif ($tahun < $currentYear) {
                    $status = 'expired';
                } elseif ($tahun <= $currentYear + self::WARNING_YEARS_AHEAD) {
                    $status = 'warning';
                } else {
                    $status = 'safe';
                }

                return [
                    'journal'   => $journal,
                    'periode'   => $journal->accreditation_periode,
                    'tahun'     => $tahun,
                    'yearsLeft' => $tahun ? $tahun - $currentYear : null,
                    'status'    => $status,
                ];
            });

        // Urutan prioritas: kedaluwarsa dulu, lalu perlu bersiap (tahun akhir
        // paling dekat duluan), lalu belum diisi, baru yang aman. Sort key
        // numerik tunggal (bukan array) supaya perbandingannya jelas.
        $statusOrder = ['expired' => 0, 'warning' => 1, 'unknown' => 2, 'safe' => 3];
        $journals = $journals->sortBy(function ($row) use ($statusOrder) {
            $tahunKey = $row['tahun'] ?? PHP_INT_MAX;
            return $statusOrder[$row['status']] * 100_000 + $tahunKey;
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

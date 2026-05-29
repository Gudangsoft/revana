<?php

namespace App\Imports;

use App\Models\ReferensiJurnal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ReferensiJurnalImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $imported = 0;
    protected int $updated  = 0;

    public function model(array $row)
    {
        $namaJurnal  = trim($row['nama_jurnal']  ?? $row['nama']   ?? '');
        $jenisJurnal = trim($row['jenis_jurnal'] ?? $row['jenis']  ?? '');
        $bidangIlmu  = trim($row['bidang_ilmu']  ?? $row['bidang'] ?? '');
        $tahun       = $row['tahun']     ?? null;
        $referensi   = trim($row['referensi']    ?? '');
        $kutipan     = trim($row['kutipan']      ?? '');

        // Lewati baris kosong
        if (empty($namaJurnal) && empty($referensi)) {
            return null;
        }

        // Kunci unik: teks referensi (setiap artikel punya referensi berbeda)
        // Fallback: nama_jurnal + 40 karakter pertama referensi
        $refKey = $referensi ?: $namaJurnal;

        $existing = ReferensiJurnal::where('referensi', $referensi)
            ->when(!$referensi, fn($q) => $q->where('nama_jurnal', $namaJurnal)->where('tahun', (int) $tahun))
            ->first();

        if ($existing) {
            // Replace penuh — semua field ditimpa dengan data dari Excel
            $existing->update([
                'nama_jurnal'  => $namaJurnal,
                'jenis_jurnal' => $jenisJurnal,
                'bidang_ilmu'  => $bidangIlmu,
                'tahun'        => $tahun ? (int) $tahun : $existing->tahun,
                'referensi'    => $referensi,
                'kutipan'      => $kutipan ?: null,
            ]);
            $this->updated++;
            return null;
        }

        $this->imported++;
        return new ReferensiJurnal([
            'nama_jurnal'  => $namaJurnal,
            'jenis_jurnal' => $jenisJurnal,
            'bidang_ilmu'  => $bidangIlmu,
            'tahun'        => $tahun ? (int) $tahun : null,
            'referensi'    => $referensi,
            'kutipan'      => $kutipan ?: null,
        ]);
    }

    public function rules(): array
    {
        return [];
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getUpdatedCount(): int  { return $this->updated; }
}

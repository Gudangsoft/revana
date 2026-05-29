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
        $namaJurnal  = $row['nama_jurnal']  ?? $row['nama']     ?? null;
        $jenisJurnal = $row['jenis_jurnal'] ?? $row['jenis']    ?? null;
        $bidangIlmu  = $row['bidang_ilmu']  ?? $row['bidang']   ?? null;
        $tahun       = $row['tahun']                             ?? null;
        $referensi   = $row['referensi']                         ?? null;
        $kutipan     = $row['kutipan']                           ?? null;

        if (empty($namaJurnal)) {
            return null;
        }

        $existing = ReferensiJurnal::where('nama_jurnal', $namaJurnal)
            ->where('tahun', (int) $tahun)
            ->first();

        if ($existing) {
            $existing->update([
                'jenis_jurnal' => $jenisJurnal ?? $existing->jenis_jurnal,
                'bidang_ilmu'  => $bidangIlmu  ?? $existing->bidang_ilmu,
                'referensi'    => $referensi   ?? $existing->referensi,
                'kutipan'      => $kutipan      ?? $existing->kutipan,
            ]);
            $this->updated++;
            return null;
        }

        $this->imported++;
        return new ReferensiJurnal([
            'nama_jurnal'  => $namaJurnal,
            'jenis_jurnal' => $jenisJurnal,
            'bidang_ilmu'  => $bidangIlmu,
            'tahun'        => (int) $tahun,
            'referensi'    => $referensi,
            'kutipan'      => $kutipan,
        ]);
    }

    public function rules(): array
    {
        return [];
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getUpdatedCount(): int  { return $this->updated; }
}

<?php

namespace App\Imports;

use App\Models\ReferensiJurnal;
use App\Services\CitationGenerator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ReferensiJurnalImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $imported = 0;
    protected int $updated  = 0;

    public function model(array $row)
    {
        $namaJurnal   = trim($row['nama_jurnal']   ?? $row['nama']   ?? '');
        $jenisJurnal  = trim($row['jenis_jurnal']  ?? $row['jenis']  ?? '');
        $bidangIlmu   = trim($row['bidang_ilmu']   ?? $row['bidang'] ?? '');
        $tahun        = $row['tahun']              ?? null;
        $referensi    = trim($row['referensi']     ?? '');
        $kutipan      = trim($row['kutipan']       ?? '');

        // Metadata artikel (untuk auto-generate format sitasi)
        $penulis      = trim($row['penulis']       ?? '');
        $judulArtikel = trim($row['judul_artikel'] ?? $row['judul']  ?? '');
        $volume       = trim($row['volume']        ?? $row['vol']    ?? '');
        $nomor        = trim($row['nomor']         ?? $row['no']     ?? $row['issue'] ?? '');
        $halaman      = trim($row['halaman']       ?? $row['hal']    ?? $row['pages'] ?? '');
        $doi          = trim($row['doi']           ?? '');

        if (empty($namaJurnal) && empty($referensi) && empty($judulArtikel)) {
            return null;
        }

        // Auto-generate semua format sitasi dari metadata
        $formatSitasi = null;
        if ($penulis || $judulArtikel) {
            $generated = CitationGenerator::generate([
                'penulis'       => $penulis,
                'judul_artikel' => $judulArtikel,
                'nama_jurnal'   => $namaJurnal,
                'tahun'         => $tahun,
                'volume'        => $volume,
                'nomor'         => $nomor,
                'halaman'       => $halaman,
                'doi'           => $doi,
            ]);
            if ($generated) {
                $formatSitasi = json_encode($generated, JSON_UNESCAPED_UNICODE);
            }
        }

        $fields = [
            'nama_jurnal'   => $namaJurnal,
            'jenis_jurnal'  => $jenisJurnal,
            'bidang_ilmu'   => $bidangIlmu,
            'tahun'         => $tahun ? (int) $tahun : null,
            'referensi'     => $referensi,
            'kutipan'       => $kutipan ?: null,
            'penulis'       => $penulis       ?: null,
            'judul_artikel' => $judulArtikel  ?: null,
            'volume'        => $volume        ?: null,
            'nomor'         => $nomor         ?: null,
            'halaman'       => $halaman       ?: null,
            'doi'           => $doi           ?: null,
            'format_sitasi' => $formatSitasi,
        ];

        // Kunci unik: teks referensi atau judul_artikel+penulis
        $existing = null;
        if ($referensi) {
            $existing = ReferensiJurnal::where('referensi', $referensi)->first();
        } elseif ($judulArtikel && $penulis) {
            $existing = ReferensiJurnal::where('judul_artikel', $judulArtikel)
                ->where('penulis', $penulis)->first();
        }

        if ($existing) {
            $existing->update($fields);
            $this->updated++;
            return null;
        }

        $this->imported++;
        return new ReferensiJurnal($fields);
    }

    public function rules(): array { return []; }

    public function getImportedCount(): int { return $this->imported; }
    public function getUpdatedCount(): int  { return $this->updated; }
}

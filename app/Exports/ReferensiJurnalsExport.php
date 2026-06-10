<?php

namespace App\Exports;

use App\Models\ReferensiJurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReferensiJurnalsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected array $filters;
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = ReferensiJurnal::query();

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nama_jurnal',   'like', "%{$s}%")
                  ->orWhere('jenis_jurnal', 'like', "%{$s}%")
                  ->orWhere('bidang_ilmu',  'like', "%{$s}%")
                  ->orWhere('referensi',    'like', "%{$s}%")
                  ->orWhere('kutipan',      'like', "%{$s}%");
            });
        }

        if (!empty($this->filters['jenis_jurnal'])) {
            $query->where('jenis_jurnal', $this->filters['jenis_jurnal']);
        }

        if (!empty($this->filters['bidang_ilmu'])) {
            $query->where('bidang_ilmu', $this->filters['bidang_ilmu']);
        }

        if (!empty($this->filters['tahun'])) {
            $query->where('tahun', $this->filters['tahun']);
        }

        return $query->orderBy('nama_jurnal')->get();
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Jurnal', 'Jenis Jurnal', 'Bidang Ilmu', 'Tahun',
            'Penulis', 'Judul Artikel', 'Volume', 'Nomor', 'Halaman', 'DOI', 'Referensi',
        ];
    }

    public function map($ref): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $ref->nama_jurnal    ?? '-',
            $ref->jenis_jurnal   ?? '-',
            $ref->bidang_ilmu    ?? '-',
            $ref->tahun          ?? '-',
            $ref->penulis        ?? '-',
            $ref->judul_artikel  ?? '-',
            $ref->volume         ?? '-',
            $ref->nomor          ?? '-',
            $ref->halaman        ?? '-',
            $ref->doi            ?? '-',
            $ref->referensi      ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 35,
            'C' => 20,
            'D' => 20,
            'E' => 8,
            'F' => 30,
            'G' => 40,
            'H' => 8,
            'I' => 8,
            'J' => 12,
            'K' => 30,
            'L' => 50,
        ];
    }
}

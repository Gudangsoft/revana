<?php

namespace App\Exports;

use App\Models\JenisJurnal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JenisJurnalsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected int $rowNumber = 0;

    public function collection()
    {
        return JenisJurnal::orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Jenis Jurnal', 'Deskripsi', 'Status'];
    }

    public function map($jenis): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $jenis->name ?? '-',
            $jenis->description ?? '-',
            $jenis->is_active ? 'Aktif' : 'Nonaktif',
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
        return ['A' => 5, 'B' => 35, 'C' => 50, 'D' => 12];
    }
}

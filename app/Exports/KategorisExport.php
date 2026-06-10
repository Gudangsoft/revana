<?php

namespace App\Exports;

use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KategorisExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected int $rowNumber = 0;

    public function collection()
    {
        return Kategori::orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Kategori', 'Deskripsi', 'Status'];
    }

    public function map($kategori): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $kategori->name ?? '-',
            $kategori->description ?? '-',
            $kategori->is_active ? 'Aktif' : 'Nonaktif',
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

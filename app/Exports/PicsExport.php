<?php

namespace App\Exports;

use App\Models\Pic;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PicsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return Pic::orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Username',
            'Email',
            'Telepon',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    public function map($pic): array
    {
        return [
            $pic->id,
            $pic->name,
            $pic->username ?? '',
            $pic->email ?? '',
            $pic->phone ?? '',
            $pic->is_active ? 'Aktif' : 'Nonaktif',
            $pic->created_at ? $pic->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0D6EFD'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 30,
            'C' => 20,
            'D' => 30,
            'E' => 18,
            'F' => 12,
            'G' => 20,
        ];
    }
}

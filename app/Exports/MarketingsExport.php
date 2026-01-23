<?php

namespace App\Exports;

use App\Models\Marketing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarketingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Marketing::orderBy('name')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Email',
            'Telepon',
            'Total Points',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    /**
     * @param Marketing $marketing
     * @return array
     */
    public function map($marketing): array
    {
        return [
            $marketing->id,
            $marketing->name,
            $marketing->email ?? '-',
            $marketing->phone ?? '-',
            $marketing->total_points ?? 0,
            $marketing->is_active ? 'Aktif' : 'Nonaktif',
            $marketing->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

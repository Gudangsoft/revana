<?php

namespace App\Exports;

use App\Models\FieldOfStudy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FieldOfStudiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected int $rowNumber = 0;

    public function collection()
    {
        return FieldOfStudy::withCount(['users', 'reviewerRegistrations'])->ordered()->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Bidang Ilmu', 'Deskripsi', 'Urutan', 'Reviewer', 'Pendaftar', 'Status'];
    }

    public function map($field): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $field->name ?? '-',
            $field->description ?? '-',
            $field->order ?? 0,
            $field->users_count ?? 0,
            $field->reviewer_registrations_count ?? 0,
            $field->is_active ? 'Aktif' : 'Nonaktif',
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
            'C' => 45,
            'D' => 10,
            'E' => 12,
            'F' => 12,
            'G' => 12,
        ];
    }
}

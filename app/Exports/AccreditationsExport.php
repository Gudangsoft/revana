<?php

namespace App\Exports;

use App\Models\Accreditation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccreditationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = Accreditation::withCount('journals');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('points', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Akreditasi',
            'Points',
            'Deskripsi',
            'Jumlah Jurnal',
            'Status',
            'Dibuat',
            'Diperbarui',
        ];
    }

    public function map($accreditation): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $accreditation->name ?? '-',
            $accreditation->points ?? 0,
            $accreditation->description ?? '-',
            $accreditation->journals_count ?? 0,
            $accreditation->is_active ? 'Aktif' : 'Nonaktif',
            $accreditation->created_at ? $accreditation->created_at->format('d/m/Y H:i') : '-',
            $accreditation->updated_at ? $accreditation->updated_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text (header)
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 25,  // Nama Akreditasi
            'C' => 10,  // Points
            'D' => 40,  // Deskripsi
            'E' => 15,  // Jumlah Jurnal
            'F' => 12,  // Status
            'G' => 18,  // Dibuat
            'H' => 18,  // Diperbarui
        ];
    }
}

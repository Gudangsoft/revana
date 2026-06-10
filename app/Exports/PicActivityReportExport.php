<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PicActivityReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected Collection $pics;
    protected int $no = 0;

    public function __construct(Collection $pics)
    {
        $this->pics = $pics;
    }

    public function collection(): Collection
    {
        return $this->pics;
    }

    public function headings(): array
    {
        return ['No', 'Nama PIC', 'Email', 'Status', 'Total Point', 'Tugas Selesai', 'Breakdown Per Pekerjaan'];
    }

    public function map($pic): array
    {
        $this->no++;

        $breakdown = $pic->step_breakdown->map(function($step) {
            $label = \App\Models\PicPointHistory::getLabelForStep($step->step);
            return "{$label}: {$step->total}pt ({$step->count}x)";
        })->join(', ');

        return [
            $this->no,
            $pic->name,
            $pic->email ?? '-',
            $pic->is_active ? 'Aktif' : 'Tidak Aktif',
            $pic->filtered_points,
            $pic->filtered_tasks,
            $breakdown ?: '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 25,
            'C' => 30,
            'D' => 12,
            'E' => 14,
            'F' => 14,
            'G' => 60,
        ];
    }
}

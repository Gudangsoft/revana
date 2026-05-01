<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanKinerjaPicSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected $picRekap,
        protected array $steps,
        protected string $namaBulan
    ) {}

    public function title(): string
    {
        return 'Rekap PIC';
    }

    public function headings(): array
    {
        $headers = ['No', 'Nama PIC', 'Role'];
        foreach ($this->steps as $label) {
            $headers[] = $label;
        }
        $headers[] = 'Total Tugas';
        $headers[] = 'Total Poin';
        return $headers;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->picRekap as $i => $row) {
            $r = [
                $i + 1,
                $row['pic']->name,
                ucfirst($row['pic']->role ?? '-'),
            ];
            foreach ($this->steps as $key => $label) {
                $r[] = $row['step_counts'][$key] ?: 0;
            }
            $r[] = $row['total_tugas'];
            $r[] = $row['total_poin'];
            $rows[] = $r;
        }

        // Total row
        $totals = ['', 'TOTAL', ''];
        foreach ($this->steps as $key => $label) {
            $totals[] = $this->picRekap->sum(fn($r) => $r['step_counts'][$key]);
        }
        $totals[] = $this->picRekap->sum('total_tugas');
        $totals[] = $this->picRekap->sum('total_poin');
        $rows[] = $totals;

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->picRekap->count() + 2; // +1 heading +1 total
        $lastCol = 3 + count($this->steps) + 2;
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);

        // Title row above headings
        $sheet->insertNewRowBefore(1, 1);
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'REKAP KINERJA PIC — ' . strtoupper($this->namaBulan));

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 13],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1d4ed8']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13],
            ],
            2 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e293b']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
            $lastRow + 1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e2e8f0']],
            ],
        ];
    }
}

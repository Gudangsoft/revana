<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanKinerjaMarketingSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected $mktRekap,
        protected string $namaBulan
    ) {}

    public function title(): string
    {
        return 'Rekap Marketing';
    }

    public function headings(): array
    {
        return ['No', 'Nama Marketing', 'Total Submit', 'Total Poin'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->mktRekap as $i => $row) {
            $rows[] = [
                $i + 1,
                $row['marketing']->name,
                $row['total_submit'],
                $row['total_poin'],
            ];
        }

        $rows[] = [
            '',
            'TOTAL',
            $this->mktRekap->sum('total_submit'),
            $this->mktRekap->sum('total_poin'),
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->mktRekap->count() + 2;

        $sheet->insertNewRowBefore(1, 1);
        $sheet->mergeCells("A1:D1");
        $sheet->setCellValue('A1', 'REKAP KINERJA MARKETING — ' . strtoupper($this->namaBulan));

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0891b2']],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e293b']],
            ],
            $lastRow + 1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e2e8f0']],
            ],
        ];
    }
}

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
use Illuminate\Support\Collection;

class LaporanHarianRekapExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected Collection $rekap,
        protected string $bulanLabel
    ) {}

    public function title(): string
    {
        return 'Rekap Harian';
    }

    public function headings(): array
    {
        return ['No', 'Nama PIC', 'Hari Aktif', 'Total Kegiatan', 'Rata-rata Capaian (%)', 'Tervalidasi', '% Validasi'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->rekap as $i => $row) {
            $pct = $row->total_kegiatan > 0
                ? round($row->total_validated / $row->total_kegiatan * 100)
                : 0;
            $rows[] = [
                $i + 1,
                $row->pic->name ?? '-',
                $row->total_hari,
                $row->total_kegiatan,
                $row->avg_capaian,
                $row->total_validated . ' / ' . $row->total_kegiatan,
                $pct,
            ];
        }

        // Total row
        $totalKegiatan  = $this->rekap->sum('total_kegiatan');
        $totalValidated = $this->rekap->sum('total_validated');
        $pctAll = $totalKegiatan > 0 ? round($totalValidated / $totalKegiatan * 100) : 0;

        $rows[] = [
            '',
            'TOTAL',
            $this->rekap->sum('total_hari'),
            $totalKegiatan,
            round($this->rekap->avg('avg_capaian') ?? 0),
            $totalValidated . ' / ' . $totalKegiatan,
            $pctAll,
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->rekap->count() + 2; // last data row before insert

        $sheet->insertNewRowBefore(1, 1);
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'REKAP CATATAN KINERJA HARIAN — ' . strtoupper($this->bulanLabel));

        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 13],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
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

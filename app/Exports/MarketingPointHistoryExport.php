<?php

namespace App\Exports;

use App\Models\Marketing;
use App\Models\MarketingPointHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarketingPointHistoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected Marketing $marketing;
    protected ?string $tanggalDari;
    protected ?string $tanggalSampai;

    public function __construct(Marketing $marketing, ?string $tanggalDari = null, ?string $tanggalSampai = null)
    {
        $this->marketing = $marketing;
        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function collection()
    {
        $query = $this->marketing->pointHistories()->with('submission');

        if ($this->tanggalDari) {
            $query->whereDate('created_at', '>=', $this->tanggalDari);
        }
        if ($this->tanggalSampai) {
            $query->whereDate('created_at', '<=', $this->tanggalSampai);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kode Submission',
            'Judul Artikel',
            'Deskripsi',
            'Point',
        ];
    }

    public function map($history): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $history->created_at->format('d/m/Y H:i'),
            $history->submission ? $history->submission->kode_submit : '-',
            $history->submission ? $history->submission->judul_artikel : '-',
            $history->description,
            $history->points_earned,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Point ' . $this->marketing->name;
    }
}

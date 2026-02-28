<?php

namespace App\Exports;

use App\Models\Pic;
use App\Models\PicPointHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PicPointHistoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected Pic $pic;
    protected ?string $tanggalDari;
    protected ?string $tanggalSampai;
    protected ?string $step;
    protected ?string $processType;

    public function __construct(Pic $pic, ?string $tanggalDari = null, ?string $tanggalSampai = null, ?string $step = null, ?string $processType = null)
    {
        $this->pic = $pic;
        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
        $this->step = $step;
        $this->processType = $processType;
    }

    public function collection()
    {
        $query = $this->pic->pointHistories()->with('submission');

        if ($this->tanggalDari) {
            $query->whereDate('created_at', '>=', $this->tanggalDari);
        }
        if ($this->tanggalSampai) {
            $query->whereDate('created_at', '<=', $this->tanggalSampai);
        }
        if ($this->step) {
            $query->where('step', $this->step);
        }
        if ($this->processType && $this->processType !== 'all') {
            $processType = $this->processType;
            $query->whereHas('submission', function($q) use ($processType) {
                if ($processType === 'normal') {
                    $q->where(function($qq) {
                        $qq->where('process_type', 'normal')
                           ->orWhereNull('process_type');
                    });
                } else {
                    $q->where('process_type', $processType);
                }
            });
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
            'Tugas',
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
            PicPointHistory::getLabelForStep($history->step),
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
        return 'Point ' . $this->pic->name;
    }
}

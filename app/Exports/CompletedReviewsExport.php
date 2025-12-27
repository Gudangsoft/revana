<?php

namespace App\Exports;

use App\Models\ReviewAssignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompletedReviewsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = ReviewAssignment::with(['journal', 'reviewer', 'reviewResult'])
            ->where('status', 'APPROVED');

        if ($this->startDate) {
            $query->whereDate('approved_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('approved_at', '<=', $this->endDate);
        }

        return $query->orderBy('approved_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Judul Jurnal',
            'Link Jurnal',
            'Akreditasi',
            'Points',
            'Terbitan',
            'Marketing',
            'PIC',
            'Nama Reviewer',
            'Email Reviewer',
            'Institusi',
            'Hasil Review',
            'Komentar Review',
            'Tanggal Ditugaskan',
            'Tanggal Diselesaikan',
            'Tanggal Disetujui',
        ];
    }

    public function map($assignment): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $assignment->journal->title,
            $assignment->journal->link,
            $assignment->journal->accreditation,
            $assignment->journal->points,
            $assignment->journal->publisher ?? '-',
            $assignment->journal->marketing ?? '-',
            $assignment->journal->pic ?? '-',
            $assignment->reviewer->name,
            $assignment->reviewer->email,
            $assignment->reviewer->institution ?? '-',
            $assignment->reviewResult ? $assignment->reviewResult->recommendation : '-',
            $assignment->reviewResult ? $assignment->reviewResult->comments : '-',
            $assignment->created_at->format('Y-m-d H:i:s'),
            $assignment->submitted_at ? $assignment->submitted_at->format('Y-m-d H:i:s') : '-',
            $assignment->approved_at ? $assignment->approved_at->format('Y-m-d H:i:s') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 50,
            'C' => 60,
            'D' => 12,
            'E' => 8,
            'F' => 30,
            'G' => 25,
            'H' => 25,
            'I' => 25,
            'J' => 30,
            'K' => 30,
            'L' => 15,
            'M' => 50,
            'N' => 20,
            'O' => 20,
            'P' => 20,
        ];
    }
}

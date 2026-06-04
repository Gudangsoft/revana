<?php

namespace App\Exports;

use App\Models\Submission;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubmissionsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $filters;
    protected int $rowNumber = 0;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function query()
    {
        $query = Submission::with([
            'journalSlot.journalMaster',
            'marketing',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction',
            'petugasValidator',
        ]);

        if (!empty($this->filters['tanggal_dari'])) {
            $query->whereDate('tanggal_submit', '>=', $this->filters['tanggal_dari']);
        }

        if (!empty($this->filters['tanggal_sampai'])) {
            $query->whereDate('tanggal_submit', '<=', $this->filters['tanggal_sampai']);
        }

        if (!empty($this->filters['journal_master_id'])) {
            $query->whereHas('journalSlot', function ($q) {
                $q->where('journal_master_id', $this->filters['journal_master_id']);
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Submit',
            'Kode LOA',
            'ID Artikel',
            'Judul Artikel',
            'Link Artikel',
            'Nama Penulis',
            'No HP Penulis',
            'Username Author',
            'Password Author',
            'Catatan Marketing',
            'PIC Marketing',
            'Petugas Submit',
            'Tanggal Submit',
            'Jurnal',
            'Slot',
            'Status',
            'Petugas Editor 1',
            'Username Editor',
            'Password Editor',
            'Editor 1 Valid',
            'Petugas Author 1',
            'Author 1 Valid',
            'Petugas Editor 2',
            'Editor 2 Valid',
            'Petugas Reviewer 1',
            'Username Reviewer 1',
            'Password Reviewer 1',
            'Catatan Reviewer 1',
            'Reviewer 1 Valid',
            'Petugas Reviewer 2',
            'Username Reviewer 2',
            'Password Reviewer 2',
            'Catatan Reviewer 2',
            'Reviewer 2 Valid',
            'Petugas Editor 3',
            'Editor 3 Valid',
            'Petugas Author 2',
            'Author 2 Valid',
            'Petugas Production',
            'Link Publish',
            'Production Valid',
            'Petugas Validator',
            'Validator Valid',
        ];
    }

    public function map($submission): array
    {
        $this->rowNumber++;

        $slotInfo = $submission->journalSlot
            ? "Vol. {$submission->journalSlot->volume} No. {$submission->journalSlot->nomor} - {$submission->journalSlot->bulan} {$submission->journalSlot->tahun}"
            : '-';

        $tanggalSubmit = '-';
        if ($submission->tanggal_submit) {
            try {
                $tanggalSubmit = \Carbon\Carbon::parse($submission->tanggal_submit)->format('d/m/Y');
            } catch (\Exception $e) {
                $tanggalSubmit = $submission->tanggal_submit;
            }
        }

        return [
            $this->rowNumber,
            $submission->kode_submit ?? '-',
            $submission->kode_loa ?? '-',
            $submission->id_artikel ?? '-',
            $submission->judul_artikel ?? '-',
            $submission->link_artikel ?? '-',
            $submission->nama_penulis ?? '-',
            $submission->no_hp_penulis ?? '-',
            $submission->username_author ?? '-',
            $submission->password_author ?? '-',
            $submission->catatan_marketing ?? '-',
            $submission->marketing?->name ?? '-',
            $submission->petugasSubmit?->name ?? '-',
            $tanggalSubmit,
            $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-',
            $slotInfo,
            $submission->status_label ?? $submission->status,
            $submission->petugasEditor1?->name ?? '-',
            $submission->username_editor ?? '-',
            $submission->password_editor ?? '-',
            $submission->editor1_valid ? 'Ya' : 'Tidak',
            $submission->petugasAuthor1?->name ?? '-',
            $submission->author1_valid ? 'Ya' : 'Tidak',
            $submission->petugasEditor2?->name ?? '-',
            $submission->editor2_valid ? 'Ya' : 'Tidak',
            $submission->petugasReviewer1?->name ?? '-',
            $submission->username_reviewer1 ?? '-',
            $submission->password_reviewer1 ?? '-',
            $submission->catatan_reviewer1 ?? '-',
            $submission->reviewer1_valid ? 'Ya' : 'Tidak',
            $submission->petugasReviewer2?->name ?? '-',
            $submission->username_reviewer2 ?? '-',
            $submission->password_reviewer2 ?? '-',
            $submission->catatan_reviewer2 ?? '-',
            $submission->reviewer2_valid ? 'Ya' : 'Tidak',
            $submission->petugasEditor3?->name ?? '-',
            $submission->editor3_valid ? 'Ya' : 'Tidak',
            $submission->petugasAuthor2?->name ?? '-',
            $submission->author2_valid ? 'Ya' : 'Tidak',
            $submission->petugasProduction?->name ?? '-',
            $submission->link_publish ?? '-',
            $submission->production_valid ? 'Ya' : 'Tidak',
            $submission->petugasValidator?->name ?? '-',
            $submission->validator_valid ? 'Ya' : 'Tidak',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
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
            'B' => 15,
            'C' => 18,
            'D' => 18,
            'E' => 40,
            'F' => 30,
            'G' => 25,
            'H' => 15,
            'I' => 15,
            'J' => 12,
            'K' => 15,
            'L' => 20,
            'M' => 12,
            'N' => 25,
            'O' => 25,
            'P' => 15,
        ];
    }
}

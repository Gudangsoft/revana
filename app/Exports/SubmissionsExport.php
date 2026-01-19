<?php

namespace App\Exports;

use App\Models\Submission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubmissionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Submission::with([
            'journalSlot.journalMaster',
            'petugasSubmit',
            'petugasEditor1',
            'petugasAuthor1',
            'petugasEditor2',
            'petugasReviewer1',
            'petugasReviewer2',
            'petugasEditor3',
            'petugasAuthor2',
            'petugasProduction',
        ]);

        if (!empty($this->filters['tanggal_dari'])) {
            $query->whereDate('tanggal_submit', '>=', $this->filters['tanggal_dari']);
        }

        if (!empty($this->filters['tanggal_sampai'])) {
            $query->whereDate('tanggal_submit', '<=', $this->filters['tanggal_sampai']);
        }

        if (!empty($this->filters['journal_master_id'])) {
            $query->whereHas('journalSlot', function($q) {
                $q->where('journal_master_id', $this->filters['journal_master_id']);
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
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
            'PIC Marketing',
            'Petugas Submit',
            'Tanggal Submit',
            'Jurnal',
            'Slot',
            'Status',
            // Editor 1
            'Petugas Editor 1',
            'Username Editor',
            'Password Editor',
            'Editor 1 Valid',
            // Author 1
            'Petugas Author 1',
            'Author 1 Valid',
            // Editor 2
            'Petugas Editor 2',
            'Editor 2 Valid',
            // Reviewer 1
            'Petugas Reviewer 1',
            'Username Reviewer 1',
            'Password Reviewer 1',
            'Catatan Reviewer 1',
            'Reviewer 1 Valid',
            // Reviewer 2
            'Petugas Reviewer 2',
            'Username Reviewer 2',
            'Password Reviewer 2',
            'Catatan Reviewer 2',
            'Reviewer 2 Valid',
            // Editor 3
            'Petugas Editor 3',
            'Editor 3 Valid',
            // Author 2
            'Petugas Author 2',
            'Author 2 Valid',
            // Production
            'Petugas Production',
            'Link Publish',
            'Production Valid',
        ];
    }

    public function map($submission): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $slotInfo = $submission->journalSlot 
            ? "Vol. {$submission->journalSlot->volume} No. {$submission->journalSlot->nomor} - {$submission->journalSlot->bulan} {$submission->journalSlot->tahun}"
            : '-';

        return [
            $rowNumber,
            $submission->kode_submit ?? '-',
            $submission->kode_loa ?? '-',
            $submission->id_artikel ?? '-',
            $submission->judul_artikel ?? '-',
            $submission->link_artikel ?? '-',
            $submission->nama_penulis ?? '-',
            $submission->no_hp_penulis ?? '-',
            $submission->username_author ?? '-',
            $submission->password_author ?? '-',
            $submission->pic_marketing ?? '-',
            $submission->petugasSubmit?->name ?? '-',
            $submission->tanggal_submit?->format('d/m/Y') ?? '-',
            $submission->journalSlot?->journalMaster?->nama_jurnal ?? '-',
            $slotInfo,
            $submission->status_label ?? $submission->status,
            // Editor 1
            $submission->petugasEditor1?->name ?? '-',
            $submission->username_editor ?? '-',
            $submission->password_editor ?? '-',
            $submission->editor1_valid ? 'Ya' : 'Tidak',
            // Author 1
            $submission->petugasAuthor1?->name ?? '-',
            $submission->author1_valid ? 'Ya' : 'Tidak',
            // Editor 2
            $submission->petugasEditor2?->name ?? '-',
            $submission->editor2_valid ? 'Ya' : 'Tidak',
            // Reviewer 1
            $submission->petugasReviewer1?->name ?? '-',
            $submission->username_reviewer1 ?? '-',
            $submission->password_reviewer1 ?? '-',
            $submission->catatan_reviewer1 ?? '-',
            $submission->reviewer1_valid ? 'Ya' : 'Tidak',
            // Reviewer 2
            $submission->petugasReviewer2?->name ?? '-',
            $submission->username_reviewer2 ?? '-',
            $submission->password_reviewer2 ?? '-',
            $submission->catatan_reviewer2 ?? '-',
            $submission->reviewer2_valid ? 'Ya' : 'Tidak',
            // Editor 3
            $submission->petugasEditor3?->name ?? '-',
            $submission->editor3_valid ? 'Ya' : 'Tidak',
            // Author 2
            $submission->petugasAuthor2?->name ?? '-',
            $submission->author2_valid ? 'Ya' : 'Tidak',
            // Production
            $submission->petugasProduction?->name ?? '-',
            $submission->link_publish ?? '-',
            $submission->production_valid ? 'Ya' : 'Tidak',
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
            'A' => 5,   // No
            'B' => 15,  // Kode Submit
            'C' => 18,  // Kode LOA
            'D' => 18,  // ID Artikel
            'E' => 40,  // Judul Artikel
            'F' => 30,  // Link Artikel
            'G' => 25,  // Nama Penulis
            'H' => 15,  // No HP
            'I' => 15,  // Username Author
            'J' => 12,  // Password Author
            'K' => 15,  // PIC Marketing
            'L' => 20,  // Petugas Submit
            'M' => 12,  // Tanggal Submit
            'N' => 25,  // Jurnal
            'O' => 25,  // Slot
            'P' => 15,  // Status
        ];
    }
}

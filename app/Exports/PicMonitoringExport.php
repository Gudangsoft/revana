<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PicMonitoringExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected Collection $submissions;
    protected int $picId;
    protected int $rowNumber = 0;

    public function __construct(Collection $submissions, int $picId)
    {
        $this->submissions = $submissions;
        $this->picId = $picId;
    }

    public function collection(): Collection
    {
        return $this->submissions;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Submit',
            'ID Artikel',
            'Judul Artikel',
            'Nama Penulis',
            'Tanggal Submit',
            'Jurnal',
            'Status',
            'Catatan Marketing',
            'PIC Marketing',
            'Tugas Saya',
            'Petugas Editor 1',
            'Editor 1 Valid',
            'Petugas Author 1',
            'Author 1 Valid',
            'Petugas Editor 2',
            'Editor 2 Valid',
            'Petugas Reviewer 1',
            'Reviewer 1 Valid',
            'Petugas Reviewer 2',
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

    public function map($s): array
    {
        $this->rowNumber++;
        $picId = $this->picId;

        // Determine which steps this PIC handles for this submission
        $myRoles = collect([
            'editor1'    => $s->petugas_editor1_id,
            'author1'    => $s->petugas_author1_id,
            'editor2'    => $s->petugas_editor2_id,
            'reviewer1'  => $s->petugas_reviewer1_id,
            'reviewer2'  => $s->petugas_reviewer2_id,
            'editor3'    => $s->petugas_editor3_id,
            'author2'    => $s->petugas_author2_id,
            'production' => $s->petugas_production_id,
            'validator'  => $s->petugas_validator_id,
        ])->filter(fn($id) => $id == $picId)->keys()->implode(', ');

        $tanggal = '-';
        if ($s->tanggal_submit) {
            try {
                $tanggal = \Carbon\Carbon::parse($s->tanggal_submit)->format('d/m/Y');
            } catch (\Exception $e) {
                $tanggal = $s->tanggal_submit;
            }
        }

        return [
            $this->rowNumber,
            $s->kode_submit ?? '-',
            $s->id_artikel ?? '-',
            $s->judul_artikel ?? '-',
            $s->nama_penulis ?? '-',
            $tanggal,
            $s->journalSlot?->journalMaster?->nama_jurnal ?? '-',
            $s->status ?? '-',
            $s->catatan_marketing ?? '-',
            $s->marketing?->name ?? '-',
            $myRoles ?: '-',
            $s->petugasEditor1?->name ?? '-',
            $s->editor1_valid ? 'Ya' : 'Tidak',
            $s->petugasAuthor1?->name ?? '-',
            $s->author1_valid ? 'Ya' : 'Tidak',
            $s->petugasEditor2?->name ?? '-',
            $s->editor2_valid ? 'Ya' : 'Tidak',
            $s->petugasReviewer1?->name ?? '-',
            $s->reviewer1_valid ? 'Ya' : 'Tidak',
            $s->petugasReviewer2?->name ?? '-',
            $s->reviewer2_valid ? 'Ya' : 'Tidak',
            $s->petugasEditor3?->name ?? '-',
            $s->editor3_valid ? 'Ya' : 'Tidak',
            $s->petugasAuthor2?->name ?? '-',
            $s->author2_valid ? 'Ya' : 'Tidak',
            $s->petugasProduction?->name ?? '-',
            $s->link_publish ?? '-',
            $s->production_valid ? 'Ya' : 'Tidak',
            $s->petugasValidator?->name ?? '-',
            $s->validator_valid ? 'Ya' : 'Tidak',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '198754'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 16,
            'C' => 16,
            'D' => 40,
            'E' => 25,
            'F' => 12,
            'G' => 25,
            'H' => 20,
            'I' => 30,
            'J' => 18,
            'K' => 25,
        ];
    }
}

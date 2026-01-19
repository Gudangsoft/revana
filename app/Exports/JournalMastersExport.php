<?php

namespace App\Exports;

use App\Models\JournalMaster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JournalMastersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = JournalMaster::with(['creator', 'slots']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('kode_jurnal', 'like', "%{$this->search}%")
                  ->orWhere('nama_jurnal', 'like', "%{$this->search}%")
                  ->orWhere('publisher', 'like', "%{$this->search}%")
                  ->orWhere('accreditation', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('nama_jurnal')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Jurnal',
            'Nama Jurnal',
            'Publisher',
            'Link Jurnal',
            'Akreditasi',
            'Points',
            'Total Slot',
            'Slot Terpakai',
            'Slot Tersedia',
            'Status',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }

    public function map($journal): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $totalSlots = $journal->slots->sum('jumlah_slot');
        $usedSlots = $journal->slots->sum('slot_terpakai');
        $availableSlots = $totalSlots - $usedSlots;

        return [
            $rowNumber,
            $journal->kode_jurnal ?? '-',
            $journal->nama_jurnal ?? '-',
            $journal->publisher ?? '-',
            $journal->link_jurnal ?? '-',
            $journal->accreditation ?? '-',
            $journal->points ?? 0,
            $totalSlots,
            $usedSlots,
            $availableSlots,
            $journal->is_active ? 'Aktif' : 'Nonaktif',
            $journal->creator->name ?? '-',
            $journal->created_at ? $journal->created_at->format('d/m/Y H:i') : '-',
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
            'B' => 15,  // Kode Jurnal
            'C' => 40,  // Nama Jurnal
            'D' => 30,  // Publisher
            'E' => 40,  // Link Jurnal
            'F' => 12,  // Akreditasi
            'G' => 10,  // Points
            'H' => 12,  // Total Slot
            'I' => 12,  // Slot Terpakai
            'J' => 12,  // Slot Tersedia
            'K' => 12,  // Status
            'L' => 20,  // Dibuat Oleh
            'M' => 18,  // Tanggal Dibuat
        ];
    }
}

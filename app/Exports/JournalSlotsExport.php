<?php

namespace App\Exports;

use App\Models\JournalSlot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JournalSlotsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = JournalSlot::with(['journalMaster', 'creator', 'submissions']);

        if (!empty($this->filters['journal_master_id'])) {
            $query->where('journal_master_id', $this->filters['journal_master_id']);
        }

        if (!empty($this->filters['tahun'])) {
            $query->where('tahun', $this->filters['tahun']);
        }

        if (!empty($this->filters['bulan'])) {
            $query->where('bulan', $this->filters['bulan']);
        }

        return $query->orderBy('tahun', 'desc')->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Slot',
            'Nama Jurnal',
            'Publisher',
            'Akreditasi',
            'Volume',
            'Nomor',
            'Bulan',
            'Tahun',
            'Jumlah Slot',
            'Slot Terpakai',
            'Slot Tersedia',
            'Status',
            'Dibuat Oleh',
            'Tanggal Dibuat',
        ];
    }

    public function map($slot): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $slot->kode_slot ?? '-',
            $slot->journalMaster->nama_jurnal ?? '-',
            $slot->journalMaster->publisher ?? '-',
            $slot->journalMaster->accreditation ?? '-',
            $slot->volume ?? '-',
            $slot->nomor ?? '-',
            $slot->bulan ?? '-',
            $slot->tahun ?? '-',
            $slot->jumlah_slot ?? 0,
            $slot->slot_terpakai ?? 0,
            $slot->slot_tersedia ?? 0,
            $slot->is_active ? 'Aktif' : 'Nonaktif',
            $slot->creator->name ?? '-',
            $slot->created_at ? $slot->created_at->format('d/m/Y H:i') : '-',
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
            'B' => 15,  // Kode Slot
            'C' => 35,  // Nama Jurnal
            'D' => 25,  // Publisher
            'E' => 12,  // Akreditasi
            'F' => 10,  // Volume
            'G' => 10,  // Nomor
            'H' => 12,  // Bulan
            'I' => 8,   // Tahun
            'J' => 12,  // Jumlah Slot
            'K' => 12,  // Slot Terpakai
            'L' => 12,  // Slot Tersedia
            'M' => 10,  // Status
            'N' => 20,  // Dibuat Oleh
            'O' => 18,  // Tanggal Dibuat
        ];
    }
}

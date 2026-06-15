<?php

namespace App\Exports;

use App\Models\Pic;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class PicPointsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $request;
    protected int $rank = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        return Pic::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Ranking',
            'Nama',
            'Username',
            'Role',
            'Total Point',
            'Point Bulan Ini',
            'Point Hari Ini',
            'Total Tugas Selesai',
            'Status',
        ];
    }

    public function map($pic): array
    {
        $this->rank++;

        return [
            $this->rank,
            $pic->name,
            $pic->username,
            $pic->role,
            $pic->total_points,
            $pic->points_this_month,
            $pic->points_today,
            $pic->total_tasks_completed,
            $pic->is_active ? 'Aktif' : 'Tidak Aktif',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 18,
            'G' => 15,
            'H' => 20,
            'I' => 15,
        ];
    }
}

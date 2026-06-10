<?php

namespace App\Exports;

use App\Models\Marketing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarketingLeaderboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected ?string $search;
    protected int $rank = 0;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = Marketing::where('is_active', true)
            ->withCount('submissions')
            ->orderByDesc('total_points');

        if ($this->search) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['Rank', 'Nama', 'Email', 'Phone', 'Total Submission', 'Total Point'];
    }

    public function map($marketing): array
    {
        $this->rank++;
        return [
            $this->rank,
            $marketing->name,
            $marketing->email ?? '-',
            $marketing->phone ?? '-',
            $marketing->submissions_count,
            $marketing->total_points ?? 0,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 25,
            'C' => 30,
            'D' => 20,
            'E' => 18,
            'F' => 14,
        ];
    }
}

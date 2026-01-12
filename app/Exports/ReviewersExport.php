<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReviewersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = User::where('role', 'reviewer')
            ->when($this->search, function($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('institution', 'like', "%{$search}%")
                      ->orWhereHas('fieldOfStudy', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->with(['badges', 'fieldOfStudy'])
            ->withCount('reviewAssignments')
            ->orderBy('name')
            ->get();

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'No. HP / WhatsApp',
            'Institusi',
            'Jabatan',
            'Pendidikan',
            'Spesialisasi',
            'Bidang Ilmu',
            'NIDN',
            'Google Scholar',
            'SINTA ID',
            'Scopus ID',
            'Bahasa Artikel',
            'Total Points',
            'Available Points',
            'Completed Reviews',
            'Active Tasks',
            'Badges',
            'Alamat',
            'Bio',
        ];
    }

    public function map($reviewer): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        // Get badges
        $badges = $reviewer->badges->pluck('name')->implode(', ');

        // Get article languages
        $languages = is_array($reviewer->article_languages) 
            ? implode(', ', $reviewer->article_languages) 
            : ($reviewer->article_languages ?? '-');

        return [
            $rowNumber,
            $reviewer->name ?? '-',
            $reviewer->email ?? '-',
            $reviewer->phone ?? '-',
            $reviewer->institution ?? '-',
            $reviewer->position ?? '-',
            $reviewer->education_level ?? '-',
            $reviewer->specialization ?? '-',
            $reviewer->fieldOfStudy->name ?? '-',
            $reviewer->nidn ?? '-',
            $reviewer->google_scholar ?? '-',
            $reviewer->sinta_id ?? '-',
            $reviewer->scopus_id ?? '-',
            $languages,
            $reviewer->total_points ?? 0,
            $reviewer->available_points ?? 0,
            $reviewer->completed_reviews ?? 0,
            $reviewer->review_assignments_count ?? 0,
            $badges ?: '-',
            $reviewer->address ?? '-',
            $reviewer->bio ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 25,  // Nama
            'C' => 30,  // Email
            'D' => 18,  // No. HP
            'E' => 30,  // Institusi
            'F' => 20,  // Jabatan
            'G' => 20,  // Pendidikan
            'H' => 25,  // Spesialisasi
            'I' => 25,  // Bidang Ilmu
            'J' => 15,  // NIDN
            'K' => 40,  // Google Scholar
            'L' => 15,  // SINTA ID
            'M' => 15,  // Scopus ID
            'N' => 20,  // Bahasa Artikel
            'O' => 15,  // Total Points
            'P' => 15,  // Available Points
            'Q' => 18,  // Completed Reviews
            'R' => 15,  // Active Tasks
            'S' => 30,  // Badges
            'T' => 40,  // Alamat
            'U' => 50,  // Bio
        ];
    }
}

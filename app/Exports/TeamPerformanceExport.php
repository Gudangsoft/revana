<?php

namespace App\Exports;

use App\Models\Submission;
use App\Models\Pic;
use App\Models\Marketing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class TeamPerformanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $step;
    protected $processType;
    protected $tanggalDari;
    protected $tanggalSampai;
    protected $config;
    protected $totalTasks = 0;
    protected $rank = 0;

    public function __construct($step = 'submit', $processType = 'all', $tanggalDari = null, $tanggalSampai = null)
    {
        $this->step = $step;
        $this->processType = $processType;
        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
        $this->config = $this->getStepConfig($step);
    }

    protected function getStepConfig($step)
    {
        $stepConfigs = [
            'submit' => [
                'title' => 'Submit',
                'field' => 'petugas_submit_id',
                'date_field' => 'created_at',
                'valid_field' => null,
            ],
            'editor1' => [
                'title' => 'Editor 1',
                'field' => 'petugas_editor1_id',
                'date_field' => 'editor1_validated_at',
                'valid_field' => 'editor1_valid',
            ],
            'author1' => [
                'title' => 'Author 1',
                'field' => 'petugas_author1_id',
                'date_field' => 'author1_validated_at',
                'valid_field' => 'author1_valid',
            ],
            'editor2' => [
                'title' => 'Editor 2',
                'field' => 'petugas_editor2_id',
                'date_field' => 'editor2_validated_at',
                'valid_field' => 'editor2_valid',
            ],
            'reviewer1' => [
                'title' => 'Reviewer 1',
                'field' => 'petugas_reviewer1_id',
                'date_field' => 'reviewer1_validated_at',
                'valid_field' => 'reviewer1_valid',
            ],
            'reviewer2' => [
                'title' => 'Reviewer 2',
                'field' => 'petugas_reviewer2_id',
                'date_field' => 'reviewer2_validated_at',
                'valid_field' => 'reviewer2_valid',
            ],
            'editor3' => [
                'title' => 'Editor 3',
                'field' => 'petugas_editor3_id',
                'date_field' => 'editor3_validated_at',
                'valid_field' => 'editor3_valid',
            ],
            'author2' => [
                'title' => 'Author 2',
                'field' => 'petugas_author2_id',
                'date_field' => 'author2_validated_at',
                'valid_field' => 'author2_valid',
            ],
            'production' => [
                'title' => 'Production',
                'field' => 'petugas_production_id',
                'date_field' => 'production_validated_at',
                'valid_field' => 'production_valid',
            ],
            'marketing' => [
                'title' => 'Marketing',
                'field' => 'marketing_id',
                'date_field' => 'created_at',
                'valid_field' => null,
                'is_marketing' => true,
            ],
        ];

        return $stepConfigs[$step] ?? $stepConfigs['submit'];
    }

    public function collection()
    {
        $config = $this->config;
        
        $query = Submission::query();
        
        // Filter process type
        if ($this->processType === 'normal') {
            $query->where(function($q) {
                $q->where('process_type', 'normal')->orWhereNull('process_type');
            });
        } elseif ($this->processType === 'fasttrack') {
            $query->where('process_type', 'fasttrack');
        }
        
        // Filter tanggal
        if ($this->tanggalDari) {
            $query->whereDate($config['date_field'], '>=', $this->tanggalDari);
        }
        if ($this->tanggalSampai) {
            $query->whereDate($config['date_field'], '<=', $this->tanggalSampai);
        }
        
        // Get data with count
        $dataQuery = $query->clone()
            ->select(
                $config['field'], 
                DB::raw('COUNT(*) as total_task'),
                DB::raw('SUM(CASE WHEN status = "PUBLISHED" THEN 1 ELSE 0 END) as completed_task')
            )
            ->whereNotNull($config['field']);
        
        // Add valid filter if applicable
        if ($config['valid_field']) {
            $dataQuery->where($config['valid_field'], true);
        }
        
        $rankings = $dataQuery
            ->groupBy($config['field'])
            ->orderByDesc('total_task')
            ->get();
        
        // Calculate total for percentage
        $this->totalTasks = $rankings->sum('total_task');
        
        // Add names
        $isMarketing = $this->step === 'marketing';
        $rankings = $rankings->map(function ($item) use ($config, $isMarketing) {
            if ($isMarketing) {
                $model = Marketing::find($item->{$config['field']});
                $item->name = $model ? $model->name : 'Unknown';
                $item->is_active = $model ? $model->is_active : false;
            } else {
                $pic = Pic::find($item->{$config['field']});
                $item->name = $pic ? $pic->name : 'Unknown';
                $item->is_active = $pic ? $pic->is_active : false;
            }
            return $item;
        });
        
        return $rankings;
    }

    public function headings(): array
    {
        $isMarketing = $this->step === 'marketing';
        return [
            'Ranking',
            $isMarketing ? 'Nama Marketing' : 'Nama PIC',
            $isMarketing ? 'Total Submission' : 'Total Tugas',
            'Selesai',
            'Persentase',
            'Status',
        ];
    }

    public function map($item): array
    {
        $this->rank++;
        $percentage = $this->totalTasks > 0 ? ($item->total_task / $this->totalTasks) * 100 : 0;

        return [
            $this->rank,
            $item->name,
            $item->total_task,
            $item->completed_task ?? 0,
            number_format($percentage, 1) . '%',
            $item->is_active ? 'Aktif' : 'Nonaktif',
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
            'B' => 30,
            'C' => 15,
            'D' => 12,
            'E' => 12,
            'F' => 12,
        ];
    }

    public function title(): string
    {
        $processLabel = $this->processType === 'all' ? 'Semua' : ($this->processType === 'normal' ? 'Normal' : 'Fasttrack');
        return $this->config['title'] . ' - ' . $processLabel;
    }
}

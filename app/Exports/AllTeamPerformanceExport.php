<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllTeamPerformanceExport implements WithMultipleSheets
{
    protected $processType;
    protected $tanggalDari;
    protected $tanggalSampai;

    public function __construct($processType = 'all', $tanggalDari = null, $tanggalSampai = null)
    {
        $this->processType = $processType;
        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function sheets(): array
    {
        $steps = [
            'submit',
            'editor1',
            'author1',
            'editor2',
            'reviewer1',
            'reviewer2',
            'editor3',
            'author2',
            'production',
            'marketing',
        ];

        $sheets = [];
        foreach ($steps as $step) {
            $sheets[] = new TeamPerformanceExport(
                $step,
                $this->processType,
                $this->tanggalDari,
                $this->tanggalSampai
            );
        }

        return $sheets;
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanKinerjaExport implements WithMultipleSheets
{
    public function __construct(
        protected $picRekap,
        protected $mktRekap,
        protected array $steps,
        protected string $namaBulan
    ) {}

    public function sheets(): array
    {
        return [
            new LaporanKinerjaPicSheet($this->picRekap, $this->steps, $this->namaBulan),
            new LaporanKinerjaMarketingSheet($this->mktRekap, $this->namaBulan),
        ];
    }
}

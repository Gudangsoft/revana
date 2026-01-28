<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisJurnal;

class JenisJurnalSeeder extends Seeder
{
    public function run()
    {
        $jenisJurnals = [
            ['name' => 'Jurnal Nasional', 'description' => 'Jurnal yang diterbitkan secara nasional', 'is_active' => true],
            ['name' => 'Jurnal Internasional', 'description' => 'Jurnal yang diterbitkan secara internasional', 'is_active' => true],
            ['name' => 'Jurnal Bereputasi', 'description' => 'Jurnal yang memiliki reputasi tinggi', 'is_active' => true],
            ['name' => 'Proceeding', 'description' => 'Prosiding seminar atau konferensi', 'is_active' => true],
            ['name' => 'Book Chapter', 'description' => 'Bab dalam buku', 'is_active' => true],
        ];

        foreach ($jenisJurnals as $jenisJurnal) {
            JenisJurnal::firstOrCreate(
                ['name' => $jenisJurnal['name']], 
                $jenisJurnal
            );
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\Accreditation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccreditationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accreditations = [
            [
                'name' => 'SINTA 1',
                'points' => 100,
                'description' => 'Akreditasi tertinggi dengan 100 poin',
                'is_active' => true,
            ],
            [
                'name' => 'SINTA 2',
                'points' => 80,
                'description' => 'Akreditasi dengan 80 poin',
                'is_active' => true,
            ],
            [
                'name' => 'SINTA 3',
                'points' => 60,
                'description' => 'Akreditasi dengan 60 poin',
                'is_active' => true,
            ],
            [
                'name' => 'SINTA 4',
                'points' => 40,
                'description' => 'Akreditasi dengan 40 poin',
                'is_active' => true,
            ],
            [
                'name' => 'SINTA 5',
                'points' => 20,
                'description' => 'Akreditasi dengan 20 poin',
                'is_active' => true,
            ],
            [
                'name' => 'SINTA 6',
                'points' => 10,
                'description' => 'Akreditasi dengan 10 poin',
                'is_active' => true,
            ],
        ];

        foreach ($accreditations as $accreditation) {
            Accreditation::firstOrCreate(
                ['name' => $accreditation['name']],
                $accreditation
            );
        }
    }
}


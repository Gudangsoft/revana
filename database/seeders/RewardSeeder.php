<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            [
                'name' => 'Uang Tunai Rp 100.000',
                'description' => 'Transfer uang tunai sebesar Rp 100.000',
                'type' => 'UANG',
                'points_required' => 100,
                'value' => 100000,
                'is_active' => true,
            ],
            [
                'name' => 'Uang Tunai Rp 250.000',
                'description' => 'Transfer uang tunai sebesar Rp 250.000',
                'type' => 'UANG',
                'points_required' => 250,
                'value' => 250000,
                'is_active' => true,
            ],
            [
                'name' => 'Uang Tunai Rp 500.000',
                'description' => 'Transfer uang tunai sebesar Rp 500.000',
                'type' => 'UANG',
                'points_required' => 500,
                'value' => 500000,
                'is_active' => true,
            ],
            [
                'name' => 'Gratis Submit 1 Jurnal',
                'description' => 'Gratis submit jurnal ke publikasi ilmiah',
                'type' => 'GRATIS_SUBMIT',
                'points_required' => 200,
                'value' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Gratis Submit 3 Jurnal',
                'description' => 'Gratis submit 3 jurnal ke publikasi ilmiah',
                'type' => 'GRATIS_SUBMIT',
                'points_required' => 500,
                'value' => null,
                'is_active' => true,
            ],
        ];

        foreach ($rewards as $reward) {
            Reward::create($reward);
        }
    }
}

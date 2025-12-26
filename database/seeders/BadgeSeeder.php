<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Reviewer Pemula',
                'description' => 'Menyelesaikan 1 review jurnal',
                'required_reviews' => 1,
                'icon' => '🌱',
            ],
            [
                'name' => 'Reviewer Aktif',
                'description' => 'Menyelesaikan 10 review jurnal',
                'required_reviews' => 10,
                'icon' => '⭐',
            ],
            [
                'name' => 'Reviewer Expert',
                'description' => 'Menyelesaikan 25 review jurnal',
                'required_reviews' => 25,
                'icon' => '🏆',
            ],
            [
                'name' => 'Reviewer Master',
                'description' => 'Menyelesaikan 50 review jurnal',
                'required_reviews' => 50,
                'icon' => '👑',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::create($badge);
        }
    }
}

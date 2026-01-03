<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FieldOfStudy;

class FieldOfStudySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [
            ['name' => 'Agriculture', 'order' => 1],
            ['name' => 'Art', 'order' => 2],
            ['name' => 'Economics', 'order' => 3],
            ['name' => 'Education', 'order' => 4],
            ['name' => 'Engineering', 'order' => 5],
            ['name' => 'Health', 'order' => 6],
            ['name' => 'Humanities', 'order' => 7],
            ['name' => 'Religion', 'order' => 8],
            ['name' => 'Science', 'order' => 9],
            ['name' => 'Social', 'order' => 10],
        ];

        foreach ($fields as $field) {
            FieldOfStudy::firstOrCreate(
                ['name' => $field['name']],
                [
                    'description' => null,
                    'is_active' => true,
                    'order' => $field['order']
                ]
            );
        }
    }
}

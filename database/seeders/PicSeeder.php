<?php

namespace Database\Seeders;

use App\Models\Pic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PicSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $pics = [
            [
                'name' => 'John Doe',
                'role' => 'AUTOR 1',
                'email' => 'author@revana.test',
                'password' => Hash::make('password'),
                'phone' => '081234567890',
                'is_active' => true,
            ],
            [
                'name' => 'Jane Smith',
                'role' => 'EDITOR 1',
                'email' => 'editor@revana.test',
                'password' => Hash::make('password'),
                'phone' => '081234567891',
                'is_active' => true,
            ],
            [
                'name' => 'Bob Wilson',
                'role' => 'REVIEWER 1',
                'email' => 'reviewer1@revana.test',
                'password' => Hash::make('password'),
                'phone' => '081234567892',
                'is_active' => true,
            ],
            [
                'name' => 'Alice Brown',
                'role' => 'REVIEWER 2',
                'email' => 'reviewer2@revana.test',
                'password' => Hash::make('password'),
                'phone' => '081234567893',
                'is_active' => true,
            ],
        ];

        foreach ($pics as $pic) {
            Pic::create($pic);
        }
    }
}

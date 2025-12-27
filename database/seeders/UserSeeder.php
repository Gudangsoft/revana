<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin REVANA',
            'email' => 'admin@revana.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Sample Reviewers
        User::create([
            'name' => 'Dr. Ahmad Reviewer',
            'email' => 'ahmad@revana.com',
            'password' => Hash::make('password'),
            'role' => 'reviewer',
        ]);

        User::create([
            'name' => 'Dr. Siti Reviewer',
            'email' => 'siti@revana.com',
            'password' => Hash::make('password'),
            'role' => 'reviewer',
        ]);

        User::create([
            'name' => 'Dr. Budi Reviewer',
            'email' => 'budi@revana.com',
            'password' => Hash::make('password'),
            'role' => 'reviewer',
        ]);
    }
}

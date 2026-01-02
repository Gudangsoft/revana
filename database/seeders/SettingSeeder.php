<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Initialize app_name from .env if not exists
        if (!Setting::where('key', 'app_name')->exists()) {
            Setting::set('app_name', env('APP_NAME', 'REVANA'));
        }
        
        // Initialize other default settings if needed
        if (!Setting::where('key', 'tagline')->exists()) {
            Setting::set('tagline', 'Recognizing Quality, Commitment, and Integrity in Peer Review');
        }
    }
}

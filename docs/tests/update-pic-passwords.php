<?php

/**
 * Script untuk update password default semua PIC
 * Password default: pic@apjikom.or.id
 * 
 * Cara menjalankan:
 * php update-pic-passwords.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pic;
use Illuminate\Support\Facades\Hash;

echo "===========================================\n";
echo "Update Password Default untuk Semua PIC\n";
echo "===========================================\n\n";

$defaultPassword = 'pic@apjikom.or.id';

// Ambil semua PIC
$pics = Pic::all();
$totalPics = $pics->count();

if ($totalPics === 0) {
    echo "Tidak ada PIC yang ditemukan di database.\n";
    exit(0);
}

echo "Ditemukan {$totalPics} PIC di database.\n";
echo "Password default: {$defaultPassword}\n\n";

$confirm = readline("Lanjutkan update password? (yes/no): ");

if (strtolower(trim($confirm)) !== 'yes') {
    echo "Update dibatalkan.\n";
    exit(0);
}

echo "\nMemulai update password...\n\n";

$successCount = 0;
$failedCount = 0;

foreach ($pics as $pic) {
    try {
        $pic->password = Hash::make($defaultPassword);
        $pic->save();
        
        $successCount++;
        echo "✓ [{$successCount}] Password updated untuk: {$pic->name} ({$pic->email})\n";
    } catch (\Exception $e) {
        $failedCount++;
        echo "✗ Failed untuk: {$pic->name} - Error: " . $e->getMessage() . "\n";
    }
}

echo "\n===========================================\n";
echo "Update Selesai!\n";
echo "===========================================\n";
echo "Berhasil: {$successCount} PIC\n";
echo "Gagal: {$failedCount} PIC\n";
echo "Total: {$totalPics} PIC\n\n";
echo "Password default untuk semua PIC: {$defaultPassword}\n";
echo "===========================================\n";

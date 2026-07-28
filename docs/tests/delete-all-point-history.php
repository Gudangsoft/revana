<?php

/**
 * Script untuk menghapus semua history point dari database
 * 
 * Usage:
 * php delete-all-point-history.php
 * 
 * PERINGATAN: Script ini akan menghapus SEMUA data history point!
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "Script Hapus Semua History Point\n";
echo "========================================\n\n";

try {
    // Test koneksi database
    DB::connection()->getPdo();
    echo "✓ Koneksi database berhasil\n\n";
    
    // Hitung jumlah data sebelum dihapus
    $picPointCount = DB::table('pic_point_histories')->count();
    $marketingPointCount = DB::table('marketing_point_histories')->count();
    
    echo "Data saat ini:\n";
    echo "- PIC Point History: {$picPointCount} records\n";
    echo "- Marketing Point History: {$marketingPointCount} records\n\n";
    
    if ($picPointCount == 0 && $marketingPointCount == 0) {
        echo "✓ Tidak ada data history point yang perlu dihapus.\n\n";
        exit(0);
    }
    
    // Konfirmasi
    echo "PERINGATAN: Anda akan menghapus SEMUA history point!\n";
    echo "Ketik 'YA' untuk melanjutkan, atau ketik apapun untuk membatalkan: ";
    $handle = fopen("php://stdin", "r");
    $confirmation = trim(fgets($handle));
    
    if (strtoupper($confirmation) !== 'YA') {
        echo "\n✗ Dibatalkan oleh user.\n\n";
        exit(0);
    }
    
    echo "\n";
    echo "Menghapus data...\n";
    
    // Hapus PIC Point History
    DB::beginTransaction();
    try {
        DB::table('pic_point_histories')->truncate();
        echo "✓ PIC Point History berhasil dihapus ({$picPointCount} records)\n";
        
        // Hapus Marketing Point History
        DB::table('marketing_point_histories')->truncate();
        echo "✓ Marketing Point History berhasil dihapus ({$marketingPointCount} records)\n";
        
        // Reset total points ke 0 untuk semua PIC
        $picsUpdated = DB::table('pics')->update(['total_points' => 0]);
        echo "✓ Total points PIC direset ke 0 ({$picsUpdated} PIC)\n";
        
        // Reset total points ke 0 untuk semua Marketing
        $marketingsUpdated = DB::table('marketings')->update(['total_points' => 0]);
        echo "✓ Total points Marketing direset ke 0 ({$marketingsUpdated} Marketing)\n";
        
        DB::commit();
        
        echo "\n========================================\n";
        echo "✓ SEMUA HISTORY POINT BERHASIL DIHAPUS!\n";
        echo "========================================\n\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
    
} catch (\Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}

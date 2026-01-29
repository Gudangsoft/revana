<?php

/**
 * Script untuk auto-validasi production yang sudah ada link publish
 * Jalankan sekali saja untuk update data lama
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Submission;

echo "=== Update Auto-Valid Production ===\n\n";

// Cari semua submission yang sudah ada link_publish tapi production_valid masih false
$submissions = Submission::whereNotNull('link_publish')
    ->where('link_publish', '!=', '')
    ->where('production_valid', false)
    ->get();

echo "Ditemukan " . $submissions->count() . " submission yang perlu di-update\n\n";

$updated = 0;

foreach ($submissions as $submission) {
    echo "Processing: {$submission->kode_submit}\n";
    echo "  - Link Publish: {$submission->link_publish}\n";
    echo "  - Petugas Production: " . ($submission->petugasProduction ? $submission->petugasProduction->name : 'Belum ada') . "\n";
    
    // Set production_valid menjadi true
    $submission->production_valid = true;
    $submission->save();
    
    echo "  ✓ Production valid di-set ke TRUE\n\n";
    $updated++;
}

echo "\n=== Selesai ===\n";
echo "Total submission yang di-update: {$updated}\n";
echo "\nSilakan refresh halaman monitoring untuk melihat perubahan.\n";

<?php
/**
 * Check if journal exists in database
 * Run this in terminal: php check-journal.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JournalMaster;

$searchName = 'FUNDAMENTUM : Jurnal Pengabdian Multidisiplin';

echo "Mencari jurnal dengan nama: {$searchName}\n";
echo str_repeat("=", 70) . "\n\n";

// Try exact match
$exact = JournalMaster::where('nama_jurnal', $searchName)->first();
if ($exact) {
    echo "✓ DITEMUKAN (exact match):\n";
    echo "  ID: {$exact->id}\n";
    echo "  Nama: {$exact->nama_jurnal}\n";
    echo "  Kode: {$exact->kode_jurnal}\n";
    echo "  Active: " . ($exact->is_active ? 'Ya' : 'Tidak') . "\n";
} else {
    echo "✗ Tidak ditemukan dengan exact match\n\n";
    
    // Try LIKE match
    $like = JournalMaster::where('nama_jurnal', 'like', "%{$searchName}%")->first();
    if ($like) {
        echo "✓ DITEMUKAN (LIKE match):\n";
        echo "  ID: {$like->id}\n";
        echo "  Nama: {$like->nama_jurnal}\n";
        echo "  Kode: {$like->kode_jurnal}\n";
        echo "  Active: " . ($like->is_active ? 'Ya' : 'Tidak') . "\n";
    } else {
        echo "✗ Tidak ditemukan dengan LIKE match\n\n";
        
        // Show similar journals
        echo "Jurnal dengan nama mirip:\n";
        $similar = JournalMaster::where('nama_jurnal', 'like', "%FUNDAMENTUM%")
            ->orWhere('nama_jurnal', 'like', "%Pengabdian%")
            ->limit(5)
            ->get();
        
        if ($similar->count() > 0) {
            foreach ($similar as $journal) {
                echo "  - {$journal->nama_jurnal} (ID: {$journal->id})\n";
            }
        } else {
            echo "  Tidak ada jurnal dengan nama mirip\n";
        }
    }
}

echo "\n" . str_repeat("=", 70) . "\n";

// Check journal slots for this journal
echo "\nSlot yang sudah ada untuk jurnal ini:\n";
$slots = \App\Models\JournalSlot::whereHas('journalMaster', function($q) use ($searchName) {
    $q->where('nama_jurnal', 'like', "%FUNDAMENTUM%");
})->get();

if ($slots->count() > 0) {
    foreach ($slots as $slot) {
        echo "  - Kode: {$slot->kode_slot}, Vol: {$slot->volume}, No: {$slot->nomor}, Tahun: {$slot->tahun}, Active: " . ($slot->is_active ? 'Ya' : 'Tidak') . "\n";
    }
} else {
    echo "  Belum ada slot untuk jurnal ini\n";
}

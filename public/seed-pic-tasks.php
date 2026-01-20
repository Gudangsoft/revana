<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pic;
use App\Models\Submission;
use App\Models\JournalSlot;

// Get first PIC
$pic = Pic::first();
if (!$pic) {
    echo "No PIC found!";
    exit;
}

echo "PIC ID: " . $pic->id . " - " . $pic->name . "\n";

// Check existing submissions for this PIC
$existingCount = Submission::where('created_by', $pic->id)->count();
echo "Existing submissions for PIC: " . $existingCount . "\n";

// Get available slot
$slot = JournalSlot::first();
if (!$slot) {
    echo "No slot found!";
    exit;
}

// If no submissions for this PIC, create some dummy data
if ($existingCount < 5) {
    $statuses = ['new', 'EDITOR1_PROCESS', 'AUTHOR1_PROCESS', 'REVIEWER1_PROCESS', 'REVIEWER2_PROCESS', 'PRODUCTION_PROCESS', 'PUBLISHED'];
    
    $dummyArticles = [
        ['judul' => 'Analisis Kualitas Beton dengan Campuran Fly Ash', 'penulis' => 'Dr. Ahmad Wijaya'],
        ['judul' => 'Studi Perilaku Struktur Baja pada Gempa', 'penulis' => 'Ir. Budi Santoso, M.T.'],
        ['judul' => 'Pengaruh Agregat terhadap Kuat Tekan Beton', 'penulis' => 'Prof. Candra Dewi'],
        ['judul' => 'Manajemen Proyek Konstruksi Jalan Tol', 'penulis' => 'Dr. Dian Pratama'],
        ['judul' => 'Optimasi Desain Pondasi Dalam', 'penulis' => 'Ir. Eko Prasetyo, Ph.D.'],
    ];
    
    foreach ($dummyArticles as $index => $article) {
        $status = $statuses[$index % count($statuses)];
        $kodeSubmit = 'SUB' . date('Y') . str_pad(100 + $index, 4, '0', STR_PAD_LEFT);
        
        // Check if kode_submit exists
        if (Submission::where('kode_submit', $kodeSubmit)->exists()) {
            $kodeSubmit = 'SUB' . date('Y') . str_pad(rand(200, 999), 4, '0', STR_PAD_LEFT);
        }
        
        Submission::create([
            'kode_submit' => $kodeSubmit,
            'journal_slot_id' => $slot->id,
            'judul_artikel' => $article['judul'],
            'nama_penulis' => $article['penulis'],
            'no_hp_penulis' => '08' . rand(1000000000, 9999999999),
            'status' => $status,
            'created_by' => $pic->id,
            'tanggal_submit' => now()->subDays(rand(1, 30)),
        ]);
        
        echo "Created: " . $kodeSubmit . " - " . $article['judul'] . " (Status: " . $status . ")\n";
    }
    
    echo "\nDone! Created 5 dummy submissions for PIC.\n";
} else {
    echo "PIC already has " . $existingCount . " submissions.\n";
    
    // Update some to have created_by = pic->id if they don't
    $updateCount = Submission::whereNull('created_by')->orWhere('created_by', '!=', $pic->id)->take(5)->update(['created_by' => $pic->id]);
    echo "Updated " . $updateCount . " submissions to belong to this PIC.\n";
}

echo "\nFinal count for PIC: " . Submission::where('created_by', $pic->id)->count() . "\n";

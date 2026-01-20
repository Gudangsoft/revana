<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pic;
use App\Models\Submission;

// Get active PICs
$pics = Pic::where('is_active', true)->get();
echo "Active PICs: " . $pics->count() . "\n";

if ($pics->count() < 2) {
    echo "Not enough PICs!\n";
    exit;
}

// Assign PICs to submissions
$submissions = Submission::all();
echo "Total Submissions: " . $submissions->count() . "\n\n";

$picIds = $pics->pluck('id')->toArray();

foreach ($submissions as $submission) {
    // Assign random PIC to each role
    $updates = [];
    
    // Petugas Submit
    if (!$submission->petugas_submit_id) {
        $updates['petugas_submit_id'] = $picIds[array_rand($picIds)];
    }
    
    // Petugas Editor1
    if (!$submission->petugas_editor1_id) {
        $updates['petugas_editor1_id'] = $picIds[array_rand($picIds)];
    }
    
    // Petugas Author1
    if (!$submission->petugas_author1_id) {
        $updates['petugas_author1_id'] = $picIds[array_rand($picIds)];
    }
    
    // Petugas Editor2
    if (!$submission->petugas_editor2_id) {
        $updates['petugas_editor2_id'] = $picIds[array_rand($picIds)];
    }
    
    // Petugas Editor3
    if (!$submission->petugas_editor3_id) {
        $updates['petugas_editor3_id'] = $picIds[array_rand($picIds)];
    }
    
    // Petugas Author2
    if (!$submission->petugas_author2_id) {
        $updates['petugas_author2_id'] = $picIds[array_rand($picIds)];
    }
    
    // Petugas Production
    if (!$submission->petugas_production_id) {
        $updates['petugas_production_id'] = $picIds[array_rand($picIds)];
    }
    
    if (!empty($updates)) {
        $submission->update($updates);
        echo "Updated {$submission->kode_submit}: " . implode(', ', array_keys($updates)) . "\n";
    }
}

echo "\n=== Sample Submission with PICs ===\n";
$sample = Submission::with(['petugasSubmit', 'petugasEditor1', 'petugasAuthor1', 'petugasEditor2', 'petugasEditor3', 'petugasAuthor2', 'petugasProduction'])->first();

if ($sample) {
    echo "Kode: {$sample->kode_submit}\n";
    echo "Petugas Submit: " . ($sample->petugasSubmit->name ?? 'N/A') . "\n";
    echo "Petugas Editor1: " . ($sample->petugasEditor1->name ?? 'N/A') . "\n";
    echo "Petugas Author1: " . ($sample->petugasAuthor1->name ?? 'N/A') . "\n";
    echo "Petugas Editor2: " . ($sample->petugasEditor2->name ?? 'N/A') . "\n";
    echo "Petugas Editor3: " . ($sample->petugasEditor3->name ?? 'N/A') . "\n";
    echo "Petugas Author2: " . ($sample->petugasAuthor2->name ?? 'N/A') . "\n";
    echo "Petugas Production: " . ($sample->petugasProduction->name ?? 'N/A') . "\n";
}

echo "\nDone!\n";

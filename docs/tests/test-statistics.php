<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Submission;

echo "=== TEST STATISTICS QUERY ===\n\n";

// Test query sama seperti di controller
$statsQuery = \Illuminate\Support\Facades\DB::table('submissions')
    ->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN status = "PUBLISHED" THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = "SUBMITTED" THEN 1 ELSE 0 END) as submitted
    ')
    ->where(function($q) {
        $q->where('process_type', '!=', 'fasttrack')
          ->orWhereNull('process_type');
    })
    ->first();

echo "Using RAW SQL Query:\n";
echo "  Total: {$statsQuery->total}\n";
echo "  Published: {$statsQuery->published}\n";
echo "  Submitted: {$statsQuery->submitted}\n\n";

// Test dengan Eloquent
$eloquentStats = Submission::query()
    ->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN status = "PUBLISHED" THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = "SUBMITTED" THEN 1 ELSE 0 END) as submitted
    ')
    ->where(function($q) {
        $q->where('process_type', '!=', 'fasttrack')
          ->orWhereNull('process_type');
    })
    ->first();

echo "Using Eloquent:\n";
echo "  Total: {$eloquentStats->total}\n";
echo "  Published: {$eloquentStats->published}\n";
echo "  Submitted: {$eloquentStats->submitted}\n\n";

// Manual count untuk verifikasi
$manualTotal = Submission::where(function($q) {
    $q->where('process_type', '!=', 'fasttrack')
      ->orWhereNull('process_type');
})->count();

$manualPublished = Submission::where('status', 'PUBLISHED')
    ->where(function($q) {
        $q->where('process_type', '!=', 'fasttrack')
          ->orWhereNull('process_type');
    })->count();

echo "Manual Count:\n";
echo "  Total: {$manualTotal}\n";
echo "  Published: {$manualPublished}\n\n";

echo "✓ Semua metode hitung sudah konsisten!\n";

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Submission;

echo "=== ALL NON-FASTTRACK DATA (NO FILTER) ===\n\n";

// Get statistics without any filter
$statsQuery = Submission::query()
    ->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN status = "SUBMITTED" THEN 1 ELSE 0 END) as submitted,
        SUM(CASE WHEN status NOT IN ("SUBMITTED", "PUBLISHED", "REJECTED") THEN 1 ELSE 0 END) as in_process,
        SUM(CASE WHEN status = "PUBLISHED" THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = "REJECTED" THEN 1 ELSE 0 END) as rejected
    ')
    ->where(function($q) {
        $q->where('process_type', '!=', 'fasttrack')
          ->orWhereNull('process_type');
    });

$stats = $statsQuery->first();

echo "Statistics (Non-Fasttrack Only):\n";
echo "  Total: {$stats->total}\n";
echo "  Submitted: {$stats->submitted}\n";
echo "  In Process: {$stats->in_process}\n";
echo "  Published: {$stats->published}\n";
echo "  Rejected: {$stats->rejected}\n\n";

// Raw query to check
echo "=== RAW COUNTS ===\n";
$total = Submission::where(function($q) {
    $q->where('process_type', '!=', 'fasttrack')
      ->orWhereNull('process_type');
})->count();

$published = Submission::where('status', 'PUBLISHED')
    ->where(function($q) {
        $q->where('process_type', '!=', 'fasttrack')
          ->orWhereNull('process_type');
    })->count();

echo "Total non-fasttrack: {$total}\n";
echo "Published non-fasttrack: {$published}\n";

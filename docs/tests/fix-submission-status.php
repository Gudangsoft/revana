<?php
/**
 * Fix stale submission statuses.
 * Run this script once to update submissions where production_valid=true but status is not PUBLISHED.
 * 
 * Usage: php artisan tinker < fix-submission-status.php
 * Or: php fix-submission-status.php (from project root, after requiring autoload)
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Submission;

echo "=== Fix Submission Status Script ===\n\n";

// Find all submissions that need status recalculation
$submissions = Submission::all();
$fixed = 0;

foreach ($submissions as $submission) {
    $oldStatus = $submission->status;
    
    // Recalculate status based on validation flags
    $submission->recalculateStatus();
    
    if ($oldStatus !== $submission->status) {
        $submission->save();
        $fixed++;
        echo "Fixed #{$submission->id} ({$submission->kode_submit}): {$oldStatus} -> {$submission->status}\n";
    }
}

echo "\n=== Done! Fixed {$fixed} submissions ===\n";

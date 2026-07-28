<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ReviewAssignment;
use App\Models\PointHistory;
use App\Models\Setting;

echo "=== Update Points untuk Review yang Sudah Approved ===\n\n";

// Get points per review from settings
$pointsPerReview = (int) Setting::get('points_per_review', 5);
echo "Points per review: {$pointsPerReview}\n\n";

// Get all approved reviews
$approvedReviews = ReviewAssignment::where('status', 'APPROVED')
    ->whereNotNull('reviewer_id')
    ->with('reviewer')
    ->get();

echo "Total approved reviews: " . $approvedReviews->count() . "\n\n";

$updated = 0;
$skipped = 0;

foreach ($approvedReviews as $assignment) {
    if (!$assignment->reviewer) {
        echo "Skipped: Assignment #{$assignment->id} - No reviewer\n";
        $skipped++;
        continue;
    }
    
    // Check if point history already exists
    $historyExists = PointHistory::where('review_assignment_id', $assignment->id)
        ->where('user_id', $assignment->reviewer_id)
        ->exists();
    
    if ($historyExists) {
        echo "Skipped: Assignment #{$assignment->id} - Points already recorded\n";
        $skipped++;
        continue;
    }
    
    // Add points
    $assignment->reviewer->increment('total_points', $pointsPerReview);
    $assignment->reviewer->increment('available_points', $pointsPerReview);
    $assignment->reviewer->increment('completed_reviews');
    
    // Create point history
    PointHistory::create([
        'user_id' => $assignment->reviewer_id,
        'review_assignment_id' => $assignment->id,
        'points' => $pointsPerReview,
        'type' => 'EARNED',
        'description' => "Review artikel: {$assignment->article_title}",
    ]);
    
    echo "✓ Updated: Assignment #{$assignment->id} - {$assignment->reviewer->name} (+{$pointsPerReview} points)\n";
    $updated++;
}

echo "\n=== Summary ===\n";
echo "Updated: {$updated}\n";
echo "Skipped: {$skipped}\n";
echo "\nDone!\n";

<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ReviewAssignment;
use App\Notifications\ReviewAssignmentNotification;

try {
    // Get first reviewer
    $reviewer = User::where('role', 'reviewer')->first();
    
    if (!$reviewer) {
        echo "No reviewer found!\n";
        exit(1);
    }
    
    echo "Testing email to: {$reviewer->email}\n";
    
    // Get first assignment
    $assignment = ReviewAssignment::first();
    
    if (!$assignment) {
        echo "No assignment found!\n";
        exit(1);
    }
    
    echo "Assignment ID: {$assignment->id}\n";
    
    // Send notification
    $reviewer->notify(new ReviewAssignmentNotification($assignment));
    
    echo "Email sent successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

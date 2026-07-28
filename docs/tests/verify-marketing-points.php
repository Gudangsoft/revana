<?php

/**
 * Script to verify marketing points accuracy
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Marketing;
use App\Models\MarketingPointHistory;

echo "=== VERIFIKASI MARKETING POINTS ===\n\n";

$marketings = Marketing::all();
$fixed = 0;

foreach ($marketings as $marketing) {
    $dbTotal = $marketing->total_points ?? 0;
    $calcTotal = MarketingPointHistory::where('marketing_id', $marketing->id)->sum('points_earned');
    
    if ($dbTotal != $calcTotal) {
        echo "⚠ Marketing: {$marketing->name}\n";
        echo "  DB Total: {$dbTotal}\n";
        echo "  Calculated: {$calcTotal}\n";
        echo "  → Fixing...\n";
        $marketing->update(['total_points' => $calcTotal]);
        $fixed++;
    } else {
        echo "✓ {$marketing->name}: {$dbTotal} points (OK)\n";
    }
}

echo "\n=== RINGKASAN ===\n";
echo "Total marketing: " . $marketings->count() . "\n";
echo "Fixed: {$fixed}\n";
echo "\n✓ Verifikasi selesai!\n";

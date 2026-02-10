<?php

/**
 * Script to synchronize marketing points for existing submissions
 * Run: php sync-marketing-points.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Submission;
use App\Models\Marketing;
use App\Models\MarketingPointHistory;

echo "=== SINKRONISASI MARKETING POINTS ===\n\n";

// Get all submissions with marketing_id but no point history
$submissions = Submission::whereNotNull('marketing_id')
    ->whereDoesntHave('marketingPointHistory')
    ->with('marketing')
    ->get();

echo "Ditemukan " . $submissions->count() . " submission yang belum ada point history untuk marketing\n\n";

if ($submissions->isEmpty()) {
    echo "✓ Semua data sudah sinkron!\n";
    exit(0);
}

$synced = 0;
$failed = 0;
$totalPointsAwarded = 0;

foreach ($submissions as $submission) {
    if (!$submission->marketing_id) {
        continue;
    }

    try {
        $marketing = Marketing::find($submission->marketing_id);
        if (!$marketing) {
            echo "⚠ Submission #{$submission->id} - Marketing ID {$submission->marketing_id} tidak ditemukan\n";
            $failed++;
            continue;
        }

        // Award points
        $pointHistory = MarketingPointHistory::awardPoints(
            $submission->marketing_id,
            $submission->id,
            "Sinkronisasi: {$submission->kode_submit} - {$submission->judul_artikel}"
        );

        if ($pointHistory) {
            $synced++;
            $totalPointsAwarded += $pointHistory->points_earned;
            echo "✓ Submission #{$submission->id} ({$submission->kode_submit}) - Marketing: {$marketing->name} (+{$pointHistory->points_earned} point)\n";
        } else {
            echo "⚠ Submission #{$submission->id} - Point sudah ada sebelumnya\n";
        }

    } catch (\Exception $e) {
        $failed++;
        echo "✗ Submission #{$submission->id} - Error: {$e->getMessage()}\n";
    }
}

echo "\n=== RINGKASAN ===\n";
echo "Berhasil disinkronkan: {$synced} submission\n";
echo "Gagal: {$failed} submission\n";
echo "Total point diberikan: {$totalPointsAwarded}\n";

// Recalculate total points for all marketings to ensure accuracy
echo "\n=== RECALCULATE TOTAL POINTS ===\n";
$marketings = Marketing::all();
foreach ($marketings as $marketing) {
    $totalPoints = MarketingPointHistory::where('marketing_id', $marketing->id)->sum('points_earned');
    $oldTotal = $marketing->total_points ?? 0;
    
    if ($totalPoints != $oldTotal) {
        $marketing->update(['total_points' => $totalPoints]);
        echo "✓ Marketing: {$marketing->name} - Updated: {$oldTotal} → {$totalPoints}\n";
    } else {
        echo "✓ Marketing: {$marketing->name} - OK ({$totalPoints} points)\n";
    }
}

echo "\n✓ Sinkronisasi selesai!\n";

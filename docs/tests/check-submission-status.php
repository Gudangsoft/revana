<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Submission;

echo "=== STATUS SUBMISSION ===\n\n";

$total = Submission::count();
echo "Total submissions: {$total}\n\n";

// Non-fasttrack
$nonFasttrack = Submission::where(function($q) {
    $q->where('process_type', '!=', 'fasttrack')
      ->orWhereNull('process_type');
})->count();

echo "Non-fasttrack submissions: {$nonFasttrack}\n";

// Fasttrack
$fasttrack = Submission::where('process_type', 'fasttrack')->count();
echo "Fasttrack submissions: {$fasttrack}\n\n";

echo "=== BREAKDOWN STATUS ===\n";
$statuses = Submission::selectRaw('status, process_type, COUNT(*) as count')
    ->groupBy('status', 'process_type')
    ->orderBy('status')
    ->get();

foreach($statuses as $s) {
    $type = $s->process_type ?: 'normal';
    echo "{$s->status} ({$type}): {$s->count}\n";
}

echo "\n=== CHECK PUBLISHED ===\n";
$published = Submission::where('status', 'PUBLISHED')->get(['id', 'kode_submit', 'judul_artikel', 'link_publish', 'process_type']);
echo "Total PUBLISHED: " . $published->count() . "\n";
foreach($published as $p) {
    $type = $p->process_type ?: 'normal';
    echo "  #{$p->id} - {$p->kode_submit} ({$type}) - " . ($p->link_publish ? '✓ Has link' : '✗ No link') . "\n";
}

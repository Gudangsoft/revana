<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Submission;

echo "=== CHECK FILTERED DATA ===\n\n";

$tanggal_dari = '2026-02-06';
$tanggal_sampai = '2026-02-06';

$query = Submission::query()
    ->where(function($q) {
        $q->where('process_type', '!=', 'fasttrack')
          ->orWhereNull('process_type');
    })
    ->whereDate('tanggal_submit', '>=', $tanggal_dari)
    ->whereDate('tanggal_submit', '<=', $tanggal_sampai);

$total = $query->count();
echo "Non-fasttrack submissions (2026-02-06): {$total}\n\n";

// Get statistics
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
    })
    ->whereDate('tanggal_submit', '>=', $tanggal_dari)
    ->whereDate('tanggal_submit', '<=', $tanggal_sampai);

$stats = $statsQuery->first();

echo "Statistics:\n";
echo "  Total: {$stats->total}\n";
echo "  Submitted: {$stats->submitted}\n";
echo "  In Process: {$stats->in_process}\n";
echo "  Published: {$stats->published}\n";
echo "  Rejected: {$stats->rejected}\n\n";

// List all with status
echo "=== BREAKDOWN ===\n";
$breakdown = Submission::query()
    ->where(function($q) {
        $q->where('process_type', '!=', 'fasttrack')
          ->orWhereNull('process_type');
    })
    ->whereDate('tanggal_submit', '>=', $tanggal_dari)
    ->whereDate('tanggal_submit', '<=', $tanggal_sampai)
    ->selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();

foreach($breakdown as $b) {
    echo "  {$b->status}: {$b->count}\n";
}

// Check if any PUBLISHED
echo "\n=== PUBLISHED SUBMISSIONS ===\n";
$published = Submission::query()
    ->where(function($q) {
        $q->where('process_type', '!=', 'fasttrack')
          ->orWhereNull('process_type');
    })
    ->whereDate('tanggal_submit', '>=', $tanggal_dari)
    ->whereDate('tanggal_submit', '<=', $tanggal_sampai)
    ->where('status', 'PUBLISHED')
    ->get(['id', 'kode_submit', 'judul_artikel', 'tanggal_submit', 'status']);

if ($published->count() > 0) {
    foreach($published as $p) {
        echo "  #{$p->id} - {$p->kode_submit} - {$p->tanggal_submit}\n";
    }
} else {
    echo "  Tidak ada PUBLISHED untuk tanggal tersebut\n";
}

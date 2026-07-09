<?php

namespace App\Console\Commands;

use App\Models\ReviewAssignment;
use Illuminate\Console\Command;

class SyncReviewerPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviewers:sync-points {--dry-run : Tampilkan apa yang akan disinkronkan tanpa benar-benar menyimpan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan ulang poin reviewer dari riwayat review yang sudah APPROVED — memberi poin ke reviewer pendamping (reviewer 2-5) yang terlewat sebelum perbaikan bug';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $assignments = ReviewAssignment::where('status', 'APPROVED')->get();
        $this->info("Memeriksa {$assignments->count()} review assignment yang sudah APPROVED...");

        $totalAwarded = 0;
        $affectedAssignments = 0;

        foreach ($assignments as $assignment) {
            $completedAt = $assignment->approved_at ?? $assignment->updated_at ?? now();

            if ($dryRun) {
                // Simulasikan tanpa menyimpan: hitung reviewer mana yang BELUM punya PointHistory
                $missing = collect($assignment->assignedReviewerIds())->filter(function ($reviewerId) use ($assignment) {
                    return !\App\Models\PointHistory::where('user_id', $reviewerId)
                        ->where('review_assignment_id', $assignment->id)
                        ->exists();
                });
                if ($missing->isNotEmpty()) {
                    $affectedAssignments++;
                    $totalAwarded += $missing->count();
                    $this->line("  [DRY-RUN] Assignment #{$assignment->id} ({$assignment->article_title}): akan memberi poin ke " . $missing->count() . ' reviewer yang terlewat');
                }
                continue;
            }

            $awarded = $assignment->awardPointsToAllReviewers($completedAt);
            if ($awarded > 0) {
                $affectedAssignments++;
                $totalAwarded += $awarded;
                $this->line("  Assignment #{$assignment->id} ({$assignment->article_title}): {$awarded} reviewer baru diberi poin");
            }
        }

        if ($dryRun) {
            $this->info("[DRY-RUN] Total: {$totalAwarded} entri poin akan dibuat di {$affectedAssignments} assignment. Jalankan tanpa --dry-run untuk benar-benar menyimpan.");
        } else {
            $this->info("Selesai. Total {$totalAwarded} entri poin baru dibuat di {$affectedAssignments} assignment.");
        }

        return 0;
    }
}

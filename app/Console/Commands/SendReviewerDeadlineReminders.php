<?php

namespace App\Console\Commands;

use App\Models\Submission;
use App\Models\SubmissionHistory;
use App\Models\User;
use App\Services\WaNotificationService;
use Illuminate\Console\Command;

class SendReviewerDeadlineReminders extends Command
{
    protected $signature = 'wa:reviewer-reminders {--days=3 : Minimum hari sejak penugasan untuk kirim reminder}';
    protected $description = 'Kirim WA reminder ke reviewer yang belum menyelesaikan review';

    public function handle(WaNotificationService $waService): int
    {
        $minDays = (int) $this->option('days');
        $cutoff  = now()->subDays($minDays);
        $sent    = 0;

        foreach (['reviewer1', 'reviewer2'] as $step) {
            $idField        = "petugas_{$step}_id";
            $validatedField = "{$step}_validated_at";

            $submissions = Submission::whereNotNull($idField)
                ->whereNull($validatedField)
                ->get();

            foreach ($submissions as $submission) {
                $assignedAt = SubmissionHistory::where('submission_id', $submission->id)
                    ->where('step', $step)
                    ->where('action', 'assigned')
                    ->orderBy('id', 'desc')
                    ->value('created_at');

                if (!$assignedAt || $assignedAt->gt($cutoff)) continue;

                $reviewer = User::find($submission->{$idField});
                if (!$reviewer?->phone) continue;

                $daysOverdue = (int) $assignedAt->diffInDays(now());
                $waService->sendDeadlineReminder($submission, $reviewer, $step, $daysOverdue);
                $sent++;

                $this->line("  Reminder → {$reviewer->name} | {$submission->kode_submit} | {$daysOverdue} hari");
            }
        }

        $this->info("Total reminder terkirim: {$sent}");
        return Command::SUCCESS;
    }
}

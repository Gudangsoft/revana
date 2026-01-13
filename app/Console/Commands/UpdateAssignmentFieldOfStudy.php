<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReviewAssignment;
use App\Models\FieldOfStudy;

class UpdateAssignmentFieldOfStudy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:update-field-of-study {field_of_study_id} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update field_of_study_id for existing assignments that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fieldOfStudyId = $this->argument('field_of_study_id');
        $updateAll = $this->option('all');

        // Validate field of study exists
        $fieldOfStudy = FieldOfStudy::find($fieldOfStudyId);
        if (!$fieldOfStudy) {
            $this->error("Field of Study with ID {$fieldOfStudyId} not found!");
            return 1;
        }

        // Get assignments without field_of_study_id
        $assignments = ReviewAssignment::whereNull('field_of_study_id')->get();

        if ($assignments->isEmpty()) {
            $this->info('No assignments found without field_of_study_id');
            return 0;
        }

        $this->info("Found {$assignments->count()} assignments without field_of_study_id");

        if (!$updateAll) {
            $this->table(
                ['ID', 'Article Title', 'Reviewer', 'Status'],
                $assignments->map(fn($a) => [
                    $a->id,
                    $a->article_title,
                    $a->reviewer->name ?? '-',
                    $a->status
                ])
            );

            if (!$this->confirm("Update all these assignments to field: {$fieldOfStudy->name}?")) {
                $this->info('Update cancelled');
                return 0;
            }
        }

        $updated = 0;
        foreach ($assignments as $assignment) {
            $assignment->field_of_study_id = $fieldOfStudyId;
            if ($assignment->save()) {
                $updated++;
            }
        }

        $this->info("Successfully updated {$updated} assignments with field_of_study_id = {$fieldOfStudyId} ({$fieldOfStudy->name})");
        return 0;
    }
}

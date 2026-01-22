<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Submission;
use App\Models\JournalSlot;
use App\Models\Pic;
use Illuminate\Support\Str;

class PicSubmissionDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a PIC user (first available)
        $pic = Pic::first();
        
        if (!$pic) {
            $this->command->error('No PIC users found. Please create a PIC user first.');
            return;
        }
        
        // Get available journal slots
        $journalSlots = JournalSlot::with('journalMaster')->get();
        
        if ($journalSlots->isEmpty()) {
            $this->command->error('No journal slots found. Please create journal slots first.');
            return;
        }
        
        $statuses = [
            'EDITOR1',
            'AUTHOR1', 
            'EDITOR2',
            'REVIEWER1',
            'REVIEWER2',
            'EDITOR3',
            'AUTHOR2',
            'PRODUCTION'
        ];
        
        $this->command->info("Creating dummy submissions for PIC: {$pic->name} (ID: {$pic->id})");
        
        // Get other PICs for assignment
        $otherPics = Pic::where('id', '!=', $pic->id)->get();
        if ($otherPics->isEmpty()) {
            $otherPics = collect([$pic]); // Use same PIC if no others
        }
        
        // Create 15 dummy submissions
        for ($i = 1; $i <= 15; $i++) {
            $journalSlot = $journalSlots->random();
            $status = $statuses[array_rand($statuses)];
            
            // Assign ALL stages with petugas (complete workflow)
            // Make current PIC assigned to 2-3 random stages
            $currentPicStages = [];
            $numCurrentPicStages = rand(2, 3);
            $allStages = ['petugas_editor1_id', 'petugas_author1_id', 'petugas_editor2_id', 'petugas_reviewer1_id', 'petugas_reviewer2_id', 'petugas_editor3_id', 'petugas_author2_id', 'petugas_production_id'];
            $currentPicStages = array_rand(array_flip($allStages), $numCurrentPicStages);
            if (!is_array($currentPicStages)) {
                $currentPicStages = [$currentPicStages];
            }
            
            $assignments = [
                'petugas_editor1_id' => in_array('petugas_editor1_id', $currentPicStages) ? $pic->id : $otherPics->random()->id,
                'petugas_author1_id' => in_array('petugas_author1_id', $currentPicStages) ? $pic->id : $otherPics->random()->id,
                'petugas_editor2_id' => in_array('petugas_editor2_id', $currentPicStages) ? $pic->id : $otherPics->random()->id,
                'petugas_reviewer1_id' => in_array('petugas_reviewer1_id', $currentPicStages) ? $pic->id : $otherPics->random()->id,
                'petugas_reviewer2_id' => in_array('petugas_reviewer2_id', $currentPicStages) ? $pic->id : $otherPics->random()->id,
                'petugas_editor3_id' => in_array('petugas_editor3_id', $currentPicStages) ? $pic->id : $otherPics->random()->id,
                'petugas_author2_id' => in_array('petugas_author2_id', $currentPicStages) ? $pic->id : $otherPics->random()->id,
                'petugas_production_id' => in_array('petugas_production_id', $currentPicStages) ? $pic->id : $otherPics->random()->id,
            ];
            
            $submission = Submission::create([
                'kode_submit' => 'SUBMIT-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'id_artikel' => 'ART-' . Str::random(6),
                'judul_artikel' => 'Artikel Dummy ' . $i . ': ' . fake()->sentence(8),
                'link_artikel' => 'https://journal-example.com/articles/' . Str::random(10),
                'nama_penulis' => fake()->name(),
                'no_hp_penulis' => '08' . rand(1000000000, 9999999999),
                'username_author' => 'author' . $i . '@example.com',
                'password_author' => 'pass' . rand(1000, 9999),
                'username_editor' => 'editor' . $i . '@example.com',
                'password_editor' => 'edpass' . rand(1000, 9999),
                'username_reviewer1' => 'reviewer1_' . $i,
                'password_reviewer1' => 'revpass' . rand(1000, 9999),
                'username_reviewer2' => 'reviewer2_' . $i,
                'password_reviewer2' => 'revpass' . rand(1000, 9999),
                'kode_loa' => 'LOA-' . Str::random(8),
                'status' => $status,
                'journal_slot_id' => $journalSlot->id,
                'created_by' => $pic->id,
                'petugas_submit_id' => rand(0, 1) === 0 ? $pic->id : null,
                'petugas_editor1_id' => $assignments['petugas_editor1_id'],
                'petugas_author1_id' => $assignments['petugas_author1_id'],
                'petugas_editor2_id' => $assignments['petugas_editor2_id'],
                'petugas_reviewer1_id' => $assignments['petugas_reviewer1_id'],
                'petugas_reviewer2_id' => $assignments['petugas_reviewer2_id'],
                'petugas_editor3_id' => $assignments['petugas_editor3_id'],
                'petugas_author2_id' => $assignments['petugas_author2_id'],
                'petugas_production_id' => $assignments['petugas_production_id'],
                'editor1_valid' => false,
                'author1_valid' => false,
                'editor2_valid' => false,
                'reviewer1_valid' => false,
                'reviewer2_valid' => false,
                'editor3_valid' => false,
                'author2_valid' => false,
                'production_valid' => false,
                'link_publish' => null,
            ]);
            
            $this->command->info("Created: {$submission->kode_submit} - Status: {$status}");
        }
        
        $this->command->info("\n✅ Successfully created 15 dummy submissions assigned to PIC: {$pic->name}");
    }
}

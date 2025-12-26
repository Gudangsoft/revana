<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RewardRedemptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing redemptions first
        RewardRedemption::truncate();
        
        // Get all reviewers and rewards
        $reviewers = User::where('role', 'reviewer')->get();
        $rewards = Reward::all();

        if ($reviewers->isEmpty() || $rewards->isEmpty()) {
            $this->command->warn('No reviewers or rewards found. Please run UserSeeder and RewardSeeder first.');
            return;
        }

        // Get rewards by tier
        $platinumRewards = $rewards->where('tier', 'Platinum');
        $goldRewards = $rewards->where('tier', 'Gold');
        $silverRewards = $rewards->where('tier', 'Silver');
        $bronzeRewards = $rewards->where('tier', 'Bronze');

        // Reviewer 1 (Dr. Ahmad) - Top Performer with ALL tier levels
        $ahmad = $reviewers->first();
        if ($ahmad) {
            // 2 Platinum rewards
            if ($platinumRewards->count() >= 2) {
                $this->createRedemption($ahmad, $platinumRewards->skip(0)->first(), 'COMPLETED');
                $this->createRedemption($ahmad, $platinumRewards->skip(1)->first(), 'COMPLETED');
            } elseif ($platinumRewards->count() == 1) {
                $this->createRedemption($ahmad, $platinumRewards->first(), 'COMPLETED');
                $this->createRedemption($ahmad, $platinumRewards->first(), 'COMPLETED');
            }
            
            // 3 Gold rewards
            if ($goldRewards->isNotEmpty()) {
                $this->createRedemption($ahmad, $goldRewards->first(), 'COMPLETED');
                $this->createRedemption($ahmad, $goldRewards->first(), 'COMPLETED');
                $this->createRedemption($ahmad, $goldRewards->first(), 'COMPLETED');
            }
            
            // 2 Silver rewards
            if ($silverRewards->isNotEmpty()) {
                $this->createRedemption($ahmad, $silverRewards->first(), 'COMPLETED');
                $this->createRedemption($ahmad, $silverRewards->first(), 'COMPLETED');
            }
            
            // 1 Bronze reward
            if ($bronzeRewards->isNotEmpty()) {
                $this->createRedemption($ahmad, $bronzeRewards->first(), 'COMPLETED');
            }
        }

        // Reviewer 2 (Dr. Siti) - High Performer (Gold, Silver, Bronze)
        if ($reviewers->count() > 1) {
            $siti = $reviewers->skip(1)->first();
            if ($siti) {
                // 2 Gold rewards
                if ($goldRewards->isNotEmpty()) {
                    $this->createRedemption($siti, $goldRewards->first(), 'COMPLETED');
                    $this->createRedemption($siti, $goldRewards->first(), 'COMPLETED');
                }
                
                // 3 Silver rewards
                if ($silverRewards->isNotEmpty()) {
                    $this->createRedemption($siti, $silverRewards->first(), 'COMPLETED');
                    $this->createRedemption($siti, $silverRewards->first(), 'COMPLETED');
                    $this->createRedemption($siti, $silverRewards->first(), 'COMPLETED');
                }
                
                // 2 Bronze rewards
                if ($bronzeRewards->isNotEmpty()) {
                    $this->createRedemption($siti, $bronzeRewards->first(), 'COMPLETED');
                    $this->createRedemption($siti, $bronzeRewards->first(), 'COMPLETED');
                }
            }
        }

        // Reviewer 3 (Dr. Budi) - Medium Performer (Silver and Bronze)
        if ($reviewers->count() > 2) {
            $budi = $reviewers->skip(2)->first();
            if ($budi) {
                // 1 Silver reward
                if ($silverRewards->isNotEmpty()) {
                    $this->createRedemption($budi, $silverRewards->first(), 'COMPLETED');
                }
                
                // 4 Bronze rewards
                if ($bronzeRewards->isNotEmpty()) {
                    $this->createRedemption($budi, $bronzeRewards->first(), 'COMPLETED');
                    $this->createRedemption($budi, $bronzeRewards->first(), 'COMPLETED');
                    $this->createRedemption($budi, $bronzeRewards->first(), 'COMPLETED');
                    $this->createRedemption($budi, $bronzeRewards->first(), 'COMPLETED');
                }
                
                // 1 Pending redemption
                if ($bronzeRewards->isNotEmpty()) {
                    $this->createRedemption($budi, $bronzeRewards->first(), 'PENDING');
                }
            }
        }

        $this->command->info('Sample reward redemptions with ALL tier levels created successfully!');
        $this->command->info('- Dr. Ahmad: 2 Platinum, 3 Gold, 2 Silver, 1 Bronze (Tier Score: 2,320)');
        $this->command->info('- Dr. Siti: 2 Gold, 3 Silver, 2 Bronze (Tier Score: 232)');
        $this->command->info('- Dr. Budi: 1 Silver, 4 Bronze (Tier Score: 14)');
    }

    private function createRedemption($user, $reward, $status)
    {
        if (!$reward) return;

        RewardRedemption::create([
            'user_id' => $user->id,
            'reward_id' => $reward->id,
            'points_used' => $reward->points_required,
            'status' => $status,
            'notes' => 'Sample redemption for testing leaderboard',
            'approved_at' => $status !== 'PENDING' ? now()->subDays(rand(1, 30)) : null,
            'completed_at' => $status === 'COMPLETED' ? now()->subDays(rand(1, 20)) : null,
        ]);
    }
}

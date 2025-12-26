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

        // Create sample redemptions for first reviewer (Dr. Ahmad) - Top performer
        $ahmad = $reviewers->first();
        if ($ahmad) {
            // Give varied rewards to show in leaderboard
            if ($goldRewards->isNotEmpty()) {
                $this->createRedemption($ahmad, $goldRewards->first(), 'COMPLETED');
            }
            if ($silverRewards->isNotEmpty()) {
                $this->createRedemption($ahmad, $silverRewards->first(), 'COMPLETED');
            }
            if ($bronzeRewards->isNotEmpty()) {
                $this->createRedemption($ahmad, $bronzeRewards->first(), 'COMPLETED');
            }
        }

        // Create sample redemptions for second reviewer (Dr. Siti)
        if ($reviewers->count() > 1) {
            $siti = $reviewers->skip(1)->first();
            if ($siti) {
                if ($silverRewards->isNotEmpty()) {
                    $this->createRedemption($siti, $silverRewards->first(), 'COMPLETED');
                }
                if ($bronzeRewards->isNotEmpty()) {
                    $this->createRedemption($siti, $bronzeRewards->first(), 'COMPLETED');
                }
            }
        }

        // Create some pending redemptions for third reviewer
        if ($reviewers->count() > 2) {
            $budi = $reviewers->skip(2)->first();
            if ($budi && $bronzeRewards->isNotEmpty()) {
                $this->createRedemption($budi, $bronzeRewards->first(), 'PENDING');
            }
        }

        $this->command->info('Sample reward redemptions created successfully!');
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

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Database\Seeder;

class RewardRedemptionSeeder extends Seeder
{
    public function run(): void
    {
        // Get sample users and rewards
        $reviewers = User::where('role', 'reviewer')->take(3)->get();
        $rewards = Reward::all();

        if ($reviewers->isEmpty() || $rewards->isEmpty()) {
            $this->command->warn('No reviewers or rewards found. Skipping RewardRedemptionSeeder.');
            return;
        }

        $rewardUang100k = $rewards->where('type', 'UANG')->where('points_required', 100)->first();
        $rewardUang250k = $rewards->where('type', 'UANG')->where('points_required', 250)->first();
        $rewardGratisSubmit = $rewards->where('type', 'GRATIS_SUBMIT')->where('points_required', 200)->first();

        // Reviewer 1 - Redemption COMPLETED dengan bukti transfer
        if ($reviewers->count() > 0 && $rewardUang100k) {
            RewardRedemption::create([
                'user_id' => $reviewers[0]->id,
                'reward_id' => $rewardUang100k->id,
                'points_used' => $rewardUang100k->points_required,
                'status' => 'COMPLETED',
                'notes' => 'Rekening BCA: 1234567890 a/n ' . $reviewers[0]->name,
                'admin_notes' => 'Transfer telah dilakukan',
                'approved_at' => now()->subDays(5),
                'completed_at' => now()->subDays(3),
                'proof_url' => 'https://example.com/bukti-transfer/TRX001.jpg',
                'proof_description' => 'Transfer telah dilakukan melalui BCA Mobile ke rekening 1234567890 atas nama ' . $reviewers[0]->name . ' sebesar Rp 100.000 pada tanggal ' . now()->subDays(3)->format('d M Y'),
            ]);
        }

        // Reviewer 2 - Redemption COMPLETED dengan bukti jurnal terbit
        if ($reviewers->count() > 1 && $rewardGratisSubmit) {
            RewardRedemption::create([
                'user_id' => $reviewers[1]->id,
                'reward_id' => $rewardGratisSubmit->id,
                'points_used' => $rewardGratisSubmit->points_required,
                'status' => 'COMPLETED',
                'notes' => 'Artikel tentang "Machine Learning in Healthcare" untuk Jurnal Teknik Informatika',
                'admin_notes' => 'Artikel telah terbit',
                'approved_at' => now()->subDays(10),
                'completed_at' => now()->subDays(2),
                'proof_url' => 'https://journal-example.com/volume5/issue2/article-ml-healthcare',
                'proof_description' => 'Artikel telah terbit di Jurnal Teknik Informatika Vol. 5 No. 2 tahun 2026. Link artikel: https://journal-example.com/volume5/issue2/article-ml-healthcare',
            ]);
        }

        // Reviewer 3 - Redemption APPROVED (belum selesai, menunggu bukti)
        if ($reviewers->count() > 2 && $rewardUang250k) {
            RewardRedemption::create([
                'user_id' => $reviewers[2]->id,
                'reward_id' => $rewardUang250k->id,
                'points_used' => $rewardUang250k->points_required,
                'status' => 'APPROVED',
                'notes' => 'Rekening Mandiri: 9876543210 a/n ' . $reviewers[2]->name,
                'approved_at' => now()->subDays(1),
            ]);
        }

        // Reviewer 1 - Redemption PENDING (baru diajukan)
        if ($reviewers->count() > 0 && $rewardUang250k) {
            RewardRedemption::create([
                'user_id' => $reviewers[0]->id,
                'reward_id' => $rewardUang250k->id,
                'points_used' => $rewardUang250k->points_required,
                'status' => 'PENDING',
                'notes' => 'Rekening BRI: 5555666677778888 a/n ' . $reviewers[0]->name,
            ]);
        }

        // Reviewer 2 - Redemption REJECTED (ditolak dengan alasan)
        if ($reviewers->count() > 1 && $rewardUang100k) {
            RewardRedemption::create([
                'user_id' => $reviewers[1]->id,
                'reward_id' => $rewardUang100k->id,
                'points_used' => $rewardUang100k->points_required,
                'status' => 'REJECTED',
                'notes' => 'Rekening BNI: 1111222233334444 a/n John Doe',
                'admin_notes' => 'Nomor rekening tidak sesuai dengan nama reviewer. Mohon kirimkan ulang dengan rekening atas nama sendiri.',
            ]);
        }

        $this->command->info('RewardRedemption dummy data created successfully!');
    }
}

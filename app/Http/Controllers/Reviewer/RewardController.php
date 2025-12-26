<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rewards = Reward::where('is_active', true)->get();
        $myRedemptions = RewardRedemption::where('user_id', $user->id)
            ->with('reward')
            ->latest()
            ->get();

        return view('reviewer.rewards.index', compact('rewards', 'myRedemptions', 'user'));
    }

    public function redeem(Request $request, Reward $reward)
    {
        $user = auth()->user();

        if (!$reward->is_active) {
            return back()->with('error', 'Reward tidak tersedia');
        }

        if ($user->available_points < $reward->points_required) {
            return back()->with('error', 'Point tidak cukup');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        // Deduct points
        $user->decrement('available_points', $reward->points_required);

        // Create redemption
        RewardRedemption::create([
            'user_id' => $user->id,
            'reward_id' => $reward->id,
            'points_used' => $reward->points_required,
            'status' => 'PENDING',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create point history
        \App\Models\PointHistory::create([
            'user_id' => $user->id,
            'points' => -$reward->points_required,
            'type' => 'REDEEMED',
            'description' => "Penukaran reward: {$reward->name}",
        ]);

        return back()->with('success', 'Reward berhasil ditukar. Menunggu persetujuan admin.');
    }
}

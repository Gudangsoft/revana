<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;

class RewardRedemptionController extends Controller
{
    public function index()
    {
        $redemptions = RewardRedemption::with(['user', 'reward'])
            ->latest()
            ->paginate(20);

        return view('admin.redemptions.index', compact('redemptions'));
    }

    public function show(RewardRedemption $redemption)
    {
        $redemption->load(['user', 'reward']);
        return view('admin.redemptions.show', compact('redemption'));
    }

    public function approve(RewardRedemption $redemption)
    {
        if ($redemption->status !== 'PENDING') {
            return back()->with('error', 'Redemption tidak dalam status pending');
        }

        $redemption->approve();

        return back()->with('success', 'Redemption telah disetujui');
    }

    public function complete(RewardRedemption $redemption)
    {
        if ($redemption->status !== 'APPROVED') {
            return back()->with('error', 'Redemption belum disetujui');
        }

        $redemption->complete();

        return back()->with('success', 'Redemption telah diselesaikan');
    }

    public function reject(Request $request, RewardRedemption $redemption)
    {
        if ($redemption->status !== 'PENDING') {
            return back()->with('error', 'Redemption tidak dalam status pending');
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string',
        ]);

        $redemption->reject($validated['admin_notes']);

        return back()->with('success', 'Redemption telah ditolak dan point dikembalikan');
    }
}

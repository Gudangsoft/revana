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

    public function approve(Request $request, RewardRedemption $redemption)
    {
        if ($redemption->status !== 'PENDING') {
            return back()->with('error', 'Redemption tidak dalam status pending');
        }

        $redemption->approve();

        return back()->with('success', 'Redemption telah disetujui. Silakan upload bukti untuk menyelesaikan.');
    }

    public function complete(Request $request, RewardRedemption $redemption)
    {
        if ($redemption->status !== 'APPROVED') {
            return back()->with('error', 'Redemption belum disetujui');
        }

        $validated = $request->validate([
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'proof_url' => 'nullable|url',
            'proof_description' => 'required|string|min:10',
        ]);

        // Upload file if exists
        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('proofs', $filename, 'public');
            $validated['proof_file'] = $path;
        }

        $redemption->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
            'proof_file' => $validated['proof_file'] ?? null,
            'proof_url' => $validated['proof_url'] ?? null,
            'proof_description' => $validated['proof_description'],
        ]);

        return back()->with('success', 'Redemption telah diselesaikan dengan bukti');
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

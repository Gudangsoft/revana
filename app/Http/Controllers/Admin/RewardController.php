<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function index()
    {
        $rewards = Reward::withCount('redemptions')
            ->latest()
            ->paginate(20);

        return view('admin.rewards.index', compact('rewards'));
    }

    public function create()
    {
        return view('admin.rewards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'points_required' => 'required|integer|min:1',
            'value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Reward::create($validated);

        return redirect()->route('admin.rewards.index')
            ->with('success', 'Reward berhasil ditambahkan');
    }

    public function edit(Reward $reward)
    {
        return view('admin.rewards.edit', compact('reward'));
    }

    public function update(Request $request, Reward $reward)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:100',
            'points_required' => 'required|integer|min:1',
            'value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $reward->update($validated);

        return redirect()->route('admin.rewards.index')
            ->with('success', 'Reward berhasil diupdate');
    }

    public function destroy(Reward $reward)
    {
        if ($reward->redemptions()->count() > 0) {
            return back()->with('error', 'Reward tidak bisa dihapus karena sudah ada yang menukarkan');
        }

        $reward->delete();

        return redirect()->route('admin.rewards.index')
            ->with('success', 'Reward berhasil dihapus');
    }

    public function toggleStatus(Reward $reward)
    {
        $reward->update(['is_active' => !$reward->is_active]);

        $status = $reward->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Reward berhasil {$status}");
    }
}

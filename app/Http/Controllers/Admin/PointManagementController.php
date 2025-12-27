<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Http\Request;

class PointManagementController extends Controller
{
    public function index()
    {
        $pointHistories = PointHistory::with(['user'])
            ->latest()
            ->paginate(20);

        return view('admin.points.index', compact('pointHistories'));
    }

    public function create()
    {
        $reviewers = User::where('role', 'reviewer')->get();
        return view('admin.points.create', compact('reviewers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'points' => 'required|integer|min:1',
            'type' => 'required|in:EARNED,REDEEMED',
            'description' => 'required|string',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Create point history
        PointHistory::create([
            'user_id' => $validated['user_id'],
            'points' => $validated['points'],
            'type' => $validated['type'],
            'description' => $validated['description'],
        ]);

        // Update user points
        if ($validated['type'] === 'EARNED') {
            $user->increment('total_points', $validated['points']);
            $user->increment('available_points', $validated['points']);
            $message = 'Points berhasil ditambahkan';
        } else {
            // REDEEMED
            if ($user->available_points < $validated['points']) {
                return back()->with('error', 'Available points reviewer tidak mencukupi');
            }
            $user->decrement('available_points', $validated['points']);
            $message = 'Points berhasil dikurangi';
        }

        return redirect()->route('admin.points.index')
            ->with('success', $message);
    }

    public function destroy(PointHistory $point)
    {
        // Reverse the point transaction
        if ($point->type === 'EARNED') {
            $point->user->decrement('total_points', $point->points);
            $point->user->decrement('available_points', $point->points);
        } else {
            $point->user->increment('available_points', $point->points);
        }

        $point->delete();

        return redirect()->route('admin.points.index')
            ->with('success', 'Point history berhasil dihapus dan points telah dikembalikan');
    }
}

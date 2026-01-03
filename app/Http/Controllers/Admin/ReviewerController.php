<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewerController extends Controller
{
    public function index()
    {
        $reviewers = User::where('role', 'reviewer')
            ->with('badges')
            ->withCount('reviewAssignments')
            ->latest()
            ->paginate(20);

        return view('admin.reviewers.index', compact('reviewers'));
    }

    public function show(User $reviewer)
    {
        if (!$reviewer->isReviewer()) {
            abort(404);
        }

        $reviewer->load([
            'badges', 
            'reviewAssignments.journal',
            'reviewAssignments.reviewResult',
            'pointHistories'
        ]);
        
        return view('admin.reviewers.show', compact('reviewer'));
    }

    public function edit(User $reviewer)
    {
        if (!$reviewer->isReviewer()) {
            abort(404);
        }

        return view('admin.reviewers.edit', compact('reviewer'));
    }

    public function update(Request $request, User $reviewer)
    {
        if (!$reviewer->isReviewer()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $reviewer->id,
            'phone' => 'nullable|string|max:20',
            'institution' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:100',
            'education_level' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'nidn' => 'nullable|string|max:50',
            'google_scholar' => 'nullable|url',
            'sinta_id' => 'nullable|string|max:100',
            'scopus_id' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'article_languages' => 'nullable|array',
            'article_languages.*' => 'in:Indonesia,English',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Update data
        $reviewer->name = $validated['name'];
        $reviewer->email = $validated['email'];
        $reviewer->phone = $validated['phone'] ?? null;
        $reviewer->institution = $validated['institution'] ?? null;
        $reviewer->position = $validated['position'] ?? null;
        $reviewer->education_level = $validated['education_level'] ?? null;
        $reviewer->specialization = $validated['specialization'] ?? null;
        $reviewer->address = $validated['address'] ?? null;
        $reviewer->nidn = $validated['nidn'] ?? null;
        $reviewer->google_scholar = $validated['google_scholar'] ?? null;
        $reviewer->sinta_id = $validated['sinta_id'] ?? null;
        $reviewer->scopus_id = $validated['scopus_id'] ?? null;
        $reviewer->bio = $validated['bio'] ?? null;
        $reviewer->article_languages = $validated['article_languages'] ?? null;

        // Update password if provided
        if ($request->filled('password')) {
            $reviewer->password = bcrypt($validated['password']);
        }

        $reviewer->save();

        return redirect()->route('admin.reviewers.show', $reviewer)
            ->with('success', 'Data reviewer berhasil diupdate.');
    }

    public function resetPassword(Request $request, User $reviewer)
    {
        if (!$reviewer->isReviewer()) {
            abort(404);
        }

        $validated = $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);

        $reviewer->password = bcrypt($validated['new_password']);
        $reviewer->save();

        return redirect()->route('admin.reviewers.show', $reviewer)
            ->with('success', 'Password reviewer berhasil direset.');
    }
}

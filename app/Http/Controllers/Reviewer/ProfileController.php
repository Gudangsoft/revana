<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('reviewer.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'institution' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'education_level' => 'nullable|in:S1,S2,S3',
            'specialization' => 'nullable|string',
            'address' => 'nullable|string',
            'nidn' => 'nullable|string|max:50',
            'google_scholar' => 'nullable|url',
            'sinta_id' => 'nullable|string|max:50',
            'scopus_id' => 'nullable|string|max:50',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            $photoPath = $request->file('photo')->store('profile-photos', 'public');
            $validated['photo'] = $photoPath;
        }

        // Handle signature upload
        if ($request->hasFile('signature')) {
            // Delete old signature if exists
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature);
            }

            $signaturePath = $request->file('signature')->store('signatures', 'public');
            $validated['signature'] = $signaturePath;
        }

        $user->update($validated);

        return redirect()->route('reviewer.profile.edit')
            ->with('success', 'Profile berhasil diupdate');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        // Check current password
        if (!\Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        $user->password = bcrypt($validated['new_password']);
        $user->save();

        return redirect()->route('reviewer.profile.edit')
            ->with('success', 'Password berhasil diubah.');
    }
}

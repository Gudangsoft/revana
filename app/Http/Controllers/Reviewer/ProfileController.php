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
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^(0|62)[0-9]{9,13}$/'],
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
        ], [
            'phone.regex' => 'Format nomor WhatsApp tidak valid. Gunakan format 08xxx atau 628xxx (contoh: 081234567890)'
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Validate file more strictly
            $file = $request->file('photo');
            if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                return back()->withErrors(['photo' => 'Format file tidak valid. Hanya JPG, JPEG, PNG yang diperbolehkan.']);
            }

            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            // Generate random filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'photo_' . $user->id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $photoPath = $file->storeAs('profile-photos', $filename, 'public');
            $validated['photo'] = $photoPath;
        }

        // Handle signature upload
        if ($request->hasFile('signature')) {
            // Validate file more strictly
            $file = $request->file('signature');
            if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                return back()->withErrors(['signature' => 'Format file tidak valid. Hanya JPG, JPEG, PNG yang diperbolehkan.']);
            }

            // Delete old signature if exists
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature);
            }

            // Generate random filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'signature_' . $user->id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $signaturePath = $file->storeAs('signatures', $filename, 'public');
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
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // At least 1 lowercase, 1 uppercase, 1 number
            ],
        ], [
            'new_password.regex' => 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, dan 1 angka.'
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

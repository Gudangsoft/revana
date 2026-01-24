<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $pic = Auth::guard('pic')->user();
        return view('pic.profile.edit', compact('pic'));
    }

    public function update(Request $request)
    {
        $pic = Auth::guard('pic')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:pics,username,' . $pic->id,
            'email' => 'required|email|max:255|unique:pics,email,' . $pic->id,
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // 2MB max
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($pic->photo && Storage::disk('public')->exists($pic->photo)) {
                Storage::disk('public')->delete($pic->photo);
            }
            
            $file = $request->file('photo');
            $filename = 'pic_' . $pic->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('photos/pics', $filename, 'public');
            $validated['photo'] = $path;
        }

        $pic->update($validated);

        return redirect()->route('pic.profile.edit')
            ->with('success', 'Profile berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $pic = Auth::guard('pic')->user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini harus diisi',
            'new_password.required' => 'Password baru harus diisi',
            'new_password.min' => 'Password baru minimal 6 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        // Check if current password is correct
        if (!Hash::check($validated['current_password'], $pic->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        // Update password
        $pic->password = Hash::make($validated['new_password']);
        $pic->save();

        return redirect()->route('pic.profile.edit')
            ->with('success', 'Password berhasil diubah');
    }
}

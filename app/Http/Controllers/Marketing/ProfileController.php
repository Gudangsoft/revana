<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $marketing = Auth::guard('marketing')->user();
        return view('marketing.profile.edit', compact('marketing'));
    }

    public function update(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:marketings,username,' . $marketing->id,
            'email' => 'required|email|max:255|unique:marketings,email,' . $marketing->id,
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // 2MB max
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($marketing->photo && Storage::disk('public')->exists($marketing->photo)) {
                Storage::disk('public')->delete($marketing->photo);
            }
            
            $file = $request->file('photo');
            $filename = 'marketing_' . $marketing->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('photos/marketings', $filename, 'public');
            $validated['photo'] = $path;
        }

        $marketing->update($validated);

        return redirect()->route('marketing.profile.edit')
            ->with('success', 'Profile berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $marketing = Auth::guard('marketing')->user();
        
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
        if (!Hash::check($validated['current_password'], $marketing->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        // Update password
        $marketing->password = Hash::make($validated['new_password']);
        $marketing->save();

        return redirect()->route('marketing.profile.edit')
            ->with('success', 'Password berhasil diubah');
    }
}

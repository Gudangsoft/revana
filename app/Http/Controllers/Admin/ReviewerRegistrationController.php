<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReviewerRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ReviewerRegistrationController extends Controller
{
    public function index()
    {
        $registrations = ReviewerRegistration::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.reviewer-registrations.index', compact('registrations'));
    }

    public function show(ReviewerRegistration $registration)
    {
        return view('admin.reviewer-registrations.show', compact('registration'));
    }

    public function approve(ReviewerRegistration $registration)
    {
        // Create user account from registration
        $user = User::create([
            'name' => $registration->full_name,
            'email' => $registration->email,
            'password' => $registration->password, // Already hashed
            'role' => 'reviewer',
            'phone' => $registration->whatsapp,
            'institution' => $registration->affiliation,
            'specialization' => $registration->field_of_study,
            'sinta_id' => $registration->sinta_id,
            'scopus_id' => $registration->scopus_id,
            'article_languages' => $registration->article_languages,
            'total_points' => 0,
            'available_points' => 0,
            'completed_reviews' => 0,
        ]);

        // Update registration status
        $registration->update([
            'status' => 'approved',
            'notes' => 'Pendaftaran disetujui dan akun reviewer telah dibuat.'
        ]);

        return redirect()->route('admin.reviewer-registrations.index')
            ->with('success', 'Pendaftaran reviewer berhasil disetujui dan akun telah dibuat.');
    }

    public function reject(Request $request, ReviewerRegistration $registration)
    {
        $request->validate([
            'notes' => 'required|string|max:500'
        ]);

        $registration->update([
            'status' => 'rejected',
            'notes' => $request->notes
        ]);

        return redirect()->route('admin.reviewer-registrations.index')
            ->with('success', 'Pendaftaran reviewer telah ditolak.');
    }

    public function destroy(ReviewerRegistration $registration)
    {
        $registration->delete();
        
        return redirect()->route('admin.reviewer-registrations.index')
            ->with('success', 'Data pendaftaran berhasil dihapus.');
    }
}

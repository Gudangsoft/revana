<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReviewerRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Setting;
use App\Models\FieldOfStudy;

class ReviewerRegistrationController extends Controller
{
    public function showForm()
    {
        $fieldOfStudies = FieldOfStudy::active()->ordered()->get();
        return view('reviewer-registration.form', compact('fieldOfStudies'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'affiliation' => 'required|string|max:255',
            'email' => 'required|email|unique:reviewer_registrations,email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'scopus_id' => 'nullable|string|max:100',
            'sinta_id' => 'required|string|max:100',
            'whatsapp' => 'required|string|max:20',
            'field_of_study_id' => 'required|exists:field_of_studies,id',
            'article_languages' => 'required|array|min:1',
            'article_languages.*' => 'in:Indonesia,English',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Hash password
        $hashedPassword = Hash::make($request->password);

        // Get field of study name for specialization
        $fieldOfStudy = FieldOfStudy::findOrFail($request->field_of_study_id);

        // Create user account immediately (auto-approve)
        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => $hashedPassword,
            'role' => 'reviewer',
            'phone' => $request->whatsapp,
            'institution' => $request->affiliation,
            'specialization' => $fieldOfStudy->name,
            'field_of_study_id' => $request->field_of_study_id,
            'sinta_id' => $request->sinta_id,
            'scopus_id' => $request->scopus_id,
            'article_languages' => $request->article_languages,
            'total_points' => 0,
            'available_points' => 0,
            'completed_reviews' => 0,
        ]);

        // Save registration record with auto-approved status
        $registration = ReviewerRegistration::create([
            'full_name' => $request->full_name,
            'affiliation' => $request->affiliation,
            'email' => $request->email,
            'password' => $hashedPassword,
            'scopus_id' => $request->scopus_id,
            'sinta_id' => $request->sinta_id,
            'whatsapp' => $request->whatsapp,
            'field_of_study' => $fieldOfStudy->name,
            'field_of_study_id' => $request->field_of_study_id,
            'article_languages' => $request->article_languages,
            'status' => 'approved',
            'notes' => 'Otomatis disetujui - Akun reviewer telah dibuat.',
        ]);

        // Redirect to login page with success message
        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Akun reviewer Anda sudah aktif. Silakan login dengan email dan password yang telah Anda daftarkan.');
    }

    public function thankYou()
    {
        return view('reviewer-registration.thank-you');
    }
}

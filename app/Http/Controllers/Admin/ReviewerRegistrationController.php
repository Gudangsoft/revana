<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReviewerRegistration;
use App\Models\FieldOfStudy;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ReviewerRegistrationController extends Controller
{
    /**
     * Show the public reviewer registration form
     */
    public function showForm()
    {
        $fieldOfStudies = FieldOfStudy::active()->ordered()->get();

        // Generate simple math CAPTCHA
        $captchaNum1 = rand(1, 20);
        $captchaNum2 = rand(1, 10);
        $captchaAnswer = $captchaNum1 + $captchaNum2;
        session(['captcha_answer' => $captchaAnswer]);

        return view('reviewer-registration.form', compact('fieldOfStudies', 'captchaNum1', 'captchaNum2'));
    }

    /**
     * Store a new reviewer registration from the public form
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:reviewer_registrations,email|unique:users,email',
            'affiliation' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'field_of_study_id' => 'required|exists:field_of_studies,id',
            'sinta_id' => 'required|string|max:50',
            'scopus_id' => 'nullable|string|max:50',
            'article_languages' => 'required|array|min:1',
            'article_languages.*' => 'in:Indonesia,English',
            'captcha' => 'required|numeric',
        ]);

        // Validate CAPTCHA answer
        if ((int) $validated['captcha'] !== (int) session('captcha_answer')) {
            return back()->withErrors(['captcha' => __('reviewer.captcha_wrong')])->withInput();
        }

        // Clear captcha from session
        session()->forget('captcha_answer');

        $fieldOfStudy = FieldOfStudy::find($validated['field_of_study_id']);

        $registration = ReviewerRegistration::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'affiliation' => $validated['affiliation'],
            'whatsapp' => $validated['whatsapp'],
            'password' => Hash::make($validated['password']),
            'field_of_study' => $fieldOfStudy ? $fieldOfStudy->name : '',
            'field_of_study_id' => $validated['field_of_study_id'],
            'sinta_id' => $validated['sinta_id'],
            'scopus_id' => $validated['scopus_id'] ?? null,
            'article_languages' => $validated['article_languages'],
            'status' => 'pending',
        ]);

        // Build WhatsApp confirmation URL if configured
        $whatsappUrl = null;
        try {
            $adminPhone = \App\Models\Setting::get('fonnte_admin_phone') ?? \App\Models\Setting::get('admin_whatsapp');
            if ($adminPhone) {
                $message = "Konfirmasi Pendaftaran Reviewer\n\n"
                    . "Nama: {$registration->full_name}\n"
                    . "Email: {$registration->email}\n"
                    . "Institusi: {$registration->affiliation}\n"
                    . "Bidang: {$registration->field_of_study}\n\n"
                    . "Saya telah mendaftar sebagai reviewer melalui portal SIPERA.";
                $whatsappUrl = "https://wa.me/{$adminPhone}?text=" . urlencode($message);
            }
        } catch (\Exception $e) {
            // Silently skip WhatsApp URL generation
        }

        return redirect()->route('reviewer-registration.form')
            ->with('success', __('reviewer.registration_success'))
            ->with('whatsapp_url', $whatsappUrl);
    }

    public function index()
    {
        // Only show pending and rejected registrations
        // Approved ones should be viewed in reviewers page
        $registrations = ReviewerRegistration::whereIn('status', ['pending', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->paginate(request()->input('per_page', 15));
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

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array|min:1',
            'registration_ids.*' => 'exists:reviewer_registrations,id'
        ]);

        $registrationIds = $request->registration_ids;
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($registrationIds as $id) {
            try {
                $registration = ReviewerRegistration::findOrFail($id);
                
                // Skip if not pending
                if ($registration->status !== 'pending') {
                    $failedCount++;
                    $errors[] = "Pendaftaran {$registration->full_name} sudah {$registration->status}";
                    continue;
                }

                // Check if email already exists
                if (User::where('email', $registration->email)->exists()) {
                    $failedCount++;
                    $errors[] = "Email {$registration->email} sudah terdaftar";
                    continue;
                }

                // Create user account from registration
                User::create([
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
                    'notes' => 'Pendaftaran disetujui melalui bulk approve dan akun reviewer telah dibuat.'
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = "Error saat approve {$registration->full_name}: " . $e->getMessage();
            }
        }

        $message = "Berhasil approve {$successCount} pendaftaran.";
        if ($failedCount > 0) {
            $message .= " {$failedCount} gagal.";
            if (!empty($errors)) {
                $message .= " Detail: " . implode(', ', array_slice($errors, 0, 3));
            }
        }

        return redirect()->route('admin.reviewer-registrations.index')
            ->with('success', $message);
    }
}

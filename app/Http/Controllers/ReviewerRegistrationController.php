<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReviewerRegistration;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Setting;

class ReviewerRegistrationController extends Controller
{
    public function showForm()
    {
        return view('reviewer-registration.form');
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
            'field_of_study' => 'required|string|max:255',
            'article_languages' => 'required|array|min:1',
            'article_languages.*' => 'in:Indonesia,English',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $registration = ReviewerRegistration::create([
            'full_name' => $request->full_name,
            'affiliation' => $request->affiliation,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'scopus_id' => $request->scopus_id,
            'sinta_id' => $request->sinta_id,
            'whatsapp' => $request->whatsapp,
            'field_of_study' => $request->field_of_study,
            'article_languages' => $request->article_languages,
            'status' => 'pending',
        ]);

        // Get admin contact from settings
        $adminContact = Setting::get('contact');
        $appName = Setting::get('app_name', 'System');
        
        if ($adminContact) {
            // Clean phone number
            $cleanPhone = preg_replace('/[^0-9]/', '', $adminContact);
            
            // Prepare WhatsApp message
            $languages = implode(', ', $request->article_languages);
            $message = "Halo Admin {$appName},%0A%0A"
                     . "Saya baru saja mendaftar sebagai reviewer dengan data:%0A%0A"
                     . "Nama: {$request->full_name}%0A"
                     . "Email: {$request->email}%0A"
                     . "Institusi: {$request->affiliation}%0A"
                     . "Bidang Ilmu: {$request->field_of_study}%0A"
                     . "Bahasa: {$languages}%0A"
                     . "SINTA ID: {$request->sinta_id}%0A%0A"
                     . "Mohon untuk memproses pendaftaran saya.%0A%0A"
                     . "Terima kasih.";
            
            // Redirect to WhatsApp with success message
            return redirect()->back()
                ->with('success', 'Pendaftaran reviewer berhasil!')
                ->with('whatsapp_url', "https://wa.me/{$cleanPhone}?text={$message}");
        }

        return redirect()->back()->with('success', 'Pendaftaran reviewer berhasil! Kami akan menghubungi Anda segera.');
    }

    public function thankYou()
    {
        return view('reviewer-registration.thank-you');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        // Baca file .env
        $settings = [
            'app_name' => Setting::get('app_name', env('APP_NAME', 'REVANA')),
            'full_name' => Setting::get('full_name', ''),
            'app_url' => env('APP_URL', 'http://localhost'),
            'mail_host' => env('MAIL_HOST', ''),
            'mail_port' => env('MAIL_PORT', '465'),
            'mail_username' => env('MAIL_USERNAME', ''),
            'mail_password' => env('MAIL_PASSWORD', ''),
            'mail_encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', ''),
            'mail_from_name' => env('MAIL_FROM_NAME', ''),
            // Database settings
            'tagline' => Setting::get('tagline', ''),
            'address' => Setting::get('address', ''),
            'contact' => Setting::get('contact', ''),
            'whatsapp_confirmation_number' => Setting::get('whatsapp_confirmation_number', ''),
            'logo' => Setting::get('logo', ''),
            'favicon' => Setting::get('favicon', ''),
            'certificate_template' => Setting::get('certificate_template', ''),
        ];
        
        return view('admin.settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'full_name' => 'nullable|string|max:500',
            'app_url' => 'required|url',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'required|string|max:255',
            'mail_encryption' => 'required|in:ssl,tls',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'address' => 'nullable|string|max:1000',
            'contact' => 'nullable|string|max:500',
            'whatsapp_confirmation_number' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,svg,ico|max:512',
            'certificate_template' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);
        
        // Update .env settings
        $envPath = base_path('.env');
        $envContent = File::get($envPath);
        
        $envContent = preg_replace(
            '/^APP_NAME=.*/m',
            'APP_NAME="' . $validated['app_name'] . '"',
            $envContent
        );
        
        $envContent = preg_replace(
            '/^APP_URL=.*/m',
            'APP_URL=' . $validated['app_url'],
            $envContent
        );
        
        if (isset($validated['mail_from_address'])) {
            $envContent = preg_replace(
                '/^MAIL_FROM_ADDRESS=.*/m',
                'MAIL_FROM_ADDRESS=' . $validated['mail_from_address'],
                $envContent
            );
        }
        
        if (isset($validated['mail_from_name'])) {
            $envContent = preg_replace(
                '/^MAIL_FROM_NAME=.*/m',
                'MAIL_FROM_NAME="' . $validated['mail_from_name'] . '"',
                $envContent
            );
        }
        
        $envContent = preg_replace(
            '/^MAIL_HOST=.*/m',
            'MAIL_HOST=' . $validated['mail_host'],
            $envContent
        );
        
        $envContent = preg_replace(
            '/^MAIL_PORT=.*/m',
            'MAIL_PORT=' . $validated['mail_port'],
            $envContent
        );
        
        $envContent = preg_replace(
            '/^MAIL_USERNAME=.*/m',
            'MAIL_USERNAME=' . $validated['mail_username'],
            $envContent
        );
        
        $envContent = preg_replace(
            '/^MAIL_PASSWORD=.*/m',
            'MAIL_PASSWORD=' . $validated['mail_password'],
            $envContent
        );
        
        $envContent = preg_replace(
            '/^MAIL_ENCRYPTION=.*/m',
            'MAIL_ENCRYPTION=' . $validated['mail_encryption'],
            $envContent
        );
        
        File::put($envPath, $envContent);
        
        // Update database settings (including app_name for real-time update)
        Setting::set('app_name', $validated['app_name']);
        
        if (isset($validated['full_name'])) {
            Setting::set('full_name', $validated['full_name']);
        }
        
        if (isset($validated['tagline'])) {
            Setting::set('tagline', $validated['tagline']);
        }
        
        if (isset($validated['address'])) {
            Setting::set('address', $validated['address']);
        }
        
        if (isset($validated['contact'])) {
            Setting::set('contact', $validated['contact']);
        }
        
        // Simpan nomor WhatsApp konfirmasi (simpan juga jika kosong)
        $whatsappNumber = $request->input('whatsapp_confirmation_number', '');
        Setting::set('whatsapp_confirmation_number', $whatsappNumber);
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            $logoPath = $request->file('logo')->store('settings', 'public');
            Setting::set('logo', $logoPath);
        }
        
        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $oldFavicon = Setting::get('favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            
            $faviconPath = $request->file('favicon')->store('settings', 'public');
            Setting::set('favicon', $faviconPath);
        }
        
        // Handle certificate template upload
        if ($request->hasFile('certificate_template')) {
            $oldTemplate = Setting::get('certificate_template');
            if ($oldTemplate && Storage::disk('public')->exists($oldTemplate)) {
                Storage::disk('public')->delete($oldTemplate);
            }
            
            $templatePath = $request->file('certificate_template')->store('settings', 'public');
            Setting::set('certificate_template', $templatePath);
        }
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting berhasil diperbarui!');
    }
    
    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        
        try {
            \Mail::raw('Ini adalah test email dari sistem SIPERA. Jika Anda menerima email ini, berarti konfigurasi email Anda berhasil!', function($message) use ($request) {
                $message->to($request->email)
                        ->subject('Test Email - SIPERA');
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Email test berhasil dikirim ke ' . $request->email . '. Silakan cek inbox atau folder spam.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email test',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

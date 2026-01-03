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
            'mail_from_address' => env('MAIL_FROM_ADDRESS', ''),
            'mail_from_name' => env('MAIL_FROM_NAME', ''),
            // Database settings
            'tagline' => Setting::get('tagline', ''),
            'address' => Setting::get('address', ''),
            'contact' => Setting::get('contact', ''),
            'whatsapp_confirmation_number' => Setting::get('whatsapp_confirmation_number', ''),
            'whatsapp_group_link' => Setting::get('whatsapp_group_link', ''),
            'logo' => Setting::get('logo', ''),
            'favicon' => Setting::get('favicon', ''),
        ];
        
        return view('admin.settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'full_name' => 'nullable|string|max:500',
            'app_url' => 'required|url',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'address' => 'nullable|string|max:1000',
            'contact' => 'nullable|string|max:500',
            'whatsapp_confirmation_number' => 'nullable|string|max:20',
            'whatsapp_group_link' => 'nullable|url|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,svg,ico|max:512',
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
        
        // Simpan link WhatsApp group
        $whatsappGroupLink = $request->input('whatsapp_group_link', '');
        Setting::set('whatsapp_group_link', $whatsappGroupLink);
        
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
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting berhasil diperbarui!');
    }
}

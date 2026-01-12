<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EmailSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'mail_host' => env('MAIL_HOST', ''),
            'mail_port' => env('MAIL_PORT', '465'),
            'mail_username' => env('MAIL_USERNAME', ''),
            'mail_password' => env('MAIL_PASSWORD', ''),
            'mail_encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', ''),
            'mail_from_name' => env('MAIL_FROM_NAME', ''),
        ];
        
        return view('admin.email-settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'required|string|max:255',
            'mail_password' => 'required|string|max:255',
            'mail_encryption' => 'required|in:ssl,tls',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:255',
        ]);
        
        // Update .env settings
        $envPath = base_path('.env');
        $envContent = File::get($envPath);
        
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
        
        // Clear config cache
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        
        return redirect()->route('admin.email-settings.index')
            ->with('success', 'Pengaturan email berhasil diperbarui!');
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

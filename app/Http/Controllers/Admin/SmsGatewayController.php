<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FonnteService;
use Illuminate\Http\Request;

class SmsGatewayController extends Controller
{
    public function index()
    {
        $settings = [
            'fonnte_api_token' => Setting::get('fonnte_api_token', ''),
            'fonnte_device_id' => Setting::get('fonnte_device_id', ''),
            'sms_gateway_enabled' => Setting::get('sms_gateway_enabled', '0'),
            'sms_notification_submit' => Setting::get('sms_notification_submit', '0'),
            'sms_notification_status_change' => Setting::get('sms_notification_status_change', '0'),
            'sms_notification_published' => Setting::get('sms_notification_published', '0'),
            'sms_default_country_code' => Setting::get('sms_default_country_code', '62'),
            'sms_template_submit' => Setting::get('sms_template_submit', "Halo {nama_penulis},\n\nArtikel Anda \"{judul_artikel}\" telah berhasil disubmit dengan kode: {kode_submit}.\n\nTerima kasih,\n{app_name}"),
            'sms_template_status_change' => Setting::get('sms_template_status_change', "Halo {nama_penulis},\n\nStatus artikel \"{judul_artikel}\" ({kode_submit}) telah diupdate menjadi: {status}.\n\nTerima kasih,\n{app_name}"),
            'sms_template_published' => Setting::get('sms_template_published', "Halo {nama_penulis},\n\nSelamat! Artikel \"{judul_artikel}\" ({kode_submit}) telah berhasil dipublikasikan.\n\nLink: {link_publish}\n\nTerima kasih,\n{app_name}"),
        ];

        return view('admin.sms-gateway.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'fonnte_api_token' => 'nullable|string|max:500',
            'fonnte_device_id' => 'nullable|string|max:255',
            'sms_gateway_enabled' => 'nullable|in:0,1',
            'sms_notification_submit' => 'nullable|in:0,1',
            'sms_notification_status_change' => 'nullable|in:0,1',
            'sms_notification_published' => 'nullable|in:0,1',
            'sms_default_country_code' => 'nullable|string|max:5',
            'sms_template_submit' => 'nullable|string|max:2000',
            'sms_template_status_change' => 'nullable|string|max:2000',
            'sms_template_published' => 'nullable|string|max:2000',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '');
        }

        // Explicitly set checkboxes that might not be present
        Setting::set('sms_gateway_enabled', $request->input('sms_gateway_enabled', '0'));
        Setting::set('sms_notification_submit', $request->input('sms_notification_submit', '0'));
        Setting::set('sms_notification_status_change', $request->input('sms_notification_status_change', '0'));
        Setting::set('sms_notification_published', $request->input('sms_notification_published', '0'));

        return redirect()->route('admin.sms-gateway.index')
            ->with('success', 'Pengaturan SMS Gateway berhasil disimpan!');
    }

    /**
     * Check Fonnte device/connection status
     */
    public function checkStatus()
    {
        $fonnte = new FonnteService();

        if (!$fonnte->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'API Token belum dikonfigurasi. Silakan simpan token terlebih dahulu.',
            ]);
        }

        $result = $fonnte->getDeviceStatus();

        return response()->json($result);
    }

    /**
     * Send a test WhatsApp message
     */
    public function testSend(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        $fonnte = new FonnteService();

        if (!$fonnte->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'API Token belum dikonfigurasi. Silakan simpan token terlebih dahulu.',
            ]);
        }

        $result = $fonnte->send(
            $request->phone,
            $request->message,
            ['countryCode' => Setting::get('sms_default_country_code', '62')]
        );

        return response()->json($result);
    }
}

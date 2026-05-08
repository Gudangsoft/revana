<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FonnteService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SmsGatewayController extends Controller
{
    /**
     * Ensure the settings table has the required key/value columns
     */
    private function ensureSettingsTableReady(): void
    {
        // Clear schema cache to get fresh column info
        \Illuminate\Support\Facades\DB::statement('SELECT 1');

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
            Log::info('SMS Gateway: Created settings table');
            return;
        }

        if (!Schema::hasColumn('settings', 'key')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('key')->unique()->after('id');
            });
            Log::info('SMS Gateway: Added key column to settings table');
        }

        if (!Schema::hasColumn('settings', 'value')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('value')->nullable()->after('key');
            });
            Log::info('SMS Gateway: Added value column to settings table');
        }
    }

    public function index()
    {
        // Ensure database table is ready
        $this->ensureSettingsTableReady();

        $keys = [
            'fonnte_api_token', 'fonnte_device_id', 'sms_gateway_enabled',
            'sms_notification_submit', 'sms_notification_status_change', 'sms_notification_published',
            'sms_default_country_code', 'sms_template_submit', 'sms_template_status_change', 'sms_template_published',
            'wa_template_credential_new', 'wa_template_credential_update',
        ];

        // Read all SMS/Fonnte settings from DB via Eloquent model (consistent with FonnteService)
        $dbSettings = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();

        // Auto-seed semua template jika belum ada atau kosong di DB
        $templateDefaults = [
            'sms_template_submit'           => "Halo {nama_penulis},\n\nArtikel Anda \"{judul_artikel}\" telah berhasil disubmit dengan kode: {kode_submit}.\n\nTerima kasih,\n{app_name}",
            'sms_template_status_change'    => "Halo {nama_penulis},\n\nStatus artikel \"{judul_artikel}\" ({kode_submit}) telah diupdate menjadi: {status}.\n\nTerima kasih,\n{app_name}",
            'sms_template_published'        => "Halo {nama_penulis},\n\nSelamat! Artikel \"{judul_artikel}\" ({kode_submit}) telah berhasil dipublikasikan.\n\nLink: {link_publish}\n\nTerima kasih,\n{app_name}",
            'wa_template_credential_new'    => self::defaultCredentialNewTemplate(),
            'wa_template_credential_update' => self::defaultCredentialUpdateTemplate(),
        ];
        foreach ($templateDefaults as $key => $default) {
            if (empty($dbSettings[$key])) {
                Setting::updateOrCreate(['key' => $key], ['value' => $default]);
                $dbSettings[$key] = $default;
            }
        }

        $settings = [
            'fonnte_api_token'              => $dbSettings['fonnte_api_token'] ?? '',
            'fonnte_device_id'              => $dbSettings['fonnte_device_id'] ?? '',
            'sms_gateway_enabled'           => $dbSettings['sms_gateway_enabled'] ?? '0',
            'sms_notification_submit'       => $dbSettings['sms_notification_submit'] ?? '0',
            'sms_notification_status_change'=> $dbSettings['sms_notification_status_change'] ?? '0',
            'sms_notification_published'    => $dbSettings['sms_notification_published'] ?? '0',
            'sms_default_country_code'      => $dbSettings['sms_default_country_code'] ?? '62',
            'sms_template_submit'           => $dbSettings['sms_template_submit'],
            'sms_template_status_change'    => $dbSettings['sms_template_status_change'],
            'sms_template_published'        => $dbSettings['sms_template_published'],
            'wa_template_credential_new'    => $dbSettings['wa_template_credential_new'],
            'wa_template_credential_update' => $dbSettings['wa_template_credential_update'],
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
            'wa_template_credential_new' => 'nullable|string|max:3000',
            'wa_template_credential_update' => 'nullable|string|max:3000',
        ]);

        try {
            // Ensure database table is ready
            $this->ensureSettingsTableReady();

            // Checkbox fields default to '0' when not submitted (unchecked)
            $checkboxFields = ['sms_gateway_enabled', 'sms_notification_submit', 'sms_notification_status_change', 'sms_notification_published'];
            foreach ($checkboxFields as $field) {
                $validated[$field] = $request->input($field, '0');
            }

            // Save using Eloquent model (handles created_at/updated_at automatically)
            foreach ($validated as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
            }

            Log::info('SMS Gateway Settings saved', [
                'keys_saved' => array_keys($validated),
                'sms_gateway_enabled' => $validated['sms_gateway_enabled'],
            ]);

            return redirect()->route('admin.sms-gateway.index')
                ->with('success', 'Pengaturan SMS Gateway berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('SMS Gateway Settings save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.sms-gateway.index')
                ->withInput()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    public static function defaultCredentialNewTemplate(): string
    {
        return "Halo *{nama}*,\n\nArtikel Anda telah berhasil disubmit ke sistem kami. Berikut detail informasinya:\n\n📄 Detail Submission\n\nKode Submit: {kode}\nJudul Artikel: {judul}\nJurnal: {namaJurnal}\nLink Submit: {linkSubmit}\n\n🔐 Akun OJS Author\nUsername: {username}\nPassword: {password}\n\n🔎 Monitoring & Verifikasi\nPemantauan status artikel dapat dilakukan melalui akun OJS Anda.\nPengecekan LoA (Letter of Acceptance) dapat dilakukan melalui:\n👉 https://verifyloa.apji.org/\n\n⚠️ Penting:\nSelama proses submission hingga publikasi, password tidak diperkenankan untuk diubah guna menjaga sinkronisasi sistem monitoring dan administrasi.\n\n📞 Informasi Tambahan:\nJika terdapat pertanyaan atau kendala, silakan menghubungi Tim Marketing kami untuk mendapatkan bantuan lebih lanjut.";
    }

    public static function defaultCredentialUpdateTemplate(): string
    {
        return "Halo *{nama}*,\n\nKredensial akun OJS Author Anda telah diperbarui. Berikut informasi terbaru:\n\n📄 Detail Submission\n\nKode Submit: {kode}\nJudul Artikel: {judul}\nJurnal: {namaJurnal}\nLink Submit: {linkSubmit}\n\n🔐 Akun OJS Author (Diperbarui)\nUsername: {username}\nPassword: {password}\n\n🔎 Monitoring & Verifikasi\nPemantauan status artikel dapat dilakukan melalui akun OJS Anda.\nPengecekan LoA (Letter of Acceptance) dapat dilakukan melalui:\n👉 https://verifyloa.apji.org/\n\n⚠️ Penting:\nSelama proses submission hingga publikasi, password tidak diperkenankan untuk diubah guna menjaga sinkronisasi sistem monitoring dan administrasi.\n\n📞 Informasi Tambahan:\nJika terdapat pertanyaan atau kendala, silakan menghubungi Tim Marketing kami untuk mendapatkan bantuan lebih lanjut.";
    }

    /**
     * Debug endpoint to check database state
     */
    public function debug()
    {
        $columns = Schema::getColumnListing('settings');
        $hasKey = Schema::hasColumn('settings', 'key');
        $hasValue = Schema::hasColumn('settings', 'value');

        $smsSettings = Setting::whereIn('key', [
            'fonnte_api_token', 'fonnte_device_id', 'sms_gateway_enabled',
            'sms_notification_submit', 'sms_notification_status_change', 'sms_notification_published',
            'sms_default_country_code', 'sms_template_submit', 'sms_template_status_change', 'sms_template_published',
        ])->get(['id', 'key', 'value', 'created_at', 'updated_at']);

        // Write test
        $writeTest = ['success' => false, 'message' => ''];
        try {
            Setting::updateOrCreate(['key' => '_debug_test_'], ['value' => now()->toDateTimeString()]);
            $readback = Setting::where('key', '_debug_test_')->value('value');
            Setting::where('key', '_debug_test_')->delete();
            $writeTest = ['success' => true, 'readback' => $readback];
        } catch (\Exception $e) {
            $writeTest = ['success' => false, 'message' => $e->getMessage()];
        }

        return response()->json([
            'table_exists' => Schema::hasTable('settings'),
            'columns' => $columns,
            'has_key_column' => $hasKey,
            'has_value_column' => $hasValue,
            'sms_settings_count' => $smsSettings->count(),
            'sms_settings' => $smsSettings,
            'write_test' => $writeTest,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]);
    }

    /**
     * Check Fonnte device/connection status
     */
    public function checkStatus(Request $request)
    {
        // Gunakan token dari request (form) atau dari database
        $token = $request->input('token') ?: Setting::get('fonnte_api_token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API Token belum diisi. Silakan masukkan token terlebih dahulu.',
            ]);
        }

        $fonnte = new FonnteService();
        $result = $fonnte->getDeviceStatusWithToken($token);

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

        // Gunakan token dari request (form) atau dari database
        $token = $request->input('token') ?: Setting::get('fonnte_api_token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API Token belum diisi. Silakan masukkan token terlebih dahulu.',
            ]);
        }

        $fonnte = new FonnteService();
        $result = $fonnte->sendWithToken(
            $token,
            $request->phone,
            $request->message,
            ['countryCode' => $request->input('countryCode', Setting::get('sms_default_country_code', '62'))]
        );

        return response()->json($result);
    }
}

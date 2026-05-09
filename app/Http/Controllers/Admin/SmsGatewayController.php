<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FonnteService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SmsGatewayController extends Controller
{
    private string $settingsFile;

    public function __construct()
    {
        $this->settingsFile = storage_path('app/sms_gateway_settings.json');
    }

    private function readFromFile(): array
    {
        if (!file_exists($this->settingsFile)) return [];
        $data = json_decode(file_get_contents($this->settingsFile), true);
        return is_array($data) ? $data : [];
    }

    private function writeToFile(array $data): void
    {
        $result = file_put_contents($this->settingsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($result === false) {
            Log::warning('SMS Gateway: file_put_contents failed', ['path' => $this->settingsFile]);
        }
    }

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
        try {
            $this->ensureSettingsTableReady();
        } catch (\Exception $e) {
            Log::warning('SMS Gateway: ensureSettingsTableReady failed', ['error' => $e->getMessage()]);
        }

        $keys = [
            'fonnte_api_token', 'fonnte_device_id', 'sms_gateway_enabled',
            'sms_notification_submit', 'sms_notification_status_change', 'sms_notification_published',
            'sms_default_country_code', 'sms_template_submit', 'sms_template_status_change', 'sms_template_published',
            'wa_template_credential_new', 'wa_template_credential_update',
        ];

        // Baca semua sumber
        $fromFile    = $this->readFromFile();
        $fromCache   = Cache::get('sms_gw_settings', []);
        $fromSession = session('sms_gw_settings', []);
        try {
            $fromDb = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            $fromDb = [];
            Log::warning('SMS Gateway: DB read failed', ['error' => $e->getMessage()]);
        }

        // Merge prioritas: File < Cache < DB < Session
        $merged = is_array($fromFile) ? $fromFile : [];
        foreach ($fromCache as $k => $v) {
            if ($v !== '' && $v !== null) $merged[$k] = $v;
        }
        foreach ($fromDb as $k => $v) {
            if ($v !== '' && $v !== null) $merged[$k] = $v;
        }
        foreach ($fromSession as $k => $v) {
            if ($v !== '' && $v !== null) $merged[$k] = $v;
        }

        // Sync: jika ada data baru dari DB/session, perbarui file
        if (!empty(array_filter($merged, fn($v) => $v !== '' && $v !== null))) {
            $this->writeToFile($merged);
        }

        $settings = $this->buildSettings($merged);

        return view('admin.sms-gateway.index', compact('settings'));
    }

    private function buildSettings(array $data): array
    {
        return [
            'fonnte_api_token'               => $data['fonnte_api_token'] ?? '',
            'fonnte_device_id'               => $data['fonnte_device_id'] ?? '',
            'sms_gateway_enabled'            => $data['sms_gateway_enabled'] ?? '0',
            'sms_notification_submit'        => $data['sms_notification_submit'] ?? '0',
            'sms_notification_status_change' => $data['sms_notification_status_change'] ?? '0',
            'sms_notification_published'     => $data['sms_notification_published'] ?? '0',
            'sms_default_country_code'       => $data['sms_default_country_code'] ?? '62',
            'sms_template_submit'            => ($data['sms_template_submit'] ?? '') ?: "Halo {nama_penulis},\n\nArtikel Anda \"{judul_artikel}\" telah berhasil disubmit dengan kode: {kode_submit}.\n\nTerima kasih,\n{app_name}",
            'sms_template_status_change'     => ($data['sms_template_status_change'] ?? '') ?: "Halo {nama_penulis},\n\nStatus artikel \"{judul_artikel}\" ({kode_submit}) telah diupdate menjadi: {status}.\n\nTerima kasih,\n{app_name}",
            'sms_template_published'         => ($data['sms_template_published'] ?? '') ?: "Halo {nama_penulis},\n\nSelamat! Artikel \"{judul_artikel}\" ({kode_submit}) telah berhasil dipublikasikan.\n\nLink: {link_publish}\n\nTerima kasih,\n{app_name}",
            'wa_template_credential_new'     => ($data['wa_template_credential_new'] ?? '') ?: self::defaultCredentialNewTemplate(),
            'wa_template_credential_update'  => ($data['wa_template_credential_update'] ?? '') ?: self::defaultCredentialUpdateTemplate(),
        ];
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

            // Sensitive fields: jangan timpa nilai yang sudah ada jika field dikosongkan
            $protectedFields = ['fonnte_api_token'];
            $existingFile = $this->readFromFile();
            foreach ($protectedFields as $field) {
                if (empty($validated[$field])) {
                    $existing = Setting::get($field) ?: ($existingFile[$field] ?? '');
                    if (!empty($existing)) {
                        unset($validated[$field]);
                    }
                }
            }

            // Save — updateOrCreate menjamin record ada di DB setelah ini
            foreach ($validated as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
            }

            // Tulis ke file lokal — fallback paling andal saat DB read bermasalah
            $fileData = array_merge($existingFile, $validated);
            foreach ($protectedFields as $field) {
                if (!array_key_exists($field, $validated) && empty($fileData[$field])) {
                    $fileData[$field] = Setting::get($field) ?? '';
                }
            }
            $this->writeToFile($fileData);
            session()->put('sms_gw_settings', $fileData);
            Cache::put('sms_gw_settings', $fileData, now()->addDays(30));

            Log::info('SMS Gateway Settings saved', ['keys_saved' => array_keys($validated)]);

            // Render view langsung dengan data yang baru disimpan (tidak redirect)
            // → form pasti tampil terisi tanpa bergantung pada DB/session read
            $settings = $this->buildSettings($fileData);
            session()->now('success', 'Pengaturan SMS Gateway berhasil disimpan!');
            return view('admin.sms-gateway.index', compact('settings'));

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

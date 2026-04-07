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

        $needsMigration = false;
        if (!Schema::hasColumn('settings', 'key')) {
            $needsMigration = true;
        }
        if (!Schema::hasColumn('settings', 'value')) {
            $needsMigration = true;
        }

        if ($needsMigration) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'key')) {
                    $table->string('key')->unique()->after('id');
                }
                if (!Schema::hasColumn('settings', 'value')) {
                    $table->text('value')->nullable()->after('key');
                }
            });
            Log::info('SMS Gateway: Auto-added missing key/value columns to settings table');
        }
    }

    public function index()
    {
        // Ensure database table is ready
        $this->ensureSettingsTableReady();

        // Read all SMS/Fonnte settings from DB in a single query for reliability
        $dbSettings = DB::table('settings')
            ->whereIn('key', [
                'fonnte_api_token', 'fonnte_device_id', 'sms_gateway_enabled',
                'sms_notification_submit', 'sms_notification_status_change', 'sms_notification_published',
                'sms_default_country_code', 'sms_template_submit', 'sms_template_status_change', 'sms_template_published',
            ])
            ->pluck('value', 'key')
            ->toArray();

        $settings = [
            'fonnte_api_token'              => $dbSettings['fonnte_api_token'] ?? '',
            'fonnte_device_id'              => $dbSettings['fonnte_device_id'] ?? '',
            'sms_gateway_enabled'           => $dbSettings['sms_gateway_enabled'] ?? '0',
            'sms_notification_submit'       => $dbSettings['sms_notification_submit'] ?? '0',
            'sms_notification_status_change'=> $dbSettings['sms_notification_status_change'] ?? '0',
            'sms_notification_published'    => $dbSettings['sms_notification_published'] ?? '0',
            'sms_default_country_code'      => $dbSettings['sms_default_country_code'] ?? '62',
            'sms_template_submit'           => $dbSettings['sms_template_submit'] ?? "Halo {nama_penulis},\n\nArtikel Anda \"{judul_artikel}\" telah berhasil disubmit dengan kode: {kode_submit}.\n\nTerima kasih,\n{app_name}",
            'sms_template_status_change'    => $dbSettings['sms_template_status_change'] ?? "Halo {nama_penulis},\n\nStatus artikel \"{judul_artikel}\" ({kode_submit}) telah diupdate menjadi: {status}.\n\nTerima kasih,\n{app_name}",
            'sms_template_published'        => $dbSettings['sms_template_published'] ?? "Halo {nama_penulis},\n\nSelamat! Artikel \"{judul_artikel}\" ({kode_submit}) telah berhasil dipublikasikan.\n\nLink: {link_publish}\n\nTerima kasih,\n{app_name}",
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

        try {
            // Ensure database table is ready
            $this->ensureSettingsTableReady();

            // Checkbox fields default to '0' when not submitted (unchecked)
            $checkboxFields = ['sms_gateway_enabled', 'sms_notification_submit', 'sms_notification_status_change', 'sms_notification_published'];
            foreach ($checkboxFields as $field) {
                $validated[$field] = $request->input($field, '0');
            }

            // Save all settings using raw DB upsert for maximum reliability
            DB::transaction(function () use ($validated) {
                foreach ($validated as $key => $value) {
                    DB::table('settings')->updateOrInsert(
                        ['key' => $key],
                        ['value' => $value ?? '', 'updated_at' => now()]
                    );
                }
            });

            Log::info('SMS Gateway Settings saved', [
                'sms_gateway_enabled' => $validated['sms_gateway_enabled'],
                'sms_notification_submit' => $validated['sms_notification_submit'],
            ]);

            return redirect()->route('admin.sms-gateway.index')
                ->with('success', 'Pengaturan SMS Gateway berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('SMS Gateway Settings save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('admin.sms-gateway.index')
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Debug endpoint to check database state
     */
    public function debug()
    {
        $columns = Schema::getColumnListing('settings');
        $hasKey = Schema::hasColumn('settings', 'key');
        $hasValue = Schema::hasColumn('settings', 'value');
        
        $allSettings = DB::table('settings')->get();
        $smsSettings = DB::table('settings')
            ->where('key', 'like', 'fonnte_%')
            ->orWhere('key', 'like', 'sms_%')
            ->get();

        return response()->json([
            'table_exists' => Schema::hasTable('settings'),
            'columns' => $columns,
            'has_key_column' => $hasKey,
            'has_value_column' => $hasValue,
            'total_records' => $allSettings->count(),
            'sms_settings' => $smsSettings,
            'all_keys' => $allSettings->pluck('key'),
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailSettingController extends Controller
{
    private string $settingsFile;

    private array $keys = [
        'mail_host', 'mail_port', 'mail_username', 'mail_password',
        'mail_encryption', 'mail_from_address', 'mail_from_name',
    ];

    public function __construct()
    {
        $this->settingsFile = storage_path('app/email_settings.json');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

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
            Log::warning('Email Settings: file_put_contents failed', ['path' => $this->settingsFile]);
        }
    }

    /** Parse .env file directly — dipakai hanya sebagai fallback paling akhir */
    private function parseEnvFile(): array
    {
        $path = base_path('.env');
        if (!file_exists($path)) return [];

        $result = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }
            $result[$key] = $value;
        }

        return $result;
    }

    /** Set a key in .env content — adds the line if the key doesn't exist */
    private function setEnvLine(string $content, string $key, string $value): string
    {
        $needsQuote = $value === '' || str_contains($value, ' ') || str_contains($value, '#');
        $line = $key . '=' . ($needsQuote ? '"' . addslashes($value) . '"' : $value);

        if (preg_match('/^' . preg_quote($key, '/') . '=/m', $content)) {
            return preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $line, $content);
        }

        return rtrim($content) . "\n" . $line . "\n";
    }

    private function buildSettings(array $data): array
    {
        return [
            'mail_host'         => $data['mail_host']         ?? '',
            'mail_port'         => $data['mail_port']         ?? '465',
            'mail_username'     => $data['mail_username']     ?? '',
            'mail_password'     => $data['mail_password']     ?? '',
            'mail_encryption'   => $data['mail_encryption']   ?? 'ssl',
            'mail_from_address' => $data['mail_from_address'] ?? '',
            'mail_from_name'    => $data['mail_from_name']    ?? '',
        ];
    }

    // ── Controller actions ────────────────────────────────────────────────────

    public function index()
    {
        // .env cuma fallback paling akhir (mis. belum pernah disimpan lewat form ini sama sekali)
        $env = $this->parseEnvFile();
        $envMapped = [
            'mail_host'         => $env['MAIL_HOST']         ?? '',
            'mail_port'         => $env['MAIL_PORT']         ?? '465',
            'mail_username'     => $env['MAIL_USERNAME']     ?? '',
            'mail_password'     => $env['MAIL_PASSWORD']     ?? '',
            'mail_encryption'   => $env['MAIL_ENCRYPTION']   ?? 'ssl',
            'mail_from_address' => $env['MAIL_FROM_ADDRESS'] ?? '',
            'mail_from_name'    => $env['MAIL_FROM_NAME']    ?? '',
        ];

        $fromFile    = $this->readFromFile();
        $fromCache   = Cache::get('email_settings', []);
        $fromSession = session('email_settings', []);
        try {
            $fromDb = Setting::whereIn('key', $this->keys)->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            $fromDb = [];
            Log::warning('Email Settings: DB read failed', ['error' => $e->getMessage()]);
        }

        // Merge prioritas: .env < File < Cache < DB < Session (session = paling baru, ditulis saat save)
        $merged = array_merge($envMapped, is_array($fromFile) ? $fromFile : []);
        foreach ($fromCache as $k => $v) {
            if ($v !== '' && $v !== null) $merged[$k] = $v;
        }
        foreach ($fromDb as $k => $v) {
            if ($v !== '' && $v !== null) $merged[$k] = $v;
        }
        foreach ($fromSession as $k => $v) {
            if ($v !== null) $merged[$k] = $v;
        }

        // Sync balik ke file kalau ada data baru dari DB/session
        if (!empty(array_filter($merged, fn($v) => $v !== '' && $v !== null))) {
            $this->writeToFile($merged);
        }

        // NB: nama variabel sengaja BUKAN "settings" — AppServiceProvider men-share
        // variabel global bernama "settings" (branding app) ke SEMUA view lewat
        // View::composer('*', ...), yang akan menimpa data ini kalau namanya bentrok.
        $emailSettings = $this->buildSettings($merged);

        return view('admin.email-settings.index', compact('emailSettings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'mail_host'         => 'required|string|max:255',
            'mail_port'         => 'required|integer|min:1|max:65535',
            'mail_username'     => 'required|string|max:255',
            'mail_password'     => 'nullable|string|max:255',
            'mail_encryption'   => 'required|in:ssl,tls,starttls',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name'    => 'nullable|string|max:255',
        ]);
        $validated['mail_port'] = (string) $validated['mail_port'];

        // Flash input sekarang agar old() selalu ada di view ini maupun request berikutnya
        $request->flash();

        // Pertahankan password lama jika field dikosongkan
        if (empty($validated['mail_password'])) {
            $existing = Setting::get('mail_password')
                ?: ($this->readFromFile()['mail_password'] ?? '')
                ?: (Cache::get('email_settings', [])['mail_password'] ?? '')
                ?: (session('email_settings', [])['mail_password'] ?? '');
            if (!empty($existing)) {
                $validated['mail_password'] = $existing;
            }
        }

        // Simpan ke semua sumber — best-effort (error ditangkap per-sumber, tidak saling menggagalkan)
        try {
            foreach ($validated as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
            }
        } catch (\Exception $e) {
            Log::warning('Email Settings: DB save failed', ['error' => $e->getMessage()]);
        }

        try {
            $existingFile = $this->readFromFile();
            $fileData     = array_merge($existingFile, $validated);
            $this->writeToFile($fileData);
        } catch (\Exception $e) {
            Log::warning('Email Settings: file save failed', ['error' => $e->getMessage()]);
        }

        session()->put('email_settings', $validated);
        Cache::put('email_settings', $validated, now()->addDays(30));

        // Best-effort sync ke .env juga, kalau ada proses lain yang baca .env langsung
        $this->tryWriteEnv($validated);

        try { \Artisan::call('config:clear'); } catch (\Exception $e) {}
        try { \Artisan::call('cache:clear');  } catch (\Exception $e) {}

        Log::info('Email Settings saved', ['keys_saved' => array_keys($validated)]);

        // Render langsung dari data yang baru disimpan — form selalu terisi
        // tanpa bergantung pada redirect + baca ulang dari DB/file.
        $emailSettings = $this->buildSettings($validated);
        session()->now('success', 'Pengaturan email berhasil disimpan!');
        return view('admin.email-settings.index', compact('emailSettings'));
    }

    /** Best-effort sync ke .env — kegagalan di sini tidak menggagalkan penyimpanan (DB/file/session sudah cukup) */
    private function tryWriteEnv(array $validated): void
    {
        try {
            $envPath = base_path('.env');
            if (!file_exists($envPath) || !is_writable($envPath)) return;

            $envContent = file_get_contents($envPath);
            if ($envContent === false) return;

            $map = [
                'MAIL_MAILER'       => 'smtp',
                'MAIL_HOST'         => $validated['mail_host'],
                'MAIL_PORT'         => (string) $validated['mail_port'],
                'MAIL_USERNAME'     => $validated['mail_username'],
                'MAIL_PASSWORD'     => $validated['mail_password'],
                'MAIL_ENCRYPTION'   => $validated['mail_encryption'],
                'MAIL_FROM_ADDRESS' => $validated['mail_from_address'] ?? '',
                'MAIL_FROM_NAME'    => $validated['mail_from_name']    ?? '',
            ];

            foreach ($map as $key => $value) {
                $envContent = $this->setEnvLine($envContent, $key, $value);
            }

            @file_put_contents($envPath, $envContent, LOCK_EX);
        } catch (\Throwable $e) {
            Log::warning('Best-effort .env sync for mail settings failed: ' . $e->getMessage());
        }
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Baca dengan prioritas sama seperti index(): session/cache/DB/file/.env
            $fromFile    = $this->readFromFile();
            $fromCache   = Cache::get('email_settings', []);
            $fromSession = session('email_settings', []);
            $fromDb      = Setting::whereIn('key', $this->keys)->pluck('value', 'key')->toArray();
            $env         = $this->parseEnvFile();

            $merged = [
                'mail_host'         => $env['MAIL_HOST']         ?? '',
                'mail_port'         => $env['MAIL_PORT']         ?? '465',
                'mail_username'     => $env['MAIL_USERNAME']     ?? '',
                'mail_password'     => $env['MAIL_PASSWORD']     ?? '',
                'mail_encryption'   => $env['MAIL_ENCRYPTION']   ?? 'ssl',
                'mail_from_address' => $env['MAIL_FROM_ADDRESS'] ?? '',
                'mail_from_name'    => $env['MAIL_FROM_NAME']    ?? '',
            ];
            foreach ([$fromFile, $fromCache, $fromDb, $fromSession] as $source) {
                foreach ($source as $k => $v) {
                    if ($v !== '' && $v !== null) $merged[$k] = $v;
                }
            }

            $host       = $merged['mail_host'];
            $port       = (int) $merged['mail_port'];
            $username   = $merged['mail_username'];
            $password   = $merged['mail_password'];
            $encryption = $merged['mail_encryption'];
            $fromAddr   = $merged['mail_from_address'] ?: $username;
            $fromName   = $merged['mail_from_name'] ?: 'SIPERA System';

            if (empty($host) || empty($username) || empty($password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi SMTP belum lengkap. Pastikan Host, Username, dan Password sudah diisi dan disimpan.',
                    'error'   => 'mail_host, mail_username, atau mail_password kosong — simpan pengaturan terlebih dahulu.',
                ], 422);
            }

            // Force-override the in-memory Mail config
            config([
                'mail.default'                 => 'smtp',
                'mail.mailers.smtp.transport'  => 'smtp',
                'mail.mailers.smtp.host'       => $host,
                'mail.mailers.smtp.port'       => $port,
                'mail.mailers.smtp.username'   => $username,
                'mail.mailers.smtp.password'   => $password,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.from.address'            => $fromAddr,
                'mail.from.name'               => $fromName,
            ]);

            // Purge cached mailer instance so it re-initialises with the new config
            app('mail.manager')->purge('smtp');

            Mail::raw(
                "Ini adalah test email dari sistem SIPERA.\n\n"
                . "Jika Anda menerima email ini, berarti konfigurasi SMTP berhasil!\n\n"
                . "Host     : {$host}\n"
                . "Port     : {$port}\n"
                . "Username : {$username}\n"
                . "Enkripsi : {$encryption}\n\n"
                . "— Tim SIPERA",
                function ($message) use ($request, $fromAddr, $fromName) {
                    $message->to($request->email)
                            ->from($fromAddr, $fromName)
                            ->subject('✅ Test Email - SIPERA');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Email test berhasil dikirim ke ' . $request->email . '. Silakan cek inbox atau folder spam.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email test',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

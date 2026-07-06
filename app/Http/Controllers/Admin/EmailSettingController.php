<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailSettingController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Parse .env file directly — bypasses PHP process env cache */
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
            // Strip surrounding quotes
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
        // Quote if value contains spaces, #, or is empty
        $needsQuote = $value === '' || str_contains($value, ' ') || str_contains($value, '#');
        $line = $key . '=' . ($needsQuote ? '"' . addslashes($value) . '"' : $value);

        if (preg_match('/^' . preg_quote($key, '/') . '=/m', $content)) {
            return preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $line, $content);
        }

        // Key not found — append
        return rtrim($content) . "\n" . $line . "\n";
    }

    // ── Controller actions ────────────────────────────────────────────────────

    public function index()
    {
        // .env cuma dipakai sebagai fallback (mis. sebelum pernah disimpan lewat form ini)
        $env = $this->parseEnvFile();

        $settings = [
            'mail_host'         => Setting::get('mail_host',         $env['MAIL_HOST']         ?? ''),
            'mail_port'         => Setting::get('mail_port',         $env['MAIL_PORT']         ?? '465'),
            'mail_username'     => Setting::get('mail_username',     $env['MAIL_USERNAME']     ?? ''),
            'mail_password'     => Setting::get('mail_password',     $env['MAIL_PASSWORD']     ?? ''),
            'mail_encryption'   => Setting::get('mail_encryption',   $env['MAIL_ENCRYPTION']   ?? 'ssl'),
            'mail_from_address' => Setting::get('mail_from_address', $env['MAIL_FROM_ADDRESS'] ?? ''),
            'mail_from_name'    => Setting::get('mail_from_name',    $env['MAIL_FROM_NAME']    ?? ''),
        ];

        return view('admin.email-settings.index', compact('settings'));
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

        // Pertahankan password lama jika field dikosongkan
        if (empty($validated['mail_password'])) {
            $validated['mail_password'] = Setting::get('mail_password', '');
        }

        // ── Simpan ke database (sumber utama — tidak tergantung permission file server) ──
        Setting::set('mail_host',         $validated['mail_host']);
        Setting::set('mail_port',         (string) $validated['mail_port']);
        Setting::set('mail_username',     $validated['mail_username']);
        Setting::set('mail_password',     $validated['mail_password']);
        Setting::set('mail_encryption',   $validated['mail_encryption']);
        Setting::set('mail_from_address', $validated['mail_from_address'] ?? '');
        Setting::set('mail_from_name',    $validated['mail_from_name']    ?? '');

        // Verifikasi tersimpan di DB — ini yang menentukan sukses/gagalnya penyimpanan
        if (Setting::get('mail_host') !== $validated['mail_host']) {
            return back()->withInput()->with('error',
                'Gagal menyimpan pengaturan email ke database. Coba lagi atau hubungi administrator server.'
            );
        }

        // Best-effort: tulis juga ke .env supaya tetap sinkron kalau ada proses lain yang baca .env langsung.
        // Kalau gagal (permission dsb), tidak masalah — DB tetap jadi sumber utama.
        $this->tryWriteEnv($validated);

        try { \Artisan::call('config:clear'); } catch (\Exception $e) {}
        try { \Artisan::call('cache:clear');  } catch (\Exception $e) {}

        return redirect()->route('admin.email-settings.index')
            ->with('success', 'Pengaturan email berhasil disimpan.');
    }

    /** Best-effort sync ke .env — kegagalan di sini tidak menggagalkan penyimpanan (DB sudah jadi sumber utama) */
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
            \Log::warning('Best-effort .env sync for mail settings failed: ' . $e->getMessage());
        }
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Baca fresh dari database (sumber utama) — bypass cache runtime
            $host       = Setting::get('mail_host', '');
            $port       = (int) Setting::get('mail_port', 465);
            $username   = Setting::get('mail_username', '');
            $password   = Setting::get('mail_password', '');
            $encryption = Setting::get('mail_encryption', 'ssl');
            $fromAddr   = Setting::get('mail_from_address') ?: $username;
            $fromName   = Setting::get('mail_from_name') ?: 'SIPERA System';

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

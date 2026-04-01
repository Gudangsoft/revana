<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $apiUrl = 'https://api.fonnte.com/send';
    protected string $deviceUrl = 'https://api.fonnte.com/device';

    /**
     * Get the stored API token
     */
    public function getToken(): ?string
    {
        return Setting::get('fonnte_api_token');
    }

    /**
     * Check if Fonnte is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->getToken());
    }

    /**
     * Send a WhatsApp message via Fonnte
     *
     * @param string $target Phone number (with or without country code)
     * @param string $message Message content
     * @param array $options Additional options (url, filename, schedule, typing, delay, countryCode)
     * @param string|null $token Optional token override (if not provided, uses saved token)
     * @return array Response from Fonnte API
     */
    public function send(string $target, string $message, array $options = [], ?string $token = null): array
    {
        $token = $token ?: $this->getToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Fonnte API token belum dikonfigurasi.',
            ];
        }

        try {
            $payload = [
                'target' => $target,
                'message' => $message,
                'countryCode' => $options['countryCode'] ?? '62',
            ];

            // Merge additional options (url, filename, etc.)
            foreach ($options as $key => $value) {
                if (!isset($payload[$key])) {
                    $payload[$key] = $value;
                }
            }

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post($this->apiUrl, $payload);

            $result = $response->json();

            Log::info('Fonnte SMS sent', [
                'target' => $target,
                'status' => $result['status'] ?? 'unknown',
                'detail' => $result['detail'] ?? '',
            ]);

            return [
                'success' => ($result['status'] ?? false) === true,
                'message' => $result['detail'] ?? $result['reason'] ?? 'Unknown response',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Fonnte SMS error', [
                'target' => $target,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send with explicit token (alias for convenience)
     */
    public function sendWithToken(string $token, string $target, string $message, array $options = []): array
    {
        return $this->send($target, $message, $options, $token);
    }

    /**
     * Get device/connection status from Fonnte
     *
     * @param string|null $token Optional token override
     */
    public function getDeviceStatus(?string $token = null): array
    {
        $token = $token ?: $this->getToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'API token belum dikonfigurasi.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post($this->deviceUrl);

            $result = $response->json();

            return [
                'success' => ($result['status'] ?? false) === true,
                'message' => $result['reason'] ?? 'Unknown',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal cek status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get device status with explicit token (alias for convenience)
     */
    public function getDeviceStatusWithToken(string $token): array
    {
        return $this->getDeviceStatus($token);
    }
}

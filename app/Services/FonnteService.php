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
     * @return array Response from Fonnte API
     */
    public function send(string $target, string $message, array $options = []): array
    {
        $token = $this->getToken();

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Fonnte API token belum dikonfigurasi.',
            ];
        }

        try {
            $payload = array_merge([
                'target' => $target,
                'message' => $message,
                'countryCode' => $options['countryCode'] ?? '62',
            ], $options);

            // Remove countryCode from options to avoid duplicate
            unset($payload['countryCode']);
            $payload['countryCode'] = $options['countryCode'] ?? '62';

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
     * Get device/connection status from Fonnte
     */
    public function getDeviceStatus(): array
    {
        $token = $this->getToken();

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
}

<?php

namespace App\Actions;

use App\Support\Config;

class ValidateLicense
{
    public function validate(string $key): array
    {
        $url = Config::get('installation.licenseUrl', '');

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                'license' => $key,
                'domain' => $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'] ?? '',
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['ok' => false, 'message' => 'Could not reach the license server. Check your connection.'];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['ok' => false, 'message' => 'License validation failed. Please check your license key.'];
        }

        $decoded = json_decode($response, true);

        $valid = $decoded !== null
            ? (($decoded['status'] ?? '') === 'success'
                || (bool) ($decoded['success'] ?? $decoded['ok'] ?? $decoded['valid'] ?? false))
            : strtolower(trim($response)) === 'true';

        if (!$valid) {
            return ['ok' => false, 'message' => 'Invalid license key. Please verify and try again.'];
        }

        return ['ok' => true];
    }
}

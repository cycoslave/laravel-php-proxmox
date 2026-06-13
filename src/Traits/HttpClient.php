<?php

namespace Cycoslave\Proxmox\Traits;

use Cycoslave\Proxmox\Exceptions\ApiException;
use Cycoslave\Proxmox\Exceptions\ConnectionException;

/**
 * HttpClient trait — raw cURL dispatcher.
 *
 * Throws typed exceptions so callers can distinguish error categories:
 *   - ConnectionException  : cURL / network-level failure
 *   - ApiException         : HTTP 4xx / 5xx or malformed JSON from Proxmox
 */
trait HttpClient
{
    /**
     * Execute a cURL request and return the decoded JSON response.
     *
     * @param  string   $method   HTTP verb (GET, POST, PUT, DELETE)
     * @param  string   $url      Fully-qualified URL
     * @param  array    $params   Body params (POST/PUT) or query params (GET/DELETE)
     * @param  string[] $headers  Auth headers resolved by Authenticator
     * @return array    Decoded JSON response body
     *
     * @throws ConnectionException  On cURL / network failure or timeout
     * @throws ApiException         On HTTP 4xx/5xx or invalid JSON
     */
    protected function sendRequest(
        string $method,
        string $url,
        array  $params  = [],
        array  $headers = [],
    ): array {
        $curl = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $this->verifyTls,
            CURLOPT_SSL_VERIFYHOST => $this->verifyTls ? 2 : 0,
            CURLOPT_CONNECTTIMEOUT => max(1, min(5, $this->timeout)),
            CURLOPT_TIMEOUT        => max(1, $this->timeout),
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
        ];

        if ($method === 'POST' || $method === 'PUT') {
            $options[CURLOPT_URL]        = $url;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        } else {
            $options[CURLOPT_URL] = empty($params)
                ? $url
                : $url . '?' . http_build_query($params);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($response === false || $error !== '') {
            throw new ConnectionException("Proxmox cURL error: {$error}");
        }

        if ($httpCode >= 400) {
            // Redact query string from logged URL (MED-2 fix)
            $safeUrl = strtok($url, '?');
            throw new ApiException(
                "Proxmox API HTTP {$httpCode} for: {$safeUrl}",
                $httpCode,
            );
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(
                'Proxmox API returned invalid JSON: ' . json_last_error_msg(),
            );
        }

        return $decoded ?? [];
    }
}
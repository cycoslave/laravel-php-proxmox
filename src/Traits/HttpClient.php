<?php

namespace Cycoslave\Proxmox\Traits;

/**
 * HttpClient trait — raw cURL dispatcher.
 *
 * Key change from original: sendRequest() now accepts explicit $headers[]
 * so Authenticator can pass auth headers through on every call.
 */
trait HttpClient
{
    /**
     * Execute a cURL request and return the decoded JSON response.
     *
     * @param  string   $method   HTTP verb
     * @param  string   $url      Full URL
     * @param  array    $params   Body (POST/PUT) or query string (GET/DELETE)
     * @param  string[] $headers  HTTP headers (auth headers from Authenticator)
     * @return array    Decoded JSON response body
     * @throws \Exception on cURL error or HTTP 4xx/5xx
     */
    private function sendRequest(
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
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
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
            throw new \Exception("Proxmox cURL error: {$error}");
        }

        if ($httpCode >= 400) {
            $safeUrl = strtok($url, '?');
            throw new \Exception("Proxmox API HTTP {$httpCode} for: {$safeUrl}");
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Proxmox API returned invalid JSON: ' . json_last_error_msg());
        }

        return $decoded ?? [];
    }
}
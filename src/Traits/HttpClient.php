<?php

namespace Cycoslave\Proxmox\Traits;

trait HttpClient
{
    /**
     * Raw cURL dispatcher — used by Authenticator and makeRequest().
     *
     * @throws \Exception
     */
    private function sendRequest(string $method, string $url, array $params = []): array
    {
        $curl = curl_init();

        $options = [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYPEER  => $this->verifyTls,
            CURLOPT_SSL_VERIFYHOST  => $this->verifyTls ? 2 : 0,
            CURLOPT_CONNECTTIMEOUT  => $this->timeout,
            CURLOPT_TIMEOUT         => $this->timeout,
            CURLOPT_CUSTOMREQUEST   => $method,
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

        if ($response === false || $error) {
            throw new \Exception("Proxmox cURL error: {$error}");
        }

        if ($httpCode >= 400) {
            throw new \Exception("Proxmox API error {$httpCode}: {$url}");
        }

        return json_decode($response, true) ?? [];
    }
}
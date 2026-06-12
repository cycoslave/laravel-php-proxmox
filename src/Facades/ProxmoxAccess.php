<?php

namespace Cycoslave\Proxmox;

use Cycoslave\Proxmox\Traits\Authenticator;
use Cycoslave\Proxmox\Traits\HttpClient;

/**
 * HTTP transport layer for a single named Proxmox connection.
 * Injected into ProxmoxNode, ProxmoxCluster, ProxmoxStorage, ProxmoxPools.
 */
class ProxmoxAccess
{
    use HttpClient, Authenticator;

    public function __construct(
        public readonly string  $host,
        public readonly int     $port         = 8006,
        public readonly string  $username     = 'root',
        public readonly string  $realm        = 'pam',
        public readonly ?string $password     = null,
        public readonly ?string $tokenId      = null,
        public readonly ?string $tokenSecret  = null,
        public readonly bool    $verifyTls    = true,
        public readonly int     $timeout      = 10,
    ) {}

    protected function baseUrl(): string
    {
        return "https://{$this->host}:{$this->port}/api2/json";
    }

    /**
     * @throws \Exception
     */
    public function get(string $path, array $params = []): array
    {
        return $this->makeRequest('GET', $path, $params);
    }

    public function post(string $path, array $data = []): array
    {
        return $this->makeRequest('POST', $path, $data);
    }

    public function put(string $path, array $data = []): array
    {
        return $this->makeRequest('PUT', $path, $data);
    }

    public function delete(string $path, array $params = []): array
    {
        return $this->makeRequest('DELETE', $path, $params);
    }

    /**
     * Internal — adds auth headers then dispatches via HttpClient::sendRequest().
     *
     * @throws \Exception
     */
    private function makeRequest(string $method, string $path, array $params = []): array
    {
        $url     = $this->baseUrl() . '/' . ltrim($path, '/');
        $headers = $this->authHeaders(); // from Authenticator trait

        // Re-open curl with headers injected
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
            $options[CURLOPT_URL] = empty($params) ? $url : $url . '?' . http_build_query($params);
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
            throw new \Exception("Proxmox API error {$httpCode} on {$method} {$path}");
        }

        return json_decode($response, true) ?? [];
    }
}
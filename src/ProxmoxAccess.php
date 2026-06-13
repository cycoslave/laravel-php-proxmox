<?php

namespace Cycoslave\Proxmox;

use Illuminate\Support\Facades\Log;
use Cycoslave\Proxmox\Traits\Authenticator;
use Cycoslave\Proxmox\Traits\HttpClient;

/**
 * ProxmoxAccess — HTTP connection layer.
 *
 * Responsibilities (per spec):
 *   - Holds connection credentials and config
 *   - Builds base URLs:  https://{host}:{port}/api2/json/{path}
 *   - Exposes get / post / put / delete
 *   - Returns decoded array or throws on error
 *   - Delegates auth to Authenticator trait (ticket or API token)
 *   - Delegates raw cURL to HttpClient trait
 *
 * Used by ProxmoxManager::resolve() and injected into all resource classes
 * (ProxmoxNode, ProxmoxCluster, ProxmoxStorage, ProxmoxPools, ProxmoxAccessApi).
 *
 * NOT to be confused with ProxmoxAccessApi, which wraps the /access/* endpoints.
 */
class ProxmoxAccess
{
    use Authenticator, HttpClient;

    public function __construct(
        protected string  $host,
        protected int     $port        = 8006,
        protected string  $username    = 'root',
        protected string  $realm       = 'pam',
        protected ?string $password    = null,
        protected ?string $tokenId     = null,
        protected ?string $tokenSecret = null,
        protected bool    $verifyTls   = true,
        protected int     $timeout     = 10,
    ) {
        Log::warning('Proxmox: TLS verification disabled. Do not use in production.', [
            'host' => $host,
        ]);
    }

    // -------------------------------------------------------------------------
    // URL builder
    // -------------------------------------------------------------------------

    /**
     * Base URL for all API calls.
     */
    protected function baseUrl(): string
    {
        return "https://{$this->host}:{$this->port}/api2/json";
    }

    // -------------------------------------------------------------------------
    // Public HTTP interface
    // -------------------------------------------------------------------------

    /**
     * GET request.
     *
     * @param  string  $path    e.g. 'nodes' or 'nodes/pve1/qemu'
     * @param  array   $params  Optional query parameters
     * @return array   Decoded JSON response
     * @throws \Exception
     */
    public function get(string $path, array $params = []): array
    {
        return $this->request('GET', $path, $params);
    }

    /**
     * POST request.
     *
     * @throws \Exception
     */
    public function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, $data);
    }

    /**
     * PUT request.
     *
     * @throws \Exception
     */
    public function put(string $path, array $data = []): array
    {
        return $this->request('PUT', $path, $data);
    }

    /**
     * DELETE request.
     *
     * @throws \Exception
     */
    public function delete(string $path, array $params = []): array
    {
        return $this->request('DELETE', $path, $params);
    }

    // -------------------------------------------------------------------------
    // Internal dispatcher
    // -------------------------------------------------------------------------

    /**
     * Build the full URL, attach auth headers, and dispatch via HttpClient.
     *
     * @throws \Exception
     */
    protected function request(string $method, string $path, array $payload = []): array
    {
        $url     = $this->baseUrl() . '/' . ltrim($path, '/');
        $headers = $this->authHeaders();   // resolved by Authenticator trait

        return $this->sendRequest($method, $url, $payload, $headers);
    }
}
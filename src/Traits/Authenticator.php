<?php

namespace Cycoslave\Proxmox\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Authenticator trait — ticket and API token auth for Proxmox.
 *
 * Auth strategy priority (per spec):
 *   1. API token  — if both tokenId and tokenSecret are set
 *   2. Ticket     — username + password, auto-refreshed on expiry
 */
trait Authenticator
{
    private ?string $ticket       = null;
    private ?string $csrf         = null;
    private ?int    $ticketExpiry = null;

    private function usingTokenAuth(): bool
    {
        return ! empty($this->tokenId) && ! empty($this->tokenSecret);
    }

    /**
     * Build auth headers for the current request.
     * API token takes precedence over ticket auth.
     *
     * @return string[]
     * @throws \Exception
     */
    private function authHeaders(): array
    {
        if ($this->usingTokenAuth()) {
            return [
                "Authorization: PVEAPIToken={$this->tokenId}={$this->tokenSecret}",
            ];
        }

        // Ticket auth — refresh when missing or within 60 s of expiry
        if ($this->ticket === null || time() >= ($this->ticketExpiry ?? 0)) {
            if (! $this->verifyTls) {
                Log::warning('Proxmox: TLS verification is disabled.', [
                    'host' => $this->host,
                ]);
            }

            $this->loginWithTicket();
        }

        return [
            'Cookie: PVEAuthCookie=' . $this->ticket,
            'CSRFPreventionToken: '  . $this->csrf,
        ];
    }

    /**
     * Perform ticket auth (POST /access/ticket).
     * Calls sendRequest() with empty headers directly — bypasses authHeaders()
     * to prevent infinite recursion (login call is itself unauthenticated).
     *
     * @throws \Exception
     */
    private function loginWithTicket(): void
    {
        $url  = $this->baseUrl() . '/access/ticket';
        $data = [
            'username' => "{$this->username}@{$this->realm}",
            'password' => $this->password,
        ];

        $response = $this->sendRequest('POST', $url, $data, []);

        if (empty($response['data']['ticket'])) {
            throw new \Exception('Proxmox ticket authentication failed.');
        }

        $this->ticket       = $response['data']['ticket'];
        $this->csrf         = $response['data']['CSRFPreventionToken'];
        $this->ticketExpiry = time() + 7200 - 60; // 2 h TTL, 60 s clock-skew buffer
    }
}
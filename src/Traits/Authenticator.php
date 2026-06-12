<?php

namespace Cycoslave\Proxmox\Traits;

use Illuminate\Support\Facades\Log;

trait Authenticator
{
    private ?string $ticket = null;
    private ?string $csrf   = null;
    private ?int    $ticketExpiry = null;

    /**
     * Perform ticket auth (POST /access/ticket).
     * Populates $this->ticket, $this->csrf, $this->ticketExpiry.
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

        $response = $this->sendRequest('POST', $url, $data);

        if (empty($response['data']['ticket'])) {
            throw new \Exception('Proxmox ticket authentication failed.');
        }

        $this->ticket       = $response['data']['ticket'];
        $this->csrf         = $response['data']['CSRFPreventionToken'];
        $this->ticketExpiry = time() + 7200 - 60; // 2 h TTL, 60 s clock-skew buffer
    }

    /**
     * Returns true when using API token auth (token_id + token_secret set).
     */
    private function usingTokenAuth(): bool
    {
        return ! empty($this->tokenId) && ! empty($this->tokenSecret);
    }

    /**
     * Build auth headers for the current auth strategy.
     * API token takes precedence over ticket auth.
     *
     * @throws \Exception
     */
    private function authHeaders(): array
    {
        if ($this->usingTokenAuth()) {
            return [
                "Authorization: PVEAPIToken={$this->tokenId}={$this->tokenSecret}",
            ];
        }

        // Ticket auth — refresh if missing or expired
        if ($this->ticket === null || time() >= ($this->ticketExpiry ?? 0)) {
            if (! $this->verifyTls) {
                Log::warning('Proxmox: TLS verification is disabled for connection.', [
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
}
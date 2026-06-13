<?php

namespace Cycoslave\Proxmox\Traits;

use Cycoslave\Proxmox\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Log;

/**
 * Authenticator trait — ticket and API token auth for Proxmox.
 *
 * Auth strategy priority:
 *   1. API token  — if both tokenId and tokenSecret are set
 *   2. Ticket     — username + password, auto-refreshed on expiry
 */
trait Authenticator
{
    // TODO: These are per-process/per-worker. Under PHP-FPM or Octane,
    //       each worker authenticates independently, causing redundant
    //       POST /access/ticket calls under load.
    //       Future improvement: back this with a shared cache (Redis)
    //       keyed on connection name, with TTL = ticketExpiry.
    private ?string $ticket       = null;
    private ?string $csrf         = null;
    private ?int    $ticketExpiry = null;

    protected function usingTokenAuth(): bool
    {
        return ! empty($this->tokenId) && ! empty($this->tokenSecret);
    }

    /**
     * Build auth headers for the current request.
     * API token takes precedence over ticket auth.
     *
     * @return string[]
     * @throws AuthenticationException
     */
    protected function authHeaders(): array
    {
        if ($this->usingTokenAuth()) {
            return [
                "Authorization: PVEAPIToken={$this->tokenId}={$this->tokenSecret}",
            ];
        }

        if ($this->ticket === null || time() >= ($this->ticketExpiry ?? 0)) {
            $this->loginWithTicket();
        }

        return [
            'Cookie: PVEAuthCookie=' . $this->ticket,
            'CSRFPreventionToken: '  . $this->csrf,
        ];
    }

    /**
     * Perform ticket auth (POST /access/ticket).
     * Calls sendRequest() with empty headers — bypasses authHeaders()
     * to prevent infinite recursion.
     *
     * @throws AuthenticationException
     */
    protected function loginWithTicket(): void
    {
        if (empty($this->password)) {
            throw new AuthenticationException(
                'Proxmox ticket auth requires a password; none is configured. ' .
                'Set PROXMOX_PASSWORD or use API token auth instead.'
            );
        }

        $url  = $this->baseUrl() . '/access/ticket';
        $data = [
            'username' => "{$this->username}@{$this->realm}",
            'password' => $this->password,
        ];

        try {
            $response = $this->sendRequest('POST', $url, $data, []);
        } catch (\Throwable $e) {
            throw new AuthenticationException(
                "Proxmox ticket authentication failed: {$e->getMessage()}",
                previous: $e,
            );
        }

        if (empty($response['data']['ticket'])) {
            throw new AuthenticationException(
                'Proxmox ticket authentication failed: no ticket in response.'
            );
        }

        $this->ticket       = $response['data']['ticket'];
        $this->csrf         = $response['data']['CSRFPreventionToken'];
        $this->ticketExpiry = time() + 7200 - 60;
    }

    /**
     * Returns redacted ticket state for __debugInfo().
     * Prevents live credentials from leaking into debug output.
     *
     * @return array<string, mixed>
     */
    public function redactedAuthState(): array
    {
        return [
            'ticket'       => $this->ticket       !== null ? '[REDACTED]' : null,
            'csrf'         => $this->csrf          !== null ? '[REDACTED]' : null,
            'ticketExpiry' => $this->ticketExpiry,
        ];
    }
}
<?php

namespace Cycoslave\Proxmox\Exceptions;

/**
 * Thrown when the Proxmox API returns an error response.
 *
 * Carries the HTTP status code so callers can branch on it:
 *
 *   } catch (ApiException $e) {
 *       if ($e->getStatusCode() === 404) { ... }
 *       if ($e->getStatusCode() === 403) { ... } // permission denied
 *   }
 */
class ApiException extends ProxmoxException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * The HTTP status code returned by Proxmox (e.g. 403, 404, 500).
     * Returns 0 for non-HTTP errors (e.g. JSON decode failure).
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
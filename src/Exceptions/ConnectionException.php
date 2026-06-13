<?php

namespace Cycoslave\Proxmox\Exceptions;

/**
 * Thrown when a low-level cURL / network error prevents the request
 * from reaching the Proxmox host at all.
 *
 * Causes:
 *   - Host unreachable / DNS failure
 *   - Connection timeout (CURLOPT_CONNECTTIMEOUT exceeded)
 *   - TLS handshake failure
 *   - Total timeout (CURLOPT_TIMEOUT exceeded)
 *
 * Example handler:
 *   } catch (ConnectionException $e) {
 *       // Retry with backoff, alert on-call
 *   }
 */
class ConnectionException extends ProxmoxException
{
    //
}
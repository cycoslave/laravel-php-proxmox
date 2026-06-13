<?php

namespace Cycoslave\Proxmox\Exceptions;

/**
 * Thrown when Proxmox authentication fails.
 *
 * Causes:
 *   - Invalid username / password during ticket login
 *   - Invalid or expired API token
 *   - Realm mismatch
 *
 * Example handler:
 *   } catch (AuthenticationException $e) {
 *       // Re-prompt for credentials or alert ops
 *   }
 */
class AuthenticationException extends ProxmoxException
{
    //
}
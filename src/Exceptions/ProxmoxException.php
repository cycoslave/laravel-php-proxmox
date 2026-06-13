<?php

namespace Cycoslave\Proxmox\Exceptions;

/**
 * Base exception for all laravel-proxmox errors.
 *
 * Catch this type to handle any package error generically:
 *   } catch (ProxmoxException $e) { ... }
 *
 * Or catch a specific subclass for granular handling.
 */
class ProxmoxException extends \RuntimeException
{
    //
}
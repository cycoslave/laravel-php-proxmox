<?php

namespace Cycoslave\Proxmox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cycoslave\Proxmox\Proxmox on(string $connection)
 *
 * @see \Cycoslave\Proxmox\ProxmoxManager
 */
class ProxmoxNode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proxmox-node';
    }
}

<?php

namespace Cycoslave\Proxmox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cycoslave\Proxmox\ProxmoxAccess connection(?string $name = null)
 * @method static \Cycoslave\Proxmox\ProxmoxNode    node(?string $connection = null)
 * @method static \Cycoslave\Proxmox\ProxmoxCluster cluster(?string $connection = null)
 * @method static \Cycoslave\Proxmox\ProxmoxStorage storage(?string $connection = null)
 * @method static \Cycoslave\Proxmox\ProxmoxPools   pools(?string $connection = null)
 * @method static void purge(?string $name = null)
 *
 * @see \Cycoslave\Proxmox\ProxmoxManager
 */
class Proxmox extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'proxmox'; // matches alias in ProxmoxServiceProvider
    }
}
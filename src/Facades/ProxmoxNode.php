<?php

namespace Irabbi360\Proxmox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Irabbi360\Proxmox\Proxmox on(string $connection)
 *
 * @see \Irabbi360\Proxmox\ProxmoxManager
 */
class ProxmoxNode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proxmox-node';
    }
}

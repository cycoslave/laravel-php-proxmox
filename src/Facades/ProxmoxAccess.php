<?php

namespace Cycoslave\Proxmox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cycoslave\Proxmox\LaravelProxmox
 */
class ProxmoxAccess extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proxmox-access';
    }
}

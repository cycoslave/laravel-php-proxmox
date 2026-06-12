<?php

namespace Cycoslave\Proxmox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cycoslave\Proxmox\LaravelProxmox
 */
class ProxmoxPools extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proxmox-pools';
    }
}

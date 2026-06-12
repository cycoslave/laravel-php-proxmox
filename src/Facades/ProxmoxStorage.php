<?php

namespace Cycoslave\Proxmox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cycoslave\Proxmox\LaravelProxmox
 */
class ProxmoxStorage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proxmox-storage';
    }
}

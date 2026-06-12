<?php

namespace Cycoslave\Proxmox\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cycoslave\Proxmox\LaravelProxmox
 */
class ProxmoxCluster extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'proxmox-cluster';
    }
}

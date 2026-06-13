<?php

namespace Cycoslave\Proxmox\Facades;

use Illuminate\Support\Facades\Facade;

class ProxmoxAccessApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'proxmox-access-api';
    }
}
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multiple Proxmox Connections
    |--------------------------------------------------------------------------
    |
    | This package is configured around named Proxmox connections.
    | Define each reachable Proxmox endpoint under "connections", and
    | set which one is the default using the "default" key.
    |
    | Example .env:
    |   PROXMOX_CONNECTION=site1
    |
    |   PROXMOX_SITE1_HOST=proxmox-site1.example.com
    |   PROXMOX_SITE1_USER=root
    |   PROXMOX_SITE1_PASSWORD=secret
    |
    |   PROXMOX_SITE2_HOST=proxmox-site2.example.com
    |   PROXMOX_SITE2_USER=root
    |   PROXMOX_SITE2_PASSWORD=secret
    |
    */

    'default' => env('PROXMOX_CONNECTION', 'default'),

    'connections' => [
        'default' => [
            'hostname' => env('PROXMOX_HOST', 'proxmox.example.com'),
            'username' => env('PROXMOX_USER', 'root'),
            'password' => env('PROXMOX_PASSWORD', ''),
            'realm' => env('PROXMOX_REALM', 'pam'),
            'port' => env('PROXMOX_PORT', 8006),
            'node' => env('PROXMOX_NODE', ''),
        ],

        // You can add more named connections in your application config:
        //
        // 'site1' => [
        //     'hostname' => env('PROXMOX_SITE1_HOST'),
        //     'username' => env('PROXMOX_SITE1_USER', 'root'),
        //     'password' => env('PROXMOX_SITE1_PASSWORD', ''),
        //     'realm' => env('PROXMOX_SITE1_REALM', 'pam'),
        //     'port' => env('PROXMOX_SITE1_PORT', 8006),
        //     'node' => env('PROXMOX_SITE1_NODE', ''),
        // ],
    ],
];

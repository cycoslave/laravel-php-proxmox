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
            'host'         => env('PROXMOX_HOST', 'proxmox.example.com'),
            'port'         => env('PROXMOX_PORT', 8006),
            'username'     => env('PROXMOX_USER', 'root'),
            'realm'        => env('PROXMOX_REALM', 'pam'),
            'password'     => env('PROXMOX_PASSWORD', null),
            'token_id'     => env('PROXMOX_TOKEN_ID', null),
            'token_secret' => env('PROXMOX_TOKEN_SECRET', null),
            'verify_tls'   => env('PROXMOX_VERIFY_TLS', true),
            'timeout'      => env('PROXMOX_TIMEOUT', 10),
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

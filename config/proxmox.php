<?php
// config/proxmox.php
return [
    'default' => env('PROXMOX_CONNECTION', 'primary'),

    'connections' => [
        'primary' => [
            'host'     => env('PROXMOX_HOST', '127.0.0.1'),
            'port'     => env('PROXMOX_PORT', 8006),
            'username' => env('PROXMOX_USER', 'root'),
            'realm'    => env('PROXMOX_REALM', 'pam'),         // pam | pve | ldap
            'password' => env('PROXMOX_PASSWORD', null),       // null if using API token
            'token_id' => env('PROXMOX_TOKEN_ID', null),       // USER@REALM!TOKENID
            'token_secret' => env('PROXMOX_TOKEN_SECRET', null),
            'verify_tls' => env('PROXMOX_VERIFY_TLS', true),
            'timeout'  => env('PROXMOX_TIMEOUT', 10),
        ],
        // Additional connections follow the same structure
    ],
];
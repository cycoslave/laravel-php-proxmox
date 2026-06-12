<?php

namespace Cycoslave\Proxmox;

use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

class ProxmoxManager
{
    protected Application $app;

    /** @var array<string, ProxmoxAccess> */
    protected array $connections = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Resolve a named ProxmoxAccess connection (lazy + cached).
     */
    public function connection(?string $name = null): ProxmoxAccess
    {
        // Resolve 'default' → config('proxmox.default')
        $name ??= $this->app['config']['proxmox.default']
            ?? throw new InvalidArgumentException('No default Proxmox connection configured.');

        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->resolve($name);
        }

        return $this->connections[$name];
    }

    /**
     * Instantiate ProxmoxAccess from the multi-connection config schema.
     */
    protected function resolve(string $name): ProxmoxAccess
    {
        // ✅ Reads connections.{name}.* — NOT the old flat keys
        $config = $this->app['config']["proxmox.connections.{$name}"]
            ?? throw new InvalidArgumentException("Proxmox connection [{$name}] is not defined.");

        return new ProxmoxAccess(
            host:        $config['host'],
            port:        (int) ($config['port']        ?? 8006),
            username:    $config['username'],
            realm:       $config['realm']               ?? 'pam',
            password:    $config['password']            ?? null,
            tokenId:     $config['token_id']            ?? null,
            tokenSecret: $config['token_secret']        ?? null,
            verifyTls:   (bool) ($config['verify_tls'] ?? true),
            timeout:     (int) ($config['timeout']      ?? 10),
        );
    }

    /**
     * Typed accessors used by the service provider singletons
     */

    public function node(?string $connection = null): ProxmoxNode
    {
        return new ProxmoxNode($this->connection($connection));
    }

    public function cluster(?string $connection = null): ProxmoxCluster
    {
        return new ProxmoxCluster($this->connection($connection));
    }

    public function storage(?string $connection = null): ProxmoxStorage
    {
        return new ProxmoxStorage($this->connection($connection));
    }

    public function pools(?string $connection = null): ProxmoxPools
    {
        return new ProxmoxPools($this->connection($connection));
    }

    /** Flush a cached connection (useful in tests). */
    public function purge(?string $name = null): void
    {
        if ($name === null) {
            $this->connections = [];
        } else {
            unset($this->connections[$name]);
        }
    }
}
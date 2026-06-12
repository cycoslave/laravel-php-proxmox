<?php

namespace Irabbi360\Proxmox;

use Illuminate\Contracts\Container\Container;

class ProxmoxManager
{
    /** @var Container */
    protected $app;

    /**
     * Cached Proxmox client instances per connection name.
     *
     * @var array<string, Proxmox>
     */
    protected $clients = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Get a Proxmox client for the given connection name.
     * If no name is given, the configured default connection is used.
     */
    public function connection(?string $name = null): Proxmox
    {
        $name = $name ?? $this->getDefaultConnection();

        if (! isset($this->clients[$name])) {
            $this->clients[$name] = $this->resolveConnection($name);
        }

        return $this->clients[$name];
    }

    /**
     * Entry point to explicitly select a connection name.
     *
     * Example:
     *   $site1 = $manager->on('site1');
     *   $vms = $site1->getVMs('pve-node');
     */
    public function on(string $name): Proxmox
    {
        return $this->connection($name);
    }

    protected function getDefaultConnection(): string
    {
        return (string) ($this->app['config']['proxmox.default'] ?? 'default');
    }

    protected function resolveConnection(string $name): Proxmox
    {
        $config = $this->getConnectionConfig($name);

        return new ProxmoxNode(
            $config['hostname'],
            $config['username'],
            $config['password'],
            $config['realm'] ?? 'pam',
            (int) ($config['port'] ?? 8006)
        );
    }

    protected function getConnectionConfig(string $name): array
    {
        $config = $this->app['config']['proxmox'];

        if (! isset($config['connections'][$name])) {
            throw new \InvalidArgumentException("Proxmox connection [{$name}] is not defined.");
        }

        return $config['connections'][$name];
    }
}

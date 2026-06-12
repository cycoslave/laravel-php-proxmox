<?php

namespace Cycoslave\Proxmox;

use Illuminate\Support\ServiceProvider;

class ProxmoxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/proxmox.php', 'proxmox'
        );

        // Core manager — resolves named connections via connections.{name} schema
        $this->app->singleton(ProxmoxManager::class, function ($app) {
            return new ProxmoxManager($app);
        });

        // Alias so Proxmox facade resolves the manager directly
        $this->app->alias(ProxmoxManager::class, 'proxmox');

        // Each facade-backed singleton resolves the correct class from the
        // default connection. The manager is responsible for reading
        // config('proxmox.connections.{name}.*') — NOT flat keys.
        $this->app->singleton('proxmox-node', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->node();           // returns ProxmoxNode
        });

        $this->app->singleton('proxmox-cluster', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->cluster();        // returns ProxmoxCluster
        });

        $this->app->singleton('proxmox-storage', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->storage();        // returns ProxmoxStorage
        });

        $this->app->singleton('proxmox-pools', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->pools();          // returns ProxmoxPools
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/proxmox.php' => config_path('proxmox.php'),
            ], 'proxmox-config');
        }
    }
}
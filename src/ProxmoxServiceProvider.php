<?php

namespace Irabbi360\Proxmox;

use Illuminate\Support\ServiceProvider;

class ProxmoxServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/proxmox.php', 'proxmox'
        );

        // Register a manager that can handle multiple named Proxmox connections
        $this->app->singleton(ProxmoxManager::class, function ($app) {
            return new ProxmoxManager($app);
        });

        // Backwards-compatible bindings for existing facades.
        // These will always resolve the default connection from the manager.
        $this->app->singleton('proxmox-node', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->connection();
        });

        $this->app->singleton('proxmox-cluster', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->connection();
        });

        $this->app->singleton('proxmox-storage', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->connection();
        });

        $this->app->singleton('proxmox-pools', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->connection();
        });

        $this->app->singleton('proxmox-access', function ($app) {
            /** @var ProxmoxManager $manager */
            $manager = $app->make(ProxmoxManager::class);

            return $manager->connection();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Publish the config file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/proxmox.php' => config_path('proxmox.php'),
            ], 'proxmox-config');
        }
    }
}

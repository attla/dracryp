<?php

namespace Attla\Pincryp;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class PincrypServiceProvider extends BaseServiceProvider
{
    /**
     * Package name.
     *
     * @var string
     */
    private const NAME = 'pincryp';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), static::NAME);

        $this->app->singleton(static::NAME, function ($app) {
            $config = $app['config'][static::NAME] ?: [];

            return new Factory(new Config(is_array($config) ? $config : []));
        });
    }

    /**
     * Bootstrap the package.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => $this->app->configPath(static::NAME . '.php'),
            ], 'config');
        }
    }

    /**
     * Get config path.
     *
     * @return string
     */
    protected function configPath()
    {
        return __DIR__ . '/../config/' . static::NAME . '.php';
    }
}

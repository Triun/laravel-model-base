<?php

namespace Triun\ModelBase;

use Illuminate\Support\ServiceProvider;
use Triun\ModelBase\Console\MakeBulkCommand;
use Triun\ModelBase\Console\MakeCommand;

class ModelBaseServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     */
    protected bool $defer = true;

    public function boot(): void
    {
        $configPath = realpath(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'model-base.php'
        );

        $this->publishes(
            [$configPath => app()->make('path.config') . DIRECTORY_SEPARATOR . 'model-base.php'],
            'config'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([MakeCommand::class, MakeBulkCommand::class]);
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $configPath = realpath(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'model-base.php'
        );

        $this->mergeConfigFrom($configPath, 'model-base');
    }
}

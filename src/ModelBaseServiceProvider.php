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
        //$viewPath = __DIR__.'/../resources/views';
        //$this->loadViewsFrom($viewPath, 'model-base');

        $configPath = realpath(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'model-base.php'
        );

        $this->publishes(
            [$configPath => app()->make('path.config') . DIRECTORY_SEPARATOR . 'model-base.php'],
            'config'
        );

        $this->mergeConfigFrom($configPath, 'model-base');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $configPath = __DIR__ . '/../config/model-base.php';
        $this->mergeConfigFrom($configPath, 'model-base');

        $this->app->singleton(
            'command.make.model-base',
            function ($app) {
                return new MakeCommand($app['files']);
            }
        );

        $this->commands('command.make.model-base');

        $this->app->singleton(
            'command.make.model-base-bulk',
            function ($app) {
                return new MakeBulkCommand($app['files']);
            }
        );

        $this->commands('command.make.model-base-bulk');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'command.make.model-base',
            'command.make.model-base-bulk',
        ];
    }
}

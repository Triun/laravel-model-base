<?php

namespace Triun\ModelBase;

use Illuminate\Support\ServiceProvider;
use Triun\ModelBase\Console\MakeCommand;
use Triun\ModelBase\Console\MakeBulkCommand;

class ModelBaseServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
//        $viewPath = __DIR__.'/../resources/views';
//        $this->loadViewsFrom($viewPath, 'model-base');

        $configPath = realpath(dirname(__DIR__)
            . DIRECTORY_SEPARATOR . 'config'
            . DIRECTORY_SEPARATOR . 'model-base.php');

        $this->publishes([$configPath => config_path('model-base.php')], 'config');

        $this->mergeConfigFrom($configPath, 'model-base');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/model-base.php';
        $this->mergeConfigFrom($configPath, 'model-base');

        $this->app['command.make.model-base'] = $this->app->share(
            function ($app) {
                return new MakeCommand($app['files']);
            }
        );

        $this->commands('command.make.model-base');

        $this->app['command.make.model-base-bulk'] = $this->app->share(
            function ($app) {
                return new MakeBulkCommand($app['files']);
            }
        );

        $this->commands('command.make.model-base-bulk');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.make.model-base',
            'command.make.model-base-bulk',
        ];
    }
}

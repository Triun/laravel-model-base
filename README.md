# Laravel Model Base

[![Latest Stable Version][ico-stable]][link-packagist]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Pre Release Version on Packagist][ico-pre-release]][link-packagist]
[![Latest Unstable Version][ico-unstable]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

Generate Eloquent Model Base for Laravel.

## About

Laravel-Model-Base is a Laravel command to perform repetitive tasks while creating new models, and saving it into a abstract model base, so you can update it any time, without need to worry about overwriting your manual changes in the model.

The main goal of the model base it's generate eloquent configurations based on the database table architecture, meaning that it could not be any business logic implemented, or any other logic not comming from the database itself. If you are interested in adding extra properties, methods, interfaces or traits, you can do so in the model itself.

This generator can be customised by the config parameters, but it can also be extended by the [modifiers](#modifiers). 

The model will be optionally generated too, but in this case, it will never be able to be overwritten by this tool.

## Installation

Require this package with composer using the following command:

```bash
composer require --dev triun/laravel-model-base
```

After updating composer, add the service provider to the providers array in config/app.php

```php
Triun\ModelBase\ModelBaseServiceProvider::class,
```

### Development only installation

To install this package on only development systems, add the `--dev` flag to your composer command:

```bash
composer require --dev triun/laravel-model-base:dev-master
```

Instead of adding the service provider in the `config/app.php` file, you should add the following code to your `app/Providers/AppServiceProvider.php` file, within the `register()` method:

```php
public function register()
{
    if ($this->app->environment() !== 'production') {
        $this->app->register(\Triun\ModelBase\ModelBaseServiceProvider::class);
    }
    // ...
}
```

This will allow your application to load the Laravel Model Base on non-production environments.

## Usage

To create one model base

```bash
php artisan make:mode-base table_name [--connection connection_name]
```

For Bulk creation

```bash
php artisan make:mode-base-bulk [--connection connection_name]
```

Note: If you don't set up the connections in the config file (`model-base.bulk.connections`),
it will try to run all available connections from your database config file (`database.connections`).

## Customize

You can publish the configuration file into your app with:

```bash
php artisan vendor:publish --provider="Triun\ModelBase\ModelBaseServiceProvider" --tag=config
```

# Lumen

If you are using Lumen, you may need to do some manual steps...

Copy the config file into your `config` directory:

```bash
cp vendor/triun/laravel-model-base/config/model-base.php config/
```

And you may also need to uncomment the following lines in `bootstrap/app.php` file:

```php
<?php
# bootstrap/app.php

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades(true, [
    Illuminate\Support\Facades\App::class => 'App',
    Illuminate\Support\Facades\File::class => 'File',
]);
$app->withEloquent();
$app->register(App\Providers\AppServiceProvider::class);
```

So we can use Facades and Eloquent, as well as add some content in the `AppServiceProvider`:

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
        
        if ($this->app->environment() !== 'production') {
            $this->app->configure('model-base');
            $this->app->register(\Triun\ModelBase\ModelBaseServiceProvider::class);
        }
    }
}
```

// TODO

Besides the configuration file, you can also add or create your own [modifiers](#modifiers).

## Modifiers

If you want to add behaviours to the generator, you can do so using the skeleton modifiers.

### Modifiers Packages

Those are some modifiers packages:

// TODO

### Add modifiers

If you already have any modifier package, you can load it by adding it in the `config/model-base.php` file, in the 'modifiers' array.

// TODO

### Create your own modifiers

How to create a modifier.

// TODO

## Documentation

The documentation for Laravel-Model-Base is available on the [Github wiki](https://github.com/Triun/laravel-model-base/wiki).

## Issues
   
Bug reports and feature requests can be submitted on the [Github Issue Tracker](https://github.com/Triun/laravel-model-base/issues).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for information.

## License

The Laravel Model Base is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


[ico-version]: https://img.shields.io/packagist/v/triun/laravel-model-base.svg
[ico-pre-release]: https://img.shields.io/packagist/vpre/triun/laravel-model-base.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://travis-ci.org/Triun/laravel-model-base.svg?branch=master
[ico-code-quality]: https://img.shields.io/scrutinizer/g/triun/laravel-model-base.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/triun/laravel-model-base.svg?style=flat-square
[ico-stable]: https://poser.pugx.org/triun/laravel-model-base/v/stable
[ico-unstable]: https://poser.pugx.org/triun/laravel-model-base/v/unstable

[link-packagist]: https://packagist.org/packages/triun/laravel-model-base
[link-travis]: https://travis-ci.org/Triun/laravel-model-base
[link-downloads]: https://packagist.org/packages/triun/laravel-model-base
[link-author]: https://github.com/triun

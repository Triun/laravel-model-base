# Laravel Model Base

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Latest Unstable Version][ico-unstable]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

Generate Eloquent Model Base for Laravel.

## About

Laravel-Model-Base is a Laravel command to perform repetitive tasks while creating new models, and saving it into a abstract model base, so you can update it any time, without need to worry about overwriting your manual changes in the model.

The main goal of the model base it's generate eloquent configurations based on the database table architecture, meaning that it could not be any business logic implemented, or any other logic not comming from the database itself. If you are interested in adding extra properties, methods, interfaces or traits, you can do so in the model itself.

This generator can be customised by the config parameters, but it can also be extended by the [modificators](#modificators). 

The model will be optionally generated too, but in this case, it will never be able to be overwritten by this tool.

## Installation

```bash
composer require --dev triun/laravel-model-base:dev-master
```

Or edit composer.json

```json
{
    "require-dev": {
        "triun/laravel-model-base": "dev-master"
    }
}
```

And run

```bash
composer update
```

Enable the service provider adding it to config/app.php

```php
<?php
return [
    // ...
    'providers' => [
        // ...
        Triun\ModelBase\ModelBaseServiceProvider::class,
     ],
     // ...
];
```

## Usage

To create one model base

```bash
php artisan make:mode-base table_name [--connection connection_name]
```

For Bulk creation

```bash
php artisan make:mode-base-bulk [--connection connection_name]
```

Note: if the connection is not defined, it would get the default one.

## Customize

In order to publish the configuration file into your app, use

```bash
php artisan vendor:publish
```

// TODO

## Modificators

If you want to add behaviours to the generator, you can do so using the skeleton modificators.

### Modifictors Packages

Those are some packages which you can use to add 

// TODO

### Add modificators

How to create a modificator.

// TODO

## License

The Laravel Model Base is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


[ico-version]: https://img.shields.io/packagist/v/triun/laravel-model-base.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://travis-ci.org/Triun/laravel-model-base.svg?branch=master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/triun/laravel-model-base.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/triun/laravel-model-base.svg?style=flat-square
[ico-unstable]: https://poser.pugx.org/triun/laravel-model-base/v/unstable

[link-packagist]: https://packagist.org/packages/triun/laravel-model-base
[link-travis]: https://travis-ci.org/Triun/laravel-model-base
[link-downloads]: https://packagist.org/packages/triun/laravel-model-base
[link-author]: https://github.com/triun
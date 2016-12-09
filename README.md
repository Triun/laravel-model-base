# laravel-model-base

Generate Eloquent Model Base for Laravel.

## Installation

```bash
composer require triun/laravel-model-base:dev-master
```

Or edit composer.json and add

```json
{
    "require": {
        "triun/laravel-model-base": "dev-master"
    }
}
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



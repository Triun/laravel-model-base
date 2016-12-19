# Contributing
  
Before you contribute code to Laravel-Model-Base, please make sure it conforms to the PSR-2 coding standard and that the PHP_Laravel-Model-Base unit tests still pass. The easiest way to contribute is to work on your own fork.

If you do this, you can run the following commands to check if everything is ready to submit.

## Dependencies

In order to load the dependencies, you should run composer:

```bash
composer install
```

## PSR-2 Specs

This package follows the PSR-2 coding standard.

- [PSR-2: Coding Style Guide](http://www.php-fig.org/psr/psr-2/)
- [OPNsense PSR-2 Coding Style Guide](https://docs.opnsense.org/development/guidelines/psr2.html)

To test if your contribution passes the standard, you can use the command:

```bash
./vendor/bin/phpcs --standard=psr2 src/
```

Which should give you no output, indicating that there are no coding standard errors.


## Unit testing

You can write your own tests and add them to the `test` directory.

To run the test command:

```bash
./vendor/bin/phpunit
```

Which should give you no failures or errors.

A coverage and logs will be created in the `build` directory.

In order to give support to older versions, you should test it also with the lowest composer packages:

```bash
composer update --prefer-stable --prefer-lowest
```

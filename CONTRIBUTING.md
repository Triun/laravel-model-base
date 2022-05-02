# Contributing
  
Before you contribute code to Laravel-Model-Base, please make sure it conforms to the PSR-2 coding standard and that the PHP_Laravel-Model-Base unit tests still pass. The easiest way to contribute is to work on your own fork.

If you do this, you can run the following commands to check if everything is ready to submit.

## Guidelines of interest

Before going through the rest of this documentation, please take some time to read:
- Documentation for [Orchestral Testbench](https://github.com/orchestral/testbench) package, that can be found on the [packages.tools/testbench](https://packages.tools/testbench)
- [Package Development](https://laravel.com/docs/9.x/packages) section of Laravel's own documentation.

## Local environment

We recommend the official php docker image:

```bash
docker run -it --rm -p 8080:80 -v "$(pwd):/opt/app" -w '/opt/app' --name php-7-4-model-base-dev php:7.4 bash
```

## Dependencies

In order to load the dependencies, you should [install composer](https://getcomposer.org/download/):

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer
apt-get -y update && apt-get install -y unzip zip
```

And run:

```bash
composer install
```

## PSR-2 Specs

This package follows the PSR-2 coding standard.

- [PSR-2: Coding Style Guide](http://www.php-fig.org/psr/psr-2/)
- [OPNsense PSR-2 Coding Style Guide](https://docs.opnsense.org/development/guidelines/psr2.html)

To test if your contribution passes the standard, you can use the command:

```bash
./vendor/bin/phpcs --standard=phpcs.xml
```

Which should give you no output, indicating that there are no coding standard errors.


## Unit testing

You can write your own tests and add them to the `test` directory.

To run the test command:

```bash
./vendor/bin/phpunit --configuration phpunit_sqlite.xml --coverage-text
```

Which should give you no failures or errors.

A coverage and logs will be created in the `build` directory.

In order to give support to older versions, you should test it also with the lowest composer packages:

```bash
composer update --prefer-stable --prefer-lowest
```

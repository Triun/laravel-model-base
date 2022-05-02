Change Log
==========

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html)
as it's described in the [Contributing Guide](CONTRIBUTING.md).

# Proposals

We do not give estimated times for completion on `Accepted` Proposals.

- [Accepted][Accepted]
- [Rejected][Rejected]

---

## [v6.0.14] - 2020-02-05

`Fixed`

- Nullable logic fixed.

## [v6.0.13] - 2019-12-31

`Changed`

- Quote default values.

## [v6.0.12] - 2019-12-31

`Changed`

- Put null after type in phpDoc.

`Fix`

- Fix traits as not a line when empty.

## [v6.0.11] - 2019-12-31

`Fix`

- Body extra line when the class is empty.

## [v6.0.10] - 2019-12-31

`Added`

- Ability to override the model files.

## [v6.0.9] - 2019-12-31

`Added`

- phpDoc of nullable values.
- Additional phpDoc description of the database type.

## [v6.0.8] - 2019-12-31

`Changed`

- Normalise value types `real` and `double` as `float`.

`Fixed`

- BIGINT to `float|int` in phpDoc.
- Primary keys can be forced to be `string` and the phpDoc as `string|float` or `string|int` to avoid Laravel issues.

## [v6.0.7] - 2019-12-30

`Fixed`

- Force type conversion to either `int` or `string`, in model primary type, in order to avoid types or code issues.

## [v6.0.6] - 2019-12-30

`Added`

- Ability to choose between short types (`int`, `bool`) or long types (`integer`, `boolean`) which are equivalent for both Laravel and phpDoc.

## [v6.0.5] - 2019-12-27

`Changed`

- Change back `double` as `float` as either `double`, `float` or `real` are the same in PHP.

`Fixed`

- string in phpDoc.

## [v6.0.4] - 2019-12-27

`Added`

- New helpers: DBALHelper and TypeHelper.

`Changed`

- SchemaUtil refactoring with breaking changes.
- Normalisation of `integer` as `int` and `boolean` as `bool`.

`Fixed`

- Model `keyType` fix: use laravel types instead of database types.

## [v6.0.3] - 2019-12-26

`Added`

- Ability to customize phpDoc properties types.

`Fixes`

- Move the custom interfaces, traits, uses, and the new phpDoc into the model, to avoid traits errors.

## [v6.0.2] - 2019-12-26

`Added`

- Custom interfaces, traits and uses.

`Changed`

- Composer updated.

`Fixes`

- Fix class use sorting.

## [v6.0.1] - 2019-09-17

`Removed`

- Removed `whereAttribute` phpDoc properties helpers.

## [v6.0.0] - 2019-09-17

`Added`

- Upgrade to Laravel 6.0.

`Removed`

- Removed support for PHP 7.1.

## [v5.8.2] - 2019-09-17

`Removed`

- Removed `whereAttribute` phpDoc properties helpers.

## [v5.8.1] - 2019-09-17

`Removed`

- Remove all `array_*` and `str_*` deprecated global helpers (https://laravel.com/docs/5.8/upgrade#support).

## [v5.8.0] - 2019-09-17

`Added`

- Upgrade to Laravel 5.8.

## [v5.7.0] - 2019-09-17

`Added`

- Upgrade to Laravel 5.7.

## [v5.6.2] - 2019-09-17

`Added`

- Support for PHP 7.3.

`Changed`

- Upgrade composer dependencies.

`Removed`

- Removed support for PHP 7.0 and hhvm.

## [v5.6.1] - 2018-05-01

`Added`

- Config setting (`bulk.connections`) to specify the default connections in bulk mode.

`Fixes`

- Bulk command exception fixed: `An argument with name "name" already exists.`.
- Connections array bug fixed.

## [v5.6.0] - 2018-05-01

`Changed`

- `connection` option accepts more than one connection, and gets all connections as the default value.

## [v5.5.1] - 2018-05-01

`Fixes`

- Fix backwards compatibility for `renames`.

## [v5.5.0] - 2018-05-01

`Added`

- In `config/model-base.php`, `table.prefixes` will remove the defined prefixes to generate the model names.

`Changed`

- In `config/model-base.php`, the key `renames` now is `table.renames`.

## [v5.4.1] - 2017-12-11

`Fixes`

- Null as lowercase to pass PSR-2 standard.

## [v5.4.0] - 2017-12-11

`Added`

- Support for Laravel 5.4
- Support for Lumen 5.4 (with some tuning explained in [README.md](README.md))
- New configurable aliases tools: `except`, `force`, `pre`, `prefix`, `suffix` and `post`.

`Changed`

- In `config/model-base.php`, the key `camel_to_snake` now is `column.camel_to_snake`.

## [v5.2.0] - 2017-07-14

`Added`

- Working collection of utils, services providers and commands to generate models and model bases.
- Support for Linux, Laravel 5.2, MySQL and PHP versions `>=5.5.9`, `5.6.x`, `7.0.x` and `7.1.x`.
It can work with `nightly`, but is not compatible with `hhvm`, as it uses different libraries.

## v1.0.0 - 2016-12-08

`INIT`

- Initial release.

[Accepted]: https://github.com/Triun/laravel-model-base/labels/Accepted
[Rejected]: https://github.com/Triun/laravel-model-base/labels/Rejected

[Unreleased]: https://github.com/Triun/laravel-model-base/compare/v6.0.14...HEAD
[v6.0.14]: https://github.com/Triun/laravel-model-base/compare/v6.0.13...v6.0.14
[v6.0.13]: https://github.com/Triun/laravel-model-base/compare/v6.0.12...v6.0.13
[v6.0.12]: https://github.com/Triun/laravel-model-base/compare/v6.0.11...v6.0.12
[v6.0.11]: https://github.com/Triun/laravel-model-base/compare/v6.0.10...v6.0.11
[v6.0.10]: https://github.com/Triun/laravel-model-base/compare/v6.0.9...v6.0.10
[v6.0.9]: https://github.com/Triun/laravel-model-base/compare/v6.0.8...v6.0.9
[v6.0.8]: https://github.com/Triun/laravel-model-base/compare/v6.0.7...v6.0.8
[v6.0.7]: https://github.com/Triun/laravel-model-base/compare/v6.0.6...v6.0.7
[v6.0.6]: https://github.com/Triun/laravel-model-base/compare/v6.0.5...v6.0.6
[v6.0.5]: https://github.com/Triun/laravel-model-base/compare/v6.0.4...v6.0.5
[v6.0.4]: https://github.com/Triun/laravel-model-base/compare/v6.0.3...v6.0.4
[v6.0.3]: https://github.com/Triun/laravel-model-base/compare/v6.0.2...v6.0.3
[v6.0.2]: https://github.com/Triun/laravel-model-base/compare/v6.0.1...v6.0.2
[v6.0.1]: https://github.com/Triun/laravel-model-base/compare/v6.0.0...v6.0.1
[v6.0.0]: https://github.com/Triun/laravel-model-base/compare/v5.8.1...v6.0.0
[v5.8.2]: https://github.com/Triun/laravel-model-base/compare/v5.8.1...v5.8.2
[v5.8.1]: https://github.com/Triun/laravel-model-base/compare/v5.8.0...v5.8.1
[v5.8.0]: https://github.com/Triun/laravel-model-base/compare/v5.7.0...v5.8.0
[v5.7.0]: https://github.com/Triun/laravel-model-base/compare/v5.6.2...v5.7.0
[v5.6.2]: https://github.com/Triun/laravel-model-base/compare/v5.6.1...v5.6.2
[v5.6.1]: https://github.com/Triun/laravel-model-base/compare/v5.6.0...v5.6.1
[v5.6.0]: https://github.com/Triun/laravel-model-base/compare/v5.5.1...v5.6.0
[v5.5.1]: https://github.com/Triun/laravel-model-base/compare/v5.5.0...v5.5.1
[v5.5.0]: https://github.com/Triun/laravel-model-base/compare/v5.4.1...v5.5.0
[v5.4.1]: https://github.com/Triun/laravel-model-base/compare/v5.4.0...v5.4.1
[v5.4.0]: https://github.com/Triun/laravel-model-base/compare/v5.2.0...v5.4.0

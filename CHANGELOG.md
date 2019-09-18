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

## [v5.9.0] - 2019-09-17

`Added`

- Custom interfaces, traits, uses and phpDoc tags type and description.

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

[Unreleased]: https://github.com/Triun/laravel-model-base/compare/v5.9.0...master
[v5.9.0]: https://github.com/Triun/laravel-model-base/compare/v5.8.2...v5.9.0
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

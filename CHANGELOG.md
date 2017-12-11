Change Log
==========

This project follows [Semantic Versioning](CONTRIBUTING.md).

# Proposals

We do not give estimated times for completion on `Accepted` Proposals.

- [Accepted](https://github.com/Triun/laravel-model-base/labels/Accepted)
- [Rejected](https://github.com/Triun/laravel-model-base/labels/Rejected)

---

## [Unreleased]

### Added

- Support for Laravel 5.4
- Support for Lumen 5.4 (with some tuning explained in [README.md](README.md))
- New configurable aliases tools: `except`, `force`, `pre`, `prefix`, `suffix` and `post`.

## Changed

- In `config/model-base.php`, the key `camel_to_snake` now is `column.camel_to_snake`.

## v5.2 - 2017-07-014

### Added

- Working collection of utils, services providers and commands to generate models and model bases.
- Support for Linux, Laravel 5.2, MySQL and PHP versions `>=5.5.9`, `5.6.x`, `7.0.x` and `7.1.x`.
It can work with `nightly`, but is not compatible with `hhvm`, as it uses different libraries.

## v1.0.0 - 2016-12-08

`INIT`

- Initial release.

[Unreleased]: https://github.com/Triun/laravel-model-base/compare/5.2...master

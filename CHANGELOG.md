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

## [v5.5.0][Unreleased] - Unreleased

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

[Unreleased]: https://github.com/Triun/laravel-model-base/compare/v5.4.1...master
[v5.4.1]: https://github.com/Triun/laravel-model-base/compare/v5.4.0...v5.4.1
[v5.4.0]: https://github.com/Triun/laravel-model-base/compare/v5.2.0...v5.4.0

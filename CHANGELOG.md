Change Log
============

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html)
as it's described in the [Contributing Guide](CONTRIBUTING.md).

# Proposals

We do not give estimated times for completion on `Accepted` Proposals.

- [Accepted][Accepted]
- [Rejected][Rejected]

---

## [Unreleased]

`Added`

- Support for Laravel 5.4
- Support for Lumen 5.4 (with some tunning explained in [README.md](README.md))
- New configurable renaming tools: `pre`, `prefix`, `suffix` and `post`.

`Changed`

- In `config/model-base.php`, the key `camel_to_snake` now is `column.rename.force`.

## v5.2.1-beta - 2017-07-014

`Added`

- Working collection of utils, services providers and commands to generate models and model bases.
- Support for Linux, Laravel 5.2, MySQL and PHP versions `>=5.5.9`, `5.6.x`, `7.0.x` and `7.1.x`.
It can work with `nightly`, but is not compatible with `hhvm`, as it uses different libraries.

## v5.2.0-beta - 2016-12-08

`INIT`

- Initial release.

[Unreleased]: https://github.com/Triun/laravel-model-base/compare/5.2...5.2

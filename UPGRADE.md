Upgrade
=======

For more details about the changes, you can see the [CHANGELOG.md](CHANGELOG.md).

# [9.0 to 9.1]

- This version will be having some changes adapting new functionalities of Laravel 9, and some methods and properties 
  may change its names and/or visibility.

## [5.8 to 9.0]

- All the classes has been upgraded so that can be compatible with PHP 8.1.
  If you are extending or invoking any class, make sure to update your code accordingly 

## [5.2 to 5.4]

### `config/model-base.php`

- Move the array named `camel_to_snake` to `column.rename.force`.

[5.8 to 9.x]: https://github.com/Triun/laravel-model-base/compare/5.8...9.x
[5.2 to 5.4]: https://github.com/Triun/laravel-model-base/compare/5.2...5.4

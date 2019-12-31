<?php

declare(strict_types=1);

namespace Triun\ModelBase\Helpers;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Schema\Column;
use Exception;
use Illuminate\Database\Connection;
use Triun\ModelBase\ModelBaseConfig;

/**
 * Class TypeHelper
 *
 * @package Triun\ModelBase\Helpers
 */
abstract class TypeHelper
{
    /**
     * @var callback[]|callable[]
     */
    private static $cast_callbacks = [];

    /**
     * true:  `int`,     `bool`
     * false: `integer`, `boolean`
     *
     * @var bool
     */
    public static $shortTypes = true;

    /**
     * @param callback|callable $callback
     *
     * @return void
     * @throws Exception
     */
    public static function registerCastCallback($callback): void
    {
        if (!is_callable($callback)) {
            throw new Exception('Cast Callback not callable');
        }

        self::$cast_callbacks[] = $callback;
    }

    /**
     * @param \Doctrine\DBAL\Types\Type $type
     *
     * @return string
     * @throws Exception
     */
    public static function getDbType(Type $type): string
    {
        return $type->getName();
    }

    /**
     * Cast Types: int, integer, real, float, double, decimal, string, bool, boolean, object, array, json, collection,
     * date, datetime, timestamp
     *
     * Custom values:
     * decimal:<digits> -> decimal
     * date:<datetime_format> -> custom_datetime
     * datetime:<datetime_format> -> custom_datetime
     *
     * @param \Doctrine\DBAL\Types\Type $type
     *
     * @return string|null
     *
     * @link https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html
     * @link https://laravel.com/docs/5.8/eloquent-mutators#attribute-casting
     * @see  \Illuminate\Database\Eloquent\Concerns\HasAttributes::castAttribute()
     */
    public static function getLaravelType(Type $type): ?string
    {
        // TODO: Add timestamp checking.
        switch ($type->getName()) {
            case Types::STRING:
            case Types::TEXT:
                return 'string';
            case Types::INTEGER:
            case Types::SMALLINT:
                return static::$shortTypes ? 'int' : 'integer';
            // real and double, in PHP, is equivalent to float
            case Types::DECIMAL: // decimal:<digits>
                //return 'decimal';
                //return 'double';
            case Types::BIGINT:
                //return 'real';
            case Types::FLOAT:
                return 'float';
            case Types::BOOLEAN:
                return static::$shortTypes ? 'bool' : 'boolean';
            case Types::OBJECT:
                return 'object';
            case Types::ARRAY:
            case Types::SIMPLE_ARRAY:
            case Types::JSON_ARRAY: // deprecated. Keeping for backwards compatibility.
                //return 'collection';
                return 'array';
            case Types::JSON:
                return 'json';
            case Types::DATE_MUTABLE:
            case Types::DATE_IMMUTABLE:
                return 'date';
            case Types::DATETIME_MUTABLE:
            case Types::DATETIME_IMMUTABLE:
            case Types::DATETIMETZ_MUTABLE:
            case Types::DATETIMETZ_IMMUTABLE:
            case Types::TIME_MUTABLE:
            case Types::TIME_IMMUTABLE:
                return 'datetime';
            case Types::BINARY:
            case Types::BLOB:
            case Types::GUID:         // ??
            case Types::DATEINTERVAL: // ??
            default:
                return null;
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Column $column
     * @param string                       $tableName
     * @param ModelBaseConfig              $config
     * @param Connection                   $conn
     *
     * @return string
     * @throws Exception
     */
    public static function getLaravelCastType(
        Column $column,
        string $tableName,
        ModelBaseConfig $config,
        Connection $conn
    ): string {
        $columnType = $column->getType();

        foreach ($config->get('cast', []) as $cast) {
            if (!isset($cast['cast_type'])) {
                throw new Exception("Cast type not defined");
            }
            if (!$config->match($cast['field'], $column->getName())) {
                continue;
            }
            if (isset($cast['db_type']) && !$config->match($cast['db_type'], $columnType->getName())) {
                continue;
            }
            if (isset($cast['table']) && !$config->match($cast['table'], $tableName)) {
                continue;
            }
            if (isset($cast['connection']) && !$config->match($cast['connection'], $conn->getName())) {
                continue;
            }

            return $cast['cast_type'];
        }

        $castType = static::getLaravelType($columnType);

        // Cast callbacks
        foreach (self::$cast_callbacks as $callback) {
            call_user_func($callback, $column, $tableName, $conn, $castType);
        }

        return $castType;
    }

    /**
     * "string"|"integer"|"int"|"boolean"|"bool"|"float"|"double"|"object"|"mixed"|"array"|"resource"|"scalar"
     * |"void"|"null"|"callback"|"false"|"true"|"self"
     *
     * @param \Doctrine\DBAL\Types\Type $type
     *
     * @return string
     * @throws Exception
     *
     * @link https://docs.phpdoc.org/references/phpdoc/tags/property.html
     * @link https://docs.phpdoc.org/references/phpdoc/types.html
     */
    public static function getPhpDocType(Type $type): string
    {
        // TODO: Add timestamp checking.
        switch ($type->getName()) {
            case Types::STRING:
            case Types::TEXT:
            case Types::GUID:
                return 'string';
            case Types::INTEGER:
            case Types::SMALLINT:
                return static::$shortTypes ? 'int' : 'integer';
            case Types::BIGINT:
                // The PHP int is not big enough to handle this int
                // So it could be returned as either string or float
                // In PHP, float, real and double is equivalent
                //return 'real';
                return 'string|float|' . (static::$shortTypes ? 'int' : 'integer');
            case Types::FLOAT:
            case Types::DECIMAL:
                return 'float';
            case Types::BOOLEAN:
                return static::$shortTypes ? 'bool' : 'boolean';
            case Types::OBJECT:
                return 'object';
            case Types::ARRAY:
            case Types::SIMPLE_ARRAY:
            case Types::JSON_ARRAY: // deprecated. Keeping for backwards compatibility.
                return 'array';
            case Types::JSON:
                //return 'json';
                return 'object|array';
            case Types::DATE_MUTABLE:
            case Types::DATE_IMMUTABLE:
            case Types::DATETIME_MUTABLE:
            case Types::DATETIME_IMMUTABLE:
            case Types::DATETIMETZ_MUTABLE:
            case Types::DATETIMETZ_IMMUTABLE:
            case Types::TIME_MUTABLE:
            case Types::TIME_IMMUTABLE:
                //return '\\' . \Carbon\Carbon::class;
                //return '\\Carbon\\Carbon';
                return 'string';
            case Types::BINARY:
            case Types::BLOB:
                //return 'resource';
                return 'mixed';
            case Types::DATEINTERVAL: // ??
            default:
                return 'mixed';
        }
    }

    /**
     * @param \Doctrine\DBAL\Types\Type $type
     *
     * @return bool
     */
    public static function isDateTime(Type $type): bool
    {
        switch ($type->getName()) {
            case Types::DATE_MUTABLE:
            case Types::DATE_IMMUTABLE:
            case Types::DATETIME_MUTABLE:
            case Types::DATETIME_IMMUTABLE:
            case Types::DATETIMETZ_MUTABLE:
            case Types::DATETIMETZ_IMMUTABLE:
            case Types::TIME_MUTABLE:
            case Types::TIME_IMMUTABLE:
                return true;
            default:
                return false;
        }
    }
}

<?php

declare(strict_types=1);

namespace Triun\ModelBase\Helpers;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Exception;
use Illuminate\Database\Connection;
use Triun\ModelBase\ModelBaseConfig;

abstract class TypeHelper
{
    /**
     * @var callable[]
     */
    private static array $cast_callbacks = [];

    /**
     * true:  `int`,     `bool`
     * false: `integer`, `boolean`
     */
    public static bool $shortTypes = true;

    /**
     * @throws Exception
     */
    public static function registerCastCallback(callable $callback): void
    {
        if (!is_callable($callback)) {
            throw new Exception('Cast Callback not callable');
        }

        self::$cast_callbacks[] = $callback;
    }

    /**
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
                // In PHP, int is not big enough
                // In PHP, float, real and double is equivalent
                // So it could be returned as either string or float
                //return 'real';
                return 'float|' . (static::$shortTypes ? 'int' : 'integer');
            case Types::FLOAT:
            case Types::DECIMAL:
                return 'float';
            case Types::BOOLEAN:
                return static::$shortTypes ? 'bool' : 'boolean';
            case Types::OBJECT:
                return 'object';
            case Types::ARRAY:
            case Types::SIMPLE_ARRAY:
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

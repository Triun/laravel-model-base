<?php

namespace Triun\ModelBase;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

use Triun\ModelBase\Modifiers;

/**
 * Class ModelBaseConfig
 *
 * @package Triun\ModelBase
 */
class ModelBaseConfig
{
    /**
     * Config file.
     */
    const CONFIG_FILE = 'model-base';

    /**
     * @var string
     */
    const WILDCARD_CONNECTION_STUD = '{{Connection}}';

    /**
     * @var string
     */
    const WILDCARD_DRIVER_STUD = '{{Driver}}';

    /**
     * @var string[]
     */
    protected $modifiers = [

        // Connection
        Modifiers\ConnectionModifier::class,

        // Table
        Modifiers\TableModifier::class,

        // Timestamps
        Modifiers\TimestampsModifier::class,

        // Dates
        Modifiers\DatesModifier::class,

        // Soft Deletes
        Modifiers\SoftDeletesModifier::class,

        // Attributes
        Modifiers\AttributesModifier::class,

        // CamelToSnake Attributes
        Modifiers\CamelToSnakeModifier::class,

        // For custom aliases
        Modifiers\ColumnAliasModifier::class,

        // PhpDoc tags
        Modifiers\PhpDocModifier::class,

        // PhpDoc tags
        Modifiers\AuthModifier::class,

        // Custom
        Modifiers\CustomModelOptionsModifier::class,

        // Relations
        // Input transformations
        // ValueObjects
    ];

    /**
     * Util configuration.
     * This config will have the following priority:
     * - config parameters in constructor.
     * - laravel config (/config/model-base.php).
     * - default config (../config/model-base.php).
     *
     * @var array
     */
    protected $items = [];

    /**
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * ModelBaseConfig constructor.
     *
     * @param \Illuminate\Database\Connection $connection
     */
    public function __construct($connection)
    {
        $this->items = array_merge(
            $this->loadConfig(static::CONFIG_FILE),
            $this->loadConfig(static::CONFIG_FILE . '.drivers.' . $connection->getDriverName()),
            $this->loadConfig(static::CONFIG_FILE . '.connections.' . $connection->getName())
            // $this->loadConfig(static::CONFIG_FILE.'.tables.'.$tableName),
            // $this->loadConfig(static::CONFIG_FILE.'.connections.'.$connection->getName().'.tables.'.$tableName),
        );

        $this->connection = $connection;
    }

    /**
     * @param $name
     *
     * @return array|mixed
     */
    protected function loadConfig($name)
    {
        return Config::has($name) ? Config::get($name) : [];
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->items, $key);
    }

    /**
     * Get the specified configuration value.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param array|string $key
     * @param mixed        $value
     *
     * @return void
     */
    /*public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $innerKey => $innerValue) {
                Arr::set($this->items, $innerKey, $innerValue);
            }
        } else {
            Arr::set($this->items, $key, $value);
        }
    }*/

    /**
     * fnmatch separated by |
     * http://php.net/fnmatch
     * '*gr[ae]y' is gray and grey
     * 'gray|grey' is also gray and grey
     * '*At|*_at finish in 'At' or '_at'
     *
     * @param string|string[] $rules
     * @param string          $value
     * @param bool            $case_sensitive
     *
     * @return bool
     */
    public function match($rules, $value, $case_sensitive = false)
    {
        //return fnmatch($rules, $value);

        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        foreach ($rules as $rule) {
            $rule = str_replace(' ', '', $rule);

            if (!$case_sensitive) {
                $rule  = strtolower($rule);
                $value = strtolower($value);
            }

            if (fnmatch($rule, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the combination of mandatory plus custom modifiers.
     *
     * The custom modifiers are defined in the config file.
     *
     * @return string[]
     */
    public function modifiers()
    {
        return array_merge($this->modifiers, $this->get('modifiers'));
    }

    /**
     * Generate the class name from the table name and the config data given.
     *
     * @param string $tableName
     *
     * @return string
     */
    public function getBaseClassName($tableName)
    {
        return $this->getClassName(
            $tableName,
            $this->get('namespace'),
            $this->get('prefix'),
            $this->get('suffix'),
            array_merge($this->get('table.renames', []), $this->get('renames', [])),
            $this->get('table.prefixes')
        );
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getModelClassName($tableName)
    {
        return $this->getClassName(
            $tableName,
            $this->get('model.namespace'),
            $this->get('model.prefix'),
            $this->get('model.suffix'),
            array_merge($this->get('table.renames', []), $this->get('renames', [])),
            $this->get('table.prefixes')
        );
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public function getAddOnClassName($className)
    {
        return $this->getClassName(
            class_basename($className),
            $this->get('addons.namespace'),
            $this->get('addons.prefix'),
            $this->get('addons.suffix'),
            array_merge($this->get('addons.table.renames', []), $this->get('addons.renames', [])),
            $this->get('addons.table.prefixes', [])
        );
    }

    /**
     * @param string   $tableName
     * @param string   $namespace
     * @param string   $prefix
     * @param string   $suffix
     * @param string[] $tableRenames
     * @param string[] $tablePrefixes
     *
     * @return string
     */
    protected function getClassName(
        string $tableName,
        string $namespace,
        string $prefix,
        string $suffix,
        array $tableRenames,
        array $tablePrefixes
    ) {
        $name = Str::studly($this->renameTableName($tableName, $tableRenames, $tablePrefixes));

        return str_replace([
            static::WILDCARD_CONNECTION_STUD,
            static::WILDCARD_DRIVER_STUD,
        ], [
            Str::studly($this->connection->getName()),
            Str::studly($this->connection->getDriverName()),
        ], $namespace . '\\' . $prefix . $name . $suffix);
    }

    /**
     * @param string $tableName
     * @param array  $tableRenames
     * @param array  $tablePrefixes
     *
     * @return string
     */
    protected function renameTableName(string $tableName, array $tableRenames, array $tablePrefixes)
    {
        if (is_array($tableRenames) && isset($tableRenames[$tableName])) {
            return trim($tableRenames[$tableName]);
        }

        foreach ($tablePrefixes as $prefix) {
            if (Str::startsWith($tableName, $prefix)) {
                return Str::singular(Str::replaceFirst($prefix, '', $tableName));
            }
        }

        return Str::singular($tableName);
    }
}

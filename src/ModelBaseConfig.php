<?php

namespace Triun\ModelBase;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

use Triun\ModelBase\Modifiers\AuthModifier;
use Triun\ModelBase\Modifiers\ConnectionModifier;
use Triun\ModelBase\Modifiers\TableModifier;
use Triun\ModelBase\Modifiers\TimestampsModifier;
use Triun\ModelBase\Modifiers\DatesModifier;
use Triun\ModelBase\Modifiers\SoftDeletesModifier;
use Triun\ModelBase\Modifiers\AttributesModifier;
use Triun\ModelBase\Modifiers\CamelToSnakeModifier;
use Triun\ModelBase\Modifiers\PhpDocModifier;

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
     * @var string[]
     */
    protected $modifiers = [

        // Connection
        ConnectionModifier::class,

        // Table
        TableModifier::class,

        // Timestamps
        TimestampsModifier::class,

        // Dates
        DatesModifier::class,

        // Soft Deletes
        SoftDeletesModifier::class,

        // Attributes
        AttributesModifier::class,

        // CamelToSnake Attributes
        CamelToSnakeModifier::class,

        // PhpDoc tags
        PhpDocModifier::class,

        // PhpDoc tags
        AuthModifier::class,

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
     * ModelBaseConfig constructor.
     *
     * @param \Illuminate\Database\Connection|string|null $connection
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
     * @param  string $key
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
     * @param  string $key
     * @param  mixed  $default
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
     * @param  array|string $key
     * @param  mixed        $value
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
//        return fnmatch($rules, $value);

        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        foreach ($rules as $rule) {
            $rule = str_replace(' ', '', $rule);

            if (!$case_sensitive) {
                $rule = strtolower($rule);
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
            $this->get('renames')
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
            $this->get('renames')
        );
    }

    /**
     * @param string   $tableName
     * @param string   $namespace
     * @param string   $prefix
     * @param string   $suffix
     * @param string[] $renames
     *
     * @return string
     */
    protected function getClassName($tableName, $namespace, $prefix, $suffix, array $renames)
    {
        $name = is_array($renames) && isset($renames[$tableName]) ?
            trim($renames[$tableName]) :
            str_singular($tableName);
        $name = studly_case($name);

        return $namespace . '\\' . $prefix . $name . $suffix;
    }
}

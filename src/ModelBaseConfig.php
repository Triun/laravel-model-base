<?php


namespace Triun\ModelBase;

use Config;
use Illuminate\Support\Arr;
use Triun\ModelBase\Modifiers\AttributesModifier;
use Triun\ModelBase\Modifiers\CamelToSnakeModifier;
use Triun\ModelBase\Modifiers\ClassBaseModifier;
use Triun\ModelBase\Modifiers\ConnectionModifier;
use Triun\ModelBase\Modifiers\DatesModifier;
use Triun\ModelBase\Modifiers\PhpDocModifier;
use Triun\ModelBase\Modifiers\RulesModifier;
use Triun\ModelBase\Modifiers\TableModifier;
use Triun\ModelBase\Modifiers\TimestampsModifier;
use Triun\ModelBase\Utils\SkeletonUtil;

/**
 * Class ModelBaseConfig
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

        // Attributes
        AttributesModifier::class,

        // Validation Rules
        RulesModifier::class,

        // CamelToSnake Attributes
        CamelToSnakeModifier::class,

        // PhpDoc tags
        PhpDocModifier::class,

        // Soft Deletes
        // Relations
        // Input transformations
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
     * @param array $params
     */
    public function __construct($connection, $params = [])
    {
        $this->items = array_merge(
            $this->loadConfig(static::CONFIG_FILE),
            $this->loadConfig(static::CONFIG_FILE.'.drivers.'.$connection->getDriverName()),
            $this->loadConfig(static::CONFIG_FILE.'.connections.'.$connection->getName()),
//            $this->loadConfig(static::CONFIG_FILE.'.tables.'.$tableName),
//            $this->loadConfig(static::CONFIG_FILE.'.connections.'.$connection->getName().'.tables.'.$tableName),
            $params
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
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->items, $key);
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string  $key
     * @param  mixed   $value
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
     * @param string|string[]   $rules
     * @param string            $value
     * @param bool              $case_sensitive
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

            if ( !$case_sensitive ) {
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
    public function getClassName($tableName)
    {
        $namespace  = $this->get('namespace');
        $prefix     = $this->get('prefix');
        $suffix     = $this->get('suffix');
        $renames    = $this->get('renames');

        $name = is_array($renames) && isset($renames[$tableName])? $renames[$tableName] : studly_case(str_singular($tableName));

        return $namespace.'\\'.$prefix.$name.$suffix;
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getModelClassName($tableName)
    {
        $namespace  = $this->get('model.namespace');
        $prefix     = $this->get('model.prefix');
        $suffix     = $this->get('model.suffix');
        $renames    = $this->get('renames');

        $name = is_array($renames) && isset($renames[$tableName])? $renames[$tableName] : studly_case(str_singular($tableName));

        return $namespace.'\\'.$prefix.$name.$suffix;
    }
}
